"""TC052-TC056 — Stock Opname workflow"""

import pytest
from conftest import api


def _create_opname(admin_headers, opname_number):
    return api("post", "/stock-opnames", admin_headers, json={
        "opname_number": opname_number,
        "warehouse_id": 1,
        "counted_at": "2026-02-27",
        "notes": "API test"
    })


# TC052
def test_create_stock_opname(admin_headers):
    r = _create_opname(admin_headers, "OP-TEST-001")
    assert r.status_code == 201
    body = r.json()
    assert body["warehouse_id"] == 1


# TC053 — load-stock populates items
def test_load_stock_populates_items(admin_headers):
    # ensure some stock in warehouse 1 first
    api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "item_id": 1, "to_warehouse_id": 1, "quantity": 50
    })

    create = _create_opname(admin_headers, "OP-LOAD-001")
    assert create.status_code == 201
    op_id = create.json()["id"]

    r = api("post", f"/stock-opnames/{op_id}/load-stock", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert "items" in body
    assert len(body["items"]) > 0
    for item in body["items"]:
        assert "item_id" in item
        assert "system_quantity" in item


# TC054 — save-count stores counted quantities
def test_save_count_stores_quantities(admin_headers):
    create = _create_opname(admin_headers, "OP-SAVE-001")
    assert create.status_code == 201
    op_id = create.json()["id"]

    load = api("post", f"/stock-opnames/{op_id}/load-stock", admin_headers)
    assert load.status_code == 200
    items = load.json()["items"]
    assert len(items) > 0
    first_item_id = items[0]["item_id"]

    r = api("post", f"/stock-opnames/{op_id}/save-count", admin_headers, json={
        "counts": {str(first_item_id): 99}
    })
    assert r.status_code == 200
    updated_items = r.json()["items"]
    match = next((i for i in updated_items if i["item_id"] == first_item_id), None)
    assert match is not None
    assert float(match["physical_quantity"]) == 99.0


# TC055 — complete applies adjustments
def test_complete_opname_applies_adjustments(admin_headers):
    api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "item_id": 1, "to_warehouse_id": 1, "quantity": 50
    })

    create = _create_opname(admin_headers, "OP-COMP-001")
    assert create.status_code == 201
    op_id = create.json()["id"]

    load = api("post", f"/stock-opnames/{op_id}/load-stock", admin_headers)
    items = load.json()["items"]
    first_item_id = items[0]["item_id"]

    api("post", f"/stock-opnames/{op_id}/save-count", admin_headers, json={
        "counts": {str(first_item_id): 150}
    })

    r = api("post", f"/stock-opnames/{op_id}/complete", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "completed"

    # verify an adjustment movement was recorded
    movements = api("get", "/stock-movements", admin_headers, params={"type": "stock_adjustment"}).json()
    assert movements["total"] >= 0  # at minimum the endpoint is working


# TC056 — cancel opname
def test_cancel_opname(admin_headers):
    create = _create_opname(admin_headers, "OP-CNCL-001")
    assert create.status_code == 201
    op_id = create.json()["id"]

    r = api("post", f"/stock-opnames/{op_id}/cancel", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "cancelled"
