<?php

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
