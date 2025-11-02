/**
 * Schema Generator Service
 * Generates business-type specific Schema.org markup
 * Ported from WordPress plugin PHP class
 */

class SchemaGenerator {
    /**
     * Generate schema markup for a page
     */
    generateSchema(siteProfile, pageSpec, contentData) {
        const businessType = siteProfile.business_type.toLowerCase();

        // Determine appropriate schema type
        if (this.isFoodBusiness(businessType)) {
            return this.generateRestaurantSchema(siteProfile, pageSpec, contentData);
        } else if (this.isHomeService(businessType)) {
            return this.generateHomeServiceSchema(siteProfile, pageSpec, contentData);
        } else if (this.isHealthWellness(businessType)) {
            return this.generateHealthSchema(siteProfile, pageSpec, contentData);
        } else if (this.isProfessionalService(businessType)) {
            return this.generateProfessionalSchema(siteProfile, pageSpec, contentData);
        } else if (this.isBeautyService(businessType)) {
            return this.generateBeautySchema(siteProfile, pageSpec, contentData);
        } else {
            return this.generateLocalBusinessSchema(siteProfile, pageSpec, contentData);
        }
    }

    /**
     * Check if business is food-related
     */
    isFoodBusiness(type) {
        const foodTypes = ['restaurant', 'bar', 'cafe', 'coffee', 'bakery', 'pizza', 'food'];
        return foodTypes.some(food => type.includes(food));
    }

    /**
     * Check if business is home service
     */
    isHomeService(type) {
        const serviceTypes = ['plumb', 'electric', 'hvac', 'landscape', 'clean', 'handyman', 'roofing', 'paint'];
        return serviceTypes.some(service => type.includes(service));
    }

    /**
     * Check if business is health/wellness
     */
    isHealthWellness(type) {
        const healthTypes = ['dentist', 'dental', 'chiropract', 'yoga', 'gym', 'fitness', 'massage', 'therapy'];
        return healthTypes.some(health => type.includes(health));
    }

    /**
     * Check if business is professional service
     */
    isProfessionalService(type) {
        const profTypes = ['account', 'lawyer', 'attorney', 'insurance', 'consultant', 'real estate'];
        return profTypes.some(prof => type.includes(prof));
    }

    /**
     * Check if business is beauty service
     */
    isBeautyService(type) {
        const beautyTypes = ['salon', 'barber', 'spa', 'nail', 'beauty', 'hair'];
        return beautyTypes.some(beauty => type.includes(beauty));
    }

    /**
     * Generate Restaurant schema
     */
    generateRestaurantSchema(siteProfile, pageSpec, contentData) {
        const schema = {
            '@context': 'https://schema.org',
            '@type': 'Restaurant',
            'name': siteProfile.business_name,
            'description': siteProfile.description || '',
            'address': this.generateAddress(siteProfile.location),
            'telephone': siteProfile.phone || '',
            'priceRange': '$$',
            'servesCuisine': this.extractCuisine(siteProfile)
        };

        // Add URL if available
        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        // Add service area if applicable
        if (siteProfile.service_area_type === 'service_area') {
            schema.areaServed = this.generateServiceArea(siteProfile);
        }

        return schema;
    }

    /**
     * Generate Home Service schema
     */
    generateHomeServiceSchema(siteProfile, pageSpec, contentData) {
        const schema = {
            '@context': 'https://schema.org',
            '@type': 'LocalBusiness',
            'name': siteProfile.business_name,
            'description': siteProfile.description || '',
            'address': this.generateAddress(siteProfile.location),
            'telephone': siteProfile.phone || '',
            'priceRange': '$$'
        };

        // Add URL
        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        // Add service area
        if (siteProfile.service_area_type === 'service_area') {
            schema.areaServed = this.generateServiceArea(siteProfile);
        }

        return schema;
    }

    /**
     * Generate Health/Wellness schema
     */
    generateHealthSchema(siteProfile, pageSpec, contentData) {
        // Determine specific type
        const typeLower = siteProfile.business_type.toLowerCase();
        let schemaType = 'MedicalBusiness';

        if (typeLower.includes('dentist') || typeLower.includes('dental')) {
            schemaType = 'Dentist';
        } else if (typeLower.includes('chiropract')) {
            schemaType = 'MedicalClinic';
        }

        const schema = {
            '@context': 'https://schema.org',
            '@type': schemaType,
            'name': siteProfile.business_name,
            'description': siteProfile.description || '',
            'address': this.generateAddress(siteProfile.location),
            'telephone': siteProfile.phone || '',
            'priceRange': '$$'
        };

        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        return schema;
    }

