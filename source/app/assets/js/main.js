$(document).ready(function () {
  // Tab handling for report details page
  if ($("#seoTabs").length) {
    // Get all tab buttons
    const tabButtons = $("#seoTabs button");

    // Add click event to tab buttons
    tabButtons.on("click", function () {
      // Get target tab
      const target = $(this).data("tabs-target");

      // Remove active class from all tabs
      $("#tabContent > div").addClass("hidden");

      // Show target tab
      $(target).removeClass("hidden");

      // Update active state for buttons
      tabButtons
        .removeClass("text-blue-600 border-blue-600")
        .addClass("text-gray-500 border-transparent");
      $(this)
        .removeClass("text-gray-500 border-transparent")
        .addClass("text-blue-600 border-blue-600");
    });
  }

  // CSV file validation
  $("#csv_file").on("change", function () {
    const fileInput = this;
    const filePath = fileInput.value;
    const allowedExtensions = /(\.csv)$/i;

    if (!allowedExtensions.exec(filePath)) {
      alert("Please upload a CSV file only");
      fileInput.value = "";
      return false;
    }
  });

  // Format the report period input on import form
  $("#report_period").on("change", function () {
    const value = $(this).val();
    if (value) {
      // Make sure it's in YYYY-MM format
      const parts = value.split("-");
      if (parts.length === 2) {
        $(this).val(parts[0] + "-" + parts[1]);
      }
    }
  });
});
