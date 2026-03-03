"""TC019-TC021 — Warehouse CRUD & validation"""

import pytest
from conftest import api


# TC019
def test_list_warehouses_with_locations(admin_headers):
    r = api("get", "/warehouses", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    codes = [w["code"] for w in data]
    assert "WH-BB" in codes
    assert "WH-PROD" in codes
    # each warehouse must have locations key
    for wh in data:
        assert "locations" in wh
        assert isinstance(wh["locations"], list)


# TC020
def test_create_warehouse(admin_headers):
    r = api("post", "/warehouses", admin_headers, json={
        "code": "WH-TEST", "name": "Gudang Test", "address": "Jl. Test No. 1"
    })
    assert r.status_code == 201
    body = r.json()
    assert "id" in body
    assert body["code"] == "WH-TEST"


# TC021 — duplicate code
def test_create_warehouse_duplicate_code(admin_headers):
    r = api("post", "/warehouses", admin_headers, json={"code": "WH-BB", "name": "Duplicate Warehouse"})
    assert r.status_code == 422
