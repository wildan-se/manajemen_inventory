"""TC044-TC051 — Production Order (WO) lifecycle & validation"""

import pytest
from conftest import api


def _ensure_stock(admin_headers, item_id, warehouse_id=1, quantity=1000):
    """Pre-load stock so WO start won't fail due to insufficient materials."""
    api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "item_id": item_id,
        "to_warehouse_id": warehouse_id, "quantity": quantity
    })


def _create_wo(admin_headers, wo_number, inputs=None, outputs=None, extra=None):
    payload = {
        "wo_number": wo_number,
        "title": f"Test WO {wo_number}",
        "warehouse_id": 1,
        "planned_start": "2026-03-01",
        "planned_end": "2026-03-10",
    }
    if inputs:
        payload["inputs"] = inputs
    if outputs:
        payload["outputs"] = outputs
    if extra:
        payload.update(extra)
    return api("post", "/production-orders", admin_headers, json=payload)


# TC044
def test_create_production_order_in_draft(admin_headers):
    r = _create_wo(admin_headers, "WO-TEST-001",
                   inputs=[{"item_id": 1, "quantity": 50}, {"item_id": 2, "quantity": 20}],
                   outputs=[{"item_id": 13, "quantity": 10}])
    assert r.status_code == 201
    body = r.json()
    assert body["status"] == "draft"
    assert "inputs" in body and len(body["inputs"]) == 2
    assert "outputs" in body and len(body["outputs"]) == 1


# TC045
def test_list_production_orders(admin_headers):
    r = api("get", "/production-orders", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert "data" in body
    assert isinstance(body["data"], list)
    for wo in body["data"]:
        assert "id" in wo and "wo_number" in wo and "status" in wo


# TC046
def test_get_production_order_detail(admin_headers):
    r = api("get", "/production-orders/1", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert body["id"] == 1
    assert "warehouse" in body
    assert "inputs" in body
    assert "outputs" in body


# TC047 — WO lifecycle: start → in_progress
def test_wo_lifecycle_start(admin_headers):
    _ensure_stock(admin_headers, 1, quantity=500)
    _ensure_stock(admin_headers, 2, quantity=500)

    create = _create_wo(admin_headers, "WO-START-TEST",
                        inputs=[{"item_id": 1, "quantity": 5}, {"item_id": 2, "quantity": 5}],
                        outputs=[{"item_id": 13, "quantity": 1}])
    assert create.status_code == 201
    wo_id = create.json()["id"]

    r = api("post", f"/production-orders/{wo_id}/start", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "in_progress"


# TC048 — WO lifecycle: start → complete
def test_wo_lifecycle_complete(admin_headers):
    _ensure_stock(admin_headers, 3, quantity=1000)

    create = _create_wo(admin_headers, "WO-COMP-TEST",
                        inputs=[{"item_id": 3, "quantity": 10}],
                        outputs=[{"item_id": 14, "quantity": 1}],
                        extra={"planned_end": "2026-03-15"})
    assert create.status_code == 201
    wo_id = create.json()["id"]

    start = api("post", f"/production-orders/{wo_id}/start", admin_headers)
    assert start.status_code == 200

    r = api("post", f"/production-orders/{wo_id}/complete", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "completed"


# TC049 — WO lifecycle: cancel
def test_wo_lifecycle_cancel(admin_headers):
    create = _create_wo(admin_headers, "WO-CNCL-TEST")
    assert create.status_code == 201
    wo_id = create.json()["id"]

    r = api("post", f"/production-orders/{wo_id}/cancel", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "cancelled"


# TC050 — planned_end before planned_start returns 422
def test_wo_invalid_date_range(admin_headers):
    r = _create_wo(admin_headers, "WO-BADDATE",
                   extra={"planned_start": "2026-03-10", "planned_end": "2026-03-01"})
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "planned_end" in errors


# TC051 — start on completed WO returns error
def test_wo_start_after_complete_returns_error(admin_headers):
    _ensure_stock(admin_headers, 4, quantity=500)

    create = _create_wo(admin_headers, "WO-DONE-TEST",
                        inputs=[{"item_id": 4, "quantity": 5}],
                        outputs=[{"item_id": 14, "quantity": 1}],
                        extra={"planned_end": "2026-03-15"})
    assert create.status_code == 201
    wo_id = create.json()["id"]

    api("post", f"/production-orders/{wo_id}/start", admin_headers)
    api("post", f"/production-orders/{wo_id}/complete", admin_headers)

    # try to start a completed WO
    r = api("post", f"/production-orders/{wo_id}/start", admin_headers)
    assert r.status_code >= 400
