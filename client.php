<?php
// Include functions
require_once 'includes/functions.php';

// Check if domain parameter exists
if (!isset($_GET['domain']) || empty($_GET['domain'])) {
    // Redirect to index if no domain provided
    header('Location: index.php');
    exit;
}

$domain = $_GET['domain'];

// Get client reports
$reports = getClientReports($domain);

// Get some stats
$totalReports = count($reports);
$latestReport = !empty($reports) ? $reports[0] : null;

// Page title
$page_title = "Reports for " . htmlspecialchars($domain);

// Include header
include 'includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center">
        <a href="index.php" class="bg-white rounded-md p-2 mr-2 hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($domain); ?></h1>
    </div>
    
    <a href="import.php?domain=<?php echo urlencode($domain); ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
        Import New Report
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Reports</p>
                <p class="text-xl font-semibold"><?php echo $totalReports; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Latest Report</p>
                <p class="text-xl font-semibold">
                    <?php echo $latestReport ? formatReportPeriod($latestReport['report_period']) : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Last Updated</p>
                <p class="text-xl font-semibold">
                    <?php echo $latestReport ? date('M j, Y', strtotime($latestReport['import_date'])) : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-700">Report History</h2>
        <div class="flex items-center">
            <span class="bg-blue-100 text-blue-800 text-sm font-medium py-1 px-3 rounded-full mr-2">
                <?php echo $totalReports; ?> Reports
            </span>
            <?php if ($totalReports > 0): ?>
                <a href="report-details.php?id=<?php echo $latestReport['report_id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-1 rounded-md">
                    View Latest
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (empty($reports)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        No reports found for this client. Import data to get started.
                    </p>
                    <div class="mt-2">
                        <a href="import.php?domain=<?php echo urlencode($domain); ?>" class="text-sm font-medium text-yellow-700 hover:text-yellow-600">
                            Import your first report &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Report Period
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Import Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Google Keywords
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bing Keywords
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports as $report): ?>
                        <?php
                        // Get keyword counts for each search engine
                        $google_count = getKeywordCount($report['report_id'], 'google_data');
                        $bing_count = getKeywordCount($report['report_id'], 'bing_data');
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-sm">
                                            <?php echo date('M', strtotime($report['report_period'] . '-01')); ?>
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo formatReportPeriod($report['report_period']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo date('F j, Y', strtotime($report['import_date'])); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo date('g:i A', strtotime($report['import_date'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($google_count > 0): ?>
                                    <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <?php echo $google_count; ?> keywords
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No data
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($bing_count > 0): ?>
                                    <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo $bing_count; ?> keywords
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No data
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="report-details.php?id=<?php echo $report['report_id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md text-sm font-medium">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Function to get keyword count for a report/search engine
function getKeywordCount($report_id, $table) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM $table WHERE report_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['count'];
    }
    
    return 0;
}

// Include footer
include 'includes/footer.php';
?>