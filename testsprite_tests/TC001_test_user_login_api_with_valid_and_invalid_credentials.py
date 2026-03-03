import requests
from bs4 import BeautifulSoup

BASE_URL = "http://127.0.0.1:8000"
TIMEOUT = 30

def get_csrf_token(session, url):
    try:
        resp = session.get(url, timeout=TIMEOUT)
        resp.raise_for_status()
    except requests.RequestException as e:
        assert False, f"Failed to get CSRF token from {url}, exception: {e}"
    # Extract CSRF token from HTML meta tag or input
    soup = BeautifulSoup(resp.text, 'html.parser')
    token_tag = soup.find('input', {'name': '_token'})
    assert token_tag, "CSRF token input not found in login page"
    csrf_token = token_tag.get('value')
    assert csrf_token, "CSRF token value missing"
    return csrf_token

def test_user_login_api_with_valid_and_invalid_credentials():
    session = requests.Session()

    login_url = f"{BASE_URL}/login"
    dashboard_url = f"{BASE_URL}/dashboard"

    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }

    # 1. Test login with valid credentials
    csrf_token = get_csrf_token(session, login_url)

    valid_login_data = {
        "email": "admin@inventory.com",
        "password": "password",
        "_token": csrf_token
    }

    try:
        response = session.post(login_url, data=valid_login_data, headers=headers, allow_redirects=False, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Valid login request failed with exception: {e}"

    # On success, Laravel Breeze redirects to /dashboard
    assert response.status_code in (302, 303), f"Expected redirect on valid login but got status {response.status_code}"
    location = response.headers.get("Location")
    assert location is not None, "Redirect location header missing on valid login"
    assert location.endswith("/dashboard"), f"Expected redirect to /dashboard, got {location}"

    # 2. Test login with invalid credentials
    csrf_token_invalid = get_csrf_token(session, login_url)  # get fresh token

    invalid_login_data = {
        "email": "wrong@example.com",
        "password": "wrongpass",
        "_token": csrf_token_invalid
    }

    try:
        response_invalid = session.post(login_url, data=invalid_login_data, headers=headers, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Invalid login request failed with exception: {e}"

    # Laravel typically returns 200 with validation error messages in body for invalid login
    assert response_invalid.status_code == 200, f"Expected 200 OK on invalid login but got {response_invalid.status_code}"
    content = response_invalid.text.lower()
    error_msg_indicators = [
        "failed",
        "invalid",
        "credentials",
        "error",
        "auth"
    ]
    assert any(indicator in content for indicator in error_msg_indicators), "Expected validation error message on invalid login not found in response"


test_user_login_api_with_valid_and_invalid_credentials()
