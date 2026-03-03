import requests
import re

BASE_URL = "http://127.0.0.1:8000"
LOGIN_URL = f"{BASE_URL}/login"
ITEMS_URL = f"{BASE_URL}/items"
HEADERS = {"Content-Type": "application/json"}
TIMEOUT = 30

def get_csrf_token(session):
    # Fetch the login page to obtain CSRF token from meta tag or cookie
    resp = session.get(LOGIN_URL, timeout=TIMEOUT)
    resp.raise_for_status()
    # Try to extract csrf token from HTML meta tag
    m = re.search(r'<meta name="csrf-token" content="([^"]+)">', resp.text)
    if m:
        return m.group(1)
    # Otherwise, fallback to cookie 'XSRF-TOKEN'
    return session.cookies.get('XSRF-TOKEN')

def test_item_management_api_create_update_delete():
    session = requests.Session()
    try:
        # First get CSRF token
        csrf_token = get_csrf_token(session)
        assert csrf_token, "CSRF token not found"

        login_payload = {
            "email": "admin@inventory.com",
            "password": "password"
        }

        login_headers = {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf_token
        }

        login_resp = session.post(LOGIN_URL, json=login_payload, headers=login_headers, timeout=TIMEOUT)
        assert login_resp.status_code == 200, f"Login failed: {login_resp.status_code} {login_resp.text}"

        categories_resp = session.get(f"{BASE_URL}/categories", timeout=TIMEOUT)
        assert categories_resp.status_code == 200, "Failed to fetch categories"
        categories_data = categories_resp.json()
        assert isinstance(categories_data, list) and len(categories_data) > 0, "No categories available"
        category_id = categories_data[0].get("id")
        assert category_id is not None, "Category id not found"

        units_resp = session.get(f"{BASE_URL}/units", timeout=TIMEOUT)
        assert units_resp.status_code == 200, "Failed to fetch units"
        units_data = units_resp.json()
        assert isinstance(units_data, list) and len(units_data) > 0, "No units available"
        unit_id = units_data[0].get("id")
        assert unit_id is not None, "Unit id not found"

        import random
        import string

        def random_string(n=6):
            return ''.join(random.choices(string.ascii_uppercase + string.digits, k=n))

        item_code = "TC003-" + random_string(5)
        item_name = "Test Item " + random_string(6)
        create_payload = {
            "code": item_code,
            "name": item_name,
            "category_id": category_id,
            "unit_id": unit_id,
            "min_stock": 5,
            "max_stock": 100,
            "status": "active"
        }

        create_headers = {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf_token
        }

        create_resp = session.post(ITEMS_URL, json=create_payload, headers=create_headers, timeout=TIMEOUT)
        assert create_resp.status_code in (200, 201), f"Failed to create item: {create_resp.status_code} {create_resp.text}"
        created_item = create_resp.json()
        item_id = created_item.get("id")
        assert item_id is not None, "Created item has no id"

        try:
            update_payload = {
                "name": item_name + " Updated",
                "max_stock": 150
            }
            update_headers = {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf_token
            }
            update_resp = session.put(f"{ITEMS_URL}/{item_id}", json=update_payload, headers=update_headers, timeout=TIMEOUT)
            assert update_resp.status_code == 200, f"Failed to update item: {update_resp.status_code} {update_resp.text}"
            updated_item = update_resp.json()
            assert updated_item.get("name") == update_payload["name"], "Item name not updated"
            assert updated_item.get("max_stock") == update_payload["max_stock"], "Item max_stock not updated"

            search_resp = session.get(f"{ITEMS_URL}?search={item_code}", timeout=TIMEOUT)
            assert search_resp.status_code == 200, "Search request failed"
            search_results = search_resp.json()
            assert any(i.get("id") == item_id for i in search_results), "Updated item not found in search results"

            filter_resp = session.get(f"{ITEMS_URL}?category_id={category_id}&status=active", timeout=TIMEOUT)
            assert filter_resp.status_code == 200, "Filter request failed"
            filter_results = filter_resp.json()
            assert isinstance(filter_results, list), "Filtered items result not list"
            assert any(i.get("id") == item_id for i in filter_results), "Filtered item not found"

        finally:
            delete_headers = {
                "X-CSRF-TOKEN": csrf_token
            }
            delete_resp = session.delete(f"{ITEMS_URL}/{item_id}", headers=delete_headers, timeout=TIMEOUT)
            assert delete_resp.status_code in (200, 204), f"Failed to delete item id {item_id}: {delete_resp.status_code} {delete_resp.text}"

            get_deleted_resp = session.get(f"{ITEMS_URL}/{item_id}", timeout=TIMEOUT)
            assert get_deleted_resp.status_code == 404 or (get_deleted_resp.status_code == 200 and not get_deleted_resp.json()), \
                "Deleted item still accessible or not 404"

    finally:
        session.close()

test_item_management_api_create_update_delete()