    /**
     * Generate Professional Service schema
     */
    generateProfessionalSchema(siteProfile, pageSpec, contentData) {
        const schema = {
            '@context': 'https://schema.org',
            '@type': 'ProfessionalService',
            'name': siteProfile.business_name,
            'description': siteProfile.description || '',
            'address': this.generateAddress(siteProfile.location),
            'telephone': siteProfile.phone || '',
            'priceRange': '$$-$$$'
        };

        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        // Add service area
        schema.areaServed = siteProfile.location;

        return schema;
    }

    /**
     * Generate Beauty Service schema
     */
    generateBeautySchema(siteProfile, pageSpec, contentData) {
        const schema = {
            '@context': 'https://schema.org',
            '@type': 'BeautySalon',
            'name': siteProfile.business_name,
            'description': siteProfile.description || '',
            'address': this.generateAddress(siteProfile.location),
            'telephone': siteProfile.phone || '',
            'priceRange': '$$'
        };

        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        return schema;
    }

    /**
     * Generate generic Local Business schema
     */
    generateLocalBusinessSchema(siteProfile, pageSpec, contentData) {
        const schema = {
            '@context': 'https://schema.org',
            '@type': 'LocalBusiness',
            'name': siteProfile.business_name,
            'description': siteProfile.description || '',
            'address': this.generateAddress(siteProfile.location),
            'telephone': siteProfile.phone || '',
            'priceRange': '$$'
        };

        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        return schema;
    }

    /**
     * Generate address from location string
     */
    generateAddress(location) {
        // Try to parse location string
        const parts = location.split(',').map(p => p.trim());

        if (parts.length >= 2) {
            return {
                '@type': 'PostalAddress',
                'addressLocality': parts[0],
                'addressRegion': parts[1] || '',
                'addressCountry': 'US'
            };
        }

        return {
            '@type': 'PostalAddress',
            'addressLocality': location,
            'addressCountry': 'US'
        };
    }

    /**
     * Generate service area
     */
    generateServiceArea(siteProfile) {
        const locationParts = siteProfile.location.split(',').map(p => p.trim());

        return {
            '@type': 'GeoCircle',
            'geoMidpoint': {
                '@type': 'GeoCoordinates',
                'addressLocality': locationParts[0],
                'addressRegion': locationParts[1] || ''
            },
            'geoRadius': `${siteProfile.service_radius || 25} miles`
        };
    }

    /**
     * Extract cuisine from profile
     */
    extractCuisine(siteProfile) {
        const businessType = siteProfile.business_type.toLowerCase();
        const description = (siteProfile.description || '').toLowerCase();

        // Try to identify cuisine type
        const cuisines = {
            'Italian': ['italian', 'pizza', 'pasta'],
            'Mexican': ['mexican', 'taco', 'burrito'],
            'Chinese': ['chinese', 'asian'],
            'Japanese': ['japanese', 'sushi'],
            'American': ['american', 'burger', 'bbq'],
            'Indian': ['indian', 'curry'],
            'Thai': ['thai'],
            'French': ['french']
        };

        for (const [cuisine, keywords] of Object.entries(cuisines)) {
            for (const keyword of keywords) {
                if (businessType.includes(keyword) || description.includes(keyword)) {
                    return cuisine;
                }
            }
        }

        return 'American';
    }

    /**
     * Generate Article schema for content pages
     */
    generateArticleSchema(siteProfile, pageSpec, contentData) {
        const schema = {
            '@context': 'https://schema.org',
            '@type': 'Article',
            'headline': pageSpec.title,
            'description': contentData.meta?.description || '',
            'author': {
                '@type': 'Organization',
                'name': siteProfile.business_name
            }
        };

        if (contentData.meta?.slug) {
            schema.url = `/${contentData.meta.slug}`;
        }

        schema.datePublished = new Date().toISOString();
        schema.dateModified = new Date().toISOString();

        return schema;
    }

    /**
     * Generate FAQ schema if content has Q&A
     */
    generateFAQSchema(questionsAndAnswers) {
        if (!questionsAndAnswers || questionsAndAnswers.length === 0) {
            return null;
        }

        const mainEntity = questionsAndAnswers.map(qa => ({
            '@type': 'Question',
            'name': qa.question,
            'acceptedAnswer': {
                '@type': 'Answer',
                'text': qa.answer
            }
        }));

        return {
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            'mainEntity': mainEntity
        };
    }

    /**
     * Generate BreadcrumbList schema
     */
    generateBreadcrumbSchema(breadcrumbs) {
        if (!breadcrumbs || breadcrumbs.length === 0) {
            return null;
        }

        const items = breadcrumbs.map((crumb, index) => ({
            '@type': 'ListItem',
            'position': index + 1,
            'name': crumb.name,
            'item': crumb.url
        }));

        return {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            'itemListElement': items
        };
    }
}

module.exports = SchemaGenerator;
