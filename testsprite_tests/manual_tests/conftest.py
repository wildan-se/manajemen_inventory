"""
Shared fixtures for all backend API tests.
Usage:  cd testsprite_tests/manual_tests && pytest -v
Requirements: pip install requests pytest
"""

import pytest
import requests

BASE_URL = "http://127.0.0.1:8000/api"

ADMIN_EMAIL    = "admin@mjmetal.co.id"
ADMIN_PASSWORD = "password"
NONADMIN_EMAIL    = "inventory@mjmetal.co.id"
NONADMIN_PASSWORD = "password"
WAREHOUSE_OP_EMAIL    = "gudang@mjmetal.co.id"
WAREHOUSE_OP_PASSWORD = "password"


def login(email: str, password: str) -> str:
    """Return a Bearer token for the given credentials."""
    r = requests.post(f"{BASE_URL}/login", json={"email": email, "password": password})
    r.raise_for_status()
    return r.json()["token"]


@pytest.fixture(scope="session")
def admin_token():
    return login(ADMIN_EMAIL, ADMIN_PASSWORD)


@pytest.fixture(scope="session")
def nonadmin_token():
    return login(NONADMIN_EMAIL, NONADMIN_PASSWORD)


@pytest.fixture(scope="session")
def warehouse_op_token():
    return login(WAREHOUSE_OP_EMAIL, WAREHOUSE_OP_PASSWORD)


@pytest.fixture(scope="session")
def admin_headers(admin_token):
    return {
        "Authorization": f"Bearer {admin_token}",
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


@pytest.fixture(scope="session")
def nonadmin_headers(nonadmin_token):
    return {
        "Authorization": f"Bearer {nonadmin_token}",
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


@pytest.fixture(scope="session")
def warehouse_op_headers(warehouse_op_token):
    return {
        "Authorization": f"Bearer {warehouse_op_token}",
        "Content-Type": "application/json",
        "Accept": "application/json",
    }


# Default JSON headers without auth — for testing unauthenticated requests
JSON_HEADERS = {"Content-Type": "application/json", "Accept": "application/json"}


def api(method: str, path: str, headers: dict, **kwargs):
    """Convenience wrapper for requests."""
    url = f"{BASE_URL}{path}"
    return getattr(requests, method.lower())(url, headers=headers, **kwargs)
