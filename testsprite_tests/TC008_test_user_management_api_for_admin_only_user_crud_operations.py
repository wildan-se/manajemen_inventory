import requests
import uuid

BASE_URL = "http://127.0.0.1:8000"
LOGIN_URL = f"{BASE_URL}/login"
USERS_URL = f"{BASE_URL}/users"
TIMEOUT = 30

def test_user_management_api_admin_only_user_crud_operations():
    session = requests.Session()

    # 1. Login as admin
    login_payload = {
        "email": "admin@inventory.com",
        "password": "password"
    }
    # Send form data (application/x-www-form-urlencoded) instead of JSON
    login_resp = session.post(LOGIN_URL, data=login_payload, timeout=TIMEOUT)
    assert login_resp.status_code == 200 or login_resp.status_code == 302, "Admin login failed"
    # Laravel Breeze redirects after login success, so 302 status is expected

    # Check if authentication cookie/session is set
    assert session.cookies, "No cookies set after login, likely login failed"

    # 2. Create a new user as admin
    random_email = f"testuser_{uuid.uuid4().hex[:8]}@example.com"
    create_payload = {
        "name": "Test User",
        "email": random_email,
        "password": "TestPass123!",
        "role": "user",  # Assume roles: admin, user
        "active": True
    }
    headers = {"Content-Type": "application/json"}
    create_resp = session.post(USERS_URL, json=create_payload, headers=headers, timeout=TIMEOUT)
    assert create_resp.status_code == 201, f"User creation failed: {create_resp.text}"
    created_user = create_resp.json()
    assert "id" in created_user, "Created user response missing id"
    user_id = created_user["id"]

    try:
        # 3. Update the created user role and active status
        update_payload = {
            "name": "Test User Updated",
            "email": random_email,
            "role": "admin",
            "active": False
        }
        update_resp = session.put(f"{USERS_URL}/{user_id}", json=update_payload, headers=headers, timeout=TIMEOUT)
        assert update_resp.status_code == 200, f"User update failed: {update_resp.text}"
        updated_user = update_resp.json()
        assert updated_user.get("role") == "admin", "User role not updated"
        assert updated_user.get("active") is False, "User active status not updated"

        # 4. Attempt to delete own account - should fail
        users_list_resp = session.get(USERS_URL, headers=headers, timeout=TIMEOUT)
        assert users_list_resp.status_code == 200, "Failed to get users list"
        users_list = users_list_resp.json()
        admin_user_id = None
        for user in users_list:
            if user.get("email") == "admin@inventory.com":
                admin_user_id = user.get("id")
                break
        assert admin_user_id is not None, "Admin user id not found"

        delete_own_resp = session.delete(f"{USERS_URL}/{admin_user_id}", headers=headers, timeout=TIMEOUT)
        assert delete_own_resp.status_code == 403 or delete_own_resp.status_code == 400, "Deleting own account should be forbidden"
        try:
            err_json = delete_own_resp.json()
            assert "error" in err_json or "message" in err_json, "Expected error message for deleting own account"
        except Exception:
            pass

        # 5. Attempt user CRUD as non-admin user: login with a non-admin user and check 403 on /users access
        non_admin_email = random_email
        non_admin_password = "TestPass123!"

        session_non_admin = requests.Session()
        login_resp_non_admin = session_non_admin.post(LOGIN_URL, data={"email": non_admin_email, "password": non_admin_password}, timeout=TIMEOUT)
        assert login_resp_non_admin.status_code == 200 or login_resp_non_admin.status_code == 302, "Non-admin login failed"

        users_access_resp = session_non_admin.get(USERS_URL, headers=headers, timeout=TIMEOUT)
        assert users_access_resp.status_code == 403, f"Non-admin access to /users should be forbidden, got {users_access_resp.status_code}"

        # 6. Delete the created user (not own account)
        delete_resp = session.delete(f"{USERS_URL}/{user_id}", headers=headers, timeout=TIMEOUT)
        assert delete_resp.status_code == 204 or delete_resp.status_code == 200, f"User deletion failed: {delete_resp.text}"

    finally:
        check_resp = session.get(f"{USERS_URL}/{user_id}", headers=headers, timeout=TIMEOUT)
        if check_resp.status_code == 200:
            session.delete(f"{USERS_URL}/{user_id}", headers=headers, timeout=TIMEOUT)

test_user_management_api_admin_only_user_crud_operations()
