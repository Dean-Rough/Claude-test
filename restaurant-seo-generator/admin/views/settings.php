<?php
/**
 * Settings Page View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap rsg-settings">
    <h1>SEO Generator Settings</h1>

    <form method="post" id="rsg-settings-form">
        <?php wp_nonce_field('rsg_settings', 'rsg_settings_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="rsg_claude_api_key">Claude API Key *</label>
                </th>
                <td>
                    <input type="password"
                           id="rsg_claude_api_key"
                           name="rsg_claude_api_key"
                           value="<?php echo esc_attr($api_key); ?>"
                           class="regular-text"
                           placeholder="sk-ant-api03-...">
                    <button type="button" id="toggle-api-key" class="button button-small">Show</button>
                    <p class="description">
                        Get your API key from <a href="https://console.anthropic.com" target="_blank">console.anthropic.com</a>
                    </p>

                    <div id="api-key-status" style="margin-top: 10px;">
                        <?php if (!empty($api_key)): ?>
                            <span class="rsg-status-indicator">● API Key Configured</span>
                        <?php else: ?>
                            <span class="rsg-status-indicator error">● No API Key Set</span>
                        <?php endif; ?>
                    </div>

                    <p>
                        <button type="button" id="test-api-key" class="button">Test Connection</button>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rsg_default_model">Claude Model</label>
                </th>
                <td>
                    <select id="rsg_default_model" name="rsg_default_model" class="regular-text">
                        <option value="claude-sonnet-4-20250514" <?php selected($model, 'claude-sonnet-4-20250514'); ?>>
                            Claude Sonnet 4 (Recommended)
                        </option>
                        <option value="claude-3-5-sonnet-20241022" <?php selected($model, 'claude-3-5-sonnet-20241022'); ?>>
                            Claude 3.5 Sonnet
                        </option>
                        <option value="claude-3-opus-20240229" <?php selected($model, 'claude-3-opus-20240229'); ?>>
                            Claude 3 Opus (Most Powerful)
                        </option>
                    </select>
                    <p class="description">
                        Select which Claude model to use for content generation. Sonnet 4 offers the best balance of quality and cost.
                    </p>
                </td>
            </tr>
        </table>

        <h2>Usage & Pricing</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Estimated Cost Per Site</th>
                <td>
                    <p><strong>$2-3 per 10-page site</strong></p>
                    <ul>
                        <li>Keyword research: ~$0.10</li>
                        <li>Architecture generation: ~$0.05</li>
                        <li>Content generation: ~$0.20 per page</li>
                    </ul>
                    <p class="description">
                        Actual costs depend on content length and complexity. Check your
                        <a href="https://console.anthropic.com" target="_blank">Anthropic Console</a> for usage details.
                    </p>
                </td>
            </tr>
        </table>

        <h2>About This Plugin</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Version</th>
                <td><?php echo RSG_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row">Description</th>
                <td>
                    Generate SEO-optimized content for local business websites using Claude AI.
                    <br>Simple workflow: Keyword research → Content generation → HTML output.
                </td>
            </tr>
            <tr>
                <th scope="row">Support</th>
                <td>
                    <a href="https://github.com/yourusername/restaurant-seo-generator" target="_blank">GitHub Repository</a> |
                    <a href="https://github.com/yourusername/restaurant-seo-generator/issues" target="_blank">Report Issue</a>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="rsg_save_settings" class="button button-primary">
                Save Settings
            </button>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle API key visibility
    $('#toggle-api-key').on('click', function() {
        const $input = $('#rsg_claude_api_key');
        const type = $input.attr('type');

        if (type === 'password') {
            $input.attr('type', 'text');
            $(this).text('Hide');
        } else {
            $input.attr('type', 'password');
            $(this).text('Show');
        }
    });

    // Test API key
    $('#test-api-key').on('click', function() {
        const apiKey = $('#rsg_claude_api_key').val();

        if (!apiKey) {
            alert('Please enter an API key first');
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('Testing...');

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_test_api_key',
                nonce: rsgAdmin.nonce,
                api_key: apiKey
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ API key is valid and working!\n\n' + response.data.message);
                    $('#api-key-status').html('<span class="rsg-status-indicator success">● API Key Valid</span>');
                } else {
                    alert('✗ API key test failed:\n\n' + response.data.message);
                    $('#api-key-status').html('<span class="rsg-status-indicator error">● API Key Invalid</span>');
                }
                $button.prop('disabled', false).text('Test Connection');
            },
            error: function() {
                alert('✗ Connection test failed. Please check your internet connection and try again.');
                $button.prop('disabled', false).text('Test Connection');
            }
        });
    });
});
</script>
