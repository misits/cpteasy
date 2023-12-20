<?php

namespace Cpteasy\includes\utils;

// Prevent direct access.
defined('ABSPATH') or exit;

/**
 * Handles custom post type registration and related AJAX actions.
 *
 * @class RegisterCpt
 */
class RegisterCpt
{
    /**
     * Registers actions for custom post types and AJAX.
     */
    public static function register()
    {
        add_action("init", [self::class, "register_cpt"]);
        // Register AJAX actions.
        add_action("wp_ajax_create_cpt_models", [self::class, "create_model_action"]);
        add_action("wp_ajax_nopriv_create_cpt_models", [self::class, "create_model_action"]);

        add_action("wp_ajax_create_cpt_template", [self::class, "create_template_action"]);
        add_action("wp_ajax_nopriv_create_cpt_template", [self::class, "create_template_action"]);

        add_action("wp_ajax_toggle_cpt_activation", [self::class, "toggle_model_action"]);
        add_action("wp_ajax_nopriv_toggle_cpt_activation", [self::class, "toggle_model_action"]);

        add_action("wp_ajax_delete_cpt_model", [self::class, "delete_model_action"]);
        add_action("wp_ajax_nopriv_delete_cpt_model", [self::class, "delete_model_action"]);

        add_action("wp_ajax_save_file_content", [self::class, "save_model_action"]);
        add_action("wp_ajax_nopriv_save_file_content", [self::class, "save_model_action"]);
    }

    /**
     * Registers custom post types.
     */
    public static function register_cpt()
    {
        $files = scandir(CPT_MODELS_WP_DIR . '/includes/models/custom');

        foreach ($files as $file) {
            if (strpos($file, '.php') !== false) {
                $post_type = strtolower(str_replace('.php', '', $file));

                add_filter('template_include', function ($template) use ($post_type) {
                    if (is_singular($post_type)) {
                        $custom_template = CPT_MODELS_WP_DIR . "/includes/templates/single-" . $post_type . ".php";

                        if (file_exists($custom_template)) {
                            return $custom_template;
                        } else {
                            return CPT_MODELS_WP_DIR . "/includes/templates/single.php";
                        }
                    }

                    return $template;
                });

                $class = 'Cpteasy\includes\models\custom\\' . ucfirst($post_type);
                $class::register();
            }
        }
    }

