<?php
/**
 * Site Profile Form View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rsg-site-profile-form">
    <h2>Create New Site Profile</h2>
    <p>Tell us about your business so we can generate perfectly tailored content.</p>

    <form id="rsg-site-profile-form" method="post">
        <?php wp_nonce_field('rsg_site_profile', 'rsg_site_profile_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="site_name">Business Name *</label>
                </th>
                <td>
                    <input type="text" id="site_name" name="site_name" class="regular-text" required>
                    <p class="description">The official name of the business</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="industry">Industry *</label>
                </th>
                <td>
                    <select id="industry" name="industry" class="regular-text" required>
                        <option value="">Select Industry</option>
                        <option value="food_drink">Food & Drink</option>
                        <option value="home_services">Home Services</option>
                        <option value="health_wellness">Health & Wellness</option>
                        <option value="professional_services">Professional Services</option>
                        <option value="beauty_personal_care">Beauty & Personal Care</option>
                        <option value="other">Other Local Business</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="business_type">Business Type *</label>
                </th>
                <td>
                    <select id="business_type" name="business_type" class="regular-text" required>
                        <option value="">Select Business Type</option>
                        <!-- Options will be populated by JavaScript based on industry -->
                    </select>
                    <p class="description">Choose the specific type of business</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="location">Location *</label>
                </th>
                <td>
                    <input type="text" id="location" name="location" class="regular-text" placeholder="Brooklyn, NY" required>
                    <p class="description">City and state for local SEO (e.g., "Brooklyn, NY")</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label>Service Area Type</label>
                </th>
                <td>
                    <label>
                        <input type="radio" name="service_area_type" value="single_location" checked>
                        Single location (customers come to you)
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="service_area_type" value="service_area">
                        Service area business (you go to customers)
                    </label>

                    <div id="service_area_radius_field" style="margin-top: 10px; display: none;">
                        <label for="service_area_radius">Service Radius:</label>
                        <select id="service_area_radius" name="service_area_radius">
                            <option value="5">5 miles</option>
                            <option value="10">10 miles</option>
                            <option value="15" selected>15 miles</option>
                            <option value="20">20 miles</option>
                            <option value="25">25 miles</option>
                            <option value="30">30 miles</option>
                            <option value="50">50 miles</option>
                        </select>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="target_audience">Target Audience *</label>
                </th>
                <td>
                    <textarea id="target_audience" name="target_audience" rows="3" class="large-text" required placeholder="e.g., Homeowners in Brooklyn dealing with plumbing emergencies and routine maintenance"></textarea>
                    <p class="description">Who are your ideal customers? Be specific.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="brand_voice">Brand Voice *</label>
                </th>
                <td>
                    <select id="brand_voice" name="brand_voice" class="regular-text" required>
                        <option value="">Select Voice</option>
                        <option value="professional_trustworthy">Professional & Trustworthy</option>
                        <option value="friendly_approachable">Friendly & Approachable</option>
                        <option value="expert_authoritative">Expert & Authoritative</option>
                        <option value="casual_down_to_earth">Casual & Down-to-earth</option>
                        <option value="luxury_premium">Luxury & Premium</option>
                    </select>
                    <p class="description">How should the content sound?</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="unique_selling_point">What Makes You Different? *</label>
                </th>
                <td>
                    <textarea id="unique_selling_point" name="unique_selling_point" rows="3" class="large-text" required placeholder="e.g., 24/7 emergency service, 30+ years experience, family-owned, flat-rate pricing"></textarea>
                    <p class="description">Your unique selling points and what sets you apart from competitors</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="key_services">Key Services/Specialties *</label>
                </th>
                <td>
                    <textarea id="key_services" name="key_services" rows="3" class="large-text" required placeholder="e.g., emergency plumbing, drain cleaning, water heater repair, bathroom remodeling"></textarea>
                    <p class="description">List your main services or specialties (comma-separated)</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="certifications">Certifications/Credentials</label>
                </th>
                <td>
                    <input type="text" id="certifications" name="certifications" class="large-text" placeholder="e.g., Licensed & Insured, Master Plumber #12345, BBB A+ Rating">
                    <p class="description">Licenses, certifications, or credentials (optional but recommended)</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="writing_sample">Writing Sample</label>
                </th>
                <td>
                    <textarea id="writing_sample" name="writing_sample" rows="5" class="large-text" placeholder="Paste a paragraph that captures your brand voice..."></textarea>
                    <p class="description">Optional: Paste existing content from your website or marketing materials to help Claude match your voice</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary button-large" id="save-site-profile">
                Save Profile & Continue
            </button>
            <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator'); ?>" class="button button-large">
                Cancel
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Business type options by industry
    const businessTypes = {
        'food_drink': [
            {value: 'restaurant', label: 'Restaurant'},
            {value: 'bar', label: 'Bar'},
            {value: 'cafe', label: 'Cafe/Coffee Shop'},
            {value: 'bakery', label: 'Bakery'},
            {value: 'pizzeria', label: 'Pizzeria'},
            {value: 'food_other', label: 'Other Food Business'}
        ],
        'home_services': [
            {value: 'plumber', label: 'Plumber'},
            {value: 'electrician', label: 'Electrician'},
            {value: 'hvac', label: 'HVAC'},
            {value: 'landscaping', label: 'Landscaping'},
            {value: 'cleaning', label: 'Cleaning Service'},
            {value: 'handyman', label: 'Handyman'},
            {value: 'roofing', label: 'Roofing'},
            {value: 'painting', label: 'Painting'},
            {value: 'home_other', label: 'Other Home Service'}
        ],
        'health_wellness': [
            {value: 'dentist', label: 'Dentist'},
            {value: 'chiropractor', label: 'Chiropractor'},
            {value: 'yoga_studio', label: 'Yoga Studio'},
            {value: 'gym', label: 'Gym/Fitness'},
            {value: 'massage', label: 'Massage Therapy'},
            {value: 'health_other', label: 'Other Health/Wellness'}
        ],
        'professional_services': [
            {value: 'accountant', label: 'Accountant/CPA'},
            {value: 'lawyer', label: 'Lawyer/Attorney'},
            {value: 'real_estate', label: 'Real Estate Agent'},
            {value: 'insurance', label: 'Insurance Agent'},
            {value: 'consultant', label: 'Consultant'},
            {value: 'prof_other', label: 'Other Professional Service'}
        ],
        'beauty_personal_care': [
            {value: 'hair_salon', label: 'Hair Salon'},
            {value: 'barber', label: 'Barber Shop'},
            {value: 'nail_salon', label: 'Nail Salon'},
            {value: 'spa', label: 'Spa'},
            {value: 'beauty_other', label: 'Other Beauty Service'}
        ],
        'other': [
            {value: 'retail', label: 'Retail Store'},
            {value: 'auto_repair', label: 'Auto Repair'},
            {value: 'pet_services', label: 'Pet Services'},
            {value: 'general', label: 'General Local Business'}
        ]
    };

    // Update business type dropdown when industry changes
    $('#industry').on('change', function() {
        const industry = $(this).val();
        const $businessType = $('#business_type');

        $businessType.empty().append('<option value="">Select Business Type</option>');

        if (industry && businessTypes[industry]) {
            businessTypes[industry].forEach(function(type) {
                $businessType.append(
                    $('<option></option>').val(type.value).text(type.label)
                );
            });
        }
    });

    // Toggle service area radius field
    $('input[name="service_area_type"]').on('change', function() {
        if ($(this).val() === 'service_area') {
            $('#service_area_radius_field').show();
        } else {
            $('#service_area_radius_field').hide();
        }
    });

    // Handle form submission
    $('#rsg-site-profile-form').on('submit', function(e) {
        e.preventDefault();

        const $button = $('#save-site-profile');
        $button.prop('disabled', true).text('Saving...');

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_save_site_profile',
                nonce: rsgAdmin.nonce,
                site_name: $('#site_name').val(),
                industry: $('#industry').val(),
                business_type: $('#business_type').val(),
                location: $('#location').val(),
                service_area_type: $('input[name="service_area_type"]:checked').val(),
                service_area_radius: $('#service_area_radius').val(),
                target_audience: $('#target_audience').val(),
                brand_voice: $('#brand_voice').val(),
                unique_selling_point: $('#unique_selling_point').val(),
                key_services: $('#key_services').val(),
                certifications: $('#certifications').val(),
                writing_sample: $('#writing_sample').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Site profile saved successfully!');
                    window.location.href = '<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=generate&site_id='); ?>' + response.data.site_id;
                } else {
                    alert('Error: ' + response.data.message);
                    $button.prop('disabled', false).text('Save Profile & Continue');
                }
            },
            error: function() {
                alert('Error saving site profile. Please try again.');
                $button.prop('disabled', false).text('Save Profile & Continue');
            }
        });
    });
});
</script>
