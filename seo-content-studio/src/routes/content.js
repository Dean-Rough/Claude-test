/**
 * Content Generation API Routes
 */

const express = require('express');
const router = express.Router();
const Database = require('../models/Database');
const ContentGenerator = require('../services/ContentGenerator');

const db = new Database();

// Generate site architecture
router.post('/architecture', async (req, res) => {
    try {
        const { siteId, keywords, competitorUrls } = req.body;

        // Get API key from settings
        const apiKey = db.getSetting('claude_api_key');
        if (!apiKey) {
            return res.status(400).json({ success: false, message: 'Claude API key not configured' });
        }

        const generator = new ContentGenerator(apiKey);
        const architecture = await generator.generateSiteArchitecture(siteId, keywords, competitorUrls);

        res.json({ success: true, architecture });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Generate all pages
router.post('/generate-all', async (req, res) => {
    try {
        const { siteId, pages } = req.body;

        const apiKey = db.getSetting('claude_api_key');
        if (!apiKey) {
            return res.status(400).json({ success: false, message: 'Claude API key not configured' });
        }

        const generator = new ContentGenerator(apiKey);

        // Start generation (this is async and takes time)
        generator.generateAllPages(siteId, pages).then(results => {
            console.log('Generation complete:', results);
        }).catch(error => {
            console.error('Generation error:', error);
        });

        res.json({ success: true, message: 'Generation started' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Generate single page
router.post('/generate-page', async (req, res) => {
    try {
        const { siteId, pageSpec } = req.body;

        const apiKey = db.getSetting('claude_api_key');
        if (!apiKey) {
            return res.status(400).json({ success: false, message: 'Claude API key not configured' });
        }

        const generator = new ContentGenerator(apiKey);
        const content = await generator.generatePage(siteId, pageSpec);

        res.json({ success: true, content });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Get generation progress
router.get('/progress', (req, res) => {
    try {
        const apiKey = db.getSetting('claude_api_key');
        const generator = new ContentGenerator(apiKey);
        const progress = generator.getProgress();

        res.json({ success: true, progress });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Get page
router.get('/page/:id', (req, res) => {
    try {
        const page = db.getPage(req.params.id);
        if (!page) {
            return res.status(404).json({ success: false, message: 'Page not found' });
        }
        res.json({ success: true, page });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Delete page
router.delete('/page/:id', (req, res) => {
    try {
        db.deletePage(req.params.id);
        res.json({ success: true, message: 'Page deleted' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Export page HTML
router.get('/export/:id', (req, res) => {
    try {
        const apiKey = db.getSetting('claude_api_key');
        const generator = new ContentGenerator(apiKey);
        const html = generator.exportPageHTML(req.params.id);

        res.setHeader('Content-Type', 'text/html');
        res.setHeader('Content-Disposition', 'attachment; filename="page.html"');
        res.send(html);
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
