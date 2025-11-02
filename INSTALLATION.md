# Installation Guide

Complete setup instructions for the SEO Content Studio project (WordPress plugin + Desktop app).

---

## 📦 What's Included

This repository contains **two versions** of the same SEO content generation tool:

1. **WordPress Plugin** (`restaurant-seo-generator/`) - Requires WordPress
2. **Desktop App** (`seo-content-studio/`) - Standalone Node.js app (no WordPress needed)

Choose the version that fits your workflow.

---

## 🖥️ Desktop App Installation (Recommended)

The standalone desktop app runs locally without WordPress.

### Prerequisites

- **Node.js 16+** ([Download here](https://nodejs.org/))
- **Claude API Key** ([Get one here](https://console.anthropic.com/))

### Quick Start

```bash
# 1. Clone the repository
git clone <repository-url>
cd Claude-test

# 2. Navigate to desktop app
cd seo-content-studio

# 3. Install dependencies
npm install

# 4. Install Playwright browsers (for testing)
npx playwright install chromium

# 5. Start the application
npm start
```

The app will automatically open in your browser at `http://localhost:3000`

### First-Time Setup

1. Navigate to **Settings** in the app
2. Enter your Claude API key
3. Click **Test Connection** to verify
4. Save settings

You're ready to generate content! 🎉

### Available Commands

```bash
npm start          # Start the desktop app
npm test           # Run Playwright e2e tests
npm run test:ui    # Run tests with interactive UI
npm run test:debug # Debug tests step-by-step
```

### Folder Structure

```
seo-content-studio/
├── src/
│   ├── app.js              # Express server
│   ├── database/           # SQLite setup
│   ├── models/             # Database operations
│   ├── services/           # Business logic (Claude, Keywords, Content)
│   └── routes/             # REST API endpoints
├── public/
│   ├── css/                # Premium UI styling
│   └── views/              # HTML templates
├── e2e/                    # Playwright tests
├── data/                   # SQLite database (auto-created)
├── package.json
└── README.md
```

---

## 🔌 WordPress Plugin Installation

### Prerequisites

- **WordPress 5.0+**
- **PHP 7.4+**
- **MySQL 5.6+**
- **Claude API Key**

### Installation Steps

```bash
# 1. Clone the repository
git clone <repository-url>
cd Claude-test

# 2. Copy plugin to WordPress
cp -r restaurant-seo-generator /path/to/wordpress/wp-content/plugins/

# OR create a ZIP file
cd restaurant-seo-generator
zip -r restaurant-seo-generator.zip .
# Upload the ZIP via WordPress Admin → Plugins → Add New → Upload
```

### Activate Plugin

1. Go to **WordPress Admin → Plugins**
2. Find "Restaurant SEO Generator"
3. Click **Activate**
4. Navigate to **SEO Generator** in the admin menu

### Configure Plugin

1. Go to **SEO Generator → Settings**
2. Enter your Claude API key
3. Click **Test Connection**
4. Save settings

### Test Plugin (Optional)

```bash
# Navigate to plugin directory
cd restaurant-seo-generator

# Run PHP syntax validation
php -l restaurant-seo-generator.php
php -l includes/*.php

# Run unit tests
cd tests
php run-all-tests.php
```

Expected output: 10/11 tests passing (91% success rate)

---

## 🧪 Testing

### Desktop App Tests (Playwright)

```bash
cd seo-content-studio

# Install test dependencies (one-time)
npm install
npx playwright install chromium

# Run all tests
npm test

# Interactive mode
npm run test:ui

# Debug mode
npm run test:debug

# View last test report
npm run test:report
```

**Current test results:**
- ✅ 5 tests passing (API, Database, Performance)
- ❌ 11 tests failing (UI templates not yet built)

### WordPress Plugin Tests

```bash
cd restaurant-seo-generator/tests

# Run all tests
php run-all-tests.php
```

**Current test results:**
- ✅ 10 tests passing
- ❌ 1 test failing (minor edge case)

---

## 🔑 Getting a Claude API Key

1. Go to [https://console.anthropic.com/](https://console.anthropic.com/)
2. Sign up or log in
3. Navigate to **API Keys**
4. Click **Create Key**
5. Copy the key (starts with `sk-ant-`)
6. Paste into Settings in either app

**Cost expectations:**
- ~$2-3 per 10-page website
- Sonnet 4 model recommended

---

## 🐛 Troubleshooting

### Desktop App Issues

**Port already in use:**
```bash
# Change port
PORT=3001 npm start
```

**Database locked:**
```bash
# Stop all instances
# Delete data/seo-studio.db
# Restart app
```

**Playwright browser issues:**
```bash
# Reinstall browsers
npx playwright install --force
```

### WordPress Plugin Issues

**Plugin won't activate:**
- Check PHP version: `php -v` (must be 7.4+)
- Check WordPress version (must be 5.0+)
- Check file permissions

**API errors:**
- Verify API key is correct
- Check internet connectivity
- Ensure WordPress can make external HTTP requests

**Database errors:**
- Check MySQL version
- Verify WordPress database credentials
- Check table prefix matches

---

## 📚 Documentation

- **Desktop App:** `seo-content-studio/README.md`
- **WordPress Plugin:** `restaurant-seo-generator/README.md`
- **Desktop App Summary:** `DESKTOP-APP-SUMMARY.md`
- **Project Summary:** `PROJECT-SUMMARY.md`
- **Testing Guide:** `TESTING-GUIDE.md`
- **Design System:** `restaurant-seo-generator/admin/css/design-system.md`

---

## 🔧 Development Setup

### For Desktop App Development

```bash
cd seo-content-studio

# Install dependencies
npm install

# Install dev tools
npm install -g nodemon  # Auto-restart on file changes

# Run in development mode
npm run dev

# Run tests in watch mode
npm test -- --headed  # See browser while testing
```

### For WordPress Plugin Development

```bash
cd restaurant-seo-generator

# Set up local WordPress (recommended: Local by Flywheel)
# https://localwp.com/

# Enable WordPress debugging
# In wp-config.php:
# define('WP_DEBUG', true);
# define('WP_DEBUG_LOG', true);

# View logs
tail -f wp-content/debug.log
```

---

## 🚀 Quick Start Checklist

### Desktop App

- [ ] Node.js 16+ installed
- [ ] Repository cloned
- [ ] `npm install` completed
- [ ] Playwright browsers installed
- [ ] Claude API key obtained
- [ ] App started with `npm start`
- [ ] API key configured in Settings
- [ ] Connection tested successfully

### WordPress Plugin

- [ ] WordPress 5.0+ installed
- [ ] PHP 7.4+ available
- [ ] Plugin copied to `wp-content/plugins/`
- [ ] Plugin activated
- [ ] Claude API key obtained
- [ ] API key configured in Settings
- [ ] Connection tested successfully

---

## 💡 Next Steps

After installation:

1. **Create a site profile** (business details, industry, location)
2. **Generate keywords** (enter seed keywords, auto-expand)
3. **Analyze competitors** (optional - enter competitor URLs)
4. **Generate site architecture** (AI creates 8-10 page plan)
5. **Generate content** (batch generation of all pages)
6. **Export HTML** (copy to your page builder)

---

## ❓ Getting Help

**For issues:**
1. Check the troubleshooting section above
2. Review the relevant README file
3. Check the testing guides
4. Open an issue on GitHub

**For questions:**
- Desktop app: See `seo-content-studio/README.md`
- WordPress plugin: See `restaurant-seo-generator/README.md`
- Testing: See `TESTING-GUIDE.md`

---

## 📝 License

ISC License

---

**Ready to generate SEO content!** 🎉
