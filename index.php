<?php
// Include functions
require_once 'includes/functions.php';

// Page title
$page_title = "Client Dashboard";

// Get client list
$clients = getClientList();

// Include header
include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-slate-900">SEO Performance Dashboard</h1>
    <a href="import.php" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-md flex items-center shadow-sm transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Import New Report
    </a>
</div>

<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Client Domains</h2>
        <span class="bg-sky-50 text-sky-700 text-xs font-medium py-1 px-2.5 rounded-full">
            <?php echo count($clients); ?> Clients
        </span>
    </div>
    
    <?php if (empty($clients)): ?>
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        No clients found. Import data to get started.
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="overflow-hidden border border-slate-200 rounded-lg">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Client Domain
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Latest Report
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Total Reports
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Last Updated
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php foreach ($clients as $domain): ?>
                        <?php 
                        // Get reports for this client
                        $reports = getClientReports($domain);
                        $latestReport = !empty($reports) ? $reports[0] : null;
                        $totalReports = count($reports);
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 bg-slate-100 rounded-full flex items-center justify-center">
                                        <span class="text-slate-700 font-medium text-sm">
                                            <?php echo strtoupper(substr($domain, 0, 1)); ?>
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900"><?php echo $domain; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($latestReport): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-emerald-50 text-emerald-700">
                                        <?php echo formatReportPeriod($latestReport['report_period']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-slate-100 text-slate-700">
                                        None
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-700"><?php echo $totalReports; ?> reports</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($latestReport): ?>
                                    <div class="text-sm text-slate-500">
                                        <?php echo date('M j, Y', strtotime($latestReport['import_date'])); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm text-slate-500">-</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="client.php?domain=<?php echo urlencode($domain); ?>" class="text-sky-600 hover:text-sky-800 mr-3">
                                    View Reports
                                </a>
                                <a href="import.php?domain=<?php echo urlencode($domain); ?>" class="text-emerald-600 hover:text-emerald-800">
                                    Import New
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
    <h2 class="text-xl font-semibold text-slate-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="import.php" class="group border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:bg-slate-50 transition-colors flex items-center">
            <div class="bg-sky-50 p-3 rounded-full mr-4 group-hover:bg-sky-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
            </div>
            <div>
                <h3 class="font-medium text-slate-900">Import New Report</h3>
                <p class="text-sm text-slate-500">Upload and process new SEO data</p>
            </div>
        </a>
        
        <div class="border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:bg-slate-50 transition-colors flex items-center cursor-pointer group">
            <div class="bg-violet-50 p-3 rounded-full mr-4 group-hover:bg-violet-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <h3 class="font-medium text-slate-900">View Statistics</h3>
                <p class="text-sm text-slate-500">See total reports and rankings</p>
            </div>
        </div>
        
        <div class="border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:bg-slate-50 transition-colors flex items-center cursor-pointer group">
            <div class="bg-emerald-50 p-3 rounded-full mr-4 group-hover:bg-emerald-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h3 class="font-medium text-slate-900">Documentation</h3>
                <p class="text-sm text-slate-500">Learn how to use the system</p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>