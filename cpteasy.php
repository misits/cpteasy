<?php
/**
 * Plugin Name: Cpteasy
 * Description: Adds custom post types as php models
 * Plugin URI: https://github.com/misits/cpteasy
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 8.0
 * Author: Martin IS IT Services
 * Author URI: https://misits.ch
 * License: GPL v2 or later
 * Text Domain: cpteasy
 * Domain Path: /languages
 */

namespace Cpteasy;

use Cpteasy\admin\AdminServices;
use Cpteasy\includes\utils\Register;
use Cpteasy\includes\utils\Size;

// Prevent direct access.
defined( 'ABSPATH' ) or exit;

// Define plugin constants.
define( 'CPTEASY_DIR', plugin_dir_path(__FILE__) );
define( 'CPTEASY_URL', plugin_dir_url(__FILE__) );

// Autoload classes.
spl_autoload_register(function ($class) {
    $filename = explode("\\", $class);
    $namespace = array_shift($filename);

    array_unshift($filename, __DIR__);

    if ($namespace === __NAMESPACE__) {
        include implode(DIRECTORY_SEPARATOR, $filename) . ".php";
    }
});

// Register classes.
$to_register = [
    AdminServices::class,
    Register::class,
    Size::class,
];

foreach ($to_register as $class) {
    $class::register();
}