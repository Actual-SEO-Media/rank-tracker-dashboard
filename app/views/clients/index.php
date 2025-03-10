<?php
    include __DIR__ . '/../layout/header.php';
?>

<div class="container mx-auto px-4 py-6">

  <!-- Quick Actions Card -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-xl font-semibold text-gray-700 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <a href="<?php echo SITE_URL; ?>/import" class="border border-gray-200 rounded-lg p-4 hover:bg-blue-50 transition-colors flex items-center group">
              <div class="bg-blue-100 p-3 rounded-full mr-4 group-hover:bg-blue-200 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                  </svg>
              </div>
              <div>
                  <h3 class="font-medium text-gray-800">Import New Report</h3>
                  <p class="text-sm text-gray-600">Upload and process new SEO data</p>
              </div>
          </a>
          
          <div class="border border-gray-200 rounded-lg p-4 hover:bg-purple-50 transition-colors flex items-center group cursor-pointer">
              <div class="bg-purple-100 p-3 rounded-full mr-4 group-hover:bg-purple-200 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
              </div>
              <div>
                  <h3 class="font-medium text-gray-800">View Statistics</h3>
                  <p class="text-sm text-gray-600">See total reports and rankings</p>
              </div>
          </div>
          
          <div class="border border-gray-200 rounded-lg p-4 hover:bg-green-50 transition-colors flex items-center group cursor-pointer">
              <div class="bg-green-100 p-3 rounded-full mr-4 group-hover:bg-green-200 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
              </div>
              <div>
                  <h3 class="font-medium text-gray-800">Documentation</h3>
                  <p class="text-sm text-gray-600">Learn how to use the system</p>
              </div>
          </div>
      </div>
  </div>
  <!-- Header/Import Button -->
<?php if (empty($clients)): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    No clients found. Import data to get started.
                </p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Client Domain
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Latest Report
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Reports
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last Updated
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($clients as $client): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-lg">
                                        <?php echo strtoupper(substr($client['domain'], 0, 1)); ?>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $client['domain']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($client['latestReport']): ?>
                                <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php 
                                    $period = $client['latestReport']['report_period'];
                                    echo date('F Y', strtotime($period . '-01'));
                                    ?>
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    None
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $client['totalReports']; ?> reports</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($client['latestReport']): ?>
                                <div class="text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($client['latestReport']['import_date'])); ?>
                                </div>
                            <?php else: ?>
                                <div class="text-sm text-gray-500">-</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo SITE_URL; ?>/reports/<?php echo urlencode($client['domain']); ?>" 
                               class="text-gray-500 hover:text-gray-700 mr-3 transition-colors"
                               title="View Reports">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="<?php echo SITE_URL; ?>/import/<?php echo urlencode($client['domain']); ?>" 
                               class="text-gray-500 hover:text-gray-700 transition-colors"
                               title="Import New">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>    
    

</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>