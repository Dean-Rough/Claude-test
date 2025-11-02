/**
 * Content Generator Service
 * Orchestrates content generation using Claude API
 * Ported from WordPress plugin PHP class
 */

const Database = require('../models/Database');
const ClaudeAPI = require('./ClaudeAPI');
const SchemaGenerator = require('./SchemaGenerator');
const path = require('path');
const fs = require('fs');

class ContentGenerator {
    constructor(apiKey = null) {
        this.db = new Database();
        this.claudeAPI = new ClaudeAPI(apiKey);
        this.schemaGenerator = new SchemaGenerator();
        this.progressData = {
            current: 0,
            total: 0,
            page: '',
            status: 'idle'
        };
    }

    /**
     * Set API key dynamically
     */
    setApiKey(apiKey) {
        this.claudeAPI.apiKey = apiKey;
    }

    /**
     * Generate site architecture from keywords
     */
    async generateSiteArchitecture(siteId, keywords, competitorUrls = []) {
        const siteProfile = this.db.getSiteProfile(siteId);

        if (!siteProfile) {
            throw new Error('Site profile not found');
        }

        // Get competitor insights if URLs provided
        let competitorSummary = null;
        if (competitorUrls && competitorUrls.length > 0) {
            competitorSummary = this.db.getCompetitorSummary(siteId);
        }

        // Generate architecture via Claude
        const architecture = await this.claudeAPI.generateSiteArchitecture(
            siteProfile,
            keywords,
            competitorSummary
        );

        if (!architecture.pages || !Array.isArray(architecture.pages)) {
            throw new Error('Invalid architecture response from Claude API');
        }

        return architecture;
    }

    /**
     * Generate all pages for a site
     */
    async generateAllPages(siteId, pages, progressCallback = null) {
        const siteProfile = this.db.getSiteProfile(siteId);

        if (!siteProfile) {
            throw new Error('Site profile not found');
        }

        // Get competitor insights
        const competitorSummary = this.db.getCompetitorSummary(siteId);

        const results = [];
        const total = pages.length;

        for (let index = 0; index < total; index++) {
            const pageSpec = pages[index];

            // Update progress
            this.progressData = {
                current: index + 1,
                total: total,
                page: pageSpec.title,
                status: 'generating'
            };

            if (progressCallback) {
                progressCallback(this.progressData);
            }

            try {
                // Generate content
                const content = await this.generatePage(siteId, pageSpec, competitorSummary);

                results.push({
                    page: pageSpec.title,
                    status: 'success',
                    word_count: content.seo_checklist?.word_count || 0,
                    page_id: content.page_id
                });
            } catch (error) {
                results.push({
                    page: pageSpec.title,
                    status: 'error',
                    message: error.message
                });
            }

            // Delay between requests to avoid rate limits
            if (index < total - 1) {
                await new Promise(resolve => setTimeout(resolve, 2000));
            }
        }

        // Clear progress
        this.progressData = {
            current: 0,
            total: 0,
            page: '',
            status: 'idle'
        };

        if (progressCallback) {
            progressCallback(this.progressData);
        }

        return results;
    }

    /**
     * Generate single page content
     */
    async generatePage(siteId, pageSpec, competitorSummary = null) {
        const siteProfile = this.db.getSiteProfile(siteId);

        if (!siteProfile) {
            throw new Error('Site profile not found');
        }

        if (!competitorSummary) {
            competitorSummary = this.db.getCompetitorSummary(siteId);
        }

        // Generate content via Claude
        const contentData = await this.claudeAPI.generatePageContent(
            siteProfile,
            pageSpec,
            competitorSummary
        );

        // Generate HTML from content
        const html = this.generateHTML(contentData);

        // Generate schema markup
        const schema = this.schemaGenerator.generateSchema(
            siteProfile,
            pageSpec,
            contentData
        );

        // Calculate word count
        const wordCount = contentData.seo_checklist?.word_count ||
            this.countWords(this.stripHTML(html));

        // Calculate SEO score
        const seoScore = this.calculateSEOScore(contentData, pageSpec);

        // Save to database
        const pageData = {
            site_id: siteId,
            title: pageSpec.title,
            slug: contentData.meta?.slug || pageSpec.slug,
            target_keyword: pageSpec.primary_keyword,
            search_intent: pageSpec.intent,
            content_type: pageSpec.page_type || 'service_page',
            content_json: JSON.stringify(contentData),
            html: html,
            meta_title: contentData.meta?.title || '',
            meta_description: contentData.meta?.description || '',
            word_count: wordCount,
            seo_score: seoScore,
            status: 'ready'
        };

        const pageId = this.db.savePage(pageData);

        contentData.page_id = pageId;

        return contentData;
    }

    /**
     * Regenerate existing page
     */
    async regeneratePage(pageId) {
        const page = this.db.getPage(pageId);

        if (!page) {
            throw new Error('Page not found');
        }

        const pageSpec = {
            title: page.title,
            slug: page.slug,
            primary_keyword: page.target_keyword,
            secondary_keywords: page.secondary_keywords ? JSON.parse(page.secondary_keywords) : [],
            intent: page.search_intent,
            page_type: page.content_type,
            target_words: page.word_count || 1500
        };

        return await this.generatePage(page.site_id, pageSpec);
    }