    /**
     * Generates custom post type class file.
     */
    public static function create_model_action()
    {
        check_ajax_referer('create_model_nonce', 'security');

        // Get form data
        $formData = $_POST['formData'];
        $formData = array_column($formData, 'value', 'name');

        $filename = CPT_MODELS_WP_DIR . '/includes/models/custom/' . ucfirst(sanitize_text_field($formData['model_name'])) . '.php';

        if (file_exists($filename)) {
            return __("Model already exists.", 'cpteady');
        }

        // Generate class PHP file content
        $phpContent = '<?php' . PHP_EOL . PHP_EOL;
        $phpContent .= 'namespace Cpteasy\includes\models\custom;' . PHP_EOL . PHP_EOL;
        $phpContent .= 'use Cpteasy\includes\models\CustomPostType;' . PHP_EOL . PHP_EOL;
        $phpContent .= 'class ' . ucfirst(sanitize_text_field($formData['model_name'])) . ' extends CustomPostType implements \\JsonSerializable' . PHP_EOL;
        $phpContent .= '{' . PHP_EOL;
        $phpContent .= '    const TYPE = \'' . strtolower(sanitize_text_field($formData['model_name'])) . '\';' . PHP_EOL;
        $phpContent .= '    const SLUG = \'' . strtolower(sanitize_text_field($formData['model_slug'])) . '\';' . PHP_EOL . PHP_EOL;
        $phpContent .= '    public static function type_settings()' . PHP_EOL;
        $phpContent .= '    {' . PHP_EOL;
        $phpContent .= '        return ' . var_export(self::prepare_settings($formData), true) . ';' . PHP_EOL;
        $phpContent .= '    }' . PHP_EOL . PHP_EOL;
        // Add the jsonSerialize method
        $phpContent .= '    public function jsonSerialize(): mixed' . PHP_EOL;
        $phpContent .= '    {' . PHP_EOL;
        $phpContent .= '        return [' . PHP_EOL;
        $phpContent .= '            "id" => $this->id(),' . PHP_EOL;
        $phpContent .= '            "title" => $this->title(),' . PHP_EOL;
        $phpContent .= '            "slug" => $this->slug(),' . PHP_EOL;
        $phpContent .= '            "link" => $this->link(),' . PHP_EOL;
        $phpContent .= '            "excerpt" => $this->excerpt(),' . PHP_EOL;
        $phpContent .= '            "content" => $this->content(),' . PHP_EOL;
        $phpContent .= '            "date" => $this->date(),' . PHP_EOL;
        $phpContent .= '        ];' . PHP_EOL;
        $phpContent .= '    }' . PHP_EOL;
        $phpContent .= '}' . PHP_EOL;

        // Save PHP file
        if (file_put_contents($filename, $phpContent) !== false) {
            echo __('Custom post type file created successfully.', 'cpteady');
        } else {
            echo __('Unable to create custom post type file.', 'cpteady');
        }

        // Generate template PHP file content
        $templateContent = '<?php' . PHP_EOL . PHP_EOL;
        $templateContent .= 'namespace Cpteasy\includes\templates\custom;' . PHP_EOL . PHP_EOL;
        $templateContent .= 'use Cpteasy\includes\models\custom\\' . ucfirst(sanitize_text_field($formData['model_name'])) . ';' . PHP_EOL;
        $templateContent .= '$model = new ' . ucfirst(sanitize_text_field($formData['model_name'])) . '(get_the_ID());' . PHP_EOL . PHP_EOL;
        $templateContent .= 'get_header();' . PHP_EOL . PHP_EOL;
        $templateContent .= '?>' . PHP_EOL . PHP_EOL;
        $templateContent .= '<section>' . PHP_EOL;
        $templateContent .= '    <h1><?= $model->title() ?></h1>' . PHP_EOL;
        $templateContent .= '    <div><?= $model->content() ?></div>' . PHP_EOL;
        $templateContent .= '    <?php $model->thumbnail(function (Media $media) { ?>' . PHP_EOL;
        $templateContent .= '        <figure>' . PHP_EOL;
        $templateContent .= '            <picture>' . PHP_EOL;
        $templateContent .= '                <source media="(min-width: 1281px)" srcset="<?= $media->src("image-xl") ?> 1x, <?= $media->src("image-xl-2x") ?> 2x">' . PHP_EOL;
        $templateContent .= '                <source media="(max-width: 1280px)" srcset="<?= $media->src("image-l") ?> 1x, <?= $media->src("image-l-2x") ?> 2x">' . PHP_EOL;
        $templateContent .= '                <source media="(max-width: 860px)" srcset="<?= $media->src("image-m") ?> 1x, <?= $media->src("image-m-2x") ?> 2x">' . PHP_EOL;
        $templateContent .= '                <source media="(max-width: 400px)" srcset="<?= $media->src("image-s") ?> 1x, <?= $media->src("image-s-2x") ?> 2x">' . PHP_EOL;
        $templateContent .= '                <img srcset="<?= $media->src("image-l") ?> 1280w, <?= $media->src("image-xl") ?> 1920w" src="<?= $media->src("image-xl") ?>" alt="<?= $media->alt() ?>">' . PHP_EOL;
        $templateContent .= '            </picture>' . PHP_EOL;
        $templateContent .= '        </figure>' . PHP_EOL;
        $templateContent .= '    <?php }); ?>' . PHP_EOL;
        $templateContent .= '</section>' . PHP_EOL . PHP_EOL;
        $templateContent .= '<?php get_footer(); ?>' . PHP_EOL;

        // Save template PHP file
        $templateFilename = CPT_MODELS_WP_DIR . '/includes/templates/custom/single-' . sanitize_text_field($formData['model_name']) . '.php';
        if (file_put_contents($templateFilename, $templateContent) !== false) {
            echo __('Custom template file created successfully.', 'cpteady');
        } else {
            echo __('Unable to create template file.', 'cpteady');
        }

        die();
    }

