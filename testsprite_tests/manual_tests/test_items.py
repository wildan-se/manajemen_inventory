"""TC022-TC028 — Item CRUD & validation"""

import pytest
from conftest import api


def _get_first_category_id(admin_headers):
    cats = api("get", "/categories", admin_headers).json()
    return cats[0]["id"]


def _get_first_unit_id(admin_headers):
    units = api("get", "/units", admin_headers).json()
    return units[0]["id"]


# TC022
def test_list_items_paginated(admin_headers):
    r = api("get", "/items", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert "data" in body
    assert isinstance(body["data"], list)
    assert len(body["data"]) >= 1
    codes = [i["code"] for i in body["data"]]
    assert "BB-001" in codes
    # check nested objects
    for item in body["data"]:
        assert "category" in item
        assert "unit" in item


# TC023
def test_create_item(admin_headers):
    cat_id = _get_first_category_id(admin_headers)
    unit_id = _get_first_unit_id(admin_headers)
    r = api("post", "/items", admin_headers, json={
        "code": "TEST-001", "name": "Item Test API",
        "category_id": cat_id, "unit_id": unit_id,
        "min_stock": 10, "max_stock": 100
    })
    assert r.status_code == 201
    body = r.json()
    assert body["code"] == "TEST-001"
    assert "category" in body
    assert "unit" in body


# TC024
def test_get_item_by_id_with_stocks(admin_headers):
    r = api("get", "/items/1", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert body["id"] == 1
    assert body["code"] == "BB-001"
    assert "stocks" in body
    assert isinstance(body["stocks"], list)


# TC025
def test_update_item(admin_headers):
    cat_id = _get_first_category_id(admin_headers)
    unit_id = _get_first_unit_id(admin_headers)
    create = api("post", "/items", admin_headers, json={
        "code": "ITEM-UPD", "name": "Item Update Test",
        "category_id": cat_id, "unit_id": unit_id
    })
    assert create.status_code == 201
    iid = create.json()["id"]

    r = api("put", f"/items/{iid}", admin_headers, json={
        "code": "ITEM-UPD", "name": "Item Updated Name",
        "category_id": cat_id, "unit_id": unit_id, "min_stock": 20
    })
    assert r.status_code == 200
    assert r.json()["name"] == "Item Updated Name"


# TC026 — invalid foreign key (category_id does not exist)
def test_create_item_invalid_category_id(admin_headers):
    unit_id = _get_first_unit_id(admin_headers)
    r = api("post", "/items", admin_headers, json={
        "code": "ITEM-BADCAT", "name": "Bad Category Item",
        "category_id": 99999, "unit_id": unit_id
    })
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "category_id" in errors


# TC027 — duplicate code
def test_create_item_duplicate_code(admin_headers):
    cat_id = _get_first_category_id(admin_headers)
    unit_id = _get_first_unit_id(admin_headers)
    r = api("post", "/items", admin_headers, json={
        "code": "BB-001", "name": "Duplicate Item",
        "category_id": cat_id, "unit_id": unit_id
    })
    assert r.status_code == 422


# TC028 — missing required fields
def test_create_item_missing_required_fields(admin_headers):
    r = api("post", "/items", admin_headers, json={})
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    for field in ("code", "name", "category_id", "unit_id"):
        assert field in errors, f"Expected validation error on '{field}'"
