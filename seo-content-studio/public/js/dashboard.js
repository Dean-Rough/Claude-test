/**
 * Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', async () => {
    await loadSites();
});

/**
 * Load all sites from API
 */
async function loadSites() {
    try {
        const response = await fetch('/api/sites');
        const data = await response.json();

        if (data.success && data.sites && data.sites.length > 0) {
            displaySites(data.sites);
        } else {
            // Keep empty state visible
            console.log('No sites found');
        }
    } catch (error) {
        console.error('Error loading sites:', error);
        showError('Failed to load sites. Please refresh the page.');
    }
}

/**
 * Display sites in grid
 */
function displaySites(sites) {
    const sitesList = document.getElementById('sites-list');
    if (!sitesList) return;

    // Clear empty state
    sitesList.innerHTML = '';

    // Create site cards
    sites.forEach(site => {
        const card = createSiteCard(site);
        sitesList.appendChild(card);
    });
}

/**
 * Create site card element
 */
function createSiteCard(site) {
    const card = document.createElement('div');
    card.className = 'rsg-site-card';

    const businessTypeEmoji = getBusinessTypeEmoji(site.business_type);
    const statusClass = site.status === 'active' ? 'status-active' : 'status-inactive';

    card.innerHTML = `
        <div class="rsg-site-card-header">
            <div class="rsg-site-icon">${businessTypeEmoji}</div>
            <span class="rsg-site-status ${statusClass}">${site.status}</span>
        </div>
        <h3>${escapeHtml(site.business_name)}</h3>
        <p class="rsg-site-meta">
            <span>${escapeHtml(site.business_type)}</span> •
            <span>${escapeHtml(site.location)}</span>
        </p>
        <div class="rsg-site-actions">
            <a href="/keyword-research/${site.id}" class="button-secondary">Keywords</a>
            <a href="/content-library/${site.id}" class="button-primary">Content</a>
        </div>
    `;

    return card;
}

/**
 * Get emoji for business type
 */
function getBusinessTypeEmoji(businessType) {
    const emojis = {
        'restaurant': '🍽️',
        'cafe': '☕',
        'plumber': '🔧',
        'electrician': '⚡',
        'dentist': '🦷',
        'lawyer': '⚖️',
        'salon': '💇',
        'gym': '💪',
        'retail': '🏪',
        'default': '🏢'
    };

    const type = businessType.toLowerCase();
    return emojis[type] || emojis['default'];
}

/**
 * Escape HTML to prevent XSS
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
    // Simple alert for now - could be enhanced with a toast notification
    const errorDiv = document.createElement('div');
    errorDiv.className = 'rsg-error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: var(--color-error); color: white; padding: 1rem; border-radius: 8px; z-index: 9999;';

    document.body.appendChild(errorDiv);

    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}
