/**
 * Handles AJAX requests for letious admin actions related to custom post types.
 * Uses jQuery for event handling and AJAX calls.
 *
 * @class CptwpAdmin
 */
jQuery(document).ready(function ($) {
  /**
   * Handles the click event for the "Create Post Type" button.
   */
  $("#create-model-form").submit(function (e) {
    e.preventDefault();

    $.ajax({
      type: "post",
      url: ajaxurl, // WordPress AJAX URL
      data: {
        action: "create_cpt_models", // AJAX action name
        security: $("#create_model_nonce").val(), // Nonce for security
        formData: $("#create-model-form").serializeArray(), // Use serializeArray() to serialize form data
      },
      success: function (response) {
        // Handle the AJAX response
        $("#response-message").removeClass();
        $("#response-message")
          .addClass("notice notice-success is-dismissible")
          .html(response);

          // reload after 2 seconds
        setTimeout(function () {
          location.reload();
        }, 500);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText); // Log the responseText for debugging
      },
    });
  });
  /**
   * Handles the click event for the "Save File Content" button.
   */
  $("#save-file-content").submit(function (e) {
    e.preventDefault();

    let nonce = cptwp_admin_vars.nonces.save_file_content; // Use the correct nonce

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
        $("#response-message").removeClass();
        $("#response-message")
          .addClass("notice notice-success is-dismissible")
          .html(response);
          
          // reload after 2 seconds
        setTimeout(function () {
          location.reload();
        }, 500);
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
    let postType = $(this).data("post-type");
    let nonce = cptwp_admin_vars.nonces.generate_template; // Use the correct nonce

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
        $("#response-message").removeClass();
        $("#response-message")
          .addClass("notice notice-success is-dismissible")
          .html(response);

          // reload after 2 seconds
        setTimeout(function () {
          location.reload();
        }, 500);
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
    let postType = $(this).data("post-type");
    let nonce = cptwp_admin_vars.nonces.delete_model; // Use the correct nonce

    // Confirm deletion
    let confirmDelete = confirm(
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
        $("#response-message").removeClass();
        $("#response-message")
          .addClass("notice notice-success is-dismissible")
          .html(response);

          // reload after 2 seconds
        setTimeout(function () {
          location.reload();
        }, 500);
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
    let postType = $(this).data("post-type");
    let nonce = cptwp_admin_vars.nonces.toggle_activation; // Use the correct nonce

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
        $("#response-message").removeClass();
        $("#response-message")
          .addClass("notice notice-success is-dismissible")
          .html(response);

          // reload after 2 seconds
          setTimeout(function () {
            location.reload();
          }, 500);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText); // Log the responseText for debugging
      },
    });
  });

  /**
   * Handles the click event for the "Generate Assets" button.
   */
  $(".create-cpt-assets").on("click", function (e) {
    e.preventDefault();
    let nonce = cptwp_admin_vars.nonces.generate_assets; // Use the correct nonce

    $.ajax({
      type: "post",
      url: cptwp_admin_vars.ajax_url, // WordPress AJAX URL
      data: {
        action: "generate_cpt_assets", // AJAX action name
        security: nonce,
      },
      success: function (response) {
        // Handle the AJAX response
        $("#response-message").removeClass();
        $("#response-message").addClass('notice notice-success is-dismissible').html(response);

        // reload after 2 seconds
        setTimeout(function () {
          location.reload();
        }, 500);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText); // Log the responseText for debugging
      },
    });
  });
});
