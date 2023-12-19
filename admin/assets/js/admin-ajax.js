/**
 * Handles AJAX requests for various admin actions related to custom post types.
 * Uses jQuery for event handling and AJAX calls.
 *
 * @class CptwpAdmin
 */
jQuery(document).ready(function ($) {
  /**
   * Handles the click event for the "Save File Content" button.
   */
  $("#save-file-content").submit(function(e) {
    e.preventDefault();

    var nonce = cptwp_admin_vars.nonces.save_file_content; // Use the correct nonce

    // Perform AJAX request
    $.ajax({
      type: "post",
      url: cptwp_admin_vars.ajax_url,
      data: {
        action: "save_file_content", // AJAX action name
        security: nonce,
        formData: $("#save-file-content").serializeArray(),
      },
      success: function (response) {
        // Handle the response if needed
        $("#response-message").removeClass().addClass('notice notice-success is-dismissible').html(response);
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
      },
    });
  });

  /**
   * Handles the click event for the "Generate Template" button.
   */
  $(".generate-template").on("click", function () {
    var postType = $(this).data("post-type");
    var nonce = cptwp_admin_vars.nonces.generate_template; // Use the correct nonce

    // Perform AJAX request
    $.ajax({
      type: "post",
      url: cptwp_admin_vars.ajax_url,
      data: {
        action: "create_cpt_template",
        security: nonce,
        post_type: postType,
      },
      success: function (response) {
        $("#response-message").removeClass().addClass('notice notice-success is-dismissible').html(response);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText); // Log the responseText for debugging
      },
    });
  });

  /**
   * Handles the click event for the "Delete Post Type" button.
   */
  $(".delete-post-type").on("click", function () {
    var postType = $(this).data("post-type");
    var nonce = cptwp_admin_vars.nonces.delete_model; // Use the correct nonce

    // Confirm deletion
    var confirmDelete = confirm(
      "Are you sure you want to delete the post type?"
    );
    if (!confirmDelete) {
      return;
    }

    // Perform AJAX request
    $.ajax({
      type: "post",
      url: cptwp_admin_vars.ajax_url,
      data: {
        action: "delete_cpt_model",
        security: nonce,
        post_type: postType,
      },
      success: function (response) {
        $("#response-message").removeClass().addClass('notice notice-success is-dismissible').html(response);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText); // Log the responseText for debugging
      },
    });
  });

  /**
   * Handles the click event for the "Toggle Activation" button.
   */
  $(".toggle-activation").on("click", function () {
    var postType = $(this).data("post-type");
    var nonce = cptwp_admin_vars.nonces.toggle_activation; // Use the correct nonce

    // Perform AJAX request
    $.ajax({
      type: "post",
      url: cptwp_admin_vars.ajax_url,
      data: {
        action: "toggle_cpt_activation",
        security: nonce,
        post_type: postType,
      },
      success: function (response) {
        $("#response-message").removeClass().addClass('notice notice-success is-dismissible').html(response);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText); // Log the responseText for debugging
      },
    });
  });
});
