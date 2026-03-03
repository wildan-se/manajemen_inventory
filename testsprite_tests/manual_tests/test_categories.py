"""TC005-TC010 — Category CRUD & validation"""

import pytest
from conftest import api


# TC005
def test_list_categories(admin_headers):
    r = api("get", "/categories", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    assert len(data) >= 1
    names = [c["name"] for c in data]
    assert "Bahan Baku" in names


# TC006
def test_create_category(admin_headers):
    r = api("post", "/categories", admin_headers, json={"name": "Test Kategori API", "description": "Dibuat via API test"})
    assert r.status_code == 201
    body = r.json()
    assert "id" in body
    assert body["name"] == "Test Kategori API"


# TC007
def test_update_category(admin_headers):
    # create first
    create = api("post", "/categories", admin_headers, json={"name": "Kategori Update Test"})
    assert create.status_code == 201
    cid = create.json()["id"]

    r = api("put", f"/categories/{cid}", admin_headers, json={"name": "Kategori Updated", "description": "Updated"})
    assert r.status_code == 200
    assert r.json()["name"] == "Kategori Updated"


# TC008
def test_delete_category(admin_headers):
    create = api("post", "/categories", admin_headers, json={"name": "Kategori To Delete"})
    assert create.status_code == 201
    cid = create.json()["id"]

    r = api("delete", f"/categories/{cid}", admin_headers)
    assert r.status_code == 200
    assert r.json()["message"] == "Deleted"


# TC009 — duplicate name
def test_create_category_duplicate_name(admin_headers):
    r = api("post", "/categories", admin_headers, json={"name": "Bahan Baku"})
    assert r.status_code == 422
    body = r.json()
    errors = body.get("errors", body.get("message", ""))
    assert errors  # some validation error present


# TC010 — missing required name
def test_create_category_missing_name(admin_headers):
    r = api("post", "/categories", admin_headers, json={"description": "No name provided"})
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "name" in errors
