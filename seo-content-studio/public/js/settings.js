/**
 * Settings Page JavaScript
 */

document.addEventListener('DOMContentLoaded', async () => {
    await loadSettings();
    setupEventListeners();
});

/**
 * Load current settings
 */
async function loadSettings() {
    try {
        const response = await fetch('/api/settings');
        const data = await response.json();

        if (data.success && data.settings) {
            // Set default model
            if (data.settings.default_model) {
                document.getElementById('default_model').value = data.settings.default_model;
            }

            // Show API key status
            if (data.settings.has_api_key) {
                showApiKeyStatus(data.settings.claude_api_key_masked);
                updateApiStatus('API key configured', 'success');
            } else {
                updateApiStatus('No API key configured', 'warning');
            }
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showMessage('Failed to load settings', 'error');
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Form submission
    document.getElementById('settings-form').addEventListener('submit', handleSaveSettings);

    // Test API button
    document.getElementById('test-api-btn').addEventListener('click', handleTestApi);
}

/**
 * Handle save settings
 */
async function handleSaveSettings(e) {
    e.preventDefault();

    const apiKey = document.getElementById('claude_api_key').value.trim();
    const defaultModel = document.getElementById('default_model').value;

    // Validate API key format if provided
    if (apiKey && !apiKey.startsWith('sk-ant-')) {
        showMessage('Invalid API key format. Should start with "sk-ant-"', 'error');
        return;
    }

    try {
        const payload = { default_model: defaultModel };
        if (apiKey) {
            payload.claude_api_key = apiKey;
        }

        const response = await fetch('/api/settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success) {
            showMessage('Settings saved successfully!', 'success');

            // Clear password field
            document.getElementById('claude_api_key').value = '';

            // Reload settings to show masked key
            setTimeout(() => loadSettings(), 1000);
        } else {
            showMessage(data.message || 'Failed to save settings', 'error');
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        showMessage('Failed to save settings', 'error');
    }
}

/**
 * Handle test API connection
 */
async function handleTestApi() {
    const button = document.getElementById('test-api-btn');
    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = 'Testing...';

    try {
        // Get API key from input or use saved one
        const apiKey = document.getElementById('claude_api_key').value.trim();
        const payload = apiKey ? { api_key: apiKey } : {};

        const response = await fetch('/api/settings/test-api', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success) {
            showMessage('✅ API connection successful!', 'success');
            updateApiStatus('API connected successfully', 'success');
        } else {
            showMessage(`❌ ${data.message}`, 'error');
            updateApiStatus(data.message, 'error');
        }
    } catch (error) {
        console.error('Error testing API:', error);
        showMessage('Failed to test API connection', 'error');
        updateApiStatus('Connection test failed', 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

/**
 * Show API key status message
 */
function showApiKeyStatus(maskedKey) {
    const statusDiv = document.getElementById('api-key-status');
    statusDiv.innerHTML = `<span class="status-success">✓ API key configured: ${maskedKey}</span>`;
}

/**
 * Update API status display
 */
function updateApiStatus(message, type = 'info') {
    const statusDiv = document.getElementById('api-status');
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : '⚠️';
    statusDiv.innerHTML = `<p>${icon} ${message}</p>`;
}

/**
 * Show form message
 */
function showMessage(message, type = 'info') {
    const messageDiv = document.getElementById('form-message');
    messageDiv.textContent = message;
    messageDiv.className = `rsg-message rsg-message-${type}`;
    messageDiv.style.display = 'block';

    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}
