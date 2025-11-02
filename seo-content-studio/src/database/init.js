/**
 * Database initialization for SQLite
 * Creates tables matching the WordPress plugin schema
 */

const Database = require('better-sqlite3');
const path = require('path');
const fs = require('fs');

// Ensure data directory exists
const dataDir = path.join(__dirname, '../../data');
if (!fs.existsSync(dataDir)) {
    fs.mkdirSync(dataDir, { recursive: true });
}

const dbPath = path.join(dataDir, 'seo-studio.db');
const db = new Database(dbPath);

// Enable foreign keys
db.pragma('foreign_keys = ON');

function initDatabase() {
    console.log('Initializing database...');

    // Sites table
    db.exec(`
        CREATE TABLE IF NOT EXISTS sites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            business_name TEXT NOT NULL,
            industry TEXT NOT NULL,
            business_type TEXT NOT NULL,
            location TEXT NOT NULL,
            service_area_type TEXT DEFAULT 'single',
            service_radius INTEGER DEFAULT 0,
            phone TEXT,
            email TEXT,
            website TEXT,
            description TEXT,
            brand_voice TEXT DEFAULT 'professional',
            writing_sample TEXT,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    `);

    // Pages table
    db.exec(`
        CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT NOT NULL,
            target_keyword TEXT NOT NULL,
            search_intent TEXT,
            content_type TEXT DEFAULT 'service_page',
            content_json TEXT,
            html TEXT,
            meta_title TEXT,
            meta_description TEXT,
            word_count INTEGER DEFAULT 0,
            seo_score INTEGER DEFAULT 0,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
        )
    `);

    // Keywords table
    db.exec(`
        CREATE TABLE IF NOT EXISTS keywords (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_id INTEGER NOT NULL,
            keyword TEXT NOT NULL,
            search_intent TEXT,
            search_volume INTEGER DEFAULT 0,
            competition TEXT DEFAULT 'unknown',
            page_assigned INTEGER,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
            FOREIGN KEY (page_assigned) REFERENCES pages(id) ON DELETE SET NULL
        )
    `);

    // Competitors table
    db.exec(`
        CREATE TABLE IF NOT EXISTS competitors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_id INTEGER NOT NULL,
            url TEXT NOT NULL,
            domain TEXT,
            title TEXT,
            meta_description TEXT,
            content_summary TEXT,
            keywords_found TEXT,
            headings TEXT,
            word_count INTEGER DEFAULT 0,
            analyzed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
        )
    `);

    // Settings table
    db.exec(`
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key TEXT UNIQUE NOT NULL,
            value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    `);

    // Create indexes
    db.exec('CREATE INDEX IF NOT EXISTS idx_pages_site_id ON pages(site_id)');
    db.exec('CREATE INDEX IF NOT EXISTS idx_pages_status ON pages(status)');
    db.exec('CREATE INDEX IF NOT EXISTS idx_keywords_site_id ON keywords(site_id)');
    db.exec('CREATE INDEX IF NOT EXISTS idx_keywords_status ON keywords(status)');
    db.exec('CREATE INDEX IF NOT EXISTS idx_competitors_site_id ON competitors(site_id)');

    console.log('✅ Database initialized successfully');
    console.log(`   Location: ${dbPath}`);
}

// Export database instance
function getDb() {
    return db;
}

module.exports = {
    initDatabase,
    getDb
};
