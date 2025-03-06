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
        
        // Find column indexes for all engines
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
            if (count($data) < count($headers)) {
                continue;
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
                    
                    $rowData['engines'][$engine] = [
                        'rank' => $rank,
                        'previous_rank' => $this->parsePreviousRank(
                            $data[$engineIndexes['prevRank']] ?? ''
                        ),
                        'difference' => $this->parseDifference(
                            $data[$engineIndexes['diff']] ?? ''
                        ),
                        'serp_features' => $data[$engineIndexes['serp']] ?? '',
                        'url' => $data[$engineIndexes['url']] ?? ''
                    ];
                }
            }
            
            $parsedData[] = $rowData;
        }
        
        fclose($handle);
        return $parsedData;
    }
    
    /**
     * Find column indexes in CSV headers
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
        
        // Find indexes for each engine
        foreach (EngineConfig::getAllEngines() as $engine => $columns) {
            $engineIndexes = [
                'rank' => array_search($columns['rankColumn'], $headers),
                'prevRank' => array_search($columns['prevRankColumn'], $headers),
                'diff' => array_search($columns['diffColumn'], $headers),
                'serp' => array_search($columns['serpColumn'], $headers),
                'url' => array_search($columns['urlColumn'], $headers)
            ];
            
            // Only include this engine if we found the rank column
            if ($engineIndexes['rank'] !== false) {
                $indexes[$engine] = $engineIndexes;
            }
        }
        
        return $indexes;
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