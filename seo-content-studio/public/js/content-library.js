/**
 * Content Library Page JavaScript
 */

let siteData = null;
let currentArchitecture = null;
let currentPageId = null;

document.addEventListener('DOMContentLoaded', async () => {
    await loadSiteData();
    await loadPages();
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
 * Load existing pages
 */
async function loadPages() {
    try {
        const response = await fetch(`/api/sites/${SITE_ID}/pages`);
        const data = await response.json();

        if (data.success && data.pages && data.pages.length > 0) {
            displayPages(data.pages);
        } else {
            showEmptyState();
        }
    } catch (error) {
        console.error('Error loading pages:', error);
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    document.getElementById('generate-architecture-btn').addEventListener('click', handleGenerateArchitecture);
}

/**
 * Handle generate architecture
 */
async function handleGenerateArchitecture() {
    const btn = document.getElementById('generate-architecture-btn');
    const originalText = btn.textContent;

    btn.disabled = true;
    btn.textContent = 'Generating...';

    try {
        // Get keywords for this site
        const keywordsResponse = await fetch(`/api/keywords/${SITE_ID}`);
        const keywordsData = await keywordsResponse.json();

        if (!keywordsData.success || !keywordsData.keywords || keywordsData.keywords.length === 0) {
            showError('Please generate keywords first');
            return;
        }

        const response = await fetch('/api/content/architecture', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                siteId: SITE_ID,
                keywords: keywordsData.keywords.map(kw => kw.keyword)
            })
        });

        const data = await response.json();

        if (data.success && data.architecture) {
            currentArchitecture = data.architecture;
            displayArchitecture(data.architecture);
        } else {
            showError(data.message || 'Failed to generate architecture');
        }
    } catch (error) {
        console.error('Error generating architecture:', error);
        showError('Failed to generate architecture. Please check your API key settings.');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

/**
 * Display architecture
 */
function displayArchitecture(architecture) {
    const section = document.getElementById('architecture-section');
    const list = document.getElementById('architecture-list');

    section.style.display = 'block';

    const html = `
        <div class="rsg-architecture">
            ${architecture.pages ? architecture.pages.map(page => `
                <div class="rsg-architecture-item">
                    <h3>${escapeHtml(page.title)}</h3>
                    <p><strong>URL:</strong> /${escapeHtml(page.slug)}</p>
                    <p><strong>Target Keyword:</strong> ${escapeHtml(page.target_keyword)}</p>
                    <p><strong>Intent:</strong> <span class="rsg-badge rsg-badge-${page.search_intent}">${page.search_intent}</span></p>
                    <p>${escapeHtml(page.description || '')}</p>
                </div>
            `).join('') : '<p>No pages in architecture</p>'}
        </div>
    `;

    list.innerHTML = html;

    // Setup generate all button
    const generateAllBtn = document.getElementById('generate-all-btn');
    generateAllBtn.onclick = handleGenerateAllPages;
}

/**
 * Handle generate all pages
 */
async function handleGenerateAllPages() {
    if (!currentArchitecture || !currentArchitecture.pages) {
        showError('No architecture available');
        return;
    }

    const btn = document.getElementById('generate-all-btn');
    const originalText = btn.textContent;

    btn.disabled = true;
    btn.textContent = 'Starting Generation...';

    try {
        const response = await fetch('/api/content/generate-all', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                siteId: SITE_ID,
                pages: currentArchitecture.pages
            })
        });

        const data = await response.json();

        if (data.success) {
            showSuccess('Page generation started! This may take several minutes...');
            startProgressPolling();
        } else {
            showError(data.message || 'Failed to start generation');
        }
    } catch (error) {
        console.error('Error generating pages:', error);
        showError('Failed to start page generation');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

/**
 * Start polling for generation progress
 */
function startProgressPolling() {
    const progressDiv = document.getElementById('generation-progress');
    progressDiv.style.display = 'block';

    const interval = setInterval(async () => {
        try {
            const response = await fetch('/api/content/progress');
            const data = await response.json();

            if (data.success && data.progress) {
                if (data.progress.completed) {
                    clearInterval(interval);
                    progressDiv.style.display = 'none';
                    showSuccess('All pages generated!');
                    await loadPages();
                }
            }
        } catch (error) {
            console.error('Error checking progress:', error);
        }
    }, 5000); // Check every 5 seconds
}

/**
 * Display pages
 */
function displayPages(pages) {
    const list = document.getElementById('pages-list');

    const html = `
        <div class="rsg-pages-grid">
            ${pages.map(page => `
                <div class="rsg-page-card">
                    <div class="rsg-page-header">
                        <h3>${escapeHtml(page.title)}</h3>
                        <span class="rsg-badge rsg-badge-${page.status}">${page.status}</span>
                    </div>
                    <p class="rsg-page-meta">
                        <strong>Keyword:</strong> ${escapeHtml(page.target_keyword)}<br>
                        <strong>Words:</strong> ${page.word_count || 0}<br>
                        <strong>SEO Score:</strong> ${page.seo_score || 0}/100
                    </p>
                    <div class="rsg-page-actions">
                        <button class="button-secondary" onclick="previewPage(${page.id})">
                            Preview
                        </button>
                        <button class="button-primary" onclick="exportPage(${page.id})">
                            Export
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    list.innerHTML = html;
}

/**
 * Show empty state
 */
function showEmptyState() {
    const list = document.getElementById('pages-list');
    list.innerHTML = `
        <div class="rsg-empty-state">
            <p>No pages generated yet. Generate site architecture to get started!</p>
        </div>
    `;
}

/**
 * Preview page
 */
async function previewPage(pageId) {
    try {
        const response = await fetch(`/api/content/page/${pageId}`);
        const data = await response.json();

        if (data.success && data.page) {
            currentPageId = pageId;
            const modal = document.getElementById('preview-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');

            modalTitle.textContent = data.page.title;
            modalBody.innerHTML = data.page.html || '<p>No content available</p>';

            modal.style.display = 'flex';
        } else {
            showError('Failed to load page');
        }
    } catch (error) {
        console.error('Error loading page:', error);
        showError('Failed to load page');
    }
}

/**
 * Close preview modal
 */
function closePreview() {
    const modal = document.getElementById('preview-modal');
    modal.style.display = 'none';
    currentPageId = null;
}

// Make closePreview global for onclick handler
window.closePreview = closePreview;
window.previewPage = previewPage;
window.exportPage = exportPage;

/**
 * Export page
 */
async function exportPage(pageId) {
    try {
        window.location.href = `/api/content/export/${pageId}`;
    } catch (error) {
        console.error('Error exporting page:', error);
        showError('Failed to export page');
    }
}

/**
 * Setup export button in modal
 */
document.getElementById('export-btn').addEventListener('click', () => {
    if (currentPageId) {
        exportPage(currentPageId);
    }
});

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
