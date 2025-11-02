/**
 * Create Site Page JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    setupEventListeners();
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Form submission
    document.getElementById('create-site-form').addEventListener('submit', handleCreateSite);

    // Service area type change
    document.getElementById('service_area_type').addEventListener('change', handleServiceAreaChange);
}

/**
 * Handle service area type change
 */
function handleServiceAreaChange(e) {
    const radiusGroup = document.getElementById('radius-group');
    const serviceAreaType = e.target.value;

    if (serviceAreaType === 'service') {
        radiusGroup.style.display = 'block';
    } else {
        radiusGroup.style.display = 'none';
        document.getElementById('service_radius').value = '';
    }
}

/**
 * Handle create site form submission
 */
async function handleCreateSite(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.textContent;

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating Site...';

    try {
        // Collect form data
        const formData = {
            business_name: document.getElementById('business_name').value.trim(),
            industry: document.getElementById('industry').value,
            business_type: document.getElementById('business_type').value.trim(),
            location: document.getElementById('location').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            website: document.getElementById('website').value.trim(),
            service_area_type: document.getElementById('service_area_type').value,
            service_radius: parseInt(document.getElementById('service_radius').value) || 0,
            description: document.getElementById('description').value.trim(),
            brand_voice: document.getElementById('brand_voice').value,
            writing_sample: document.getElementById('writing_sample').value.trim(),
            status: 'active'
        };

        // Validate required fields
        if (!formData.business_name || !formData.industry || !formData.business_type || !formData.location) {
            showMessage('Please fill in all required fields', 'error');
            return;
        }

        // Submit to API
        const response = await fetch('/api/sites', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            showMessage('Site created successfully! Redirecting...', 'success');

            // Redirect to keyword research page
            setTimeout(() => {
                window.location.href = `/keyword-research/${data.site_id}`;
            }, 1500);
        } else {
            showMessage(data.message || 'Failed to create site', 'error');
        }
    } catch (error) {
        console.error('Error creating site:', error);
        showMessage('Failed to create site. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

/**
 * Show form message
 */
function showMessage(message, type = 'info') {
    const messageDiv = document.getElementById('form-message');
    messageDiv.textContent = message;
    messageDiv.className = `rsg-message rsg-message-${type}`;
    messageDiv.style.display = 'block';

    // Scroll to message
    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
