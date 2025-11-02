/**
 * Sites API Routes
 */

const express = require('express');
const router = express.Router();
const Database = require('../models/Database');

const db = new Database();

// Get all sites
router.get('/', (req, res) => {
    try {
        const sites = db.getAllSites();
        res.json({ success: true, sites });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Get single site
router.get('/:id', (req, res) => {
    try {
        const site = db.getSiteProfile(req.params.id);
        if (!site) {
            return res.status(404).json({ success: false, message: 'Site not found' });
        }
        res.json({ success: true, site });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Create or update site
router.post('/', (req, res) => {
    try {
        const siteId = db.saveSiteProfile(req.body, req.body.id || null);
        const site = db.getSiteProfile(siteId);
        res.json({ success: true, site_id: siteId, site });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Delete site
router.delete('/:id', (req, res) => {
    try {
        db.deleteSite(req.params.id);
        res.json({ success: true, message: 'Site deleted' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Get site statistics
router.get('/:id/stats', (req, res) => {
    try {
        const stats = db.getSiteStatistics(req.params.id);
        res.json({ success: true, stats });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Get site pages
router.get('/:id/pages', (req, res) => {
    try {
        const pages = db.getSitePages(req.params.id, req.query.status);
        res.json({ success: true, pages });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
