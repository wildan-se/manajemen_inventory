"""TC060-TC068 — User Management API & admin role enforcement"""

import pytest
from conftest import api


# TC060
def test_list_users_as_admin(admin_headers):
    r = api("get", "/users", admin_headers)
    assert r.status_code == 200
    data = r.json()
    assert isinstance(data, list)
    assert len(data) >= 5  # seeded users
    emails = [u["email"] for u in data]
    assert "admin@mjmetal.co.id" in emails
    for u in data:
        assert "id" in u and "name" in u and "email" in u and "role" in u
        # password must not be exposed
        assert "password" not in u


# TC061
def test_create_user_as_admin(admin_headers):
    r = api("post", "/users", admin_headers, json={
        "name": "Test User API",
        "email": "testuser@mjmetal.co.id",
        "password": "password123",
        "role": "warehouse_operator"
    })
    assert r.status_code == 201
    body = r.json()
    assert body["name"] == "Test User API"
    assert body["role"] == "warehouse_operator"
    assert "password" not in body


# TC062
def test_update_user_as_admin(admin_headers):
    # create user to update
    create = api("post", "/users", admin_headers, json={
        "name": "Update Test",
        "email": "updatetest@mjmetal.co.id",
        "password": "password123",
        "role": "warehouse_operator"
    })
    assert create.status_code == 201
    uid = create.json()["id"]

    r = api("put", f"/users/{uid}", admin_headers, json={
        "name": "Updated Name",
        "email": "updatetest@mjmetal.co.id",
        "role": "supervisor"
    })
    assert r.status_code == 200
    assert r.json()["name"] == "Updated Name"
    assert r.json()["role"] == "supervisor"


# TC063
def test_delete_user_as_admin(admin_headers):
    create = api("post", "/users", admin_headers, json={
        "name": "To Delete",
        "email": "todelete@mjmetal.co.id",
        "password": "password123",
        "role": "warehouse_operator"
    })
    assert create.status_code == 201
    uid = create.json()["id"]

    r = api("delete", f"/users/{uid}", admin_headers)
    assert r.status_code == 200
    assert r.json()["message"] == "Deleted"


# TC064 — cannot delete own account
def test_delete_own_account_returns_422(admin_headers):
    me = api("get", "/me", admin_headers).json()
    own_id = me["id"]

    r = api("delete", f"/users/{own_id}", admin_headers)
    assert r.status_code == 422
    body = r.json()
    msg = body.get("message", "")
    assert "yourself" in msg.lower() or "self" in msg.lower() or "yourself" in str(body).lower()


# TC065 — non-admin GET /users returns 403
def test_list_users_as_nonadmin_returns_403(nonadmin_headers):
    r = api("get", "/users", nonadmin_headers)
    assert r.status_code == 403


# TC066 — non-admin POST /users returns 403
def test_create_user_as_warehouse_op_returns_403(warehouse_op_headers):
    r = api("post", "/users", warehouse_op_headers, json={
        "name": "Unauthorized",
        "email": "unauth@test.com",
        "password": "password123",
        "role": "warehouse_operator"
    })
    assert r.status_code == 403


# TC067 — duplicate email
def test_create_user_duplicate_email(admin_headers):
    r = api("post", "/users", admin_headers, json={
        "name": "Dup Email",
        "email": "admin@mjmetal.co.id",
        "password": "password123",
        "role": "warehouse_operator"
    })
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "email" in errors


# TC068 — invalid role value
def test_create_user_invalid_role(admin_headers):
    r = api("post", "/users", admin_headers, json={
        "name": "Bad Role",
        "email": "badrole@mjmetal.co.id",
        "password": "password123",
        "role": "superadmin"
    })
    assert r.status_code == 422
    errors = r.json().get("errors", {})
    assert "role" in errors
