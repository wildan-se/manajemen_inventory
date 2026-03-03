import requests

BASE_URL = "http://127.0.0.1:8000"
LOGIN_URL = BASE_URL + "/login"
STOCK_MOVEMENTS_URL = BASE_URL + "/stock-movements"
ITEMS_URL = BASE_URL + "/items"
WAREHOUSES_URL = BASE_URL + "/warehouses"

EMAIL = "admin@inventory.com"
PASSWORD = "password"
TIMEOUT = 30


def test_stock_movement_creation_api_various_types():
    session = requests.Session()
    # Step 1: Login
    login_payload = {
        "email": EMAIL,
        "password": PASSWORD,
    }
    login_resp = session.post(LOGIN_URL, data=login_payload, timeout=TIMEOUT)
    assert login_resp.status_code == 200 or login_resp.status_code == 302, "Login failed"
    # Laravel Breeze on success typically redirects (302)
    # We proceed with authenticated session cookies

    headers = {
        "Accept": "application/json",
        "X-Requested-With": "XMLHttpRequest",
    }

    # Step 2: Retrieve some items to use in stock movements
    # GET /items to pick a valid item id; fallback if empty
    items_resp = session.get(ITEMS_URL, headers=headers, timeout=TIMEOUT)
    assert items_resp.status_code == 200, "Failed to get items"
    items_data = items_resp.json()
    assert isinstance(items_data, list) or "data" in items_data, "Invalid items response format"

    # Depending on API structure, adapt:
    if isinstance(items_data, dict) and "data" in items_data:
        items_list = items_data["data"]
    elif isinstance(items_data, list):
        items_list = items_data
    else:
        items_list = []

    assert items_list, "No items available to test stock movements"
    item_id = items_list[0]["id"]

    # Step 3: Retrieve warehouses for source and destination warehouses
    wh_resp = session.get(WAREHOUSES_URL, headers=headers, timeout=TIMEOUT)
    assert wh_resp.status_code == 200, "Failed to get warehouses"
    wh_data = wh_resp.json()
    if isinstance(wh_data, dict) and "data" in wh_data:
        warehouses = wh_data["data"]
    elif isinstance(wh_data, list):
        warehouses = wh_data
    else:
        warehouses = []
    assert len(warehouses) >= 2, "Need at least two warehouses for transfer test"
    wh_src = warehouses[0]["id"]
    wh_dst = warehouses[1]["id"]

    created_movements = []

    try:
        # 3.1: GOODS RECEIPT (type 'goods_receipt')
        gr_payload = {
            "movement_type": "goods_receipt",
            "item_id": item_id,
            "quantity": 10,
            "unit_price": 100,
            "destination_warehouse_id": wh_dst,
            "description": "Test goods receipt",
        }
        gr_resp = session.post(
            STOCK_MOVEMENTS_URL, json=gr_payload, headers=headers, timeout=TIMEOUT
        )
        assert gr_resp.status_code == 201, f"Goods receipt creation failed: {gr_resp.text}"
        gr_created = gr_resp.json()
        assert "id" in gr_created, "Goods receipt response missing id"
        created_movements.append(gr_created["id"])

        # 3.2: MATERIAL ISSUE (type 'material_issue')
        # To test validation for sufficient stock, first try valid qty <= stock
        # Assume stock available at wh_dst from previous goods receipt
        issue_payload = {
            "movement_type": "material_issue",
            "item_id": item_id,
            "quantity": 5,
            "source_warehouse_id": wh_dst,
            "description": "Test material issue",
        }
        issue_resp = session.post(
            STOCK_MOVEMENTS_URL, json=issue_payload, headers=headers, timeout=TIMEOUT
        )
        assert issue_resp.status_code == 201, f"Material issue creation failed: {issue_resp.text}"
        issue_created = issue_resp.json()
        assert "id" in issue_created, "Material issue response missing id"
        created_movements.append(issue_created["id"])

        # 3.3: MATERIAL ISSUE with insufficient stock (quantity more than available)
        # Attempt material issue with quantity 100000 (large) expected to fail validation
        insufficient_issue_payload = {
            "movement_type": "material_issue",
            "item_id": item_id,
            "quantity": 100000,
            "source_warehouse_id": wh_dst,
            "description": "Test material issue insufficient stock",
        }
        insufficient_resp = session.post(
            STOCK_MOVEMENTS_URL,
            json=insufficient_issue_payload,
            headers=headers,
            timeout=TIMEOUT,
        )
        assert insufficient_resp.status_code == 422 or insufficient_resp.status_code == 400, "Expected validation error for insufficient stock on material issue"
        err_json = insufficient_resp.json()
        # The error message should mention insufficient stock
        error_msgs = str(err_json).lower()
        assert "stock" in error_msgs or "insufficient" in error_msgs or "saldo" in error_msgs or "ti" in error_msgs, "Error message should indicate insufficient stock for material issue"

        # 3.4: TRANSFER (type 'transfer') valid case
        transfer_payload = {
            "movement_type": "transfer",
            "item_id": item_id,
            "quantity": 2,
            "unit_price": 150,
            "source_warehouse_id": wh_dst,
            "destination_warehouse_id": wh_src,
            "description": "Test transfer",
        }
        transfer_resp = session.post(
            STOCK_MOVEMENTS_URL, json=transfer_payload, headers=headers, timeout=TIMEOUT
        )
        assert transfer_resp.status_code == 201, f"Transfer creation failed: {transfer_resp.text}"
        transfer_created = transfer_resp.json()
        assert "id" in transfer_created, "Transfer response missing id"
        created_movements.append(transfer_created["id"])

        # 3.5: TRANSFER with insufficient stock
        insufficient_transfer_payload = {
            "movement_type": "transfer",
            "item_id": item_id,
            "quantity": 100000,
            "unit_price": 150,
            "source_warehouse_id": wh_dst,
            "destination_warehouse_id": wh_src,
            "description": "Test transfer insufficient stock",
        }
        insufficient_transfer_resp = session.post(
            STOCK_MOVEMENTS_URL,
            json=insufficient_transfer_payload,
            headers=headers,
            timeout=TIMEOUT,
        )
        assert insufficient_transfer_resp.status_code == 422 or insufficient_transfer_resp.status_code == 400, "Expected validation error for insufficient stock on transfer"
        err_transfer_json = insufficient_transfer_resp.json()
        err_msgs_tr = str(err_transfer_json).lower()
        assert (
            "stock" in err_msgs_tr or "insufficient" in err_msgs_tr or "saldo" in err_msgs_tr or "ti" in err_msgs_tr
        ), "Error message should indicate insufficient stock for transfer"

        # 3.6: ADJUSTMENT (type 'adjustment'), positive quantity (increase stock)
        adjustment_payload = {
            "movement_type": "adjustment",
            "item_id": item_id,
            "quantity": 3,
            "unit_price": 120,
            "destination_warehouse_id": wh_src,
            "description": "Test positive adjustment",
        }
        adjustment_resp = session.post(
            STOCK_MOVEMENTS_URL, json=adjustment_payload, headers=headers, timeout=TIMEOUT
        )
        assert adjustment_resp.status_code == 201, f"Adjustment creation failed: {adjustment_resp.text}"
        adjustment_created = adjustment_resp.json()
        assert "id" in adjustment_created, "Adjustment response missing id"
        created_movements.append(adjustment_created["id"])

        # 3.7: ADJUSTMENT negative quantity (decrease stock) - should allow if stock available
        adjustment_negative_payload = {
            "movement_type": "adjustment",
            "item_id": item_id,
            "quantity": -2,
            "unit_price": 0,  # unit_price might not matter for negative adjustment
            "source_warehouse_id": wh_src,
            "description": "Test negative adjustment",
        }
        adjustment_neg_resp = session.post(
            STOCK_MOVEMENTS_URL, json=adjustment_negative_payload, headers=headers, timeout=TIMEOUT
        )
        if adjustment_neg_resp.status_code == 201:
            adj_neg_created = adjustment_neg_resp.json()
            assert "id" in adj_neg_created, "Negative adjustment response missing id"
            created_movements.append(adj_neg_created["id"])
        else:
            # If insufficient stock for negative adjustment, expect validation error with 422 or 400
            assert adjustment_neg_resp.status_code in (422, 400), "Negative adjustment error unexpected status"
            error_msg_neg = str(adjustment_neg_resp.json()).lower()
            assert "stock" in error_msg_neg or "insufficient" in error_msg_neg or "saldo" in error_msg_neg, "Expected insufficient stock error for negative adjustment"

    finally:
        # Cleanup created stock movements
        for mov_id in created_movements:
            del_resp = session.delete(
                f"{STOCK_MOVEMENTS_URL}/{mov_id}",
                headers=headers,
                timeout=TIMEOUT,
            )
            # Allow 2xx or 404 (if already deleted)
            assert del_resp.status_code in (200, 204, 404), f"Failed to delete stock movement {mov_id}"

    session.close()


test_stock_movement_creation_api_various_types()
