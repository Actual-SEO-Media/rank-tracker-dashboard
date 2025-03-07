<?php
require_once __DIR__ . '/../config/EngineConfig.php';

class RankingDataParser {
    /**
     * Processes the CSV file and extracts ranking data
     * 
     * @param string $filepath Path to the CSV file
     * @return array|boolean Array of processed data or false on failure
     */
    public function parseCSVFile($filepath) {
        if (($handle = fopen($filepath, "r")) === FALSE) {
            return false;
        }
        
        // Read header row
        $headers = fgetcsv($handle);
        
        // Convert headers to UTF-8 and clean them
        $headers = array_map(function($header) {
            $header = iconv('CP1252', 'UTF-8//IGNORE', $header);
            return trim($header);
        }, $headers);
        
        // Find column indexes for all engines with smart matching
        $columnIndexes = $this->findColumnIndexes($headers);
        
        // Validate required columns
        if (!isset($columnIndexes['keyword']) || 
            !isset($columnIndexes['visibility']) || 
            !isset($columnIndexes['visibility_difference'])) {
            fclose($handle);
            return false;
        }
        
        $parsedData = [];
        
        // Process data rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Skip short rows
            if (count($data) < 3) {
                continue;
            }
            
            // Pad data array if needed
            if (count($data) < count($headers)) {
                $data = array_pad($data, count($headers), '');
            }
            
            // Clean and convert data
            $data = array_map(function($value) {
                return iconv('CP1252', 'UTF-8//IGNORE', trim($value));
            }, $data);
            
            // Extract common data
            $keyword = $data[$columnIndexes['keyword']];
            $visibility = $this->parseNumericValue($data[$columnIndexes['visibility']]);
            $visibility_difference = $this->parseNumericValue($data[$columnIndexes['visibility_difference']]);
            
            $rowData = [
                'keyword' => $keyword,
                'visibility' => $visibility,
                'visibility_difference' => $visibility_difference,
                'engines' => []
            ];
            
            // Process each search engine's data
            foreach (EngineConfig::getAllEngines() as $engine => $columns) {
                // Skip if we don't have the column indexes for this engine
                if (!isset($columnIndexes[$engine])) {
                    continue;
                }
                
                $engineIndexes = $columnIndexes[$engine];
                
                // Check if rank column exists and has a value
                if (isset($engineIndexes['rank']) && isset($data[$engineIndexes['rank']])) {
                    // Set rank based on whether it contains "not in top"
                    $rankValue = $data[$engineIndexes['rank']];
                    $rank = null;
                    
                    if (strpos(strtolower($rankValue), 'not') !== false) {
                        $rank = EngineConfig::NOT_RANKED;
                    } else {
                        $rank = $this->parseNumericValue($rankValue);
                    }
                    
                    // Fix problematic characters in URL
                    $url = isset($engineIndexes['url']) ? $data[$engineIndexes['url']] : '';
                    $url = $this->fixSpecialChars($url);
                    
                    $rowData['engines'][$engine] = [
                        'rank' => $rank,
                        'previous_rank' => $this->parsePreviousRank(
                            $data[$engineIndexes['prevRank']] ?? ''
                        ),
                        'difference' => $this->parseDifference(
                            $data[$engineIndexes['diff']] ?? ''
                        ),
                        'serp_features' => $data[$engineIndexes['serp']] ?? '',
                        'url' => $url
                    ];
                }
            }
            
            $parsedData[] = $rowData;
        }
        
