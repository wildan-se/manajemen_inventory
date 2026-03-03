import requests

BASE_URL = "http://localhost:8000"

def test_post_login_with_invalid_credentials():
    # Step 1: Obtain Bearer token with valid admin credentials
    login_url = f"{BASE_URL}/api/login"
    valid_login_payload = {
        "email": "admin@mjmetal.co.id",
        "password": "password"
    }
    try:
        login_resp = requests.post(login_url, json=valid_login_payload, timeout=30)
        login_resp.raise_for_status()
        token = login_resp.json().get("token") or login_resp.json().get("access_token")
        assert token, "Failed to obtain token with valid credentials"
    except (requests.RequestException, AssertionError) as e:
        raise RuntimeError(f"Setup login failed: {e}")

    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json",
        "Accept": "application/json"
    }

    # Step 2: Test login with invalid credentials
    invalid_login_payload = {
        "email": "wrong@example.com",
        "password": "wrongpass"
    }
    try:
        resp = requests.post(login_url, json=invalid_login_payload, headers=headers, timeout=30)
    except requests.RequestException as e:
        raise RuntimeError(f"Request to /api/login with invalid credentials failed: {e}")

    # Step 3: Validate response status code and error message
    # Assuming API returns 401 or 422 for invalid credentials with JSON error message
    assert resp.status_code in (401, 422), f"Expected 401 or 422 status code for invalid credentials, got {resp.status_code}"
    try:
        json_data = resp.json()
    except ValueError:
        raise AssertionError("Response is not JSON")

    # Check typical error message keys for validation errors or login failure
    error_present = False
    error_messages = []
    if "errors" in json_data:
        error_messages = json_data["errors"]
        error_present = bool(error_messages)
    elif "message" in json_data:
        error_messages = [json_data["message"]]
        error_present = True if json_data["message"] else False
    elif "error" in json_data:
        error_messages = [json_data["error"]]
        error_present = True if json_data["error"] else False

    assert error_present, "Expected error message in response JSON for invalid login"


test_post_login_with_invalid_credentials()
