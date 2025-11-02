/**
 * Keyword Research Page JavaScript
 */

let currentKeywords = [];
let siteData = null;

document.addEventListener('DOMContentLoaded', async () => {
    await loadSiteData();
    await loadSavedKeywords();
    setupEventListeners();
});

/**
 * Load site data
 */
async function loadSiteData() {
    try {
        const response = await fetch(`/api/sites/${SITE_ID}`);
        const data = await response.json();

        if (data.success && data.site) {
            siteData = data.site;
            document.getElementById('site-name').textContent = data.site.business_name;
        }
    } catch (error) {
        console.error('Error loading site:', error);
    }
}

/**
 * Load saved keywords
 */
async function loadSavedKeywords() {
    try {
        const response = await fetch(`/api/keywords/${SITE_ID}`);
        const data = await response.json();

        if (data.success && data.keywords && data.keywords.length > 0) {
            displaySavedKeywords(data.keywords);
            document.getElementById('continue-btn').style.display = 'inline-block';
            document.getElementById('continue-btn').href = `/content-library/${SITE_ID}`;
        }
    } catch (error) {
        console.error('Error loading keywords:', error);
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    document.getElementById('keyword-form').addEventListener('submit', handleGenerateKeywords);
    document.getElementById('competitor-form').addEventListener('submit', handleAnalyzeCompetitor);
}

/**
 * Handle generate keywords
 */
async function handleGenerateKeywords(e) {
    e.preventDefault();

    const generateBtn = document.getElementById('generate-btn');
    const originalText = generateBtn.textContent;

    generateBtn.disabled = true;
    generateBtn.textContent = 'Generating...';

    try {
        const seedKeywords = document.getElementById('seed_keywords').value
            .split('\n')
            .map(kw => kw.trim())
            .filter(kw => kw.length > 0);

        const autoExpand = document.getElementById('auto_expand').checked;

        const response = await fetch('/api/keywords/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                seedKeywords,
                location: siteData.location,
                autoExpand,
                siteName: siteData.business_name
            })
        });

        const data = await response.json();

        if (data.success && data.keywords) {
            currentKeywords = data.keywords;
            displayKeywords(data.keywords);
        } else {
            showError(data.message || 'Failed to generate keywords');
        }
    } catch (error) {
        console.error('Error generating keywords:', error);
        showError('Failed to generate keywords. Please try again.');
    } finally {
        generateBtn.disabled = false;
        generateBtn.textContent = originalText;
    }
}

/**
 * Handle analyze competitor
 */
async function handleAnalyzeCompetitor(e) {
    e.preventDefault();

    const analyzeBtn = document.getElementById('analyze-btn');
    const originalText = analyzeBtn.textContent;

    analyzeBtn.disabled = true;
    analyzeBtn.textContent = 'Analyzing...';

    try {
        const url = document.getElementById('competitor_url').value.trim();

        const response = await fetch('/api/keywords/analyze-competitor', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url, siteId: SITE_ID })
        });

        const data = await response.json();

        if (data.success && data.analysis) {
            displayCompetitorAnalysis(data.analysis);
        } else {
            showError(data.message || 'Failed to analyze competitor');
        }
    } catch (error) {
        console.error('Error analyzing competitor:', error);
        showError('Failed to analyze competitor. Please try again.');
    } finally {
        analyzeBtn.disabled = false;
        analyzeBtn.textContent = originalText;
    }
}

/**
 * Display keywords
 */
function displayKeywords(keywords) {
    const section = document.getElementById('keywords-section');
    const list = document.getElementById('keywords-list');

    section.style.display = 'block';

    const table = `
        <table class="rsg-table">
            <thead>
                <tr>
                    <th>Keyword</th>
                    <th>Search Intent</th>
                    <th>Competition</th>
                </tr>
            </thead>
            <tbody>
                ${keywords.map(kw => `
                    <tr>
                        <td><strong>${escapeHtml(kw.keyword)}</strong></td>
                        <td><span class="rsg-badge rsg-badge-${kw.search_intent}">${kw.search_intent || 'unknown'}</span></td>
                        <td>${kw.competition || 'N/A'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    list.innerHTML = table;

    // Setup save button
    const saveBtn = document.getElementById('save-keywords-btn');
    saveBtn.onclick = handleSaveKeywords;
}

/**
 * Handle save keywords
 */
async function handleSaveKeywords() {
    const saveBtn = document.getElementById('save-keywords-btn');
    const originalText = saveBtn.textContent;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    try {
        const response = await fetch('/api/keywords/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                siteId: SITE_ID,
                keywords: currentKeywords
            })
        });

        const data = await response.json();

        if (data.success) {
            showSuccess('Keywords saved successfully!');
            await loadSavedKeywords();
        } else {
            showError(data.message || 'Failed to save keywords');
        }
    } catch (error) {
        console.error('Error saving keywords:', error);
        showError('Failed to save keywords. Please try again.');
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
}

/**
 * Display saved keywords
 */
function displaySavedKeywords(keywords) {
    const section = document.getElementById('saved-keywords-section');
    const list = document.getElementById('saved-keywords-list');

    section.style.display = 'block';

    const table = `
        <table class="rsg-table">
            <thead>
                <tr>
                    <th>Keyword</th>
                    <th>Search Intent</th>
                </tr>
            </thead>
            <tbody>
                ${keywords.map(kw => `
                    <tr>
                        <td><strong>${escapeHtml(kw.keyword)}</strong></td>
                        <td><span class="rsg-badge rsg-badge-${kw.search_intent}">${kw.search_intent || 'unknown'}</span></td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    list.innerHTML = table;
}

/**
 * Display competitor analysis
 */
function displayCompetitorAnalysis(analysis) {
    const resultsDiv = document.getElementById('competitor-results');
    resultsDiv.style.display = 'block';

    resultsDiv.innerHTML = `
        <div class="rsg-info-box">
            <h3>Competitor: ${escapeHtml(analysis.url)}</h3>
            <p><strong>Title:</strong> ${escapeHtml(analysis.title || 'N/A')}</p>
            <p><strong>Description:</strong> ${escapeHtml(analysis.description || 'N/A')}</p>
            <p><strong>Keywords Found:</strong> ${analysis.keywords ? analysis.keywords.length : 0}</p>
            ${analysis.keywords && analysis.keywords.length > 0 ? `
                <div style="margin-top: 1rem;">
                    <strong>Top Keywords:</strong>
                    <ul>
                        ${analysis.keywords.slice(0, 10).map(kw => `<li>${escapeHtml(kw)}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show error message
 */
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'rsg-message rsg-message-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';

    document.body.appendChild(errorDiv);

    setTimeout(() => errorDiv.remove(), 5000);
}

/**
 * Show success message
 */
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'rsg-message rsg-message-success';
    successDiv.textContent = message;
    successDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';

    document.body.appendChild(successDiv);

    setTimeout(() => successDiv.remove(), 3000);
}
