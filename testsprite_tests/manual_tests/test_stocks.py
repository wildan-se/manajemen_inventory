"""TC029-TC035 — Stocks & Stock Movements"""

import pytest
from conftest import api


# TC029
def test_list_stocks(admin_headers):
    r = api("get", "/stocks", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    assert len(data) > 0
    for record in data:
        assert "item_id" in record
        assert "warehouse_id" in record
        assert "quantity" in record
        assert "item" in record
        assert "warehouse" in record


# TC030
def test_list_stock_movements(admin_headers):
    r = api("get", "/stock-movements", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert "data" in body
    assert isinstance(body["data"], list)
    # seeded: 34 movements
    assert body["total"] >= 34


# TC031 — goods_receipt increases stock
def test_goods_receipt_increases_stock(admin_headers):
    # record baseline
    before_r = api("get", "/stocks", admin_headers, params={"warehouse_id": 1, "item_id": 1})
    assert before_r.status_code == 200
    before_stocks = before_r.json()
    before_qty = sum(float(s["quantity"]) for s in before_stocks)

    r = api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "item_id": 1,
        "to_warehouse_id": 1, "quantity": 50,
        "reference_number": "GR-TEST-001"
    })
    assert r.status_code == 201
    body = r.json()
    assert body["type"] == "goods_receipt"

    after_r = api("get", "/stocks", admin_headers, params={"warehouse_id": 1, "item_id": 1})
    after_qty = sum(float(s["quantity"]) for s in after_r.json())
    assert after_qty == pytest.approx(before_qty + 50, abs=0.01)


# TC032 — material_issue decreases stock
def test_material_issue_decreases_stock(admin_headers):
    # ensure sufficient stock first
    api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "item_id": 1, "to_warehouse_id": 1, "quantity": 200
    })

    before_r = api("get", "/stocks", admin_headers, params={"warehouse_id": 1, "item_id": 1})
    before_qty = sum(float(s["quantity"]) for s in before_r.json())

    r = api("post", "/stock-movements", admin_headers, json={
        "type": "material_issue", "item_id": 1,
        "from_warehouse_id": 1, "quantity": 20,
        "reference_number": "MI-TEST-001"
    })
    assert r.status_code == 201

    after_r = api("get", "/stocks", admin_headers, params={"warehouse_id": 1, "item_id": 1})
    after_qty = sum(float(s["quantity"]) for s in after_r.json())
    assert after_qty == pytest.approx(before_qty - 20, abs=0.01)


# TC033 — stock quantity correctness: receipt → issue sequence
def test_stock_quantity_correctness_after_sequence(admin_headers):
    # Ensure item 2 (BB-002) in warehouse 1
    before_r = api("get", "/stocks", admin_headers, params={"warehouse_id": 1, "item_id": 2})
    before_qty = sum(float(s["quantity"]) for s in before_r.json())

    api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "item_id": 2, "to_warehouse_id": 1, "quantity": 200
    })
    api("post", "/stock-movements", admin_headers, json={
        "type": "material_issue", "item_id": 2, "from_warehouse_id": 1, "quantity": 30
    })

    after_r = api("get", "/stocks", admin_headers, params={"warehouse_id": 1, "item_id": 2})
    after_qty = sum(float(s["quantity"]) for s in after_r.json())
    assert after_qty == pytest.approx(before_qty + 200 - 30, abs=0.01)

    # also verify stock-summary reflects this
    summary = api("get", "/reports/stock-summary", admin_headers).json()
    item2 = next((i for i in summary if i["code"] == "BB-002"), None)
    assert item2 is not None
    assert float(item2["total_qty"] or 0) >= after_qty - 1.0


# TC034 — invalid movement type
def test_stock_movement_invalid_type(admin_headers):
    r = api("post", "/stock-movements", admin_headers, json={
        "type": "invalid_type", "item_id": 1, "to_warehouse_id": 1, "quantity": 10
    })
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "type" in errors


# TC035 — missing item_id
def test_stock_movement_missing_item_id(admin_headers):
    r = api("post", "/stock-movements", admin_headers, json={
        "type": "goods_receipt", "to_warehouse_id": 1, "quantity": 10
    })
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "item_id" in errors
