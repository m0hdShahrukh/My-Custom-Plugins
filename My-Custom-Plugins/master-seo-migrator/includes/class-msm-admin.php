<?php
if (! defined('ABSPATH')) exit;

class MSM_Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_menu'));
        add_action('admin_init', array($this, 'handle_form_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function add_plugin_menu()
    {
        add_menu_page(
            'Master SEO Migrator',
            'SEO Migrator',
            'manage_options',
            'master-seo-migrator',
            array($this, 'render_dashboard'),
            'dashicons-migrate',
            99
        );
    }

    public function handle_form_actions()
    {
        if (isset($_POST['msm_action']) && $_POST['msm_action'] === 'export_data') {
            check_admin_referer('msm_export_action', 'msm_nonce_field');
            if (! current_user_can('manage_options')) wp_die('Unauthorized');

            require_once MSM_PLUGIN_DIR . 'includes/class-msm-exporter.php';
            $exporter = new MSM_Exporter();
            $exporter->handle_export();
        }
        // Add THIS new check:
        if (isset($_POST['msm_action']) && $_POST['msm_action'] === 'export_images') {
            check_admin_referer('msm_export_action', 'msm_nonce_field');
            if (! current_user_can('manage_options')) wp_die('Unauthorized');

            require_once MSM_PLUGIN_DIR . 'includes/class-msm-exporter.php';
            $exporter = new MSM_Exporter();
            $exporter->handle_image_export(); // Call the new function
        }
    }

    public function enqueue_assets($hook)
    {
        // Only load on our plugin page
        if (strpos($hook, 'master-seo-migrator') === false) {
            return;
        }

        // CSS
        wp_enqueue_style('msm-admin-css', MSM_PLUGIN_URL . 'assets/css/msm-admin.css', array(), MSM_VERSION);

        // JS
        wp_enqueue_script('msm-admin-js', MSM_PLUGIN_URL . 'assets/js/msm-admin.js', array('jquery'), MSM_VERSION, true);

        // Pass variables to JS (Nonce & URL)
        wp_localize_script('msm-admin-js', 'msm_vars', array(
            'nonce' => wp_create_nonce('msm_import_nonce')
        ));
    }

    public function render_dashboard()
    {
        $active_plugin = MSM_Common::get_active_seo_plugin();
        $plugin_name = ucfirst($active_plugin);
?>
        <div class="wrap">
            <h1>🚀 Master SEO Migrator</h1>
            <p>A professional tool to migrate SEO data between websites. Supports Yoast, RankMath, and AIOSEO with smart ID/Slug matching and batch processing.</p>
            <p>Developed by <a href="https://webeesocial.com" target="_blank" rel="noopener noreferrer">WeBeeSocial</a></p>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h2>1. Export SEO Meta Data</h2>
                    <p>Detected Plugin: <strong><?php echo $plugin_name; ?></strong></p>
                    <p>Generate a universal migration file from this site.</p>
                    <form method="post">
                        <?php wp_nonce_field('msm_export_action', 'msm_nonce_field'); ?>
                        <input type="hidden" name="msm_action" value="export_data">
                        <?php submit_button('Download Master CSV', 'secondary', 'submit', false); ?>
                    </form>
                </div>

                <div class="card" style="flex: 1; min-width: 300px; padding: 20px; border-left: 4px solid #2271b1;">
                    <h2>2. Import SEO Data (Meta Tags / Img ALT Tags)</h2>
                    <p>Import data to: <strong><?php echo $plugin_name; ?></strong></p>
                    <p>Select the "Master CSV" file to update SEO tags or ALT tags on this site.</p>

                    <input type="file" id="msm-csv-file" accept=".csv" style="margin-bottom: 10px;">
                    <br>
                    <button id="msm-start-import" class="button button-primary button-large">Start Import Process</button>

                    <div class="msm-stats">
                        <div class="msm-stat-card">
                            <span class="msm-stat-number" id="msm-stat-total">0</span>
                            Total Rows
                        </div>
                        <div class="msm-stat-card" style="color: green;">
                            <span class="msm-stat-number" id="msm-stat-success">0</span>
                            Updated
                        </div>
                        <div class="msm-stat-card" style="color: red;">
                            <span class="msm-stat-number" id="msm-stat-failed">0</span>
                            Failed
                        </div>
                    </div>

                    <div class="msm-progress-wrapper">
                        <div class="msm-progress-bar"></div>
                    </div>

                    <div class="msm-log-box"></div>
                </div>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;">
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px; border-left: 4px solid #2271b1;">
                    <p><strong>Export Images Alt Tags</strong></p>
                    <form method="post">
                        <?php wp_nonce_field('msm_export_action', 'msm_nonce_field'); ?>
                        <input type="hidden" name="msm_action" value="export_images">
                        <?php submit_button('Download Images CSV', 'secondary', 'submit', false); ?>
                    </form>
                </div>
            </div>
        </div>
<?php
    }
}