    /**
     * Generate HTML from content data
     */
    generateHTML(contentData) {
        if (!contentData.content) {
            throw new Error('Invalid content data structure');
        }

        const content = contentData.content;
        const meta = contentData.meta || {};

        let html = '';

        // SEO Meta comment
        html += '<!-- SEO Meta Tags -->\n';
        html += '<!--\n';
        html += `Title: ${this.escapeHTML(meta.title || '')}\n`;
        html += `Description: ${this.escapeHTML(meta.description || '')}\n`;
        html += `URL Slug: ${this.escapeHTML(meta.slug || '')}\n`;
        html += '-->\n\n';

        // Page Content
        html += '<article class="rsg-content">\n\n';
        html += `    <h1>${this.escapeHTML(content.h1)}</h1>\n\n`;

        // Intro
        html += '    <div class="rsg-intro">\n';
        html += `        ${this.autop(content.intro)}\n`;
        html += '    </div>\n\n';

        // Sections
        if (content.sections && Array.isArray(content.sections)) {
            content.sections.forEach(section => {
                html += '    <section class="rsg-section">\n';
                html += `        <h2>${this.escapeHTML(section.h2)}</h2>\n\n`;

                if (section.content) {
                    html += '        <div class="rsg-section-content">\n';
                    html += `            ${this.autop(section.content)}\n`;
                    html += '        </div>\n\n';
                }

                // H3 subsections
                if (section.h3_subsections && Array.isArray(section.h3_subsections)) {
                    section.h3_subsections.forEach(subsection => {
                        html += '        <div class="rsg-subsection">\n';
                        html += `            <h3>${this.escapeHTML(subsection.h3)}</h3>\n`;
                        html += '            <div class="rsg-subsection-content">\n';
                        html += `                ${this.autop(subsection.content)}\n`;
                        html += '            </div>\n';
                        html += '        </div>\n';
                    });
                }

                html += '    </section>\n\n';
            });
        }

        // Conclusion
        if (content.conclusion) {
            html += '    <div class="rsg-conclusion">\n';
            html += `        ${this.autop(content.conclusion)}\n`;
            html += '    </div>\n\n';
        }

        html += '</article>';

        return html;
    }

    /**
     * Calculate SEO score based on checklist
     */
    calculateSEOScore(contentData, pageSpec) {
        let score = 0;
        const maxScore = 100;

        if (!contentData.seo_checklist) {
            return 50; // Default middle score
        }

        const checklist = contentData.seo_checklist;

        // Keyword in title (15 points)
        if (checklist.keyword_in_title) {
            score += 15;
        }

        // Keyword in H1 (15 points)
        if (checklist.keyword_in_h1) {
            score += 15;
        }

        // Keyword in first 100 words (15 points)
        if (checklist.keyword_in_first_100) {
            score += 15;
        }

        // Location mentions (15 points, scaled)
        if (checklist.location_mentions) {
            const locationScore = Math.min(checklist.location_mentions * 2.5, 15);
            score += locationScore;
        }

        // Word count meets target (20 points)
        if (checklist.word_count && pageSpec.target_words) {
            const wordRatio = checklist.word_count / pageSpec.target_words;
            if (wordRatio >= 0.9 && wordRatio <= 1.2) {
                score += 20;
            } else if (wordRatio >= 0.7) {
                score += 10;
            }
        }

        // Meta description present (10 points)
        if (contentData.meta?.description && contentData.meta.description.length > 0) {
            score += 10;
        }

        // Sections/structure (10 points)
        if (contentData.content?.sections && contentData.content.sections.length >= 5) {
            score += 10;
        }

        return Math.min(score, maxScore);
    }

    /**
     * Get generation progress
     */
    getProgress() {
        return this.progressData;
    }

    /**
     * Export page as complete HTML file
     */
    exportPageHTML(pageId) {
        const page = this.db.getPage(pageId);

        if (!page) {
            throw new Error('Page not found');
        }

        const site = this.db.getSiteProfile(page.site_id);

        // Parse schema markup if it's a string
        let schemaMarkup = page.schema_markup;
        if (typeof schemaMarkup === 'string') {
            try {
                schemaMarkup = JSON.parse(schemaMarkup);
            } catch (e) {
                schemaMarkup = null;
            }
        }

        // Generate complete HTML
        let html = '<!DOCTYPE html>\n';
        html += '<html lang="en">\n';
        html += '<head>\n';
        html += '    <meta charset="UTF-8">\n';
        html += '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n';
        html += `    <title>${this.escapeHTML(page.meta_title)}</title>\n`;
        html += `    <meta name="description" content="${this.escapeAttr(page.meta_description)}">\n`;

        // Add schema markup
        if (schemaMarkup) {
            html += '    <script type="application/ld+json">\n';
            html += JSON.stringify(schemaMarkup, null, 4);
            html += '\n    </script>\n';
        }

        html += '</head>\n';
        html += '<body>\n\n';
        html += page.html;
        html += '\n\n</body>\n';
        html += '</html>';

        return html;
    }

    /**
     * Helper: Escape HTML
     */
    escapeHTML(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Helper: Escape HTML attributes
     */
    escapeAttr(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Helper: Auto-paragraph (converts newlines to <p> tags)
     */
    autop(text) {
        if (!text) return '';

        // If already contains HTML tags, return as is
        if (/<[a-z][\s\S]*>/i.test(text)) {
            return text;
        }

        // Split by double newlines
        const paragraphs = text.split(/\n\n+/);

        return paragraphs
            .map(p => p.trim())
            .filter(p => p.length > 0)
            .map(p => `<p>${this.escapeHTML(p)}</p>`)
            .join('\n        ');
    }

    /**
     * Helper: Strip HTML tags
     */
    stripHTML(html) {
        return html.replace(/<[^>]*>/g, '');
    }

    /**
     * Helper: Count words
     */
    countWords(text) {
        return text.trim().split(/\s+/).filter(w => w.length > 0).length;
    }
}

module.exports = ContentGenerator;