    /**
     * Checks if a template file exists for the given post type.
     *
     * @param string $post_type - The post type to check for.
     * @return bool - Whether the template file exists or not.
     */
    public static function has_template($post_type)
    {
        $filename = CPT_MODELS_WP_DIR . '/includes/templates/custom/single-' . sanitize_text_field($post_type) . '.php';

        return file_exists($filename);
    }

    /**
     * Generates custom template file for the given post type.
     */
    public static function create_template_action()
    {
        check_ajax_referer('generate_template_nonce', 'security');

        // Get post type from AJAX request
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

        $filename = CPT_MODELS_WP_DIR . '/includes/templates/custom/single-' . strtolower(sanitize_text_field($post_type)) . '.php';

        if (file_exists($filename)) {
            echo __("Template already exists.", 'cpteady');
            die();
        }

        // Generate template PHP file content
        $phpContent = '<?php' . PHP_EOL . PHP_EOL;
        $phpContent .= 'namespace Cpteasy\includes\templates\custom;' . PHP_EOL . PHP_EOL;
        $phpContent .= 'use Cpteasy\includes\models\Media;' . PHP_EOL . PHP_EOL;
        $phpContent .= 'use Cpteasy\includes\models\custom\\' . ucfirst(sanitize_text_field($post_type)) . ';' . PHP_EOL . PHP_EOL;
        $phpContent .= '$model = new ' . ucfirst(sanitize_text_field($post_type)) . '(get_the_ID());' . PHP_EOL . PHP_EOL;
        $phpContent .= 'get_header();' . PHP_EOL . PHP_EOL;
        $phpContent .= '?>' . PHP_EOL . PHP_EOL;
        $phpContent .= '<section>' . PHP_EOL;
        $phpContent .= '    <h1><?= $model->title() ?></h1>' . PHP_EOL;
        $phpContent .= '    <div><?= $model->content() ?></div>' . PHP_EOL;
        $phpContent .= '    <?php $model->thumbnail(function (Media $media) { ?>' . PHP_EOL;
        $phpContent .= '        <figure>' . PHP_EOL;
        $phpContent .= '            <picture>' . PHP_EOL;
        $phpContent .= '                <source media="(min-width: 1281px)" srcset="<?= $media->src("image-xl") ?> 1x, <?= $media->src("image-xl-2x") ?> 2x">' . PHP_EOL;
        $phpContent .= '                <source media="(max-width: 1280px)" srcset="<?= $media->src("image-l") ?> 1x, <?= $media->src("image-l-2x") ?> 2x">' . PHP_EOL;
        $phpContent .= '                <source media="(max-width: 860px)" srcset="<?= $media->src("image-m") ?> 1x, <?= $media->src("image-m-2x") ?> 2x">' . PHP_EOL;
        $phpContent .= '                <source media="(max-width: 400px)" srcset="<?= $media->src("image-s") ?> 1x, <?= $media->src("image-s-2x") ?> 2x">' . PHP_EOL;
        $phpContent .= '                <img srcset="<?= $media->src("image-l") ?> 1280w, <?= $media->src("image-xl") ?> 1920w" src="<?= $media->src("image-xl") ?>" alt="<?= $media->alt() ?>">' . PHP_EOL;
        $phpContent .= '            </picture>' . PHP_EOL;
        $phpContent .= '        </figure>' . PHP_EOL;
        $phpContent .= '    <?php }); ?>' . PHP_EOL;
        $phpContent .= '</section>' . PHP_EOL . PHP_EOL;
        $phpContent .= '<?php get_footer(); ?>' . PHP_EOL;

        // Save PHP file
        if (file_put_contents($filename, $phpContent) !== false) {
            echo __('Custom template created successfully.', 'cpteady');
        } else {
            echo __('Unable to create template file.', 'cpteady');
        }

        die();
    }

