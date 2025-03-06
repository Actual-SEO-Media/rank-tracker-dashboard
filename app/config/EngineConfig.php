<?php
/**
 * Configuration file for search engine definitions
 * Contains column mappings for different search engines in the CSV import
 */
class EngineConfig {
    // Search engine definitions with their CSV column names
    const ENGINES = [
        'google' => [
            'rankColumn' => 'Google HOU Rank',
            'prevRankColumn' => 'Google HOU Previous Rank',
            'diffColumn' => 'Google HOU Difference',
            'serpColumn' => 'Google HOU SERP Features',
            'urlColumn' => 'Google HOU URL Found'
        ],
        'google_mobile' => [
            'rankColumn' => 'Google Mobile HOU Rank',
            'prevRankColumn' => 'Google Mobile HOU Previous Rank',
            'diffColumn' => 'Google Mobile HOU Difference',
            'serpColumn' => 'Google Mobile HOU SERP Features',
            'urlColumn' => 'Google Mobile HOU URL Found'
        ],
        'yahoo' => [
            'rankColumn' => 'Yahoo! Rank',
            'prevRankColumn' => 'Yahoo! Previous Rank',
            'diffColumn' => 'Yahoo! Difference',
            'serpColumn' => 'Yahoo! SERP Features',
            'urlColumn' => 'Yahoo! URL Found'
        ],
        'bing' => [
            'rankColumn' => 'Bing US Rank',
            'prevRankColumn' => 'Bing US Previous Rank',
            'diffColumn' => 'Bing US Difference',
            'serpColumn' => 'Bing US SERP Features',
            'urlColumn' => 'Bing US URL Found'
        ]
    ];
    
    // Special value constants for ranking data
    const NOT_RANKED = 101;   // Value for "not in top..." rankings
    const DROPPED = -100;     // Value for "dropped" difference
    const ENTERED = 100;      // Value for "entered" difference
    const NO_CHANGE = 0;      // Value for "stays out" difference
    
    /**
     * Get all supported search engines
     * 
     * @return array List of search engine keys
     */
    public static function getEngineKeys() {
        return array_keys(self::ENGINES);
    }
    
    /**
     * Get configuration for a specific engine
     * 
     * @param string $engine The engine key
     * @return array|null Engine configuration or null if not found
     */
    public static function getEngine($engine) {
        return self::ENGINES[$engine] ?? null;
    }
    
    /**
     * Get all engine configurations
     * 
     * @return array All engine configurations
     */
    public static function getAllEngines() {
        return self::ENGINES;
    }
}