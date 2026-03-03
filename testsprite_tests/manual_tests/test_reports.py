"""TC057-TC059 — Reports API"""

import pytest
from conftest import api


# TC057
def test_stock_summary_returns_all_items(admin_headers):
    r = api("get", "/reports/stock-summary", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    assert len(data) >= 16  # at least seeded items
    for item in data:
        assert "id" in item
        assert "code" in item
        assert "name" in item
        assert "total_qty" in item  # may be None or number
        assert "category" in item
        assert "unit" in item
    # at least one item should have total_qty > 0
    positive = [i for i in data if i["total_qty"] and float(i["total_qty"]) > 0]
    assert len(positive) > 0


# TC058
def test_low_stock_returns_items_below_minimum(admin_headers):
    r = api("get", "/reports/low-stock", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    for item in data:
        assert "min_stock" in item and item["min_stock"] is not None
        total = float(item["total_qty"] or 0)
        min_s = float(item["min_stock"])
        assert total < min_s, (
            f"Item {item['code']} has total_qty={total} >= min_stock={min_s} but appears in low-stock report"
        )


# TC059
def test_movement_history_type_filter(admin_headers):
    r = api("get", "/reports/movement-history", admin_headers, params={"type": "goods_receipt"})
    assert r.status_code == 200
    body = r.json()
    assert "data" in body
    # all returned records must be goods_receipt
    for record in body["data"]:
        assert record["type"] == "goods_receipt"

    # test date range filter
    r2 = api("get", "/reports/movement-history", admin_headers,
             params={"date_from": "2026-01-01", "date_to": "2026-12-31"})
    assert r2.status_code == 200
    assert "data" in r2.json()
