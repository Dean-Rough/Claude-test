/**
 * Settings API Routes
 */

const express = require('express');
const router = express.Router();
const Database = require('../models/Database');
const ClaudeAPI = require('../services/ClaudeAPI');

const db = new Database();

// Get all settings
router.get('/', (req, res) => {
    try {
        const settings = {
            claude_api_key: db.getSetting('claude_api_key') || '',
            default_model: db.getSetting('default_model') || 'claude-sonnet-4-20250514'
        };

        // Mask API key for security
        if (settings.claude_api_key) {
            settings.claude_api_key_masked = settings.claude_api_key.substring(0, 10) + '...' +
                settings.claude_api_key.substring(settings.claude_api_key.length - 4);
            settings.has_api_key = true;
            delete settings.claude_api_key; // Don't send full key to frontend
        } else {
            settings.has_api_key = false;
        }

        res.json({ success: true, settings });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Save settings
router.post('/', (req, res) => {
    try {
        const { claude_api_key, default_model } = req.body;

        if (claude_api_key) {
            db.saveSetting('claude_api_key', claude_api_key);
        }

        if (default_model) {
            db.saveSetting('default_model', default_model);
        }

        res.json({ success: true, message: 'Settings saved' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Test API connection
router.post('/test-api', async (req, res) => {
    try {
        const apiKey = req.body.api_key || db.getSetting('claude_api_key');

        if (!apiKey) {
            return res.status(400).json({ success: false, message: 'API key required' });
        }

        const claudeAPI = new ClaudeAPI(apiKey);
        const result = await claudeAPI.testConnection();

        res.json({ success: true, ...result });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