    /**
     * Toggles activation of the custom post type.
     */
    public static function toggle_model_action()
    {
        check_ajax_referer('toggle_activation_nonce', 'security');

        // Get post type from AJAX request
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
        $filename = CPT_MODELS_WP_DIR . '/includes/models/custom/' . ucfirst(sanitize_text_field($post_type)) . '.php';

        // If model is not registered, check if it exists as a class file
        if (!class_exists('Cpteasy\includes\models\custom\\' . ucfirst($post_type))) {
            echo __("Model does not exist.", 'cpteady');
            die();
        }

        // Update specified keys in the file content
        $file_content = file_get_contents($filename);

        $to_toggle = [
            'publicly_queryable',
            'show_ui',
            'show_in_rest',
            'show_in_nav_menus',
            'show_in_menu',
            'exclude_from_search',
        ];

        foreach ($to_toggle as $key) {
            // Toggle the boolean value
            $file_content = preg_replace_callback(
                "/'{$key}' => (true|false),/",
                function ($matches) use ($key) {
                    return "'{$key}' => " . ($matches[1] === 'true' ? 'false' : 'true') . ",";
                },
                $file_content
            );
        }

        echo file_put_contents($filename, $file_content) !== false ? __('Model toggled successfully.', 'cpteady') : __('Unable to toggle model.', 'cpteady');

        die();
    }

    public static function save_model_action()
    {
        check_ajax_referer('save_file_content_nonce', 'security');

        // Get form data
        $formData = $_POST['formData'];
        $formData = array_column($formData, 'value', 'name');

         // Get correct directory
         $directory = CPT_MODELS_WP_DIR . '/includes/templates';
         $custom_directory = CPT_MODELS_WP_DIR . '/includes/templates/custom';
 
          // Initialize $file_path
          $file_path = '';
 
          // Check if the file is in the custom directory
          if (is_file($custom_directory . '/' . $formData['file'])) {
              $file_path = $custom_directory . '/' . $formData['file'];
          } elseif (is_file($directory . '/' . $formData['file'])) {
              // Check if the file is in the main directory
              $file_path = $directory . '/' . $formData['file'];
          }

        if (!file_exists($file_path)) {
            echo _("Model does not exist.", 'cpteady');
        }
       
        // Update file content

        echo file_put_contents($file_path, stripslashes($formData['file-content'])) !== false ? __('Model saved successfully.', 'cpteady') : __('Unable to save model.', 'cpteady');

        die();
    }

    /**
     * Deletes the custom post type.
     */
    public static function delete_model_action()
    {
        check_ajax_referer('delete_post_type_nonce', 'security');

        // Get post type from AJAX request
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
        $filename = CPT_MODELS_WP_DIR . '/includes/models/custom/' . ucfirst(sanitize_text_field($post_type)) . '.php';
        $templateFilename = CPT_MODELS_WP_DIR . '/includes/templates/custom/single-' . sanitize_text_field($post_type) . '.php';

        // If model is not registered, check if it exists as a class file
        if (!class_exists('Cpteasy\includes\models\custom\\' . ucfirst($post_type))) {
            echo __("Model does not exist.", 'cpteady');
            die();
        }

        echo unlink($filename) !== false ? __('Model deleted successfully.', 'cpteady') : __('Unable to delete model.', 'cpteady');
        echo unlink($templateFilename) !== false ? __('Template deleted successfully.', 'cpteady') : __('Unable to delete template.', 'cpteady');

        die();
    }


    public static function prepare_settings(array $formData)
    {
        return [
            "menu_position" => 2,
            "label" => __($formData['model_label'], "cpteasy"),
            "labels" => [
                "name" => __($formData['model_name'], "cpteasy"),
                "singular_name" => __($formData['model_singular_name'], "cpteasy"),
                "menu_name" => __($formData['model_menu_name'], "cpteasy"),
                "all_items" => __($formData['model_all_items'], "cpteasy"),
                "add_new" => __($formData['model_add_new'], "cpteasy"),
                "add_new_item" => __($formData['model_add_new_item'], "cpteasy"),
                "edit_item" => __($formData['model_edit_item'], "cpteasy"),
                "new_item" => __($formData['model_new_item'], "cpteasy"),
                "view_item" => __($formData['model_view_item'], "cpteasy"),
                "view_items" => __($formData['model_view_items'], "cpteasy"),
                "search_items" => __($formData['model_search_items'], "cpteasy")
            ],
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "show_in_nav_menus" => true,
            "rest_base" => "",
            "has_archive" => true,
            "show_in_menu" => true,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => ["slug" => sanitize_text_field($formData['model_slug']), "with_front" => false],
            "query_var" => true,
            "menu_icon" => "dashicons-icon-" . $formData['icon'],
            "supports" => ["title", "editor", "thumbnail", "excerpt"],
        ];
    }
}