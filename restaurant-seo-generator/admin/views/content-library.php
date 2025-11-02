<?php
/**
 * Content Library View
 */

if (!defined('ABSPATH')) {
    exit;
}

$db = new RSG_Database();
$site = $db->get_site_profile($site_id);
$pages = $db->get_site_pages($site_id);

if (!$site) {
    echo '<p>Site not found.</p>';
    return;
}
?>

<div class="rsg-content-library">
    <h2>Content Library: <?php echo esc_html($site['site_name']); ?></h2>

    <?php if (empty($pages)): ?>
        <div class="rsg-empty-state">
            <p>No pages generated yet for this site.</p>
            <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=generate&site_id=' . $site_id); ?>" class="button button-primary">
                Generate Content
            </a>
        </div>
    <?php else: ?>
        <div class="rsg-library-stats">
            <?php
            $stats = $db->get_site_statistics($site_id);
            ?>
            <div class="stat-box">
                <span class="stat-number"><?php echo $stats['total_pages']; ?></span>
                <span class="stat-label">Total Pages</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo number_format($stats['total_words']); ?></span>
                <span class="stat-label">Total Words</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo $stats['ready']; ?></span>
                <span class="stat-label">Ready to Use</span>
            </div>
        </div>

        <div class="rsg-pages-list">
            <?php foreach ($pages as $page): ?>
                <div class="rsg-page-card">
                    <div class="rsg-page-header">
                        <h3><?php echo esc_html($page['title']); ?></h3>
                        <span class="rsg-page-status <?php echo $page['status']; ?>">
                            <?php echo ucfirst($page['status']); ?>
                        </span>
                    </div>

                    <div class="rsg-page-meta">
                        <span class="rsg-meta-item">
                            <strong>Keyword:</strong> <?php echo esc_html($page['primary_keyword']); ?>
                        </span>
                        <span class="rsg-meta-item">
                            <strong>Intent:</strong>
                            <span class="rsg-intent-badge <?php echo 'intent-' . $page['intent']; ?>">
                                <?php echo $page['intent']; ?>
                            </span>
                        </span>
                        <span class="rsg-meta-item">
                            <strong>Words:</strong> <?php echo number_format($page['word_count']); ?>
                        </span>
                        <span class="rsg-meta-item">
                            <strong>SEO Score:</strong> <?php echo $page['seo_score']; ?>/100
                        </span>
                    </div>

                    <div class="rsg-page-preview">
                        <strong>Meta Title:</strong> <?php echo esc_html($page['meta_title']); ?><br>
                        <strong>Meta Description:</strong> <?php echo esc_html($page['meta_description']); ?>
                    </div>

                    <div class="rsg-page-actions">
                        <button type="button" class="button rsg-preview-page" data-page-id="<?php echo $page['id']; ?>">
                            👁 Preview
                        </button>
                        <button type="button" class="button button-primary rsg-copy-html" data-page-id="<?php echo $page['id']; ?>">
                            📋 Copy HTML
                        </button>
                        <button type="button" class="button rsg-regenerate-page" data-page-id="<?php echo $page['id']; ?>">
                            🔄 Regenerate
                        </button>
                        <button type="button" class="button button-link rsg-delete-page" data-page-id="<?php echo $page['id']; ?>">
                            Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="rsg-export-actions">
            <button type="button" class="button button-large" id="export-all-zip">
                📦 Export All as ZIP
            </button>
            <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=generate&site_id=' . $site_id); ?>" class="button button-primary button-large">
                + Generate More Pages
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Preview Modal -->
<div id="rsg-preview-modal" class="rsg-modal" style="display: none;">
    <div class="rsg-modal-content">
        <div class="rsg-modal-header">
            <h3 id="preview-title">Page Preview</h3>
            <button class="rsg-modal-close">&times;</button>
        </div>
        <div class="rsg-modal-body">
            <div id="preview-meta"></div>
            <div id="preview-content"></div>
        </div>
        <div class="rsg-modal-footer">
            <button type="button" class="button button-primary rsg-copy-from-preview">Copy HTML</button>
            <button type="button" class="button rsg-modal-close">Close</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const pages = <?php echo json_encode($pages); ?>;

    // Preview page
    $('.rsg-preview-page').on('click', function() {
        const pageId = $(this).data('page-id');
        const page = pages.find(p => p.id == pageId);

        if (!page) return;

        $('#preview-title').text(page.title);
        $('#preview-meta').html(
            '<p><strong>Meta Title:</strong> ' + page.meta_title + '</p>' +
            '<p><strong>Meta Description:</strong> ' + page.meta_description + '</p>' +
            '<p><strong>URL Slug:</strong> /' + page.slug + '</p>' +
            '<p><strong>Word Count:</strong> ' + page.word_count + ' | <strong>SEO Score:</strong> ' + page.seo_score + '/100</p>'
        );
        $('#preview-content').html('<div class="rsg-content-preview">' + page.content_html + '</div>');
        $('#rsg-preview-modal').fadeIn();
        $('.rsg-copy-from-preview').data('page-id', pageId);
    });

    // Close modal
    $('.rsg-modal-close').on('click', function() {
        $('#rsg-preview-modal').fadeOut();
    });

    // Copy HTML
    $('.rsg-copy-html, .rsg-copy-from-preview').on('click', function() {
        const pageId = $(this).data('page-id');
        const page = pages.find(p => p.id == pageId);

        if (!page) return;

        // Build complete HTML with meta and schema
        let html = '<!-- SEO Meta Tags -->\n';
        html += '<!-- Title: ' + page.meta_title + ' -->\n';
        html += '<!-- Description: ' + page.meta_description + ' -->\n';
        html += '<!-- URL Slug: /' + page.slug + ' -->\n\n';

        if (page.schema_markup) {
            html += '<!-- Schema Markup (add to page head) -->\n';
            html += '<script type="application/ld+json">\n';
            html += JSON.stringify(JSON.parse(page.schema_markup), null, 2);
            html += '\n<\/script>\n\n';
        }

        html += '<!-- Page Content -->\n';
        html += page.content_html;

        // Copy to clipboard
        navigator.clipboard.writeText(html).then(function() {
            alert('✓ HTML copied to clipboard!\n\nYou can now paste it into your page builder.');
            if ($(this).hasClass('rsg-copy-from-preview')) {
                $('#rsg-preview-modal').fadeOut();
            }
        }.bind(this), function() {
            alert('Failed to copy to clipboard. Please try again.');
        });
    });

    // Regenerate page
    $('.rsg-regenerate-page').on('click', function() {
        if (!confirm('Regenerate this page? This will replace the existing content.')) {
            return;
        }

        const pageId = $(this).data('page-id');
        const $button = $(this);

        $button.prop('disabled', true).text('Regenerating...');

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_regenerate_page',
                nonce: rsgAdmin.nonce,
                page_id: pageId
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ Page regenerated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    $button.prop('disabled', false).text('🔄 Regenerate');
                }
            }
        });
    });

    // Delete page
    $('.rsg-delete-page').on('click', function() {
        if (!confirm('Delete this page? This cannot be undone.')) {
            return;
        }

        const pageId = $(this).data('page-id');

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_delete_page',
                nonce: rsgAdmin.nonce,
                page_id: pageId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });

    // Export all as ZIP
    $('#export-all-zip').on('click', function() {
        alert('ZIP export feature coming soon!\n\nFor now, use the Copy HTML button on each page.');
    });
});
</script>
