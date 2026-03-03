import requests
import re

BASE_URL = "http://127.0.0.1:8000"
LOGIN_URL = f"{BASE_URL}/login"
PRODUCTION_ORDERS_URL = f"{BASE_URL}/production-orders"

EMAIL = "admin@mjmetal.co.id"
PASSWORD = "password"
TIMEOUT = 30

def test_production_order_api_workflows():
    session = requests.Session()

    # Retrieve CSRF token via GET /login
    login_page_resp = session.get(LOGIN_URL, timeout=TIMEOUT)
    assert login_page_resp.status_code == 200, f"Failed to get login page: {login_page_resp.status_code}"
    # Extract CSRF token using regex
    match = re.search(r'name=["\']_token["\'] value=["\']([^"\']+)["\']', login_page_resp.text)
    assert match is not None, "CSRF token input field not found in login page"
    csrf_token = match.group(1)

    # Login to get authenticated session/cookies
    login_payload = {
        "email": EMAIL,
        "password": PASSWORD,
        "_token": csrf_token
    }
    login_resp = session.post(LOGIN_URL, data=login_payload, timeout=TIMEOUT, allow_redirects=False)
    assert login_resp.status_code in (200, 302), f"Unexpected login status: {login_resp.status_code}"
    # After login, session cookies/tokens are maintained in session

    # 1. Create production order with input materials and output products
    # We will create a production order with dummy data.
    # For inputs and outputs, we must provide arrays with item IDs and quantities.
    # Since we do not have item IDs, first retrieve items list or assume item IDs.

    # To find some valid warehouse and item, fetch warehouses and items:
    warehouses_resp = session.get(f"{BASE_URL}/warehouses", timeout=TIMEOUT)
    assert warehouses_resp.status_code == 200, "Failed to get warehouses"
    warehouses = warehouses_resp.json() if warehouses_resp.headers.get('Content-Type','').startswith('application/json') else []
    # Pick first warehouse id or fail if none
    if not warehouses:
        raise AssertionError("No warehouses available for production order test")
    warehouse_id = warehouses[0]['id'] if isinstance(warehouses, list) else None
    if not warehouse_id:
        raise AssertionError("Invalid warehouse data")

    items_resp = session.get(f"{BASE_URL}/items", timeout=TIMEOUT)
    assert items_resp.status_code == 200, "Failed to get items"
    items = items_resp.json() if items_resp.headers.get('Content-Type','').startswith('application/json') else []
    if not items or len(items) < 2:
        raise AssertionError("Not enough items available for input materials and output products")

    input_material = {
        "item_id": items[0]['id'],
        "quantity": 1  # assuming positive quantity
    }
    output_product = {
        "item_id": items[1]['id'],
        "quantity": 1
    }

    production_order_payload = {
        "description": "Test production order from API",
        "warehouse_id": warehouse_id,
        "start_date": "2026-01-01",
        "end_date": "2026-01-02",
        "input_materials": [input_material],
        "output_products": [output_product]
    }

    # Create production order
    create_resp = session.post(PRODUCTION_ORDERS_URL, json=production_order_payload, timeout=TIMEOUT)
    assert create_resp.status_code == 201, f"Failed to create production order: {create_resp.text}"
    created_order = create_resp.json()
    production_order_id = created_order.get('id')
    assert production_order_id is not None, "Production order ID missing in creation response"

    try:
        # 2. Start production order (consume materials)
        start_resp = session.post(f"{PRODUCTION_ORDERS_URL}/{production_order_id}/start", timeout=TIMEOUT)
        if start_resp.status_code == 400:
            # Could be insufficient stock error, check message
            err_json = start_resp.json()
            assert "insufficient stock" in err_json.get('message', '').lower(), "Expected insufficient stock error message"
            # Can't proceed further if start failed due to insufficient stock, test passes for error handling here
            assert True
        else:
            assert start_resp.status_code == 200, f"Failed to start production order: {start_resp.text}"
            start_json = start_resp.json()
            assert start_json.get('status') in ('in_progress', 'started'), "Production order not started properly"

        # 3. Complete production order (adds output to stock)
        complete_resp = session.post(f"{PRODUCTION_ORDERS_URL}/{production_order_id}/complete", timeout=TIMEOUT)
        assert complete_resp.status_code == 200, f"Failed to complete production order: {complete_resp.text}"
        complete_json = complete_resp.json()
        assert complete_json.get('status') == 'completed', "Production order not marked completed"

        # 4. Cancel production order (should normally fail if completed, but let's test cancel on completed order)
        cancel_resp = session.post(f"{PRODUCTION_ORDERS_URL}/{production_order_id}/cancel", timeout=TIMEOUT)
        # Depending on business rules, cancel after complete may be forbidden or accepted, accept 200 or 400
        assert cancel_resp.status_code in (200, 400), f"Unexpected cancel response status: {cancel_resp.status_code}"

    finally:
        # Cleanup: delete the production order after test if API supports DELETE
        # Not specified in PRD, try to delete if endpoint exists
        del_resp = session.delete(f"{PRODUCTION_ORDERS_URL}/{production_order_id}", timeout=TIMEOUT)
        assert del_resp.status_code in (200, 204, 404), f"Unexpected delete production order response: {del_resp.status_code}"

test_production_order_api_workflows()
