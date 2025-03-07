<?php
require_once __DIR__ . '/../config/EngineConfig.php';


// TODO: Make better navigation
function include_navigation($params = []) {
    $defaults = [
        'report' => ['report_id' => 0, 'client_domain' => ''],
        'navigation' => ['prev_report_id' => null, 'next_report_id' => null, 'available_periods' => []],
        'period' => date('Y-m')
    ];
    
    $data = array_merge($defaults, $params);
    
    extract($data);
    
    include __DIR__ . '/../views/layout/navigation.php';
}

function findColumnValue($data, $engine, $columnType) {
    $baseColumnName = ENGINES[$engine][$columnType] ?? '';
    
    foreach ($data as $columnName => $value) {
        // Match column names with possible variations like "US", "HOU", or other region-based differences
        $pattern = "/^" . preg_quote($baseColumnName, '/') . "(?: US| HOU|)?$/i";
        
        if (preg_match($pattern, $columnName)) {
            return $value;
        }
    }
    
    return null; // Return null if no matching column is found
}