<?php
/**
 * Plugin Name: Master SEO Migrator by Shahrukh
 * Description: A professional tool to migrate SEO data between websites. Supports Yoast, RankMath, and AIOSEO with smart ID/Slug matching and batch processing.
 * Version: 1.0.0
 * Plugin URI:  https://shahrukh-cv.netlify.app/
 * Author: Mohd Shahrukh
 * Author URI: https://shahrukh-cv.netlify.app/
 * Text Domain: shahrukh-master-seo-migrator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define Plugin Constants
define( 'MSM_VERSION', '1.0.0' );
define( 'MSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Main Plugin Class
class Master_SEO_Migrator {

    public function __construct() {
        $this->load_dependencies();
        $this->init_admin();
    }

    private function load_dependencies() {
        // Load Core Classes
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-common.php';
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-compat.php';
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-batch.php';
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-matcher.php';
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-exporter.php';
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-importer.php';
        require_once MSM_PLUGIN_DIR . 'includes/class-msm-admin.php';
    }

    private function init_admin() {
        if ( is_admin() ) {
            new MSM_Admin();
        }
    }
}

// Initialize the Plugin
function run_master_seo_migrator() {
    $plugin = new Master_SEO_Migrator();
}
run_master_seo_migrator();