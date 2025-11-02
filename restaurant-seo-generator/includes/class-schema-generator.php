<?php
/**
 * Schema Markup Generator Class
 *
 * Generates business-type specific schema markup
 */

if (!defined('ABSPATH')) {
    exit;
}

class RSG_Schema_Generator {

    /**
     * Generate schema markup for a page
     */
    public function generate_schema($site_profile, $page_spec, $content_data) {
        $business_type = strtolower($site_profile['business_type']);

        // Determine appropriate schema type
        if ($this->is_food_business($business_type)) {
            return $this->generate_restaurant_schema($site_profile, $page_spec, $content_data);
        } elseif ($this->is_home_service($business_type)) {
            return $this->generate_home_service_schema($site_profile, $page_spec, $content_data);
        } elseif ($this->is_health_wellness($business_type)) {
            return $this->generate_health_schema($site_profile, $page_spec, $content_data);
        } elseif ($this->is_professional_service($business_type)) {
            return $this->generate_professional_schema($site_profile, $page_spec, $content_data);
        } elseif ($this->is_beauty_service($business_type)) {
            return $this->generate_beauty_schema($site_profile, $page_spec, $content_data);
        } else {
            return $this->generate_local_business_schema($site_profile, $page_spec, $content_data);
        }
    }

    /**
     * Check if business is food-related
     */
    private function is_food_business($type) {
        $food_types = ['restaurant', 'bar', 'cafe', 'coffee', 'bakery', 'pizza', 'food'];
        foreach ($food_types as $food) {
            if (strpos($type, $food) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if business is home service
     */
    private function is_home_service($type) {
        $service_types = ['plumb', 'electric', 'hvac', 'landscape', 'clean', 'handyman', 'roofing', 'paint'];
        foreach ($service_types as $service) {
            if (strpos($type, $service) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if business is health/wellness
     */
    private function is_health_wellness($type) {
        $health_types = ['dentist', 'dental', 'chiropract', 'yoga', 'gym', 'fitness', 'massage', 'therapy'];
        foreach ($health_types as $health) {
            if (strpos($type, $health) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if business is professional service
     */
    private function is_professional_service($type) {
        $prof_types = ['account', 'lawyer', 'attorney', 'insurance', 'consultant', 'real estate'];
        foreach ($prof_types as $prof) {
            if (strpos($type, $prof) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if business is beauty service
     */
    private function is_beauty_service($type) {
        $beauty_types = ['salon', 'barber', 'spa', 'nail', 'beauty', 'hair'];
        foreach ($beauty_types as $beauty) {
            if (strpos($type, $beauty) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate Restaurant schema
     */
    private function generate_restaurant_schema($site_profile, $page_spec, $content_data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $site_profile['site_name'],
            'description' => $site_profile['unique_selling_point'],
            'address' => $this->generate_address($site_profile['location']),
            'telephone' => '',
            'priceRange' => '$$',
            'servesCuisine' => $this->extract_cuisine($site_profile),
        ];

        // Add URL if available
        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        // Add service area if applicable
        if ($site_profile['service_area_type'] === 'service_area') {
            $schema['areaServed'] = $this->generate_service_area($site_profile);
        }

        return $schema;
    }

    /**
     * Generate Home Service schema
     */
    private function generate_home_service_schema($site_profile, $page_spec, $content_data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $site_profile['site_name'],
            'description' => $site_profile['unique_selling_point'],
            'address' => $this->generate_address($site_profile['location']),
            'telephone' => '',
            'priceRange' => '$$',
        ];

        // Add URL
        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        // Add service area
        if ($site_profile['service_area_type'] === 'service_area') {
            $schema['areaServed'] = $this->generate_service_area($site_profile);
        }

        // Add services catalog
        if (!empty($site_profile['key_services'])) {
            $schema['hasOfferCatalog'] = $this->generate_service_catalog($site_profile['key_services']);
        }

        return $schema;
    }

    /**
     * Generate Health/Wellness schema
     */
    private function generate_health_schema($site_profile, $page_spec, $content_data) {
        // Determine specific type
        $type_lower = strtolower($site_profile['business_type']);
        $schema_type = 'MedicalBusiness';

        if (strpos($type_lower, 'dentist') !== false || strpos($type_lower, 'dental') !== false) {
            $schema_type = 'Dentist';
        } elseif (strpos($type_lower, 'chiropract') !== false) {
            $schema_type = 'MedicalClinic';
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $schema_type,
            'name' => $site_profile['site_name'],
            'description' => $site_profile['unique_selling_point'],
            'address' => $this->generate_address($site_profile['location']),
            'telephone' => '',
            'priceRange' => '$$',
        ];

        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        return $schema;
    }

    /**
     * Generate Professional Service schema
     */
    private function generate_professional_schema($site_profile, $page_spec, $content_data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => $site_profile['site_name'],
            'description' => $site_profile['unique_selling_point'],
            'address' => $this->generate_address($site_profile['location']),
            'telephone' => '',
            'priceRange' => '$$-$$$',
        ];

        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        // Add service area
        $schema['areaServed'] = $site_profile['location'];

        return $schema;
    }

    /**
     * Generate Beauty Service schema
     */
    private function generate_beauty_schema($site_profile, $page_spec, $content_data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BeautySalon',
            'name' => $site_profile['site_name'],
            'description' => $site_profile['unique_selling_point'],
            'address' => $this->generate_address($site_profile['location']),
            'telephone' => '',
            'priceRange' => '$$',
        ];

        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        return $schema;
    }

    /**
     * Generate generic Local Business schema
     */
    private function generate_local_business_schema($site_profile, $page_spec, $content_data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $site_profile['site_name'],
            'description' => $site_profile['unique_selling_point'],
            'address' => $this->generate_address($site_profile['location']),
            'telephone' => '',
            'priceRange' => '$$',
        ];

        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        return $schema;
    }

    /**
     * Generate address from location string
     */
    private function generate_address($location) {
        // Try to parse location string
        $parts = array_map('trim', explode(',', $location));

        if (count($parts) >= 2) {
            return [
                '@type' => 'PostalAddress',
                'addressLocality' => $parts[0],
                'addressRegion' => isset($parts[1]) ? $parts[1] : '',
                'addressCountry' => 'US',
            ];
        }

        return [
            '@type' => 'PostalAddress',
            'addressLocality' => $location,
            'addressCountry' => 'US',
        ];
    }

    /**
     * Generate service area
     */
    private function generate_service_area($site_profile) {
        $location_parts = array_map('trim', explode(',', $site_profile['location']));

        return [
            '@type' => 'GeoCircle',
            'geoMidpoint' => [
                '@type' => 'GeoCoordinates',
                'addressLocality' => $location_parts[0],
                'addressRegion' => isset($location_parts[1]) ? $location_parts[1] : '',
            ],
            'geoRadius' => $site_profile['service_area_radius'] . ' miles',
        ];
    }

    /**
     * Generate service catalog
     */
    private function generate_service_catalog($services_string) {
        $services = array_map('trim', explode(',', $services_string));

        $items = [];
        foreach ($services as $service) {
            $items[] = [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => $service,
                ],
            ];
        }

        return [
            '@type' => 'OfferCatalog',
            'name' => 'Services',
            'itemListElement' => $items,
        ];
    }

    /**
     * Extract cuisine from profile
     */
    private function extract_cuisine($site_profile) {
        $business_type = strtolower($site_profile['business_type']);
        $services = strtolower($site_profile['key_services']);

        // Try to identify cuisine type
        $cuisines = [
            'italian' => ['italian', 'pizza', 'pasta'],
            'mexican' => ['mexican', 'taco', 'burrito'],
            'chinese' => ['chinese', 'asian'],
            'japanese' => ['japanese', 'sushi'],
            'american' => ['american', 'burger', 'bbq'],
            'indian' => ['indian', 'curry'],
            'thai' => ['thai'],
            'french' => ['french'],
        ];

        foreach ($cuisines as $cuisine => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($business_type, $keyword) !== false || strpos($services, $keyword) !== false) {
                    return ucfirst($cuisine);
                }
            }
        }

        return 'American';
    }

    /**
     * Generate Article schema for content pages
     */
    public function generate_article_schema($site_profile, $page_spec, $content_data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $page_spec['title'],
            'description' => isset($content_data['meta']['description']) ? $content_data['meta']['description'] : '',
            'author' => [
                '@type' => 'Organization',
                'name' => $site_profile['site_name'],
            ],
        ];

        if (isset($content_data['meta']['slug'])) {
            $schema['url'] = home_url('/' . $content_data['meta']['slug']);
        }

        $schema['datePublished'] = date('c');
        $schema['dateModified'] = date('c');

        return $schema;
    }

    /**
     * Generate FAQ schema if content has Q&A
     */
    public function generate_faq_schema($questions_and_answers) {
        if (empty($questions_and_answers)) {
            return null;
        }

        $main_entity = [];

        foreach ($questions_and_answers as $qa) {
            $main_entity[] = [
                '@type' => 'Question',
                'name' => $qa['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $qa['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $main_entity,
        ];
    }

    /**
     * Generate BreadcrumbList schema
     */
    public function generate_breadcrumb_schema($breadcrumbs) {
        if (empty($breadcrumbs)) {
            return null;
        }

        $items = [];
        $position = 1;

        foreach ($breadcrumbs as $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
