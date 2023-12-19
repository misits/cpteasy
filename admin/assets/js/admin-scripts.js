/**
 * Handles UI interactions and CodeMirror initialization for the admin page.
 * Uses jQuery for event handling and CodeMirror for code editing.
 * 
 * @class AdminPageHandler
 */
jQuery(document).ready(function ($) {
    /**
     * Initializes CodeMirror for the textarea with the ID "file-content".
     */
    function initializeCodeMirror() {
      if (document.getElementById("file-content")) {
        var editor = CodeMirror.fromTextArea(
          document.getElementById("file-content"),
          {
            lineNumbers: true,
            matchBrackets: true,
            mode: "php",
            indentUnit: 4,
            indentWithTabs: true,
            enterMode: "keep",
            tabMode: "shift",
          }
        );
      }
    }
  
    // Initialize CodeMirror if not already initialized
    if (!window.codemirrorInitialized) {
      initializeCodeMirror();
      window.codemirrorInitialized = true;
    }
  
    /**
     * Handles switching between tabs and updating the URL hash.
     * 
     * @param {string} tabId - The ID of the tab to switch to.
     */
    function handleTabSwitch(tabId) {
      // Hide all tab contents
      $(".tab-content").hide();

      $(".tab-content").css("opacity", "1");
  
      // Remove active class from all tabs
      $(".nav-tab").removeClass("nav-tab-active");
  
      // Show the selected tab content
      $(tabId).show();
  
      // Add active class to the clicked tab
      $('.nav-tab[href="' + tabId + '"]').addClass("nav-tab-active");
    }
  
    // Get initial tab ID from URL hash or default to "#tab1"
    var initialTabId = window.location.hash || "#tab1";
    handleTabSwitch(initialTabId);
  
    // Tab click event
    $(".nav-tab-wrapper a").on("click", function (e) {
      e.preventDefault();
  
      // Get the href attribute of the clicked tab
      var tabId = $(this).attr("href");
  
      // Update the URL hash
      window.location.hash = tabId;
  
      // Handle tab switching
      handleTabSwitch(tabId);

      // remove the success message
      $("#response-message").removeClass().html("");
    });
  
    // Make the list collapsible
    $(".collapsible-title").click(function () {
      $(this).next(".collapsible-list").toggleClass("collapsed");
    });
  });
  