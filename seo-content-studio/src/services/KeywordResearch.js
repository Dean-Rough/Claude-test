/**
 * Keyword Research Service
 * Handles keyword generation and classification using Google Autocomplete
 * Ported from WordPress plugin PHP class
 */

const axios = require('axios');
const cheerio = require('cheerio');

class KeywordResearch {
    constructor() {
        this.basePatterns = [
            '', // Base keyword
            'best',
            'top',
            'near me',
            'in',
            'services',
            'cost',
            'how to',
            'why',
            'what is',
            'reviews'
        ];
    }

    /**
     * Generate keywords from seed keywords
     */
    async generateKeywords(seedKeywords, location = '', autoExpand = true) {
        const allKeywords = new Set();

        // Add seed keywords
        seedKeywords.forEach(kw => allKeywords.add(kw.trim().toLowerCase()));

        if (autoExpand) {
            // Expand using autocomplete
            for (const seed of seedKeywords) {
                try {
                    const suggestions = await this.getAutocompleteSuggestions(seed, location);
                    suggestions.forEach(s => allKeywords.add(s.toLowerCase()));
                } catch (error) {
                    console.error(`Error getting suggestions for "${seed}":`, error.message);
                }

                // Add pattern-based variations
                this.basePatterns.forEach(pattern => {
                    let variant;
                    if (pattern === '') {
                        variant = location ? `${seed} ${location}` : seed;
                    } else if (pattern === 'near me') {
                        variant = `${seed} ${pattern}`;
                    } else if (pattern === 'in') {
                        variant = location ? `${seed} in ${location}` : null;
                    } else {
                        variant = `${pattern} ${seed}`;
                    }

                    if (variant) {
                        allKeywords.add(variant.toLowerCase());
                    }
                });
            }
        }

        return Array.from(allKeywords);
    }

    /**
     * Get autocomplete suggestions from Google
     */
    async getAutocompleteSuggestions(query, location = '') {
        try {
            const searchQuery = location ? `${query} ${location}` : query;
            const url = `https://www.google.com/complete/search?client=chrome&q=${encodeURIComponent(searchQuery)}`;

            const response = await axios.get(url, {
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                },
                timeout: 10000
            });

            if (response.data && Array.isArray(response.data[1])) {
                return response.data[1].slice(0, 10); // Return top 10 suggestions
            }

            return [];
        } catch (error) {
            console.error('Autocomplete error:', error.message);
            return [];
        }
    }

    /**
     * Classify keyword intent using pattern matching
     */
    classifyIntent(keyword, siteName = '') {
        const keywordLower = keyword.toLowerCase();

        // Transactional patterns
        const transactionalPatterns = [
            'near me', 'order', 'delivery', 'buy', 'purchase', 'hire',
            'book', 'schedule', 'appointment', 'quote', 'estimate',
            'emergency', '24/7', 'now', 'today', 'call', 'contact'
        ];

        // Commercial patterns
        const commercialPatterns = [
            'best', 'top', 'review', 'vs', 'compare', 'affordable',
            'cheap', 'pricing', 'cost', 'price', 'rated', 'recommended'
        ];

        // Informational patterns
        const informationalPatterns = [
            'how', 'what', 'why', 'when', 'where', 'guide', 'tip',
            'learn', 'tutorial', 'steps', 'process', 'explain'
        ];

        // Check for navigational (brand/site name)
        if (siteName && keywordLower.includes(siteName.toLowerCase())) {
            return 'navigational';
        }

        // Check patterns in order of specificity
        if (transactionalPatterns.some(pattern => keywordLower.includes(pattern))) {
            return 'transactional';
        }

        if (commercialPatterns.some(pattern => keywordLower.includes(pattern))) {
            return 'commercial';
        }

        if (informationalPatterns.some(pattern => keywordLower.includes(pattern))) {
            return 'informational';
        }

        // Default to informational
        return 'informational';
    }

    /**
     * Classify multiple keywords
     */
    async classifyKeywords(keywords, siteName = '') {
        return keywords.map(keyword => {
            return {
                keyword: keyword,
                search_intent: this.classifyIntent(keyword, siteName),
                search_volume: 0, // Placeholder (would need paid API for real volume)
                competition: 'unknown'
            };
        });
    }

    /**
     * Analyze competitor website
     */
    async analyzeCompetitor(url) {
        try {
            const response = await axios.get(url, {
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                },
                timeout: 15000
            });

            const $ = cheerio.load(response.data);

            // Extract basic info
            const title = $('title').text().trim();
            const metaDescription = $('meta[name="description"]').attr('content') || '';

            // Extract headings
            const headings = [];
            $('h1, h2, h3').each((i, elem) => {
                const text = $(elem).text().trim();
                if (text && text.length > 3) {
                    headings.push(text);
                }
            });

            // Extract keywords (simple - from title, headings, and meta)
            const keywordsFound = new Set();
            const text = `${title} ${metaDescription} ${headings.join(' ')}`;
            const words = text.toLowerCase()
                .replace(/[^\w\s]/g, ' ')
                .split(/\s+/)
                .filter(w => w.length > 4); // Filter short words

            // Simple frequency analysis
            const wordFreq = {};
            words.forEach(word => {
                wordFreq[word] = (wordFreq[word] || 0) + 1;
            });

            // Get top keywords
            Object.entries(wordFreq)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 20)
                .forEach(([word]) => keywordsFound.add(word));

            // Count words (approximate)
            const bodyText = $('body').text();
            const wordCount = bodyText.split(/\s+/).length;

            // Extract domain
            const urlObj = new URL(url);
            const domain = urlObj.hostname;

            // Content summary (first paragraph)
            const firstParagraph = $('p').first().text().trim().substring(0, 300);

            return {
                url: url,
                domain: domain,
                title: title,
                meta_description: metaDescription,
                keywords_found: Array.from(keywordsFound),
                headings: headings.slice(0, 10), // Top 10 headings
                word_count: wordCount,
                content_summary: firstParagraph
            };
        } catch (error) {
            console.error('Competitor analysis error:', error.message);
            return {
                url: url,
                domain: '',
                title: '',
                meta_description: '',
                keywords_found: [],
                headings: [],
                word_count: 0,
                content_summary: '',
                error: error.message
            };
        }
    }

    /**
     * Analyze multiple competitors
     */
    async analyzeCompetitors(urls) {
        const results = [];

        for (const url of urls) {
            const analysis = await this.analyzeCompetitor(url);
            results.push(analysis);

            // Small delay to avoid rate limiting
            await new Promise(resolve => setTimeout(resolve, 1000));
        }

        return results;
    }
}

module.exports = KeywordResearch;
