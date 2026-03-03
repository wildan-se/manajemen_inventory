import asyncio
from playwright import async_api
from playwright.async_api import expect

async def run_test():
    pw = None
    browser = None
    context = None

    try:
        # Start a Playwright session in asynchronous mode
        pw = await async_api.async_playwright().start()

        # Launch a Chromium browser in headless mode with custom arguments
        browser = await pw.chromium.launch(
            headless=True,
            args=[
                "--window-size=1280,720",         # Set the browser window size
                "--disable-dev-shm-usage",        # Avoid using /dev/shm which can cause issues in containers
                "--ipc=host",                     # Use host-level IPC for better stability
                "--single-process"                # Run the browser in a single process mode
            ],
        )

        # Create a new browser context (like an incognito window)
        context = await browser.new_context()
        context.set_default_timeout(5000)

        # Open a new page in the browser context
        page = await context.new_page()

        # Interact with the page elements to simulate user flow
        # -> Navigate to http://localhost:8000
        await page.goto("http://localhost:8000", wait_until="commit", timeout=10000)
        
        # -> Fill the email and password fields with the provided admin credentials and click the 'Log in' button.
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=/html/body/div/div[2]/form/div/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('admin@mjmetal.co.id')
        
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=/html/body/div/div[2]/form/div[2]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('password')
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=/html/body/div/div[2]/form/div[4]/button').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Navigate to the Suppliers index page (/suppliers) to open the suppliers listing.
        await page.goto("http://localhost:8000/suppliers", wait_until="commit", timeout=10000)
        
        # -> Click the "+ Tambah Supplier" button to open the create supplier form (click element index 799).
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=/html/body/div/div/main/div/a').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Fill the supplier form fields (code and name required) and click the 'Simpan' submit button to create the supplier.
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=/html/body/div/div/main/div/div/form/div/div/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('SUP-TEST-001')
        
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=/html/body/div/div/main/div/div/form/div/div[2]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('Supplier Test Automation')
        
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=/html/body/div/div/main/div/div/form/div/div[3]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('supplier.test@example.com')
        
        # -> Click the 'Simpan' submit button to submit the create supplier form (click element index 1240).
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=/html/body/div/div/main/div/div/form/div[3]/button').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # --> Assertions to verify final state
        frame = context.pages[-1]
        frame = context.pages[-1]
        # Verify we are on the suppliers index page
        assert "/suppliers" in frame.url
        await page.wait_for_timeout(1000)
        # Use the available element xpath to run checks against the page content
        elem = frame.locator('xpath=/html/body/div[1]/aside/nav/a/svg').nth(0)
        # Ensure the referenced element is visible (sanity check)
        assert await elem.is_visible()
        # Verify success message is present in the page text
        success_found = await elem.evaluate("node => document.body.innerText.includes('Supplier created successfully.')")
        assert success_found, "Expected success message 'Supplier created successfully.' not found on page"
        # Verify the newly created supplier code is listed
        supplier_found = await elem.evaluate("node => document.body.innerText.includes('SUP-TEST-001')")
        assert supplier_found, "Expected supplier code 'SUP-TEST-001' not found on page"
        # Verify the newly created supplier name is listed
        name_found = await elem.evaluate("node => document.body.innerText.includes('Supplier Test Automation')")
        assert name_found, "Expected supplier name 'Supplier Test Automation' not found on page"
        # Verify the newly created supplier email is listed
        email_found = await elem.evaluate("node => document.body.innerText.includes('supplier.test@example.com')")
        assert email_found, "Expected supplier email 'supplier.test@example.com' not found on page"
        await asyncio.sleep(5)

    finally:
        if context:
            await context.close()
        if browser:
            await browser.close()
        if pw:
            await pw.stop()

asyncio.run(run_test())
    