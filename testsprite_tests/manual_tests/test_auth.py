"""TC001-TC004 — Authentication & token guard"""

import requests
import pytest
from conftest import BASE_URL, ADMIN_EMAIL, ADMIN_PASSWORD, JSON_HEADERS, api


# TC001
def test_login_valid_credentials():
    r = requests.post(
        f"{BASE_URL}/login",
        json={"email": ADMIN_EMAIL, "password": ADMIN_PASSWORD},
        headers=JSON_HEADERS,
    )
    assert r.status_code == 200
    data = r.json()
    assert "token" in data and data["token"]
    assert "user" in data
    assert data["user"]["email"] == ADMIN_EMAIL


# TC002
def test_login_invalid_credentials():
    r = requests.post(
        f"{BASE_URL}/login",
        json={"email": ADMIN_EMAIL, "password": "wrongpassword"},
        headers=JSON_HEADERS,
    )
    assert r.status_code in (401, 422)
    assert "token" not in r.json()


# TC003
def test_me_with_valid_token(admin_headers):
    r = api("get", "/me", admin_headers)
    assert r.status_code == 200
    assert r.json()["email"] == ADMIN_EMAIL


# TC004 — unauthenticated request must return 401
def test_protected_endpoint_without_token():
    r = requests.get(f"{BASE_URL}/categories", headers=JSON_HEADERS)
    assert r.status_code == 401
