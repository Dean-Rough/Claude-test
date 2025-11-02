# SEO Content Studio - Desktop Application

A standalone desktop application for generating SEO-optimized content for local businesses using Claude AI.

**This is a direct port of the WordPress SEO Generator plugin** - all functionality, UI styling, and business logic has been converted from WordPress/PHP to a standalone Node.js application.

## 🎯 What is This?

This desktop app provides the same functionality as the WordPress plugin but runs entirely locally without requiring WordPress:

- **Keyword Research** using free Google Autocomplete
- **AI Content Generation** using Claude Sonnet 4
- **Site Architecture Planning** for local business websites
- **Schema.org Markup** generation (business-type specific)
- **HTML Export** ready to copy into any page builder
- **Modern UI** with the same premium Stripe/Linear/Vercel-quality design

## 🚀 Quick Start

### Prerequisites

- **Node.js 16+** (check with `node --version`)
- **Claude API key** from https://console.anthropic.com

### Installation

```bash
# 1. Install dependencies
npm install

# 2. Start the application
npm start
```

The app will automatically open in your default browser at http://localhost:3000

## 📁 Project Structure

```
seo-content-studio/
├── src/
│   ├── app.js                      # Main Express server
│   ├── database/
│   │   └── init.js                 # SQLite database setup
│   ├── models/
│   │   └── Database.js             # Database operations
│   ├── services/
│   │   ├── ClaudeAPI.js            # Claude AI integration
│   │   ├── KeywordResearch.js      # Keyword generation
│   │   ├── ContentGenerator.js     # Content orchestration
│   │   └── SchemaGenerator.js      # Schema.org markup
│   └── routes/
│       ├── sites.js                # Site management API
│       ├── keywords.js             # Keyword research API
│       ├── content.js              # Content generation API
│       └── settings.js             # Settings API
├── public/
│   ├── css/                        # Premium UI styles (ported from plugin)
│   ├── js/                         # Frontend JavaScript
│   └── views/                      # HTML templates
├── data/
│   └── seo-studio.db               # SQLite database (auto-created)
├── package.json
└── README.md
```

## 🎨 Features

### Same as WordPress Plugin

All features from the WordPress plugin are available:

✅ **Site Profile Management**
- Industry-specific business types (Food & Drink, Home Services, Health & Wellness, etc.)
- Service area configuration
- Brand voice settings

✅ **Keyword Research**
- Free Google Autocomplete API
- Pattern-based intent classification
- Auto-expansion from seed keywords

✅ **Competitor Analysis**
- Website scraping and analysis
- Common topic extraction
- Keyword frequency analysis

✅ **AI Content Generation**
- Business-type specific prompts
- 8-10 pages per site
- 1,500+ words per page
- SEO scoring

✅ **Schema Markup**
- Restaurant, LocalBusiness, Dentist, ProfessionalService, BeautySalon
- Service area geo-targeting
- FAQ and Breadcrumb schemas

