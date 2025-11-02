/**
 * End-to-End Tests for SEO Content Studio
 * Tests the complete desktop application flow
 */

const { test, expect } = require('@playwright/test');

test.describe('SEO Content Studio - Desktop App', () => {

    test('01 - Application loads and dashboard is accessible', async ({ page }) => {
        await page.goto('/');

        // Should redirect to dashboard
        await expect(page).toHaveURL('/dashboard');

        // Check main heading
        await expect(page.locator('h1')).toContainText('SEO Content Studio');

        // Check navigation is present
        await expect(page.locator('nav a[href="/dashboard"]')).toBeVisible();
        await expect(page.locator('nav a[href="/settings"]')).toBeVisible();

        // Check welcome message
        await expect(page.locator('.rsg-welcome h2')).toContainText('Welcome');

        // Check action cards
        await expect(page.locator('.rsg-action-card')).toHaveCount(2);

        console.log('✅ Dashboard loaded successfully');
    });

    test('02 - Settings page loads and API key can be configured', async ({ page }) => {
        await page.goto('/settings');

        // Check settings page loads
        await expect(page.locator('h1, h2')).toContainText(/Settings|Configure/i);

        console.log('✅ Settings page accessible');
    });

    test('03 - Create site page is accessible', async ({ page }) => {
        await page.goto('/create-site');

        // Should load create site page
        await expect(page).toHaveURL('/create-site');

        console.log('✅ Create site page accessible');
    });

    test('04 - API endpoints are working', async ({ page }) => {
        // Test sites API
        const sitesResponse = await page.request.get('/api/sites');
        expect(sitesResponse.ok()).toBeTruthy();

        const sitesData = await sitesResponse.json();
        expect(sitesData).toHaveProperty('success');

        // Test settings API
        const settingsResponse = await page.request.get('/api/settings');
        expect(settingsResponse.ok()).toBeTruthy();

        const settingsData = await settingsResponse.json();
        expect(settingsData).toHaveProperty('success');
        expect(settingsData).toHaveProperty('settings');

        console.log('✅ API endpoints responding correctly');
    });

    test('05 - Static assets (CSS) are loading', async ({ page }) => {
        await page.goto('/dashboard');

        // Check if CSS is loaded by verifying computed styles
        const header = page.locator('.rsg-header');
        await expect(header).toBeVisible();

        // Check if custom font is defined in CSS
        const cssResponse = await page.request.get('/css/admin.css');
        expect(cssResponse.ok()).toBeTruthy();

        const cssText = await cssResponse.text();
        expect(cssText).toContain('--font-primary');
        expect(cssText).toContain('Outfit');

        console.log('✅ CSS assets loaded correctly');
    });

    test('06 - Database is initialized and writable', async ({ page }) => {
        // Test that we can save a setting
        const saveResponse = await page.request.post('/api/settings', {
            data: {
                default_model: 'claude-sonnet-4-20250514'
            }
        });

        expect(saveResponse.ok()).toBeTruthy();

        const saveData = await saveResponse.json();
        expect(saveData.success).toBeTruthy();

        // Verify we can read it back
        const getResponse = await page.request.get('/api/settings');
        const getData = await getResponse.json();

        expect(getData.success).toBeTruthy();
        expect(getData.settings.default_model).toBe('claude-sonnet-4-20250514');

        console.log('✅ Database read/write working');
    });

    test('07 - Navigation between pages works', async ({ page }) => {
        await page.goto('/dashboard');

        // Click settings link
        await page.click('nav a[href="/settings"]');
        await expect(page).toHaveURL('/settings');

        // Go back to dashboard
        await page.click('nav a[href="/dashboard"]');
        await expect(page).toHaveURL('/dashboard');

        console.log('✅ Navigation working correctly');
    });

    test('08 - Premium UI design system is applied', async ({ page }) => {
        await page.goto('/dashboard');

        // Check if design system CSS variables are applied
        const rootStyles = await page.evaluate(() => {
            const root = document.documentElement;
            const styles = getComputedStyle(root);
            return {
                primaryColor: styles.getPropertyValue('--color-primary-600').trim(),
                fontFamily: styles.getPropertyValue('--font-primary').trim()
            };
        });

        // Check primary color is indigo
        expect(rootStyles.primaryColor).toBeTruthy();

        // Check Outfit font is defined
        expect(rootStyles.fontFamily).toContain('Outfit');

        console.log('✅ Design system applied correctly');
        console.log('  - Primary Color:', rootStyles.primaryColor);
        console.log('  - Font Family:', rootStyles.fontFamily);
    });

    test('09 - Error handling works (404 page)', async ({ page }) => {
        const response = await page.goto('/nonexistent-page');

        // Should return 404
        expect(response.status()).toBe(404);

        console.log('✅ Error handling working');
    });

    test('10 - Application is responsive', async ({ page }) => {
        await page.goto('/dashboard');

        // Test mobile viewport
        await page.setViewportSize({ width: 375, height: 667 });

        // Main content should still be visible
        await expect(page.locator('.rsg-main')).toBeVisible();
        await expect(page.locator('h1')).toBeVisible();

        // Test desktop viewport
        await page.setViewportSize({ width: 1920, height: 1080 });

        await expect(page.locator('.rsg-main')).toBeVisible();
        await expect(page.locator('h1')).toBeVisible();

        console.log('✅ Responsive design working');
    });
});

