import requests

BASE_URL = "http://localhost:8000"
LOGIN_ENDPOINT = "/api/login"
TIMEOUT = 30


def test_post_login_with_valid_credentials():
    url = f"{BASE_URL}{LOGIN_ENDPOINT}"
    payload = {"email": "admin@mjmetal.co.id", "password": "password"}
    headers = {"Content-Type": "application/json"}

    try:
        response = requests.post(url, json=payload, headers=headers, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request to {url} failed: {e}"

    assert response.status_code == 200, f"Expected HTTP 200 OK, got {response.status_code}"
    try:
        json_data = response.json()
    except ValueError:
        assert False, "Response is not valid JSON"

    assert "token" in json_data, "Response JSON does not contain 'token' key"
    token = json_data["token"]
    assert isinstance(token, str) and len(token) > 0, "Token is empty or not a string"


test_post_login_with_valid_credentials()