        fclose($handle);
        return $parsedData;
    }
    
    /**
     * Find column indexes in CSV headers with smart pattern matching
     * 
     * @param array $headers Array of CSV header names
     * @return array Associative array of column indexes
     */
    private function findColumnIndexes($headers) {
        $indexes = [
            'keyword' => array_search('Keyword', $headers),
            'visibility' => array_search('Visibility', $headers),
            'visibility_difference' => array_search('Visibility Difference', $headers)
        ];
        
        // Fall back to case-insensitive search for standard columns if needed
        if ($indexes['keyword'] === false) {
            $indexes['keyword'] = $this->findCaseInsensitive('Keyword', $headers);
        }
        
        if ($indexes['visibility'] === false) {
            $indexes['visibility'] = $this->findCaseInsensitive('Visibility', $headers);
        }
        
        if ($indexes['visibility_difference'] === false) {
            $indexes['visibility_difference'] = $this->findCaseInsensitive('Visibility Difference', $headers);
        }
        
        // Define engine identifier patterns for flexible matching
        $enginePatterns = [
            'google' => ['google(?!.*(mobile|\.com))'], // Google but not mobile or .com
            'google_mobile' => ['google.*mobile', 'google\.com.*mobile'],
            'yahoo' => ['yahoo'],
            'bing' => ['bing']
        ];
        
        // Define column type patterns
        $columnTypes = [
            'rank' => 'rank$', // Ends with rank
            'prevRank' => '(previous|prev).*rank',
            'diff' => 'diff',
            'serp' => 'serp',
            'url' => 'url'
        ];
        
        // Map engines to their column indexes using pattern matching
        foreach ($enginePatterns as $engineKey => $patterns) {
            $engineIndexes = [];
            $hasRankColumn = false;
            
            // For each column type, find the matching header
            foreach ($columnTypes as $columnType => $typePattern) {
                foreach ($patterns as $enginePattern) {
                    // Create a pattern to match this engine and column type
                    // Ignore regional indicators (HOU, US, etc) for matching
                    $fullPattern = "/$enginePattern.*?(?:HOU|US|UK|AU|DE|CA)?.*$typePattern/i";
                    
                    // Find matching header
                    foreach ($headers as $index => $header) {
                        if (preg_match($fullPattern, $header)) {
                            $engineIndexes[$columnType] = $index;
                            
                            if ($columnType === 'rank') {
                                $hasRankColumn = true;
                            }
                            
                            break 2; // Found a match, move to next column type
                        }
                    }
                }
            }
            
            // Only add this engine if we found the rank column
            if ($hasRankColumn) {
                $indexes[$engineKey] = $engineIndexes;
            }
        }
        
        // Fallback to more direct matching if needed
        if (!isset($indexes['google']) || !isset($indexes['google_mobile'])) {
            $this->tryDirectMatching($headers, $indexes);
        }
        
        return $indexes;
    }
    
    /**
     * Try direct matching approach as a fallback
     * 
     * @param array $headers CSV headers
     * @param array &$indexes Reference to column indexes array
     */
    private function tryDirectMatching($headers, &$indexes) {
        // Get engine configurations
        $engines = EngineConfig::getAllEngines();
        
        // Try direct matching for Google and Google Mobile if they weren't found by pattern matching
        foreach (['google', 'google_mobile'] as $engineKey) {
            if (isset($indexes[$engineKey])) {
                continue; // Skip if already found
            }
            
            $engineColumns = $engines[$engineKey];
            $engineIndexes = [];
            $rankFound = false;
            
            // Check each column for this engine
            foreach ($engineColumns as $columnKey => $columnName) {
                // Extract the basic pattern by removing regional identifiers
                $baseColumnName = preg_replace('/(HOU|US|UK|AU)/', '', $columnName);
                $baseColumnName = trim($baseColumnName);
                
                // Try to find a match with any regional identifier
                foreach ($headers as $index => $header) {
                    // Remove 'HOU', 'US', etc. from the header for comparison
                    $baseHeader = preg_replace('/(HOU|US|UK|AU)/', '', $header);
                    $baseHeader = trim($baseHeader);
                    
                    // Compare the base names (case-insensitive)
                    if (strcasecmp($baseHeader, $baseColumnName) === 0) {
                        switch ($columnKey) {
                            case 'rankColumn':
                                $engineIndexes['rank'] = $index;
                                $rankFound = true;
                                break;
                            case 'prevRankColumn':
                                $engineIndexes['prevRank'] = $index;
                                break;
                            case 'diffColumn':
                                $engineIndexes['diff'] = $index;
                                break;
                            case 'serpColumn':
                                $engineIndexes['serp'] = $index;
                                break;
                            case 'urlColumn':
                                $engineIndexes['url'] = $index;
                                break;
                        }
                        break;
                    }
                }
            }
            
            // Add engine indexes if rank was found
            if ($rankFound) {
                $indexes[$engineKey] = $engineIndexes;
            }
        }
    }
    
    /**
     * Find a string in an array using case-insensitive comparison
     * 
     * @param string $needle The string to find
     * @param array $haystack The array to search in
     * @return int|bool The index of the match or false if not found
     */
    private function findCaseInsensitive($needle, $haystack) {
        foreach ($haystack as $index => $item) {
            if (strcasecmp($needle, $item) === 0) {
                return $index;
            }
        }
        return false;
    }
    
    /**
     * Fix special characters that cause database errors
     * 
     * @param string $string The input string
     * @return string The fixed string
     */
    private function fixSpecialChars($string) {
        // Replace specific problematic characters
        $problematicChars = [
            "\x85", // Ellipsis
            "\x91", "\x92", // Single quotes
            "\x93", "\x94", // Double quotes
            "\x96", // En dash
            "\x97"  // Em dash
        ];
        
        $replacements = [
            '...', // Replace ellipsis with three dots
            "'", "'", // Replace quotes with standard single quotes
            '"', '"', // Replace quotes with standard double quotes
            '-', // Replace en dash with hyphen
            '--' // Replace em dash with two hyphens
        ];
        
        return str_replace($problematicChars, $replacements, $string);
    }
    
    /**
     * Parse numeric value from string - handles empty, non-numeric values, and formats like "1(2)"
     */
    private function parseNumericValue($value) {
        if (empty($value)) {
            return 0;
        }
        
        // Handle formats like "1(2)" - extract the first number only
        if (preg_match('/^(\d+)\(\d+\)$/', $value, $matches)) {
            return (int)$matches[1];
        }
        
        // Extract numbers from string if mixed
        if (preg_match('/(-?\d+(\.\d+)?)/', $value, $matches)) {
            return floatval($matches[1]);
        }
        
        return is_numeric($value) ? floatval($value) : 0;
    }
    
    /**
     * Parse previous rank value - handle 'not in top' cases and formats like "1(2)"
     */
    private function parsePreviousRank($value) {
        if (empty($value)) {
            return 0;
        }
        
        // Check for "not in top" strings
        if (strpos(strtolower($value), 'not') !== false) {
            return EngineConfig::NOT_RANKED;
        }
        
        // Handle formats like "1(2)" - extract the first number only
        if (preg_match('/^(\d+)\(\d+\)$/', $value, $matches)) {
            return (int)$matches[1];
        }
        
        return $this->parseNumericValue($value);
    }
    
    /**
     * Parse difference value - handle special cases like 'dropped', 'entered', etc.
     */
    private function parseDifference($value) {
        if (empty($value)) {
            return EngineConfig::NO_CHANGE;
        }
        
        $value = strtolower(trim($value));
        
        // Handle special text cases
        switch ($value) {
            case 'dropped':
                return EngineConfig::DROPPED;
            case 'entered':
                return EngineConfig::ENTERED;
            case 'stays out':
                return EngineConfig::NO_CHANGE;
            case 'new':
                return EngineConfig::ENTERED;
            default:
                return $this->parseNumericValue($value);
        }
    }
}