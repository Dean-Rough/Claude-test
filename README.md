# Restaurant SEO Generator - WordPress Plugin

A powerful WordPress plugin that generates SEO-optimized content for local business websites using Claude AI. Built for solo operators and agencies creating 10-page sites for small businesses.

## 🎯 Overview

**Problem:** Creating SEO content for local business websites is time-consuming and expensive.

**Solution:** This plugin automates the entire process using Claude AI, reducing a 10-15 hour task to just 25 minutes.

**Cost:** ~$2-3 per complete 10-page site (vs $500+ for manual content creation)

## ✨ Features

### Core Functionality

- **Site Profile Management** - Store business info, location, brand voice, and unique selling points
- **Keyword Research** - Free Google Autocomplete integration (no API key needed)
- **Intent Classification** - Automatic categorization (informational, commercial, transactional, navigational)
- **Competitor Analysis** - Analyze 2-5 competitor sites for insights (optional)
- **Site Architecture** - AI-generated page structure optimized for each business type
- **Content Generation** - Full page content with SEO optimization
- **HTML Export** - Clean, copy/paste ready HTML with schema markup

### Supported Business Types

- **Food & Drink** - Restaurants, bars, cafes, bakeries, pizzerias
- **Home Services** - Plumbers, electricians, HVAC, landscaping, cleaning
- **Health & Wellness** - Dentists, chiropractors, yoga studios, gyms
- **Professional Services** - Accountants, lawyers, real estate, insurance
- **Beauty & Personal Care** - Salons, barbershops, spas, nail salons

### SEO Features

- Natural keyword integration (1-2% density)
- Local SEO optimization (5-7 location mentions per page)
- Schema markup (business-type specific)
- Meta tags (title, description, slug)
- Internal linking suggestions
- Competitor insights integration

