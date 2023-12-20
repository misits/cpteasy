<?php

namespace Cpteasy\admin;

use Cpteasy\includes\utils\Register;
use Cpteasy\includes\utils\Icon;

// Prevent direct access.
defined('ABSPATH') or exit;

class AdminServices
{

    public static function register()
    {
        add_action("admin_menu", [self::class, "register_admin_menu"]);
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('cpteasy-icomoon-style', plugins_url('/assets/stylesheets/icomoon.css', __FILE__));
            wp_enqueue_style('cpteasy-style', plugins_url('/assets/stylesheets/admin.css', __FILE__));
            wp_enqueue_script('cpteasy-scripts', plugins_url('/assets/js/admin-scripts.js', __FILE__), array('jquery'), null, true);
        });
    }

    /**
     * Register admin menu.
     */
    public static function register_admin_menu()
    {
        add_options_page(
            'Cpteasy', // page_title
            'Cpteasy', // menu_title
            'manage_options', // capability
            'cpteasy', // menu_slug
            [self::class, "render_admin_page"] // function
        );
    }

    /**
     * Render file editor.
     */

    public static function enqueue_codemirror_scripts()
    {
        // Enqueue CodeMirror stylesheet
        wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css');


        // Enqueue CodeMirror script
        wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js', array('jquery'), null, true);

        // Enqueue PHP mode for CodeMirror
        wp_enqueue_script('codemirror-mode-php', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/clike/clike.min.js', array('codemirror'), null, true);

        // Enqueue additional required scripts
        wp_enqueue_script('codemirror-addon-edit/matchbrackets', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/matchbrackets.min.js', array('codemirror'), null, true);
        wp_enqueue_script('codemirror-mode/htmlmixed/htmlmixed', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.min.js', array('codemirror'), null, true);
        wp_enqueue_script('codemirror-mode/xml/xml', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/xml/xml.min.js', array('codemirror'), null, true);
        wp_enqueue_script('codemirror-mode/javascript/javascript', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/javascript/javascript.min.js', array('codemirror'), null, true);
        wp_enqueue_script('codemirror-mode/css/css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/css/css.min.js', array('codemirror'), null, true);
        wp_enqueue_script('codemirror-addon/php/php', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/php/php.min.js', array('codemirror'), null, true);
    }


    /**
     * Render admin page.
     */
    public static function render_admin_page()
    {
?>
        <div class="wrap">
            <h2><?= __('Cpteasy', 'cpteasy'); ?></h2>

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="#tab1"><?= __('List of Post Types', 'cpteasy'); ?></a>
                <a class="nav-tab" href="#tab2"><?= __('Create new model', 'cpteasy'); ?></a>
                <a class="nav-tab" href="#tab3"><?= __('File editor', 'cpteasy'); ?></a>
            </h2>

            <?php
            // Enqueue scripts
            wp_enqueue_script('cpteasy-ajax-scripts', plugins_url('/assets/js/admin-ajax.js', __FILE__), array('jquery'), null, true);

            wp_localize_script('cpteasy-ajax-scripts', 'cptwp_admin_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonces' => array(
                    'delete_model' => wp_create_nonce('delete_post_type_nonce'),
                    'generate_template' => wp_create_nonce('generate_template_nonce'),
                    'toggle_activation' => wp_create_nonce('toggle_activation_nonce'),
                    'save_file_content' => wp_create_nonce('save_file_content_nonce'),
                    'generate_assets' => wp_create_nonce('generate_assets_nonce'),
                ),
            ));
            ?>

            <div id="response-message"></div> 
              
            <?php self::render_post_types_tab(); ?>

            <?php self::render_create_model_tab(); ?>

            <?php self::render_file_editor_tab(); ?>
        </div>
    <?php
    }

    /**
     * Render file editor.
     */
    public static function render_file_editor()
    {
        $directory = CPTEASY_DIR . '/includes/templates';
        $custom_directory = CPTEASY_DIR . '/includes/templates/custom';
        $assets_directory = CPTEASY_DIR . '/includes/templates/custom/assets';

        // Get the list of files from both directories
        $files = array_merge(scandir($directory), scandir($custom_directory), scandir($assets_directory));

        if (!$files) {
            echo '<div class="error"><p>' . esc_html__('Unable to read directory.', 'cpteasy') . '</p></div>';
            return;
        }

        $file = isset($_GET['file']) ? $_GET['file'] : null;

        // Initialize $file_path
        $file_path = '';

        // Check if the file is in the custom directory
        if (is_file($custom_directory . '/' . $file)) {
            $file_path = $custom_directory . '/' . $file;
        } elseif (is_file($directory . '/' . $file)) {
            // Check if the file is in the main directory
            $file_path = $directory . '/' . $file;
        } elseif (is_file($assets_directory . '/' . $file)) {
            // Check if the file is in the assets directory
            $file_path = $assets_directory . '/' . $file;
        }

        // Check if $file_path is not an empty string before reading the file content
        $file_content = $file_path !== '' ? file_get_contents($file_path) : '';
        $file_content = esc_textarea($file_content);

        self::enqueue_codemirror_scripts();

    ?>
        <div class="wrap cpteasy-editor">


            <form  id="save-file-content">
                <input id="file-name" type="hidden" name="file" value="<?php echo esc_attr($file); ?>">
                <textarea id="file-content" name="file-content" data-extension="<?php echo esc_attr(pathinfo($file, PATHINFO_EXTENSION)); ?>"><?php echo $file_content; ?></textarea>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php echo esc_attr__('Save Changes', 'cpteasy'); ?>">
                    </p>
            </form>


            <div class="folders-structures">
                <p class="collapsible-title"><?= esc_html__('Templates', 'cpteasy'); ?></p>
                <ul class="collapsible-list">
                    <?php
                    // Main directory
                    foreach ($files as $filename) {
                        if ((is_file($directory . '/' . $filename) || is_file($custom_directory . '/' . $filename)) && pathinfo($filename, PATHINFO_EXTENSION) === 'php') {
                            $file_url = esc_url(add_query_arg(
                                array(
                                    'page' => 'cpteasy',
                                    'file' => esc_attr($filename),
                                ),
                                admin_url('options-general.php')
                            ));

                            // Check if a file is currently selected in the URL
                            $current_file = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '';

                            // Add 'active' class to the file if it matches the current file in the URL
                            $active_class = ($current_file === $filename) ? ' class="active"' : '';

                            // Append the hash fragment to the URL to indicate the active tab
                            $file_url .= '#tab3';

                            echo '<li class="collapsible-item"><a href="' . $file_url . '"' . $active_class . '>' . esc_html($filename) . '</a></li>';
                        }
                    }
                    ?>
                </ul>
                <p class="collapsible-title"><?= esc_html__('Assets', 'cpteasy'); ?></p>
                <ul class="collapsible-list">
                    <?php
                    // Main directory
                    foreach ($files as $filename) {
                        if (is_file($assets_directory . '/' . $filename) && (pathinfo($filename, PATHINFO_EXTENSION) === 'js' || pathinfo($filename, PATHINFO_EXTENSION) === 'css')) {
                            $file_url = esc_url(add_query_arg(
                                array(
                                    'page' => 'cpteasy',
                                    'file' => esc_attr($filename),
                                ),
                                admin_url('options-general.php')
                            ));

                            // Check if a file is currently selected in the URL
                            $current_file = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '';

                            // Add 'active' class to the file if it matches the current file in the URL
                            $active_class = ($current_file === $filename) ? ' class="active"' : '';

                            // Append the hash fragment to the URL to indicate the active tab
                            $file_url .= '#tab3';

                            echo '<li class="collapsible-item"><a href="' . $file_url . '"' . $active_class . '>' . esc_html($filename) . '</a></li>';
                        }
                    }
                    ?>
                </ul>
                <?php if (!Register::has_assets()) { ?>
                    <button type="submit" class="create-cpt-assets button button-small button-secondary" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                        <?php echo esc_html__('Generate Assets', 'cpteasy'); ?>
                    </button>
                <?php } ?>
            </div>
        </div>
    <?php
    }

    /**
     * Render post types tab.
     */
    private static function render_post_types_tab()
    {
    ?>
        <div id="tab1" class="tab-content">
            <h3><?= __('List of Post Types', 'cpteasy'); ?></h3>

            <?php
            // Get all active post types
            $post_types = get_post_types([
                'public' => true,
            ], 'objects');

            if (!empty($post_types)) {
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?= __('ID', 'cpteasy'); ?></th>
                            <th><?= __('Name', 'cpteasy'); ?></th>
                            <th><?= __('Label', 'cpteasy'); ?></th>
                            <th><?= __('Status', 'cpteasy'); ?></th>
                            <th><?= __('Actions', 'cpteasy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($post_types as $post_type) {
                        ?>
                            <tr>
                                <td><?php echo esc_html($post_type->name); ?></td>
                                <td><?php echo esc_html($post_type->label); ?></td>
                                <td><?php echo esc_html($post_type->labels->singular_name); ?></td>
                                <?php if (Register::has_template($post_type->name)) { ?>
                                    <td><?php echo ($post_type->show_ui && $post_type->publicly_queryable) ? __('Active', 'cpteady') : __('Inactive', 'cpteady'); ?></td>
                                <?php } else { ?>
                                    <td><?php echo ($post_type->public) ? __('Active', 'cpteady') : __('Inactive', 'cpteady'); ?></td>
                                <?php } ?> 
                                <td>
                                    <?php
                                    if (!in_array($post_type->name, ['post', 'page', 'attachment'])) {
                                    ?>
                                        <button class="delete-post-type button button-small button-tertirary" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-post-type="<?php echo esc_attr($post_type->name); ?>">
                                            <?= __('Delete', 'cpteasy'); ?>
                                        </button>
                                        <?php if (!Register::has_template($post_type->name)) { ?>
                                            <button class="generate-template button button-small button-secondary" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-post-type="<?php echo esc_attr($post_type->name); ?>">
                                                <?= __('Add custom template', 'cpteasy'); ?>
                                            </button>
                                        <?php } ?>
                                        <button class="toggle-activation button button-small button-primary" data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" data-post-type="<?php echo esc_attr($post_type->name); ?>" data-active="<?php echo esc_attr($post_type->publicly_queryable ? 'true' : 'false'); ?>">
                                            <?php echo $post_type->publicly_queryable ? __('Disable', 'cpteady') : __('Enable', 'cpteady'); ?>
                                        </button>
                                </td>
                            </tr>
                    <?php
                                    }
                                }
                    ?>
                    </tbody>
                </table>
            <?php
            } else {
                echo '<p>'. __('No active post types found.', 'cpteasy') . '</p>';
            }
            ?>
        </div>
    <?php
    }

    /**
     * Render create model tab.
     */
    private static function render_create_model_tab()
    {
    ?>
        <div id="tab2" class="tab-content">
            <h3><?= __('Create New Model', 'cpteasy'); ?></h3>

            <form id="create-model-form">
                <div class="fields">
                    <?php
                    // Define the field data
                    $fields = array(
                        'model_name' => __("Name", "cpteasy"),
                        'model_label' => __("Label", "cpteasy"),
                        'model_singular_name' => __("Singular Name", "cpteasy"),
                        'model_slug' => __("Slug", "cpteasy"),
                        'model_menu_name' => __("Menu Name", "cpteasy"),
                        'model_all_items' => __("All Items", "cpteasy"),
                        'model_add_new' => __("Add New", "cpteasy"),
                        'model_add_new_item' => __("Add new Item", "cpteasy"),
                        'model_edit_item' => __("Edit Item", "cpteasy"),
                        'model_new_item' => __("New Item", "cpteasy"),
                        'model_view_item' => __("View Item", "cpteasy"),
                        'model_view_items' => __("View Items", "cpteasy"),
                        'model_search_items' => __("Search Items", "cpteasy"),
                    );

                    $placeholder = array(
                        'model_name' => __("Demo", "cpteasy"),
                        'model_label' => __("Demos", "cpteasy"),
                        'model_singular_name' => __("Demo", "cpteasy"),
                        'model_slug' => __("demos", "cpteasy"),
                        'model_menu_name' => __("Demos", "cpteasy"),
                        'model_all_items' => __("All demos", "cpteasy"),
                        'model_add_new' => __("Add new", "cpteasy"),
                        'model_add_new_item' => __("Add new demo", "cpteasy"),
                        'model_edit_item' => __("Edit demo", "cpteasy"),
                        'model_new_item' => __("New demo", "cpteasy"),
                        'model_view_item' => __("View demo", "cpteasy"),
                        'model_view_items' => __("View demos", "cpteasy"),
                        'model_search_items' => __("Search demo", "cpteasy"),
                    );

                    // HTML form fields
                    foreach ($fields as $field_name => $label) {
                    ?>
                        <div class="field">
                            <label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($label); ?>:</label>
                            <input value="<?php echo $placeholder[$field_name] ?>" type="text" id="<?php echo esc_attr($field_name); ?>" name="<?php echo esc_attr($field_name); ?>" required>
                        </div>
                    <?php
                    }
                    ?>

                    <!-- Icon Select Menu -->
                    <div class="field">
                        <label for="model_icon">Icon: <span id="icon_preview" class="icon-preview"></span></label>
                        <select id="model_icon" name="model_icon">
                            <?php foreach (Icon::ICONS as $icon_key => $icon_value) : ?>
                                <option value="<?php echo esc_attr($icon_key); ?>">
                                    <?php echo esc_html($icon_value); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php wp_nonce_field('create_model_nonce', 'create_model_nonce'); ?>

                <div class="field field--submit">
                    <input class="button button-small button-primary button-hero" type="submit" value="Create Model">
                </div>
            </form>
        </div>
    <?php
    }

    /**
     * Render file editor tab.
     */
    private static function render_file_editor_tab()
    {
    ?>
        <div id="tab3" class="tab-content">
            <h3><?php echo esc_html__('File Editor', 'cpteasy'); ?></h3>

            <?php self::render_file_editor(); ?>
        </div>
    <?php
    }
}

