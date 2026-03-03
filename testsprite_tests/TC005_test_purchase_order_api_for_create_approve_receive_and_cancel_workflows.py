import requests

BASE_URL = "http://127.0.0.1:8000"
LOGIN_ENDPOINT = "/login"
PO_ENDPOINT = "/purchase-orders"
TIMEOUT = 30

def test_purchase_order_workflows():
    session = requests.Session()
    # Login
    login_payload = {
        "email": "admin@inventory.com",
        "password": "password"
    }
    headers = {
        "Accept": "application/json"
    }
    try:
        login_resp = session.post(
            BASE_URL + LOGIN_ENDPOINT,
            data=login_payload,
            headers=headers,
            timeout=TIMEOUT,
            allow_redirects=False
        )
        # Expect redirect (302) on successful login, or json with token/session cookie
        assert login_resp.status_code in (200, 302), f"Login failed with status {login_resp.status_code}"

        # After login, the session cookie should be set automatically by requests.Session()

        # Step 1: Create a new purchase order with dynamic line items
        # For creating PO, we need supplier_id and line items (simulate minimal data)
        # We will fetch a supplier to use for PO line items or use a mock supplier_id (e.g. 1)
        # Because suppliers are not specified, we assume supplier_id=1 exists.
        po_create_payload = {
            "supplier_id": 1,
            "order_date": "2026-02-20",
            "expected_date": "2026-03-01",
            "line_items": [
                {
                    "item_id": 1,
                    "quantity": 10,
                    "unit_price": 100.0
                },
                {
                    "item_id": 2,
                    "quantity": 5,
                    "unit_price": 250.0
                }
            ]
        }

        po_create_headers = headers.copy()
        po_create_headers["Content-Type"] = "application/json"

        po_create_resp = session.post(
            BASE_URL + PO_ENDPOINT,
            json=po_create_payload,
            headers=po_create_headers,
            timeout=TIMEOUT
        )
        assert po_create_resp.status_code == 201, f"PO creation failed: {po_create_resp.text}"
        po_data = po_create_resp.json()
        po_id = po_data.get("id")
        assert po_id is not None, "PO ID missing from creation response"

        try:
            # Step 2: Approve the created purchase order
            approve_resp = session.post(
                f"{BASE_URL}{PO_ENDPOINT}/{po_id}/approve",
                headers=headers,
                timeout=TIMEOUT
            )
            assert approve_resp.status_code == 200, f"PO approve failed: {approve_resp.text}"
            approve_data = approve_resp.json()
            assert approve_data.get("status") == "approved", "PO status not updated to approved"

            # Step 3: Receive goods per PO line item
            # Simulate receiving full quantities for each line item
            # We must get the PO details to know line item IDs or quantities
            po_detail_resp = session.get(
                f"{BASE_URL}{PO_ENDPOINT}/{po_id}",
                headers=headers,
                timeout=TIMEOUT
            )
            assert po_detail_resp.status_code == 200, f"Failed to get PO details: {po_detail_resp.text}"
            po_detail = po_detail_resp.json()
            line_items = po_detail.get("line_items")
            assert isinstance(line_items, list) and len(line_items) > 0, "No PO line items found"

            receive_lines_payload = []
            for line in line_items:
                line_id = line.get("id") or line.get("line_id") or line.get("purchase_order_line_id")
                if not line_id:
                    # Fallback: line id might be line['id']
                    line_id = line.get("id")
                quantity = line.get("quantity")
                if line_id is None or quantity is None:
                    continue
                receive_lines_payload.append({
                    "line_item_id": line_id,
                    "received_quantity": quantity
                })

            receive_payload = {
                "received_lines": receive_lines_payload
            }

            receive_resp = session.post(
                f"{BASE_URL}{PO_ENDPOINT}/{po_id}/receive",
                json=receive_payload,
                headers=po_create_headers,
                timeout=TIMEOUT
            )
            assert receive_resp.status_code == 200, f"PO receive failed: {receive_resp.text}"
            receive_data = receive_resp.json()
            assert receive_data.get("status") == "received" or receive_data.get("status") == "approved", "PO status after receive is incorrect"

            # Step 4: Cancel the purchase order with confirmation
            cancel_resp = session.post(
                f"{BASE_URL}{PO_ENDPOINT}/{po_id}/cancel",
                headers=headers,
                timeout=TIMEOUT
            )
            assert cancel_resp.status_code == 200, f"PO cancel failed: {cancel_resp.text}"
            cancel_data = cancel_resp.json()
            assert cancel_data.get("status") == "cancelled", "PO status not updated to cancelled"

        finally:
            # Cleanup: delete the created PO if API allows deletion (not specified in PRD)
            del_resp = session.delete(
                f"{BASE_URL}{PO_ENDPOINT}/{po_id}",
                headers=headers,
                timeout=TIMEOUT
            )
            # Accept 200, 204 or 404 (if already deleted)
            assert del_resp.status_code in (200, 204, 404), f"Failed to delete PO: {del_resp.text}"

    finally:
        session.close()

test_purchase_order_workflows()