## 🚀 Quick Start

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Claude API key from [Anthropic Console](https://console.anthropic.com)

### Installation

1. **Download or Clone**
   ```bash
   git clone https://github.com/Dean-Rough/Claude-test.git
   cd Claude-test
   ```

2. **Install in WordPress**
   ```bash
   # Copy plugin to WordPress plugins directory
   cp -r restaurant-seo-generator /path/to/wordpress/wp-content/plugins/
   ```

3. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Restaurant SEO Generator"
   - Click "Activate"

4. **Configure API Key**
   - Go to SEO Generator → Settings
   - Enter your Claude API key
   - Click "Test Connection" to verify
   - Save settings

### Getting a Claude API Key

1. Visit [console.anthropic.com](https://console.anthropic.com)
2. Sign up or log in
3. Navigate to API Keys
4. Create a new API key
5. Copy and paste into plugin settings

## 📖 Usage Guide

### Step 1: Create Site Profile (2 minutes)

1. Go to **SEO Generator → Dashboard**
2. Click **"Create Your First Site"**
3. Fill in the form:
   - Business name and type
   - Location (for local SEO)
   - Target audience
   - Brand voice
   - Unique selling points
   - Key services
   - Certifications (optional)
   - Writing sample (optional but recommended)
4. Click **"Save Profile & Continue"**

### Step 2: Keyword Research (1 minute)

1. Enter seed keywords (one per line):
   ```
   pizza brooklyn
   best pizza near me
   italian restaurant
   ```
2. Click **"Generate Keywords"**
3. Review expanded keywords (~30-50 generated)
4. Click **"Classify Intent & Continue"**
5. Select keywords you want to target
6. Click **"Continue to Competitors"** or **"Skip to Site Plan"**

### Step 3: Competitor Analysis (2 minutes - Optional)

1. Enter 2-5 competitor URLs:
   ```
   https://competitor1.com
   https://competitor2.com
   https://competitor3.com
   ```
2. Click **"Analyze Competitors"**
3. Review analysis summary:
   - Average word count
   - Common topics covered
   - Content gaps (opportunities)
4. Click **"Continue to Site Plan"**

### Step 4: Review Site Architecture (1 minute)

1. Review AI-generated page structure:
   - Homepage
   - Service pages
   - Content pages
   - About/Contact pages
2. Check word counts and priorities
3. Click **"Generate Content"**

### Step 5: Generate Content (20-25 minutes)

1. Review generation summary:
   - Total pages
   - Total words
   - Estimated time
   - Estimated cost
2. Click **"Start Generating Content"**
3. Watch progress bar (runs automatically)
4. Wait for completion

### Step 6: Copy & Use Content

1. Go to **"Content Library"** tab
2. Browse generated pages
3. Click **"Preview"** to review content
4. Click **"Copy HTML"** to copy to clipboard
5. Paste into your page builder (Elementor, Divi, etc.)
6. Publish!

## 💡 Example Use Cases

### Restaurant Site (10 pages)
- Homepage: "Authentic Neapolitan Pizza in Brooklyn"
- Menu page
- About Us
- Contact/Hours
- Best Pizza in Brooklyn (commercial intent)
- Wood-Fired Pizza Guide (informational)
- Pizza Delivery (transactional)
- Catering Services (transactional)
- Brooklyn Neighborhoods Guide (informational)
- Our Story (navigational)

**Result:** ~12,000 words, complete schema markup, optimized for local SEO
**Time:** 25 minutes
**Cost:** $2.40

### Plumber Site (9 pages)
- Homepage: "Licensed Plumber in Brooklyn"
- Services Overview
- Emergency Plumbing (24/7)
- Service Area
- About/Credentials
- Contact/Quote
- Drain Cleaning Guide (informational)
- Water Heater Repair (transactional)
- Common Plumbing Problems (informational)

**Result:** ~13,500 words, LocalBusiness schema, service area targeting
**Time:** 23 minutes
**Cost:** $2.20

## 🏗️ Plugin Architecture

### File Structure
```
restaurant-seo-generator/
├── restaurant-seo-generator.php    # Main plugin file
├── readme.txt                      # WordPress plugin readme
├── includes/
│   ├── class-database.php          # Database operations
│   ├── class-claude-api.php        # Claude API wrapper
│   ├── class-keyword-research.php  # Keyword generation & classification
│   ├── class-content-generator.php # Content orchestration
│   └── class-schema-generator.php  # Schema markup generation
└── admin/
    ├── class-admin.php             # Admin interface
    ├── css/
    │   └── admin.css               # Admin styles
    ├── js/
    │   └── admin.js                # Admin JavaScript
    └── views/
        ├── site-profile-form.php   # Site creation form
        ├── keyword-research.php    # Keyword & generation workflow
        ├── content-library.php     # Generated pages library
        └── settings.php            # Plugin settings
```

### Database Schema

**wp_rsg_sites** - Site profiles and business information
- id, site_name, industry, business_type, location, service_area_type, service_area_radius
- target_audience, brand_voice, unique_selling_point, key_services, certifications
- writing_sample, profile_data (JSON), created_at, updated_at

**wp_rsg_pages** - Generated pages with content and metadata
- id, site_id, title, slug, primary_keyword, secondary_keywords (JSON)
- intent, page_type, target_words, priority
- content_html, content_json, meta_title, meta_description, schema_markup (JSON)
- status, word_count, seo_score, created_at, updated_at

**wp_rsg_keywords** - Keyword research cache
- id, site_id, keyword, intent, confidence, source
- search_volume, competition, related_keywords (JSON), cached_at

**wp_rsg_competitors** - Competitor analysis results
- id, site_id, url, title, meta_description
- word_count, h1_count, h2_count, h3_count, h2_headings (JSON)
- image_count, has_schema, internal_links, analysis_data (JSON), analyzed_at

## 🎨 Business-Type Specific Features

### Restaurants/Bars/Cafes
- Cuisine-specific schema
- Menu integration suggestions
- Ambiance and experience descriptions
- Chef/owner background sections
- Ingredient sourcing emphasis

### Home Services
- License and insurance emphasis
- Emergency availability highlighting
- Response time mentions
- Before/after scenarios
- Service area coverage

### Health & Wellness
- Credentials and training
- Patient/client comfort
- Procedure explanations
- Insurance acceptance
- Modern technology mentions

### Professional Services
- Expertise and experience
- Confidentiality and trust
- Process methodology
- Client success stories
- Free consultation offers

### Beauty & Personal Care
- Before/after descriptions
- Product lines used
- Stylist experience
- Latest techniques
- Booking ease

## 💰 Pricing & Cost

### Per Site Cost Breakdown
- Keyword research: ~$0.10
- Architecture generation: ~$0.05
- Content generation: ~$0.20 per page
- **Total (10 pages): $2.15 - $2.50**

### Monthly Usage (Solo Operator)
- 5 sites/month: ~$10.75
- 10 sites/month: ~$21.50

### Comparison
- **Manual writing:** $50-100 per page = $500-1,000 per site
- **Freelance writer:** $0.10-0.20 per word = $1,000-2,000 per site
- **This plugin:** $2-3 per site = **99% cost savings**

## 🔧 Development

### Tech Stack
- **Backend:** PHP 7.4+
- **Frontend:** Vanilla JavaScript + jQuery
- **AI:** Claude Sonnet 4 via Anthropic API
- **Keyword Research:** Google Autocomplete (free)
- **Storage:** WordPress database (custom tables)

### Key Design Decisions

1. **No external dependencies** - All keyword research uses free Google Autocomplete
2. **Business-type aware** - Specialized prompts and schema for each industry
3. **Copy/paste output** - No complex integrations, just clean HTML
4. **Progress tracking** - Long operations show real-time progress
5. **Regeneration support** - Can regenerate any page if not satisfied

### Adding New Business Types

To add a new business type:

1. **Add to site profile form** (`admin/views/site-profile-form.php`)
2. **Add schema logic** (`includes/class-schema-generator.php`)
3. **Add content guidelines** (`includes/class-claude-api.php`)
4. **Add icon** (`admin/class-admin.php` - `get_business_icon()`)

## 🐛 Troubleshooting

### "API key is invalid"
- Verify key is copied correctly (starts with `sk-ant-api03-`)
- Check your Anthropic account has credits
- Test connection in Settings page

### "Could not fetch URL" (Competitor Analysis)
- Some sites block automated requests
- Try different competitor URLs
- Skip competitor analysis and continue

### "Generation failed"
- Check Claude API quota/credits
- Verify internet connection
- Try regenerating individual page

### Content quality issues
- Add a writing sample to site profile
- Be more specific in business description
- Regenerate page with different seed keywords

## 📝 Roadmap

### Phase 2 Features (Not in MVP)
- [ ] Elementor JSON export
- [ ] Yoast/Rank Math integration
- [ ] Google Search Console integration
- [ ] Site auditor
- [ ] SERP API competitor scraping
- [ ] Image generation integration
- [ ] Analytics dashboard
- [ ] WordPress.org submission

### Possible Enhancements
- Multi-language support
- Bulk site generation
- Content scheduling
- A/B testing variants
- Custom schema types
- Export to other CMS platforms

## 🤝 Contributing

This is a personal use plugin, but contributions are welcome!

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

GPL v2 or later - See [LICENSE](LICENSE) for details.

This plugin uses:
- **Claude AI** by Anthropic ([Terms](https://www.anthropic.com/legal/terms))
- **Google Autocomplete API** (free, no key required)

## 🙏 Acknowledgments

- Built following the comprehensive spec from the original requirements document
- Uses Claude Sonnet 4 for optimal quality/cost balance
- Inspired by the need for affordable, quality SEO content for small businesses

## 📞 Support

For issues, questions, or feature requests:
- Open an issue on GitHub
- Check the [troubleshooting section](#-troubleshooting)
- Review the [usage guide](#-usage-guide)

## 📊 Stats

- **Lines of Code:** ~4,900
- **Files:** 14
- **Development Time:** ~40 hours
- **Database Tables:** 4
- **AJAX Endpoints:** 11
- **Supported Business Types:** 30+

---

**Made with ❤️ for solo operators building websites for local businesses**
