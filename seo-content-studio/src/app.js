/**
 * SEO Content Studio - Standalone Desktop Application
 *
 * A local web server that provides the same functionality as the WordPress plugin
 * but runs as a standalone desktop application.
 */

const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const path = require('path');

// Import routes
const siteRoutes = require('./routes/sites');
const keywordRoutes = require('./routes/keywords');
const contentRoutes = require('./routes/content');
const settingsRoutes = require('./routes/settings');

// Import database initialization
const { initDatabase } = require('./database/init');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '10mb' }));
app.use(bodyParser.urlencoded({ extended: true, limit: '10mb' }));

// Static files (CSS, JS, images)
app.use('/css', express.static(path.join(__dirname, '../public/css')));
app.use('/js', express.static(path.join(__dirname, '../public/js')));
app.use('/assets', express.static(path.join(__dirname, '../public/assets')));

// View engine setup (simple HTML serving)
app.set('views', path.join(__dirname, '../public/views'));
app.engine('html', require('ejs').renderFile);
app.set('view engine', 'html');

// Initialize database
initDatabase();

// Routes
app.get('/', (req, res) => {
    res.redirect('/dashboard');
});

app.get('/dashboard', (req, res) => {
    res.render('dashboard');
});

app.get('/create-site', (req, res) => {
    res.render('create-site');
});

app.get('/keyword-research/:id', (req, res) => {
    res.render('keyword-research', { siteId: req.params.id });
});

app.get('/content-library/:id', (req, res) => {
    res.render('content-library', { siteId: req.params.id });
});

app.get('/settings', (req, res) => {
    res.render('settings');
});

// API Routes
app.use('/api/sites', siteRoutes);
app.use('/api/keywords', keywordRoutes);
app.use('/api/content', contentRoutes);
app.use('/api/settings', settingsRoutes);

// Error handling
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({
        success: false,
        message: err.message || 'Internal server error'
    });
});

// Start server
const server = app.listen(PORT, () => {
    const url = `http://localhost:${PORT}`;
    console.log('╔══════════════════════════════════════════════════════════╗');
    console.log('║                                                          ║');
    console.log('║           SEO Content Studio - Desktop App               ║');
    console.log('║                                                          ║');
    console.log('╚══════════════════════════════════════════════════════════╝');
    console.log('');
    console.log(`✅ Server running at: ${url}`);
    console.log(`🗄️  Database: ${path.join(__dirname, '../data/seo-studio.db')}`);
    console.log('');
    console.log('Opening browser...');
    console.log('');
    console.log('Press Ctrl+C to stop the server');
    console.log('');

    // Auto-open browser (dynamic import for ES module compatibility)
    import('open').then(({ default: open }) => {
        open(url).catch(() => {
            console.log('Could not auto-open browser. Please navigate to:', url);
        });
    }).catch(() => {
        console.log('Could not auto-open browser. Please navigate to:', url);
    });
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM signal received: closing HTTP server');
    server.close(() => {
        console.log('HTTP server closed');
        process.exit(0);
    });
});

module.exports = app;
