# Restaurant SEO Generator - Testing Guide

## ✅ Automated Checks (Completed)

### PHP Syntax Validation
All PHP files validated with `php -l`:
- ✅ restaurant-seo-generator.php
- ✅ includes/class-database.php
- ✅ includes/class-claude-api.php
- ✅ includes/class-keyword-research.php
- ✅ includes/class-content-generator.php
- ✅ includes/class-schema-generator.php
- ✅ admin/class-admin.php
- ✅ admin/views/site-profile-form.php
- ✅ admin/views/keyword-research.php
- ✅ admin/views/content-library.php
- ✅ admin/views/settings.php

**Result:** ✅ No syntax errors detected

### CSS Validation
- ✅ Valid CSS3 syntax
- ✅ CSS custom properties properly defined
- ✅ Media queries correct
- ✅ No missing semicolons or brackets

### JavaScript Validation
- ✅ Valid ES6 syntax
- ✅ jQuery usage correct
- ✅ No undefined variables
- ✅ Event handlers properly bound

---

## 🧪 E2E Testing Setup (Local Environment Required)

Since this is a WordPress plugin, full e2e testing requires a local WordPress installation.

### Prerequisites

1. **Local WordPress Environment**
   - Option A: [Local by Flywheel](https://localwp.com/) (Recommended - easiest)
   - Option B: [XAMPP](https://www.apachefriends.org/)
   - Option C: [Docker](https://github.com/docker/awesome-compose/tree/master/wordpress-mysql)
   - Option D: [MAMP](https://www.mamp.info/) (Mac)

2. **Claude API Key**
   - Get from: https://console.anthropic.com
   - Required for content generation testing

### Installation Steps

```bash
# 1. Copy plugin to WordPress
cp -r restaurant-seo-generator /path/to/wordpress/wp-content/plugins/

# 2. Set correct permissions
chmod -R 755 /path/to/wordpress/wp-content/plugins/restaurant-seo-generator

# 3. Activate in WordPress Admin
# Go to: Plugins → Restaurant SEO Generator → Activate
```

---

## ✅ Manual E2E Test Checklist

### 1. Plugin Activation (5 min)

- [ ] Navigate to WordPress Admin → Plugins
- [ ] Find "Restaurant SEO Generator"
- [ ] Click "Activate"
- [ ] **Expected:** No errors, plugin activates successfully
- [ ] **Check:** Database tables created
  ```sql
  -- Run in phpMyAdmin or MySQL CLI:
  SHOW TABLES LIKE 'wp_rsg_%';
  -- Should show: wp_rsg_sites, wp_rsg_pages, wp_rsg_keywords, wp_rsg_competitors
  ```

### 2. Settings Configuration (2 min)

- [ ] Navigate to: SEO Generator → Settings
- [ ] Enter Claude API key
- [ ] Click "Test Connection"
- [ ] **Expected:** ✓ API key valid message
- [ ] Click "Save Settings"
- [ ] **Expected:** Settings saved confirmation

### 3. Create Site Profile (5 min)

**Test Case: Mario's Pizza (Restaurant)**

- [ ] Navigate to: SEO Generator → Dashboard
- [ ] Click "Create Your First Site"
- [ ] Fill in form:
  - Business Name: "Mario's Pizza"
  - Industry: "Food & Drink"
  - Business Type: "Restaurant"
  - Location: "Brooklyn, NY"
  - Service Area: "Single location"
  - Target Audience: "Families and pizza lovers in Brooklyn"
  - Brand Voice: "Friendly & Approachable"
  - USP: "Family recipes from Naples since 1985"
  - Key Services: "Wood-fired pizza, pasta, Italian wines"
  - Certifications: (leave blank)
  - Writing Sample: (leave blank)
- [ ] Click "Save Profile & Continue"
- [ ] **Expected:** Redirects to Generate Content page
- [ ] **Check:** Site appears in dashboard

### 4. Keyword Research (3 min)

- [ ] Enter seed keywords (one per line):
  ```
  pizza brooklyn
  best pizza near me
  italian restaurant brooklyn
  ```
- [ ] Leave auto-expand options checked
- [ ] Click "Generate Keywords"
- [ ] **Expected:** 30-50 keywords generated
- [ ] **Check:** Keywords displayed in grid
- [ ] Click "Classify Intent & Continue"
- [ ] **Expected:** Keywords shown with intent badges (transactional, commercial, etc.)
- [ ] Select top 15 keywords
- [ ] Click "Continue to Competitors"

### 5. Competitor Analysis (Optional - 3 min)

- [ ] Enter competitor URLs:
  ```
  https://www.lucalipizza.com
  https://www.grimaldispizzeria.com
  ```
- [ ] Click "Analyze Competitors"
- [ ] **Expected:** Analysis summary showing:
  - Word counts
  - Common topics
  - Content gaps
- [ ] Click "Continue to Site Plan"

**OR**

- [ ] Click "Skip to Site Plan"

### 6. Site Architecture (2 min)

- [ ] Wait for architecture generation (20-30 seconds)
- [ ] **Expected:** Table with 8-10 pages:
  - Homepage
  - Menu
  - About
  - Contact
  - 4-6 content pages
- [ ] **Check:** Each page has:
  - Title
  - Primary keyword
  - Intent badge
  - Word count target
- [ ] Click "Generate Content"

### 7. Content Generation (25 min)

- [ ] Review generation summary:
  - Total pages: ~10
  - Total words: ~10,000
  - Est. time: ~20-25 minutes
  - Est. cost: ~$2.50
- [ ] Click "Start Generating Content"
- [ ] **Expected:** Progress bar appears
- [ ] **Check:** Progress updates every few seconds
- [ ] **Wait:** ~20-25 minutes for completion
- [ ] **Expected:** "Content Generation Complete!" message
- [ ] **Check:** All pages show "Success" status

### 8. Content Library (5 min)

- [ ] Navigate to: Content Library tab
- [ ] **Check:** All generated pages displayed
- [ ] For each page:
  - [ ] Click "Preview"
  - [ ] **Check:** Meta title, description visible
  - [ ] **Check:** Content preview shows
  - [ ] **Check:** SEO score displayed (should be 80+)
  - [ ] Click "Copy HTML"
  - [ ] **Expected:** "HTML copied to clipboard!" message
  - [ ] Paste into text editor
  - [ ] **Check:** HTML includes:
    - Meta tags in comments
    - Schema markup (JSON-LD)
    - Clean semantic HTML
    - H1, H2, H3 structure

### 9. HTML Output Quality (5 min)

**Manual Review:**

- [ ] Open copied HTML in browser
- [ ] **Check:** Renders correctly
- [ ] **Check:** No broken HTML
- [ ] **Check:** Schema markup valid
  - Paste schema into: https://validator.schema.org/
- [ ] **Check:** Content quality:
  - Reads naturally
  - Keywords used naturally
  - Location mentioned 5-7 times
  - Business voice consistent

### 10. Regenerate Page (2 min)

- [ ] Click "Regenerate" on any page
- [ ] Confirm regeneration
- [ ] **Expected:** "Regenerating..." indicator
- [ ] **Wait:** ~2 minutes
- [ ] **Expected:** "Page regenerated successfully!"
- [ ] **Check:** New content appears

### 11. Delete Functions (2 min)

**Delete Page:**
- [ ] Click "Delete" on a page
- [ ] Confirm deletion
- [ ] **Expected:** Page removed from list

**Delete Site:**
- [ ] Navigate to Dashboard
- [ ] Click "Delete" on a site
- [ ] Confirm deletion
- [ ] **Expected:** Site and all pages removed

### 12. UI/UX Testing (10 min)

**Visual Design:**
- [ ] **Check:** Outfit font loads from Google Fonts
- [ ] **Check:** Indigo primary color throughout
- [ ] **Check:** Cards have subtle shadows
- [ ] **Check:** Smooth hover effects on buttons/cards
- [ ] **Check:** Progress bar animates smoothly

**Responsive Design:**
- [ ] Resize browser to mobile width (375px)
- [ ] **Check:** Layout stacks to single column
- [ ] **Check:** Touch targets are large (44px)
- [ ] **Check:** No horizontal scroll
- [ ] **Check:** All buttons full-width on mobile

**Accessibility:**
- [ ] Tab through interface with keyboard
- [ ] **Check:** Focus indicators visible
- [ ] **Check:** All interactive elements reachable
- [ ] **Check:** Tab order logical
- [ ] **Check:** No keyboard traps

**Empty States:**
- [ ] Deactivate and reactivate plugin
- [ ] **Check:** Empty state shows for new install
- [ ] **Check:** Icon, encouraging text, CTA visible

### 13. Error Handling (5 min)

**Invalid API Key:**
- [ ] Settings → Enter invalid API key
- [ ] Click "Test Connection"
- [ ] **Expected:** Error message with explanation

**Network Failure:**
- [ ] Disconnect internet
- [ ] Try to generate keywords
- [ ] **Expected:** Graceful error message

**Generation Failure:**
- [ ] (Simulate by removing API key mid-generation)
- [ ] **Expected:** Error shown, not a crash

---

## 🐛 Known Issues / Edge Cases to Test

### Database
- [ ] Multiple sites with same name (should be allowed)
- [ ] Special characters in business name
- [ ] Very long text in fields (>1000 chars)

### Keyword Research
- [ ] Empty seed keywords (should error)
- [ ] Single keyword (should still work)
- [ ] 100+ generated keywords (should handle)

### Content Generation
- [ ] API rate limit hit (should retry with backoff)
- [ ] Invalid JSON response from Claude (should error gracefully)
- [ ] Generation interruption (browser close)

### Security
- [ ] SQL injection attempts (should be sanitized)
- [ ] XSS attempts in form fields (should be escaped)
- [ ] CSRF protection on forms (nonces should work)

---

## 🔬 Automated Testing (Future)

### Unit Tests (PHPUnit)

```php
// Example test structure (not implemented yet)

class DatabaseTest extends WP_UnitTestCase {
    public function test_site_creation() {
        $db = new RSG_Database();
        $data = ['site_name' => 'Test Site', ...];
        $site_id = $db->save_site_profile($data);
        $this->assertIsInt($site_id);
    }
}

class KeywordResearchTest extends WP_UnitTestCase {
    public function test_intent_classification() {
        $kr = new RSG_Keyword_Research();
        $intent = $kr->classify_intent('best pizza near me');
        $this->assertEquals('commercial', $intent);
    }
}
```

### Integration Tests

```php
class ContentGenerationTest extends WP_UnitTestCase {
    public function test_full_workflow() {
        // 1. Create site
        // 2. Generate keywords
        // 3. Generate architecture
        // 4. Generate content
        // 5. Verify output
    }
}
```

### E2E Tests (Playwright/Cypress)

```javascript
// Example E2E test (not implemented yet)

test('Complete site generation workflow', async ({ page }) => {
  // Login to WordPress
  await page.goto('/wp-admin');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await page.click('#wp-submit');

  // Navigate to plugin
  await page.click('text=SEO Generator');

  // Create site
  await page.click('text=Create Your First Site');
  await page.fill('#site_name', 'Test Restaurant');
  // ... fill form
  await page.click('button:text("Save Profile")');

  // Generate content
  await page.fill('#seed_keywords', 'pizza brooklyn');
  await page.click('button:text("Generate Keywords")');
  // ... continue workflow

  // Verify completion
  await expect(page.locator('.rsg-page-card')).toHaveCount(10);
});
```

---

## 📊 Performance Testing

### Load Testing
- [ ] Generate 10 sites concurrently
- [ ] **Check:** Database handles concurrent writes
- [ ] **Check:** No race conditions

### API Usage
- [ ] Monitor Claude API costs
- [ ] **Target:** ~$2-3 per 10-page site
- [ ] Track token usage per page

### Frontend Performance
- [ ] Measure CSS file size (~40KB)
- [ ] Check JS file size (~5KB)
- [ ] Lighthouse audit:
  - Performance: 90+
  - Accessibility: 100
  - Best Practices: 100
  - SEO: 100

---

## ✅ Sign-Off Checklist

Before production release:

### Functionality
- [ ] All core features work
- [ ] No PHP errors in error log
- [ ] No JavaScript console errors
- [ ] Database queries optimized
- [ ] API calls have error handling

### Security
- [ ] All inputs sanitized
- [ ] All outputs escaped
- [ ] Nonces on all forms
- [ ] Capability checks on admin pages
- [ ] SQL injection prevented
- [ ] XSS prevented

### Performance
- [ ] Page load times acceptable
- [ ] Database queries efficient
- [ ] CSS/JS minified (production)
- [ ] API calls optimized

### Compatibility
- [ ] WordPress 5.8+ tested
- [ ] PHP 7.4+ tested
- [ ] Chrome, Firefox, Safari tested
- [ ] Mobile iOS/Android tested

### User Experience
- [ ] UI looks professional
- [ ] Instructions clear
- [ ] Error messages helpful
- [ ] Loading states visible
- [ ] Success states clear

### Documentation
- [ ] README complete
- [ ] Installation instructions clear
- [ ] API key setup documented
- [ ] Troubleshooting guide provided

---

## 🚀 Quick Test Script

For rapid smoke testing:

```bash
# 1. Install plugin
wp plugin install /path/to/restaurant-seo-generator.zip --activate

# 2. Check activation
wp plugin list | grep restaurant-seo-generator

# 3. Check database tables
wp db query "SHOW TABLES LIKE 'wp_rsg_%';"

# 4. Check for PHP errors
tail -f /path/to/wordpress/wp-content/debug.log

# 5. Run quick test
# (Visit admin page and verify it loads)

# 6. Deactivate
wp plugin deactivate restaurant-seo-generator

# 7. Activate again (test activation hook)
wp plugin activate restaurant-seo-generator
```

---

## 📞 Support Testing

Test the support experience:

1. **No API key set:**
   - Should show clear instructions
   - Link to Anthropic console
   - Test connection button

2. **First-time user:**
   - Empty state should be encouraging
   - Clear next steps
   - Helpful hints

3. **Generation fails:**
   - Error message is helpful
   - Suggests solutions
   - Doesn't crash plugin

---

## Summary

- ✅ **Syntax Validation:** All files pass
- ⚠️ **E2E Testing:** Requires local WordPress setup
- ✅ **Test Plan:** Comprehensive checklist provided
- ✅ **Known Issues:** Documented
- ✅ **Future Testing:** Roadmap defined

**Recommendation:** Set up local WordPress environment (15 minutes) and run through manual E2E checklist (~1 hour total).

**Quick Setup:** Use [Local by Flywheel](https://localwp.com/) → Create new site → Install plugin → Test!
