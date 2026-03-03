import requests
import uuid

BASE_URL = "http://127.0.0.1:8000"
LOGIN_URL = f"{BASE_URL}/login"
CATEGORIES_URL = f"{BASE_URL}/categories"
TIMEOUT = 30

def login(email: str, password: str) -> requests.Session:
    session = requests.Session()
    resp = session.post(
        LOGIN_URL,
        data={"email": email, "password": password},
        headers={"Content-Type": "application/x-www-form-urlencoded"},
        allow_redirects=False,
        timeout=TIMEOUT
    )
    assert resp.status_code == 302, f"Expected 302 redirect, got {resp.status_code}"
    assert 'location' in resp.headers, "No location header in login response"
    assert resp.headers['location'].startswith('/dashboard'), f"Unexpected redirect location: {resp.headers['location']}"
    return session

def test_category_management_api_create_update_delete():
    admin_email = "admin@inventory.com"
    admin_password = "password"

    session = login(admin_email, admin_password)
    headers = {"Accept": "application/json"}

    # 1. Create a new category with a unique name
    unique_name = f"TestCategory-{uuid.uuid4()}"
    create_payload = {
        "name": unique_name,
        "description": "Initial description"
    }

    created_category_id = None

    try:
        # Create Category
        resp_create = session.post(
            CATEGORIES_URL,
            json=create_payload,
            headers=headers,
            timeout=TIMEOUT
        )
        assert resp_create.status_code == 201 or resp_create.status_code == 200, f"Expected 201 or 200, got {resp_create.status_code}"
        created_category = resp_create.json()
        assert "id" in created_category, "No id in create response"
        created_category_id = created_category["id"]
        assert created_category["name"] == unique_name
        assert created_category["description"] == create_payload["description"]

        # 2. Attempt to create another category with the duplicate name, expect validation error
        resp_dup = session.post(
            CATEGORIES_URL,
            json={"name": unique_name, "description": "Another description"},
            headers=headers,
            timeout=TIMEOUT
        )
        assert resp_dup.status_code == 422 or resp_dup.status_code == 400, "Expected client error for duplicate name"
        dup_resp_json = {}
        try:
            dup_resp_json = resp_dup.json()
        except Exception:
            pass
        # Try to detect duplicate name validation error presence
        dup_name_error_found = False
        if isinstance(dup_resp_json, dict):
            for v in dup_resp_json.values():
                if isinstance(v, list):
                    for msg in v:
                        if "duplicate" in msg.lower() or "unique" in msg.lower():
                            dup_name_error_found = True
        assert dup_name_error_found, "Expected duplicate name validation error"

        # 3. Update the created category
        updated_name = f"{unique_name}-Updated"
        updated_description = "Updated description"
        update_payload = {
            "name": updated_name,
            "description": updated_description
        }
        resp_update = session.put(
            f"{CATEGORIES_URL}/{created_category_id}",
            json=update_payload,
            headers=headers,
            timeout=TIMEOUT
        )
        assert resp_update.status_code == 200, f"Expected 200 on update, got {resp_update.status_code}"
        updated_category = resp_update.json()
        assert updated_category["name"] == updated_name
        assert updated_category["description"] == updated_description

        # 4. Confirm that updating to a duplicate name returns error
        # First, create another category to have a name for duplication test
        another_name = f"AnotherCategory-{uuid.uuid4()}"
        resp_create_2 = session.post(
            CATEGORIES_URL,
            json={"name": another_name, "description": "Another category"},
            headers=headers,
            timeout=TIMEOUT
        )
        assert resp_create_2.status_code in (200,201), f"Failed to create second category, status {resp_create_2.status_code}"
        another_category = resp_create_2.json()
        another_category_id = another_category.get("id")

        try:
            # Update first category to the second category name (duplicate)
            resp_update_dup = session.put(
                f"{CATEGORIES_URL}/{created_category_id}",
                json={"name": another_name, "description": "Attempt duplicate update"},
                headers=headers,
                timeout=TIMEOUT
            )
            assert resp_update_dup.status_code in (400,422), "Expected validation error for duplicate name on update"
            update_dup_json = {}
            try:
                update_dup_json = resp_update_dup.json()
            except Exception:
                pass
            dup_update_error_found = False
            if isinstance(update_dup_json, dict):
                for v in update_dup_json.values():
                    if isinstance(v, list):
                        for msg in v:
                            if "duplicate" in msg.lower() or "unique" in msg.lower():
                                dup_update_error_found = True
            assert dup_update_error_found, "Expected duplicate name validation error when updating"
        finally:
            if another_category_id:
                session.delete(f"{CATEGORIES_URL}/{another_category_id}", headers=headers, timeout=TIMEOUT)

        # 5. Delete the created category
        resp_delete = session.delete(
            f"{CATEGORIES_URL}/{created_category_id}",
            headers=headers,
            timeout=TIMEOUT
        )
        assert resp_delete.status_code == 200 or resp_delete.status_code == 204, f"Expected 200 or 204 on delete, got {resp_delete.status_code}"

        # 6. Confirm deletion by GET (should 404 or not found)
        resp_get_deleted = session.get(
            f"{CATEGORIES_URL}/{created_category_id}",
            headers=headers,
            timeout=TIMEOUT
        )
        assert resp_get_deleted.status_code == 404, "Expected 404 for deleted category"

        created_category_id = None  # Already deleted

    finally:
        # Cleanup if category still exists
        if created_category_id:
            try:
                session.delete(f"{CATEGORIES_URL}/{created_category_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass


test_category_management_api_create_update_delete()
