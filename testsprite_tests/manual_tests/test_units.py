"""TC011-TC013 — Unit CRUD & validation"""

import pytest
from conftest import api


# TC011
def test_list_units(admin_headers):
    r = api("get", "/units", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    names = [u["name"] for u in data]
    assert "Kilogram" in names
    assert "Pieces" in names
    # check structure
    assert all("id" in u and "name" in u and "abbreviation" in u for u in data)


# TC012
def test_create_unit(admin_headers):
    r = api("post", "/units", admin_headers, json={"name": "Unit Test", "abbreviation": "ut"})
    assert r.status_code == 201
    body = r.json()
    assert "id" in body
    assert body["name"] == "Unit Test"
    assert body["abbreviation"] == "ut"


# TC013 — duplicate name
def test_create_unit_duplicate_name(admin_headers):
    r = api("post", "/units", admin_headers, json={"name": "Kilogram", "abbreviation": "kg"})
    assert r.status_code == 422
    assert "errors" in r.json() or "message" in r.json()
