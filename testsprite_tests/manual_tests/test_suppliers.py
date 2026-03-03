"""TC014-TC018 — Supplier CRUD & validation"""

import pytest
from conftest import api


# TC014
def test_list_suppliers(admin_headers):
    r = api("get", "/suppliers", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    codes = [s["code"] for s in data]
    assert "SUP-001" in codes
    assert all("id" in s and "name" in s and "code" in s for s in data)


# TC015
def test_create_supplier(admin_headers):
    r = api("post", "/suppliers", admin_headers, json={
        "code": "SUP-TEST", "name": "Supplier Test API",
        "email": "test@supplier.com", "phone": "021-999999",
        "address": "Test Address", "contact_person": "Test Person"
    })
    assert r.status_code == 201
    body = r.json()
    assert "id" in body
    assert body["code"] == "SUP-TEST"


# TC016
def test_get_supplier_by_id(admin_headers):
    r = api("get", "/suppliers/1", admin_headers)
    assert r.status_code == 200
    body = r.json()
    assert body["id"] == 1
    assert body["code"] == "SUP-001"


# TC017
def test_update_supplier(admin_headers):
    create = api("post", "/suppliers", admin_headers, json={
        "code": "SUP-UPD", "name": "Supplier Update",
        "email": "upd@test.com", "phone": "021-100",
        "address": "Addr", "contact_person": "Person"
    })
    assert create.status_code == 201
    sid = create.json()["id"]

    r = api("put", f"/suppliers/{sid}", admin_headers, json={
        "code": "SUP-UPD", "name": "Supplier Updated Name",
        "email": "updated@test.com", "phone": "021-111",
        "address": "New Addr", "contact_person": "New Person"
    })
    assert r.status_code == 200
    assert r.json()["name"] == "Supplier Updated Name"


# TC018 — duplicate code
def test_create_supplier_duplicate_code(admin_headers):
    r = api("post", "/suppliers", admin_headers, json={"code": "SUP-001", "name": "Duplicate Supplier"})
    assert r.status_code == 422
