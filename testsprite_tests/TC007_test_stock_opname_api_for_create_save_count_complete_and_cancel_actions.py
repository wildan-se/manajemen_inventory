import requests
import urllib.parse
BASE_URL = "http://127.0.0.1:8000"
LOGIN_EMAIL = "admin@inventory.com"
LOGIN_PASSWORD = "password"
TIMEOUT = 30

def test_stock_opname_api_create_save_count_complete_and_cancel_actions():
    session = requests.Session()
    try:
        # 1. Login and get csrf token and session cookie
        login_get = session.get(f"{BASE_URL}/login", timeout=TIMEOUT)
        assert login_get.status_code == 200, "Failed to load login page"

        # Extract CSRF token from cookie and URL decode it
        csrf_token = session.cookies.get('XSRF-TOKEN')
        assert csrf_token, "CSRF token missing on login page"
        csrf_token = urllib.parse.unquote(csrf_token)

        headers = {
            "X-XSRF-TOKEN": csrf_token,
            "Referer": f"{BASE_URL}/login",
            "Accept": "application/json",
        }
        login_data = {
            "email": LOGIN_EMAIL,
            "password": LOGIN_PASSWORD,
            "_token": csrf_token
        }
        login_post = session.post(f"{BASE_URL}/login", data=login_data, headers=headers, timeout=TIMEOUT, allow_redirects=False)
        # Laravel Breeze usually redirects on success, check redirect
        assert login_post.status_code in (302, 303), "Login did not redirect, likely failed"
        cookies_dict = session.cookies.get_dict()
        assert any(key.startswith('laravel_session') for key in cookies_dict), "Session cookie not set after login"

        # After login, confirm dashboard access
        dash = session.get(f"{BASE_URL}/dashboard", timeout=TIMEOUT)
        assert dash.status_code == 200, "Dashboard access failed after login"

        # Grab csrf token again post login for subsequent actions
        csrf_token = session.cookies.get('XSRF-TOKEN')
        assert csrf_token, "CSRF token missing after login"
        csrf_token = urllib.parse.unquote(csrf_token)
        headers["X-XSRF-TOKEN"] = csrf_token
        headers["Referer"] = f"{BASE_URL}/stock-opnames"

        # 2. Create stock opname session - We need a warehouse ID, so fetch warehouses first
        wh_resp = session.get(f"{BASE_URL}/warehouses", headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert wh_resp.status_code == 200, "Failed to fetch warehouses"
        warehouses = wh_resp.json()
        assert isinstance(warehouses, list) and len(warehouses) > 0, "No warehouses found to create opname"
        warehouse_id = None
        # Try to pick a warehouse with id (assuming JSON array of dicts with id)
        for wh in warehouses:
            if 'id' in wh:
                warehouse_id = wh['id']
                break
        assert warehouse_id is not None, "No valid warehouse id found"

        # Prepare create opname payload
        from datetime import datetime
        opname_create_data = {
            "warehouse_id": warehouse_id,
            "date": datetime.now().strftime("%Y-%m-%d"),
            "_token": csrf_token
        }

        create_resp = session.post(
            f"{BASE_URL}/stock-opnames",
            data=opname_create_data,
            headers=headers,
            timeout=TIMEOUT
        )
        assert create_resp.status_code == 201 or create_resp.status_code == 200, f"Failed to create stock opname session, status {create_resp.status_code}"
        opname = create_resp.json()
        opname_id = opname.get("id")
        assert opname_id, "Created opname session response missing id"

        try:
            # 3. Get opname details to read existing items and system_qty for counts
            detail_resp = session.get(f"{BASE_URL}/stock-opnames/{opname_id}", headers={"Accept": "application/json"}, timeout=TIMEOUT)
            assert detail_resp.status_code == 200, "Failed to fetch stock opname details"
            detail_json = detail_resp.json()

            # Assume detail_json contains list or dict with items and system_qty fields
            items = detail_json.get("items") or detail_json.get("stock_items") or []
            if not items:
                # if items list empty, we can't proceed save-count, fail test
                raise AssertionError("No stock items found in opname detail to save physical count")

            # Prepare physical counts with same quantities as system_qty to simulate no differences
            counts_payload = []
            for item in items:
                item_id = item.get("id") or item.get("item_id")
                system_qty = item.get("system_qty") or 0
                if not item_id:
                    continue
                counts_payload.append({
                    "item_id": item_id,
                    "physical_count": system_qty
                })
            assert counts_payload, "No valid items to save physical count"

            save_count_data = {
                "counts": counts_payload,
                "_token": csrf_token
            }

            save_resp = session.post(
                f"{BASE_URL}/stock-opnames/{opname_id}/save-count",
                json=save_count_data,
                headers={**headers, "Content-Type": "application/json"},
                timeout=TIMEOUT
            )
            assert save_resp.status_code == 200, f"Failed to save physical count, status {save_resp.status_code}"
            save_resp_json = save_resp.json()
            assert save_resp_json.get("success") is True or save_resp_json.get("message"), "Physical count save response invalid"

            # 4. View differences - get opname detail again and check difference column present for items
            diff_resp = session.get(f"{BASE_URL}/stock-opnames/{opname_id}", headers={"Accept": "application/json"}, timeout=TIMEOUT)
            assert diff_resp.status_code == 200, "Failed to fetch opname detail after save count"
            diff_json = diff_resp.json()
            diff_items = diff_json.get("items") or diff_json.get("stock_items") or []
            assert diff_items, "No stock items found after save count to verify differences"
            # Check difference field presence for at least one item
            has_difference_field = any('difference' in itm for itm in diff_items)
            assert has_difference_field, "No difference field found in opname items after save count"

            # 5. Complete opname - apply adjustments and mark complete
            complete_data = {"_token": csrf_token}
            complete_resp = session.post(
                f"{BASE_URL}/stock-opnames/{opname_id}/complete",
                data=complete_data,
                headers=headers,
                timeout=TIMEOUT
            )
            # 200 or 204 accepted
            assert complete_resp.status_code in (200, 204), f"Failed to complete opname session, status {complete_resp.status_code}"

            # Optionally confirm opname marked complete by fetching detail and checking status
            confirmed_resp = session.get(f"{BASE_URL}/stock-opnames/{opname_id}", headers={"Accept": "application/json"}, timeout=TIMEOUT)
            assert confirmed_resp.status_code == 200, "Failed to fetch opname detail after complete"
            confirmed_json = confirmed_resp.json()
            # Assumption: completed opname has a status field or is_completed boolean
            status = confirmed_json.get("status") or confirmed_json.get("state") or confirmed_json.get("is_completed")
            assert status in ("completed", "done", True, 1), "Opname session not marked completed after complete action"

        finally:
            # 6. Cancel opname session if still possible (may not be allowed after complete)
            # Try cancel only if not completed, else ignore error
            cancel_resp = session.post(
                f"{BASE_URL}/stock-opnames/{opname_id}/cancel",
                data={"_token": csrf_token},
                headers=headers,
                timeout=TIMEOUT
            )
            # Accept 200, 204 or 400 if already completed
            assert cancel_resp.status_code in (200, 204, 400), "Cancel opname request failed with unexpected status"

    finally:
        session.close()

test_stock_opname_api_create_save_count_complete_and_cancel_actions()
