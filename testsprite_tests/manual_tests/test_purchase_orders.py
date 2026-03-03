"""TC036-TC043 — Purchase Order lifecycle & validation"""

import pytest
from conftest import api


def _create_draft_po(admin_headers, po_number, item_id=1, quantity=10, unit_price=5000):
    r = api("post", "/purchase-orders", admin_headers, json={
        "po_number": po_number,
        "supplier_id": 1,
        "warehouse_id": 1,
        "expected_date": "2026-03-30",
        "items": [{"item_id": item_id, "quantity": quantity, "unit_price": unit_price}]
    })
    return r


# TC036
def test_create_purchase_order_in_draft(admin_headers):
    r = _create_draft_po(admin_headers, "PO-TEST-001")
    assert r.status_code == 201
    body = r.json()
    assert body["po_number"] == "PO-TEST-001"
    assert body["status"] == "draft"
    assert "supplier" in body
    assert "items" in body
    assert len(body["items"]) == 1


# TC037
def test_list_purchase_orders(admin_headers):
    r = api("get", "/purchase-orders", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert "data" in body
    assert isinstance(body["data"], list)
    for po in body["data"]:
        assert "id" in po and "po_number" in po and "status" in po


# TC038
def test_get_purchase_order_detail(admin_headers):
    r = api("get", "/purchase-orders/1", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert body["id"] == 1
    assert "supplier" in body
    assert "warehouse" in body
    assert "items" in body
    assert all("item" in i for i in body["items"])


# TC039 — PO lifecycle: draft → approved
def test_po_lifecycle_approve(admin_headers):
    create = _create_draft_po(admin_headers, "PO-APPROVE-TEST")
    assert create.status_code == 201
    po_id = create.json()["id"]

    r = api("post", f"/purchase-orders/{po_id}/approve", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "approved"


# TC040 — PO lifecycle: draft → approved → received, stock increases
def test_po_lifecycle_receive_increases_stock(admin_headers):
    create = _create_draft_po(admin_headers, "PO-RECV-TEST", item_id=3, quantity=100, unit_price=50000)
    assert create.status_code == 201
    po_id = create.json()["id"]

    # approve
    approve = api("post", f"/purchase-orders/{po_id}/approve", admin_headers)
    assert approve.status_code == 200

    # get PO item id
    detail = api("get", f"/purchase-orders/{po_id}", admin_headers).json()
    poi_id = detail["items"][0]["id"]

    # record stock before
    before_stocks = api("get", "/stocks", admin_headers, params={"item_id": 3, "warehouse_id": 1}).json()
    before_qty = sum(float(s["quantity"]) for s in before_stocks)

    # receive
    receive = api("post", f"/purchase-orders/{po_id}/receive", admin_headers, json={
        "items": [{"purchase_order_item_id": poi_id, "received_quantity": 100}]
    })
    assert receive.status_code == 200

    # verify stock increased
    after_stocks = api("get", "/stocks", admin_headers, params={"item_id": 3, "warehouse_id": 1}).json()
    after_qty = sum(float(s["quantity"]) for s in after_stocks)
    assert after_qty >= before_qty + 100 - 0.01


# TC041 — cancel PO
def test_po_lifecycle_cancel(admin_headers):
    create = _create_draft_po(admin_headers, "PO-CANCEL-TEST")
    assert create.status_code == 201
    po_id = create.json()["id"]

    r = api("post", f"/purchase-orders/{po_id}/cancel", admin_headers)
    assert r.status_code == 200
    assert r.json()["status"] == "cancelled"


# TC042 — double approve returns error
def test_po_double_approve_returns_error(admin_headers):
    create = _create_draft_po(admin_headers, "PO-DBLAPP-TEST")
    assert create.status_code == 201
    po_id = create.json()["id"]

    first = api("post", f"/purchase-orders/{po_id}/approve", admin_headers)
    assert first.status_code == 200

    second = api("post", f"/purchase-orders/{po_id}/approve", admin_headers)
    assert second.status_code >= 400  # 400, 422, or 500
    assert second.json().get("message") or second.json().get("errors")


# TC043 — missing required fields
def test_create_po_missing_fields(admin_headers):
    r = api("post", "/purchase-orders", admin_headers, json={})
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    for field in ("po_number", "supplier_id", "warehouse_id", "expected_date", "items"):
        assert field in errors, f"Expected validation error on '{field}'"
