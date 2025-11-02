/**
 * Keywords API Routes
 */

const express = require('express');
const router = express.Router();
const Database = require('../models/Database');
const KeywordResearch = require('../services/KeywordResearch');

const db = new Database();
const keywordService = new KeywordResearch();

// Generate keywords
router.post('/generate', async (req, res) => {
    try {
        const { seedKeywords, location, autoExpand } = req.body;
        const keywords = await keywordService.generateKeywords(seedKeywords, location, autoExpand);
        const classified = await keywordService.classifyKeywords(keywords, req.body.siteName);

        res.json({ success: true, keywords: classified });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Save keywords to database
router.post('/save', (req, res) => {
    try {
        const { siteId, keywords } = req.body;

        // Delete existing keywords for this site
        db.deleteSiteKeywords(siteId);

        // Save new keywords
        keywords.forEach(kw => {
            db.saveKeyword({
                site_id: siteId,
                keyword: kw.keyword,
                search_intent: kw.search_intent,
                search_volume: kw.search_volume || 0,
                competition: kw.competition || 'unknown'
            });
        });

        res.json({ success: true, message: 'Keywords saved' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Get keywords for site
router.get('/:siteId', (req, res) => {
    try {
        const keywords = db.getSiteKeywords(req.params.siteId);
        res.json({ success: true, keywords });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Analyze competitor
router.post('/analyze-competitor', async (req, res) => {
    try {
        const { url, siteId } = req.body;
        const analysis = await keywordService.analyzeCompetitor(url);

        // Save to database
        if (siteId) {
            db.saveCompetitorAnalysis(siteId, analysis);
        }

        res.json({ success: true, analysis });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
