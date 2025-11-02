/**
 * Database Model
 * Handles all database operations for sites, pages, keywords, and competitors
 * Ported from WordPress plugin PHP class
 */

const { getDb } = require('../database/init');

class Database {
    constructor() {
        this.db = getDb();
    }

    /**
     * Save or update a site profile
     */
    saveSiteProfile(data, siteId = null) {
        try {
            const now = new Date().toISOString();

            if (siteId) {
                // Update existing site
                const stmt = this.db.prepare(`
                    UPDATE sites SET
                        business_name = ?,
                        industry = ?,
                        business_type = ?,
                        location = ?,
                        service_area_type = ?,
                        service_radius = ?,
                        phone = ?,
                        email = ?,
                        website = ?,
                        description = ?,
                        brand_voice = ?,
                        writing_sample = ?,
                        status = ?,
                        updated_at = ?
                    WHERE id = ?
                `);

                stmt.run(
                    data.business_name,
                    data.industry,
                    data.business_type,
                    data.location,
                    data.service_area_type || 'single',
                    data.service_radius || 0,
                    data.phone || '',
                    data.email || '',
                    data.website || '',
                    data.description || '',
                    data.brand_voice || 'professional',
                    data.writing_sample || '',
                    data.status || 'active',
                    now,
                    siteId
                );

                return siteId;
            } else {
                // Insert new site
                const stmt = this.db.prepare(`
                    INSERT INTO sites (
                        business_name, industry, business_type, location,
                        service_area_type, service_radius, phone, email,
                        website, description, brand_voice, writing_sample,
                        status, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                `);

                const result = stmt.run(
                    data.business_name,
                    data.industry,
                    data.business_type,
                    data.location,
                    data.service_area_type || 'single',
                    data.service_radius || 0,
                    data.phone || '',
                    data.email || '',
                    data.website || '',
                    data.description || '',
                    data.brand_voice || 'professional',
                    data.writing_sample || '',
                    data.status || 'active',
                    now,
                    now
                );

                return result.lastInsertRowid;
            }
        } catch (error) {
            console.error('Error saving site profile:', error);
            throw error;
        }
    }

    /**
     * Get site profile by ID
     */
    getSiteProfile(siteId) {
        try {
            const stmt = this.db.prepare('SELECT * FROM sites WHERE id = ?');
            return stmt.get(siteId);
        } catch (error) {
            console.error('Error getting site profile:', error);
            throw error;
        }
    }

    /**
     * Get all sites
     */
    getAllSites(status = null) {
        try {
            let query = 'SELECT * FROM sites';
            const params = [];

            if (status) {
                query += ' WHERE status = ?';
                params.push(status);
            }

            query += ' ORDER BY created_at DESC';

            const stmt = this.db.prepare(query);
            return params.length > 0 ? stmt.all(...params) : stmt.all();
        } catch (error) {
            console.error('Error getting sites:', error);
            throw error;
        }
    }

    /**
     * Delete a site
     */
    deleteSite(siteId) {
        try {
            const stmt = this.db.prepare('DELETE FROM sites WHERE id = ?');
            stmt.run(siteId);
            return true;
        } catch (error) {
            console.error('Error deleting site:', error);
            throw error;
        }
    }

    /**
     * Save a page
     */
    savePage(data) {
        try {
            const now = new Date().toISOString();

            if (data.id) {
                // Update existing page
                const stmt = this.db.prepare(`
                    UPDATE pages SET
                        title = ?,
                        slug = ?,
                        target_keyword = ?,
                        search_intent = ?,
                        content_type = ?,
                        content_json = ?,
                        html = ?,
                        meta_title = ?,
                        meta_description = ?,
                        word_count = ?,
                        seo_score = ?,
                        status = ?,
                        updated_at = ?
                    WHERE id = ?
                `);

                stmt.run(
                    data.title,
                    data.slug,
                    data.target_keyword,
                    data.search_intent || '',
                    data.content_type || 'service_page',
                    data.content_json || '',
                    data.html || '',
                    data.meta_title || '',
                    data.meta_description || '',
                    data.word_count || 0,
                    data.seo_score || 0,
                    data.status || 'draft',
                    now,
                    data.id
                );

                return data.id;
            } else {
                // Insert new page
                const stmt = this.db.prepare(`
                    INSERT INTO pages (
                        site_id, title, slug, target_keyword, search_intent,
                        content_type, content_json, html, meta_title,
                        meta_description, word_count, seo_score, status,
                        created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                `);

                const result = stmt.run(
                    data.site_id,
                    data.title,
                    data.slug,
                    data.target_keyword,
                    data.search_intent || '',
                    data.content_type || 'service_page',
                    data.content_json || '',
                    data.html || '',
                    data.meta_title || '',
                    data.meta_description || '',
                    data.word_count || 0,
                    data.seo_score || 0,
                    data.status || 'draft',
                    now,
                    now
                );

                return result.lastInsertRowid;
            }
        } catch (error) {
            console.error('Error saving page:', error);
            throw error;
        }
    }

    /**
     * Get page by ID
     */
    getPage(pageId) {
        try {
            const stmt = this.db.prepare('SELECT * FROM pages WHERE id = ?');
            return stmt.get(pageId);
        } catch (error) {
            console.error('Error getting page:', error);
            throw error;
        }
    }

    /**
     * Get all pages for a site
     */
    getSitePages(siteId, status = null) {
        try {
            let query = 'SELECT * FROM pages WHERE site_id = ?';
            const params = [siteId];

            if (status) {
                query += ' AND status = ?';
                params.push(status);
            }

            query += ' ORDER BY created_at DESC';

            const stmt = this.db.prepare(query);
            return stmt.all(...params);
        } catch (error) {
            console.error('Error getting site pages:', error);
            throw error;
        }
    }

