<?php
/**
 * Keyword Research & Content Generation View
 */

if (!defined('ABSPATH')) {
    exit;
}

$db = new RSG_Database();
$site = $db->get_site_profile($site_id);

if (!$site) {
    echo '<p>Site not found.</p>';
    return;
}
?>

<div class="rsg-keyword-research">
    <h2>Generate Content: <?php echo esc_html($site['site_name']); ?></h2>

    <div class="rsg-tabs">
        <button class="rsg-tab-button active" data-tab="keywords">1. Keywords</button>
        <button class="rsg-tab-button" data-tab="competitors">2. Competitors (Optional)</button>
        <button class="rsg-tab-button" data-tab="architecture">3. Site Plan</button>
        <button class="rsg-tab-button" data-tab="generate">4. Generate</button>
    </div>

    <!-- Tab 1: Keyword Research -->
    <div class="rsg-tab-content active" id="tab-keywords">
        <h3>Keyword Research</h3>
        <p>Enter seed keywords related to your business. We'll expand them automatically.</p>

        <table class="form-table">
            <tr>
                <th><label for="seed_keywords">Seed Keywords</label></th>
                <td>
                    <textarea id="seed_keywords" rows="5" class="large-text" placeholder="Enter one keyword per line:&#10;pizza brooklyn&#10;best pizza near me&#10;italian restaurant"></textarea>
                    <p class="description">One keyword per line. We'll generate variations automatically.</p>
                </td>
            </tr>
            <tr>
                <th><label>Auto-expand options</label></th>
                <td>
                    <label>
                        <input type="checkbox" id="expand_local" checked> Add local variations (using your location)
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" id="expand_questions" checked> Add question keywords (how, what, where, etc.)
                    </label>
                </td>
            </tr>
        </table>

        <p>
            <button type="button" id="generate-keywords" class="button button-primary">Generate Keywords</button>
        </p>

        <div id="keywords-results" style="display: none;">
            <h4>Generated Keywords (<span id="keyword-count">0</span>)</h4>
            <div id="keywords-list"></div>
            <p>
                <button type="button" id="classify-keywords" class="button button-primary">Classify Intent & Continue</button>
            </p>
        </div>

        <div id="classified-keywords" style="display: none;">
            <h4>Keywords with Intent Classification</h4>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="5%">
                            <input type="checkbox" id="select-all-keywords">
                        </th>
                        <th width="60%">Keyword</th>
                        <th width="20%">Intent</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody id="classified-keywords-list"></tbody>
            </table>
            <p>
                <button type="button" id="proceed-to-competitors" class="button button-primary">Continue to Competitors →</button>
                <button type="button" id="skip-to-architecture" class="button">Skip to Site Plan →</button>
            </p>
        </div>
    </div>

    <!-- Tab 2: Competitor Analysis -->
    <div class="rsg-tab-content" id="tab-competitors">
        <h3>Competitor Analysis (Optional)</h3>
        <p>Analyze 2-5 competitor websites to understand what's working in your niche.</p>

        <table class="form-table">
            <tr>
                <th><label for="competitor_urls">Competitor URLs</label></th>
                <td>
                    <textarea id="competitor_urls" rows="5" class="large-text" placeholder="https://competitor1.com&#10;https://competitor2.com&#10;https://competitor3.com"></textarea>
                    <p class="description">Enter 2-5 competitor URLs, one per line</p>
                </td>
            </tr>
        </table>

        <p>
            <button type="button" id="analyze-competitors" class="button button-primary">Analyze Competitors</button>
            <button type="button" id="skip-competitors" class="button">Skip This Step</button>
        </p>

        <div id="competitor-results" style="display: none;">
            <h4>Analysis Results</h4>
            <div id="competitor-summary"></div>
            <p>
                <button type="button" id="proceed-to-architecture" class="button button-primary">Continue to Site Plan →</button>
            </p>
        </div>
    </div>

    <!-- Tab 3: Site Architecture -->
    <div class="rsg-tab-content" id="tab-architecture">
        <h3>Site Architecture</h3>
        <p>Based on your keywords and competitor analysis, here's the recommended site structure:</p>

        <div id="architecture-loading" style="display: none;">
            <p><span class="spinner is-active"></span> Generating site architecture...</p>
        </div>

        <div id="architecture-results" style="display: none;">
            <div id="architecture-summary"></div>
            <div id="architecture-pages"></div>
            <p>
                <button type="button" id="edit-architecture" class="button">Edit Structure</button>
                <button type="button" id="proceed-to-generate" class="button button-primary">Generate Content →</button>
            </p>
        </div>
    </div>

    <!-- Tab 4: Generate Content -->
    <div class="rsg-tab-content" id="tab-generate">
        <h3>Generate Content</h3>
        <p>Ready to generate your content? This will create all pages based on your site plan.</p>

        <div id="generation-summary" style="display: none;">
            <table class="form-table">
                <tr>
                    <th>Total Pages:</th>
                    <td id="total-pages"></td>
                </tr>
                <tr>
                    <th>Total Words:</th>
                    <td id="total-words"></td>
                </tr>
                <tr>
                    <th>Est. Time:</th>
                    <td id="est-time"></td>
                </tr>
                <tr>
                    <th>Est. Cost:</th>
                    <td id="est-cost"></td>
                </tr>
            </table>

            <p>
                <button type="button" id="start-generation" class="button button-primary button-large">Start Generating Content</button>
            </p>
        </div>

        <div id="generation-progress" style="display: none;">
            <h4>Generating Content...</h4>
            <div class="rsg-progress-bar">
                <div class="rsg-progress-fill" id="progress-bar"></div>
            </div>
            <p id="progress-text">Preparing...</p>
            <div id="progress-details"></div>
        </div>

        <div id="generation-complete" style="display: none;">
            <h4>✓ Content Generation Complete!</h4>
            <div id="generation-results"></div>
            <p>
                <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=library&site_id=' . $site_id); ?>" class="button button-primary button-large">
                    View Generated Pages →
                </a>
            </p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let keywords = [];
    let classifiedKeywords = [];
    let competitorData = null;
    let siteArchitecture = null;
    const siteId = <?php echo $site_id; ?>;
    const siteName = '<?php echo esc_js($site['site_name']); ?>';
    const location = '<?php echo esc_js($site['location']); ?>';

    // Tab switching
    $('.rsg-tab-button').on('click', function() {
        const tab = $(this).data('tab');
        $('.rsg-tab-button').removeClass('active');
        $('.rsg-tab-content').removeClass('active');
        $(this).addClass('active');
        $('#tab-' + tab).addClass('active');
    });

    // Generate keywords
    $('#generate-keywords').on('click', function() {
        const seedKeywords = $('#seed_keywords').val().split('\n').filter(k => k.trim());

        if (seedKeywords.length === 0) {
            alert('Please enter at least one seed keyword');
            return;
        }

        $(this).prop('disabled', true).text('Generating...');

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_generate_keywords',
                nonce: rsgAdmin.nonce,
                seed_keywords: seedKeywords,
                location: location,
                auto_expand: true
            },
            success: function(response) {
                if (response.success) {
                    keywords = response.data.keywords;
                    displayKeywords(keywords);
                    $('#keywords-results').show();
                    $('#generate-keywords').prop('disabled', false).text('Generate Keywords');
                } else {
                    alert('Error: ' + response.data.message);
                    $('#generate-keywords').prop('disabled', false).text('Generate Keywords');
                }
            }
        });
    });

    function displayKeywords(kws) {
        $('#keyword-count').text(kws.length);
        let html = '<div class="rsg-keywords-grid">';
        kws.forEach(function(kw) {
            html += '<div class="rsg-keyword-item">' + kw + '</div>';
        });
        html += '</div>';
        $('#keywords-list').html(html);
    }

    // Classify keywords
    $('#classify-keywords').on('click', function() {
        $(this).prop('disabled', true).text('Classifying...');

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_classify_intent',
                nonce: rsgAdmin.nonce,
                keywords: keywords,
                site_name: siteName
            },
            success: function(response) {
                if (response.success) {
                    classifiedKeywords = response.data.keywords;
                    displayClassifiedKeywords(classifiedKeywords);
                    $('#classified-keywords').show();
                    $('#classify-keywords').prop('disabled', false).text('Classify Intent & Continue');
                } else {
                    alert('Error: ' + response.data.message);
                    $('#classify-keywords').prop('disabled', false).text('Classify Intent & Continue');
                }
            }
        });
    });

    function displayClassifiedKeywords(kws) {
        let html = '';
        kws.forEach(function(kw, index) {
            const intentColor = getIntentColor(kw.intent);
            html += '<tr>';
            html += '<td><input type="checkbox" class="keyword-checkbox" data-index="' + index + '" checked></td>';
            html += '<td>' + kw.keyword + '</td>';
            html += '<td><span class="rsg-intent-badge ' + intentColor + '">' + kw.intent + '</span></td>';
            html += '<td><button type="button" class="button button-small remove-keyword" data-index="' + index + '">Remove</button></td>';
            html += '</tr>';
        });
        $('#classified-keywords-list').html(html);
    }

    function getIntentColor(intent) {
        switch(intent) {
            case 'transactional': return 'intent-transactional';
            case 'commercial': return 'intent-commercial';
            case 'navigational': return 'intent-navigational';
            default: return 'intent-informational';
        }
    }

    // Remove keyword
    $(document).on('click', '.remove-keyword', function() {
        const index = $(this).data('index');
        classifiedKeywords.splice(index, 1);
        displayClassifiedKeywords(classifiedKeywords);
    });

    // Select all keywords
    $('#select-all-keywords').on('change', function() {
        $('.keyword-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Proceed to competitors
    $('#proceed-to-competitors, #skip-to-architecture').on('click', function() {
        const selectedKeywords = getSelectedKeywords();
        if (selectedKeywords.length === 0) {
            alert('Please select at least one keyword');
            return;
        }
        classifiedKeywords = selectedKeywords;

        if ($(this).attr('id') === 'skip-to-architecture') {
            generateArchitecture();
        } else {
            $('.rsg-tab-button[data-tab="competitors"]').click();
        }
    });

    function getSelectedKeywords() {
        const selected = [];
        $('.keyword-checkbox:checked').each(function() {
            const index = $(this).data('index');
            selected.push(classifiedKeywords[index]);
        });
        return selected;
    }

    // Analyze competitors
    $('#analyze-competitors, #skip-competitors').on('click', function() {
        if ($(this).attr('id') === 'skip-competitors') {
            generateArchitecture();
            return;
        }

        const urls = $('#competitor_urls').val().split('\n').filter(u => u.trim());
        if (urls.length === 0) {
            alert('Please enter at least one competitor URL');
            return;
        }

        $(this).prop('disabled', true).text('Analyzing...');
        let completed = 0;

        urls.forEach(function(url) {
            $.ajax({
                url: rsgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rsg_analyze_competitor',
                    nonce: rsgAdmin.nonce,
                    url: url.trim(),
                    site_id: siteId
                },
                success: function(response) {
                    completed++;
                    if (completed === urls.length) {
                        $('#competitor-results').show();
                        $('#competitor-summary').html('<p>✓ Analyzed ' + urls.length + ' competitors successfully!</p>');
                        $('#analyze-competitors').prop('disabled', false).text('Analyze Competitors');
                        competitorData = true;
                    }
                }
            });
        });
    });

    // Proceed to architecture
    $('#proceed-to-architecture').on('click', function() {
        generateArchitecture();
    });

    function generateArchitecture() {
        $('.rsg-tab-button[data-tab="architecture"]').click();
        $('#architecture-loading').show();

        const selectedKeywords = getSelectedKeywords();

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_generate_architecture',
                nonce: rsgAdmin.nonce,
                site_id: siteId,
                keywords: JSON.stringify(selectedKeywords),
                competitor_urls: $('#competitor_urls').val() ? JSON.stringify($('#competitor_urls').val().split('\n').filter(u => u.trim())) : '[]'
            },
            success: function(response) {
                $('#architecture-loading').hide();
                if (response.success) {
                    siteArchitecture = response.data;
                    displayArchitecture(siteArchitecture);
                    $('#architecture-results').show();
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    }

    function displayArchitecture(arch) {
        const pages = arch.pages;
        const totalWords = pages.reduce((sum, p) => sum + (p.target_words || 0), 0);

        let html = '<div class="rsg-architecture-summary">';
        html += '<h4>Site Plan: ' + pages.length + ' Pages, ~' + totalWords.toLocaleString() + ' Words</h4>';
        html += '</div>';
        $('#architecture-summary').html(html);

        html = '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        html += '<th>Page</th><th>Primary Keyword</th><th>Intent</th><th>Words</th><th>Priority</th>';
        html += '</tr></thead><tbody>';

        pages.forEach(function(page) {
            html += '<tr>';
            html += '<td><strong>' + page.title + '</strong></td>';
            html += '<td>' + page.primary_keyword + '</td>';
            html += '<td><span class="rsg-intent-badge ' + getIntentColor(page.intent) + '">' + page.intent + '</span></td>';
            html += '<td>' + (page.target_words || 0) + '</td>';
            html += '<td>' + (page.priority || 'medium') + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#architecture-pages').html(html);
    }

    // Proceed to generate
    $('#proceed-to-generate').on('click', function() {
        $('.rsg-tab-button[data-tab="generate"]').click();
        showGenerationSummary();
    });

    function showGenerationSummary() {
        const pages = siteArchitecture.pages;
        const totalWords = pages.reduce((sum, p) => sum + (p.target_words || 0), 0);
        const estTime = Math.ceil(pages.length * 2.5);
        const estCost = (pages.length * 0.20).toFixed(2);

        $('#total-pages').text(pages.length);
        $('#total-words').text(totalWords.toLocaleString() + ' words');
        $('#est-time').text(estTime + ' minutes');
        $('#est-cost').text('$' + estCost);
        $('#generation-summary').show();
    }

    // Start generation
    $('#start-generation').on('click', function() {
        $(this).prop('disabled', true);
        $('#generation-summary').hide();
        $('#generation-progress').show();

        $.ajax({
            url: rsgAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rsg_generate_content',
                nonce: rsgAdmin.nonce,
                site_id: siteId,
                pages: JSON.stringify(siteArchitecture.pages)
            },
            success: function(response) {
                $('#generation-progress').hide();
                if (response.success) {
                    displayGenerationResults(response.data);
                    $('#generation-complete').show();
                } else {
                    alert('Error: ' + response.data.message);
                    $('#start-generation').prop('disabled', false);
                    $('#generation-summary').show();
                }
            }
        });

        // Poll for progress
        pollProgress();
    });

    function pollProgress() {
        const interval = setInterval(function() {
            $.ajax({
                url: rsgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rsg_get_generation_progress',
                    nonce: rsgAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const progress = response.data;
                        if (progress.complete) {
                            clearInterval(interval);
                        } else {
                            updateProgress(progress);
                        }
                    }
                }
            });
        }, 2000);
    }

    function updateProgress(progress) {
        const percent = (progress.current / progress.total) * 100;
        $('#progress-bar').css('width', percent + '%');
        $('#progress-text').text('Generating page ' + progress.current + ' of ' + progress.total + ': ' + progress.page);
    }

    function displayGenerationResults(results) {
        let html = '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        html += '<th>Page</th><th>Status</th><th>Words</th>';
        html += '</tr></thead><tbody>';

        results.forEach(function(result) {
            html += '<tr>';
            html += '<td>' + result.page + '</td>';
            html += '<td>' + (result.status === 'success' ? '✓ Success' : '✗ Error') + '</td>';
            html += '<td>' + (result.word_count || 0) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#generation-results').html(html);
    }
});
</script>