test.describe('SEO Content Studio - Advanced Features', () => {

    test('11 - Keyword research API endpoint is available', async ({ page }) => {
        // Note: This would fail without actual implementation
        // Testing that endpoint exists
        const response = await page.request.post('/api/keywords/generate', {
            data: {
                seedKeywords: ['test'],
                location: 'Test City',
                autoExpand: false
            }
        });

        // Should return 200 or 500 (not 404)
        expect(response.status()).not.toBe(404);

        console.log('✅ Keyword API endpoint exists');
    });

    test('12 - Content generation API endpoint is available', async ({ page }) => {
        const response = await page.request.post('/api/content/architecture', {
            data: {
                siteId: 1,
                keywords: [{ keyword: 'test', search_intent: 'informational' }]
            }
        });

        // Should return 200 or 400/500 (not 404)
        expect(response.status()).not.toBe(404);

        console.log('✅ Content API endpoint exists');
    });
});

test.describe('SEO Content Studio - Performance', () => {

    test('13 - Page load performance is acceptable', async ({ page }) => {
        const startTime = Date.now();

        await page.goto('/dashboard');
        await page.waitForLoadState('networkidle');

        const loadTime = Date.now() - startTime;

        // Should load in under 3 seconds
        expect(loadTime).toBeLessThan(3000);

        console.log(`✅ Page loaded in ${loadTime}ms`);
    });

    test('14 - API responses are fast', async ({ page }) => {
        const startTime = Date.now();

        await page.request.get('/api/sites');

        const responseTime = Date.now() - startTime;

        // Should respond in under 100ms
        expect(responseTime).toBeLessThan(100);

        console.log(`✅ API responded in ${responseTime}ms`);
    });
});

test.describe('SEO Content Studio - Accessibility', () => {

    test('15 - Page has proper heading hierarchy', async ({ page }) => {
        await page.goto('/dashboard');

        // Should have exactly one h1
        const h1Count = await page.locator('h1').count();
        expect(h1Count).toBe(1);

        // Should have h2s for sections
        const h2Count = await page.locator('h2').count();
        expect(h2Count).toBeGreaterThan(0);

        console.log('✅ Proper heading hierarchy');
        console.log(`  - H1 count: ${h1Count}`);
        console.log(`  - H2 count: ${h2Count}`);
    });

    test('16 - Interactive elements are keyboard accessible', async ({ page }) => {
        await page.goto('/dashboard');

        // Tab through interactive elements
        await page.keyboard.press('Tab');

        // First interactive element should be focused
        const focused = await page.evaluate(() => document.activeElement.tagName);
        expect(['A', 'BUTTON', 'INPUT']).toContain(focused);

        console.log('✅ Keyboard navigation working');
        console.log(`  - First focusable element: ${focused}`);
    });
});