    /**
     * Delete a page
     */
    deletePage(pageId) {
        try {
            const stmt = this.db.prepare('DELETE FROM pages WHERE id = ?');
            stmt.run(pageId);
            return true;
        } catch (error) {
            console.error('Error deleting page:', error);
            throw error;
        }
    }

    /**
     * Save a keyword
     */
    saveKeyword(data) {
        try {
            const now = new Date().toISOString();

            const stmt = this.db.prepare(`
                INSERT INTO keywords (
                    site_id, keyword, search_intent, search_volume,
                    competition, page_assigned, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            `);

            const result = stmt.run(
                data.site_id,
                data.keyword,
                data.search_intent || '',
                data.search_volume || 0,
                data.competition || 'unknown',
                data.page_assigned || null,
                data.status || 'active',
                now
            );

            return result.lastInsertRowid;
        } catch (error) {
            console.error('Error saving keyword:', error);
            throw error;
        }
    }

    /**
     * Get all keywords for a site
     */
    getSiteKeywords(siteId) {
        try {
            const stmt = this.db.prepare(`
                SELECT * FROM keywords
                WHERE site_id = ?
                ORDER BY created_at DESC
            `);
            return stmt.all(siteId);
        } catch (error) {
            console.error('Error getting keywords:', error);
            throw error;
        }
    }

    /**
     * Delete keywords for a site
     */
    deleteSiteKeywords(siteId) {
        try {
            const stmt = this.db.prepare('DELETE FROM keywords WHERE site_id = ?');
            stmt.run(siteId);
            return true;
        } catch (error) {
            console.error('Error deleting keywords:', error);
            throw error;
        }
    }

    /**
     * Save competitor analysis
     */
    saveCompetitorAnalysis(siteId, competitorData) {
        try {
            const now = new Date().toISOString();

            const stmt = this.db.prepare(`
                INSERT INTO competitors (
                    site_id, url, domain, title, meta_description,
                    content_summary, keywords_found, headings,
                    word_count, analyzed_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            `);

            const result = stmt.run(
                siteId,
                competitorData.url,
                competitorData.domain || '',
                competitorData.title || '',
                competitorData.meta_description || '',
                competitorData.content_summary || '',
                JSON.stringify(competitorData.keywords_found || []),
                JSON.stringify(competitorData.headings || []),
                competitorData.word_count || 0,
                now
            );

            return result.lastInsertRowid;
        } catch (error) {
            console.error('Error saving competitor analysis:', error);
            throw error;
        }
    }

    /**
     * Get competitor summary for a site
     */
    getCompetitorSummary(siteId) {
        try {
            const stmt = this.db.prepare(`
                SELECT * FROM competitors
                WHERE site_id = ?
                ORDER BY analyzed_at DESC
            `);
            const competitors = stmt.all(siteId);

            if (competitors.length === 0) {
                return null;
            }

            // Extract common topics from competitors
            const allKeywords = [];
            const allHeadings = [];

            competitors.forEach(comp => {
                if (comp.keywords_found) {
                    const keywords = JSON.parse(comp.keywords_found);
                    allKeywords.push(...keywords);
                }
                if (comp.headings) {
                    const headings = JSON.parse(comp.headings);
                    allHeadings.push(...headings);
                }
            });

            // Find common topics (simple frequency analysis)
            const topicFrequency = {};
            [...allKeywords, ...allHeadings].forEach(topic => {
                const normalized = topic.toLowerCase().trim();
                if (normalized.length > 3) {
                    topicFrequency[normalized] = (topicFrequency[normalized] || 0) + 1;
                }
            });

            // Get top 20 common topics
            const commonTopics = Object.entries(topicFrequency)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 20)
                .map(([topic]) => topic);

            return {
                competitors: competitors,
                common_topics: commonTopics,
                total_analyzed: competitors.length
            };
        } catch (error) {
            console.error('Error getting competitor summary:', error);
            throw error;
        }
    }

    /**
     * Get setting by key
     */
    getSetting(key) {
        try {
            const stmt = this.db.prepare('SELECT value FROM settings WHERE key = ?');
            const result = stmt.get(key);
            return result ? result.value : null;
        } catch (error) {
            console.error('Error getting setting:', error);
            throw error;
        }
    }

    /**
     * Save setting
     */
    saveSetting(key, value) {
        try {
            const now = new Date().toISOString();

            const stmt = this.db.prepare(`
                INSERT INTO settings (key, value, created_at, updated_at)
                VALUES (?, ?, ?, ?)
                ON CONFLICT(key) DO UPDATE SET
                    value = excluded.value,
                    updated_at = excluded.updated_at
            `);

            stmt.run(key, value, now, now);
            return true;
        } catch (error) {
            console.error('Error saving setting:', error);
            throw error;
        }
    }

    /**
     * Get site statistics
     */
    getSiteStatistics(siteId) {
        try {
            const totalPages = this.db.prepare('SELECT COUNT(*) as count FROM pages WHERE site_id = ?').get(siteId).count;
            const readyPages = this.db.prepare('SELECT COUNT(*) as count FROM pages WHERE site_id = ? AND status = ?').get(siteId, 'ready').count;
            const totalWords = this.db.prepare('SELECT SUM(word_count) as total FROM pages WHERE site_id = ?').get(siteId).total || 0;
            const totalKeywords = this.db.prepare('SELECT COUNT(*) as count FROM keywords WHERE site_id = ?').get(siteId).count;

            return {
                total_pages: totalPages,
                ready_pages: readyPages,
                total_words: totalWords,
                total_keywords: totalKeywords
            };
        } catch (error) {
            console.error('Error getting site statistics:', error);
            throw error;
        }
    }
}

module.exports = Database;