✅ **Premium UI**
- Outfit font from Google Fonts
- Indigo primary color (#4F46E5)
- WCAG 2.1 AA accessibility
- Mobile-responsive design

### Desktop-Specific Benefits

🖥️ **No WordPress Required**
- Runs entirely locally
- No server setup
- No plugin installation

💾 **SQLite Database**
- Portable single-file database
- Easy backups
- No MySQL required

🌐 **Auto-Browser Launch**
- Opens automatically on start
- Clean web interface
- Works in any modern browser

## 🔧 Configuration

### API Key Setup

1. Navigate to **Settings** in the app
2. Enter your Claude API key
3. Click **Test Connection** to verify
4. Save settings

The API key is stored locally in SQLite and never transmitted anywhere except to Anthropic's Claude API.

### Cost Expectations

- **Per 10-page site:** ~$2-3 in Claude API usage
- **Model:** Claude Sonnet 4 (recommended)
- **Average time:** ~20-25 minutes for full site generation

## 📊 Tech Stack

**Backend:**
- Node.js + Express
- SQLite (better-sqlite3)
- Axios for HTTP requests
- Cheerio for web scraping

**Frontend:**
- EJS templates
- Same CSS/JS as WordPress plugin
- Modern vanilla JavaScript

**AI:**
- Claude Sonnet 4 API
- Business-type specific prompts
- JSON-structured responses

## 🗄️ Database Schema

Four main tables (same as WordPress plugin):

- `sites` - Business profiles
- `pages` - Generated content
- `keywords` - Keyword research results
- `competitors` - Competitor analysis
- `settings` - Application configuration

## 🚢 Deployment Options

### Option 1: Run Locally (Current)

```bash
npm start
```

### Option 2: Package as Executable (Future)

You can package this as a standalone executable using:
- **pkg** - Create single executables for Windows/Mac/Linux
- **nexe** - Compile to native binary
- **Electron** - Full desktop app with window chrome

Example with pkg:
```bash
npm install -g pkg
pkg package.json --targets node18-win-x64,node18-macos-x64,node18-linux-x64
```

### Option 3: Docker (Future)

Create a Dockerfile for easy deployment:
```bash
docker build -t seo-content-studio .
docker run -p 3000:3000 seo-content-studio
```

## 🔄 Comparison: WordPress Plugin vs Desktop App

| Feature | WordPress Plugin | Desktop App |
|---------|------------------|-------------|
| **Installation** | Plugin upload | `npm install` |
| **Database** | MySQL (WordPress) | SQLite (local file) |
| **Backend** | PHP classes | JavaScript (Node.js) |
| **Frontend** | WordPress admin | Express + EJS |
| **Deployment** | WordPress site required | Standalone executable possible |
| **UI/UX** | Identical | Identical |
| **Features** | Full feature set | Full feature set |
| **Portability** | WordPress dependent | Fully portable |

## 📝 Usage Workflow

1. **Create Site Profile**
   - Enter business details
   - Select industry and type
   - Configure service area

2. **Keyword Research**
   - Enter seed keywords
   - Auto-expand with Google Autocomplete
   - Classify intent (informational, commercial, transactional)

3. **Competitor Analysis** (Optional)
   - Enter competitor URLs
   - Analyze content structure
   - Extract common topics

4. **Generate Architecture**
   - AI creates 8-10 page plan
   - Review and adjust
   - Approve page structure

5. **Generate Content**
   - Batch generation of all pages
   - Real-time progress tracking
   - SEO scoring per page

6. **Export & Use**
   - Copy HTML to page builder
   - Export complete pages
   - Use schema markup

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Change port in src/app.js or set environment variable
PORT=3001 npm start
```

### Database Locked
```bash
# Stop all running instances
# Delete data/seo-studio.db
# Restart application
```

### API Key Issues
- Verify key is valid at https://console.anthropic.com
- Check API quota and usage
- Test connection in Settings

## 🔐 Security Notes

- API key stored locally in SQLite
- No cloud sync or external transmission
- All data stays on your machine
- Use `.gitignore` to exclude `data/` directory

## 📈 Future Enhancements

Potential improvements:
- [ ] Dark mode support
- [ ] Custom icon library (replace emoji)
- [ ] Bulk site generation
- [ ] Image generation integration (DALL-E/Midjourney)
- [ ] Export to ZIP with all pages
- [ ] Template library
- [ ] Multi-language support

## 🤝 Credits

Converted from the WordPress SEO Generator plugin. All core functionality, UI design, and business logic ported from PHP to JavaScript while maintaining feature parity.

## 📄 License

ISC License

## 🆘 Support

For issues or questions:
1. Check the Troubleshooting section
2. Review the original WordPress plugin documentation
3. Open an issue on GitHub

---

**Ready to ship!** 🚀

All functionality from the WordPress plugin is now available as a standalone desktop application.
