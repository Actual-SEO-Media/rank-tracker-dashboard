<?php
// Include functions
require_once 'includes/functions.php';

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Increase PHP limits
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

// Add encoding detection and cleaning functions
function detectEncoding($file) {
    $content = file_get_contents($file);
    $detected = mb_detect_encoding($content, ['UTF-8', 'UTF-16', 'ISO-8859-1', 'ISO-8859-15', 'Windows-1252', 'ASCII'], true);
    return $detected ?: 'Windows-1252';
}

function cleanText($str, $sourceEncoding = 'Windows-1252') {
    if ($sourceEncoding != 'UTF-8') {
        $str = iconv($sourceEncoding, 'UTF-8//TRANSLIT//IGNORE', $str);
    }
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);
    return $str;
}

// Initialize variables
$success = false;
$error = '';
$domain = isset($_GET['domain']) ? $_GET['domain'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are provided
    if (empty($_POST['client_domain'])) {
        $error = 'Client domain is required';
    } elseif (empty($_POST['report_period'])) {
        $error = 'Report period is required';
    } elseif (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
        $error = 'Please select a valid CSV file';
    } else {
        // Get form data
        $client_domain = trim($_POST['client_domain']);
        $report_period = trim($_POST['report_period']);
        $import_date = date('Y-m-d');
        $file = $_FILES['csv_file'];
        
        // Validate CSV file
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            $error = 'Only CSV files are allowed';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = uniqid('seo_') . '_' . $file['name'];
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $error = 'Failed to upload file. Error code: ' . $file['error'];
            } else {
                // Detect file encoding
                $fileEncoding = detectEncoding($filepath);
                
                // Start database transaction
                $conn->begin_transaction();
                try {
                    // Check if report exists for this domain and period
                    $report_id = 0;
                    if (reportExists($client_domain, $report_period)) {
                        // Get existing report ID
                        $stmt = $conn->prepare("SELECT report_id FROM reports WHERE client_domain = ? AND report_period = ?");
                        $stmt->bind_param("ss", $client_domain, $report_period);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $report_id = $row['report_id'];
                        }
                    } else {
                        // Create new report
                        $stmt = $conn->prepare("INSERT INTO reports (import_date, report_period, client_domain, file_name) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $import_date, $report_period, $client_domain, $file['name']);
                        $stmt->execute();
                        $report_id = $conn->insert_id;
                    }
                    
                    if ($report_id > 0) {
                        // Process CSV file and import data
                        if (($handle = fopen($filepath, "r")) !== FALSE) {
                            // Get header row to identify columns
                            $headers_raw = fgetcsv($handle);
                            $headers = array_map(function($h) use ($fileEncoding) {
                                return cleanText($h, $fileEncoding);
                            }, $headers_raw);
                            
                            // Clear existing data for this report
                            $tables = ['google_data', 'google_mobile_data', 'yahoo_data', 'bing_data'];
                            foreach ($tables as $table) {
                                $stmt = $conn->prepare("DELETE FROM $table WHERE report_id = ?");
                                $stmt->bind_param("i", $report_id);
                                $stmt->execute();
                            }
                            
                            // Prepare statements for data insertion
                            $google_stmt = $conn->prepare("INSERT INTO google_data (report_id, keyword, visibility, visibility_difference, rank, previous_rank, difference, serp_features, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $google_mobile_stmt = $conn->prepare("INSERT INTO google_mobile_data (report_id, keyword, visibility, visibility_difference, rank, previous_rank, difference, serp_features, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $yahoo_stmt = $conn->prepare("INSERT INTO yahoo_data (report_id, keyword, visibility, visibility_difference, rank, previous_rank, difference, serp_features, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $bing_stmt = $conn->prepare("INSERT INTO bing_data (report_id, keyword, visibility, visibility_difference, rank, previous_rank, difference, serp_features, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            
                            // Find column indexes based on header names
                            $keyword_idx = array_search('Keyword', $headers);
                            $visibility_idx = array_search('Visibility', $headers);
                            $vis_diff_idx = array_search('Visibility Difference', $headers);
                            
                            // Google columns
                            $google_rank_idx = array_search('Google HOU Rank', $headers);
                            $google_prev_idx = array_search('Google HOU Previous Rank', $headers);
                            $google_diff_idx = array_search('Google HOU Difference', $headers);
                            $google_serp_idx = array_search('Google HOU SERP Features', $headers);
                            $google_url_idx = array_search('Google HOU URL Found', $headers);
                            
                            // Google Mobile columns
                            $gmobile_rank_idx = array_search('Google Mobile HOU Rank', $headers);
                            $gmobile_prev_idx = array_search('Google Mobile HOU Previous Rank', $headers);
                            $gmobile_diff_idx = array_search('Google Mobile HOU Difference', $headers);
                            $gmobile_serp_idx = array_search('Google Mobile HOU SERP Features', $headers);
                            $gmobile_url_idx = array_search('Google Mobile HOU URL Found', $headers);
                            
                            // Yahoo columns
                            $yahoo_rank_idx = array_search('Yahoo! Rank', $headers);
                            $yahoo_prev_idx = array_search('Yahoo! Previous Rank', $headers);
                            $yahoo_diff_idx = array_search('Yahoo! Difference', $headers);
                            $yahoo_serp_idx = array_search('Yahoo! SERP Features', $headers);
                            $yahoo_url_idx = array_search('Yahoo! URL Found', $headers);
                            
                            // Bing columns
                            $bing_rank_idx = array_search('Bing US Rank', $headers);
                            $bing_prev_idx = array_search('Bing US Previous Rank', $headers);
                            $bing_diff_idx = array_search('Bing US Difference', $headers);
                            $bing_serp_idx = array_search('Bing US SERP Features', $headers);
                            $bing_url_idx = array_search('Bing US URL Found', $headers);
                            
                            // Error if required columns are missing
                            if ($keyword_idx === false || $visibility_idx === false || $vis_diff_idx === false) {
                                throw new Exception('CSV file is missing required columns');
                            }
                            
                            // Initialize counters
                            $google_count = 0;
                            $gmobile_count = 0;
                            $yahoo_count = 0;
                            $bing_count = 0;
                            
                            // Read data rows
                            while (($data = fgetcsv($handle)) !== FALSE) {
                                // Extract common data
                                $keyword = cleanText($data[$keyword_idx], $fileEncoding);
                                $visibility = intval($data[$visibility_idx]);
                                $visibility_difference = intval($data[$vis_diff_idx]);
                                
                                // Process Google data
                                if ($google_rank_idx !== false && !empty($data[$google_rank_idx])) {
                                    $rank = intval($data[$google_rank_idx]);
                                    $previous_rank = intval($data[$google_prev_idx]);
                                    $difference = intval($data[$google_diff_idx]);
                                    $serp_features = cleanText($data[$google_serp_idx], $fileEncoding);
                                    $url = cleanText($data[$google_url_idx], $fileEncoding);
                                    
                                    $google_stmt->bind_param("isiiiiiss", $report_id, $keyword, $visibility, $visibility_difference, $rank, $previous_rank, $difference, $serp_features, $url);
                                    $google_stmt->execute();
                                    $google_count++;
                                }
                                
                                // Process Google Mobile data
                                if ($gmobile_rank_idx !== false && !empty($data[$gmobile_rank_idx])) {
                                    $rank = intval($data[$gmobile_rank_idx]);
                                    $previous_rank = intval($data[$gmobile_prev_idx]);
                                    $difference = intval($data[$gmobile_diff_idx]);
                                    $serp_features = cleanText($data[$gmobile_serp_idx], $fileEncoding);
                                    $url = cleanText($data[$gmobile_url_idx], $fileEncoding);
                                    
                                    $google_mobile_stmt->bind_param("isiiiiiss", $report_id, $keyword, $visibility, $visibility_difference, $rank, $previous_rank, $difference, $serp_features, $url);
                                    $google_mobile_stmt->execute();
                                    $gmobile_count++;
                                }
                                
                                // Process Yahoo data
                                if ($yahoo_rank_idx !== false && !empty($data[$yahoo_rank_idx])) {
                                    $rank = intval($data[$yahoo_rank_idx]);
                                    $previous_rank = intval($data[$yahoo_prev_idx]);
                                    $difference = intval($data[$yahoo_diff_idx]);
                                    $serp_features = cleanText($data[$yahoo_serp_idx], $fileEncoding);
                                    $url = cleanText($data[$yahoo_url_idx], $fileEncoding);
                                    
                                    $yahoo_stmt->bind_param("isiiiiiss", $report_id, $keyword, $visibility, $visibility_difference, $rank, $previous_rank, $difference, $serp_features, $url);
                                    $yahoo_stmt->execute();
                                    $yahoo_count++;
                                }
                                
                                // Process Bing data
                                if ($bing_rank_idx !== false && !empty($data[$bing_rank_idx])) {
                                    $rank = intval($data[$bing_rank_idx]);
                                    $previous_rank = intval($data[$bing_prev_idx]);
                                    $difference = intval($data[$bing_diff_idx]);
                                    $serp_features = cleanText($data[$bing_serp_idx], $fileEncoding);
                                    $url = cleanText($data[$bing_url_idx], $fileEncoding);
                                    
                                    $bing_stmt->bind_param("isiiiiiss", $report_id, $keyword, $visibility, $visibility_difference, $rank, $previous_rank, $difference, $serp_features, $url);
                                    $bing_stmt->execute();
                                    $bing_count++;
                                }
                            }
                            fclose($handle);
                            
                            // Close prepared statements
                            $google_stmt->close();
                            $google_mobile_stmt->close();
                            $yahoo_stmt->close();
                            $bing_stmt->close();
                            
                            // Commit transaction
                            $conn->commit();
                            $success = true;
                            $success_message = "Successfully imported data: Google ($google_count rows), Google Mobile ($gmobile_count rows), Yahoo ($yahoo_count rows), Bing ($bing_count rows).";
                        } else {
                            $error = 'Failed to open CSV file';
                            $conn->rollback();
                        }
                    } else {
                        $error = 'Failed to create report';
                        $conn->rollback();
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Error during import: ' . $e->getMessage();
                }
            }
        }
    }
}

// Page title
$page_title = "Import SEO Data";


// Include header
include 'includes/header.php';
?>

<div class="mb-4">
  <a href="index.php" class="text-slate-900 hover:text-slate-700 flex items-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
    </svg>
    Back to Dashboard
  </a>
</div>

<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
  <h2 class="text-xl font-semibold text-slate-900 mb-6">Import SEO Data</h2>
  
  <?php if ($success): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6">
      <p><?php echo $success_message; ?></p>
      <p class="mt-2">
        <a href="client.php?domain=<?php echo urlencode($client_domain); ?>" class="text-emerald-700 font-medium hover:underline inline-flex items-center">
          View reports for <?php echo htmlspecialchars($client_domain); ?>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </a>
      </p>
    </div>
  <?php endif; ?>
  
  <?php if ($error): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 mb-6">
      <p><?php echo $error; ?></p>
    </div>
  <?php endif; ?>
  
  <form action="import.php" method="post" enctype="multipart/form-data" class="space-y-4">
    <div>
      <label for="client_domain" class="block text-sm font-medium text-slate-700 mb-1">
        Client Domain*
      </label>
      <input type="text" id="client_domain" name="client_domain"
             value="<?php echo htmlspecialchars($domain); ?>"
             class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm
                    placeholder-slate-400
                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500"
             required>
      <p class="text-sm text-slate-500 mt-1">Example: astrocityscrap.com</p>
    </div>
    
    <div>
      <label for="report_period" class="block text-sm font-medium text-slate-700 mb-1">
        Report Period* (YYYY-MM)
      </label>
      <input type="month" id="report_period" name="report_period"
             value="<?php echo date('Y-m'); ?>"
             class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-sm shadow-sm
                    placeholder-slate-400
                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500"
             required>
      <p class="text-sm text-slate-500 mt-1">The month this report covers</p>
    </div>
    
    <div>
      <label for="csv_file" class="block text-sm font-medium text-slate-700 mb-1">
        CSV File*
      </label>
      <div class="flex items-center justify-center w-full">
        <label for="csv_file" class="flex flex-col items-center justify-center w-full px-3 py-6 border-2 border-slate-300 border-dashed rounded-md cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
          <div class="flex flex-col items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="mt-2 text-sm text-slate-500">
              <span class="font-medium">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-slate-500">
              CSV files only
            </p>
          </div>
          <input id="csv_file" name="csv_file" type="file" accept=".csv" class="hidden" required />
        </label>
      </div>
    </div>
    
    <div class="pt-4">
      <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-sm font-medium shadow-sm flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
        Import Data
      </button>
    </div>
  </form>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>