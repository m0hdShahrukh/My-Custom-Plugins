<?php
if (! defined('ABSPATH')) exit;

class MSM_Batch
{

    public function __construct()
    {
        // Handle AJAX requests for batch processing
        add_action('wp_ajax_msm_process_batch', array($this, 'process_batch'));
    }

    public function process_batch()
    {
        check_ajax_referer('msm_import_nonce', 'nonce');
        if (! current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $rows = isset($_POST['rows']) ? $_POST['rows'] : array();
        if (empty($rows)) wp_send_json_error('No data');

        $results = array('processed' => 0, 'updated' => 0, 'failed' => 0, 'log' => array());

        // AUTODETECT MODE: Check if this is an Image CSV
        // We check if the first row has the 'filename' key
        $is_image_mode = isset($rows[0]['filename']);

        if ($is_image_mode) {
            require_once MSM_PLUGIN_DIR . 'includes/class-msm-images.php';
        } else {
            require_once MSM_PLUGIN_DIR . 'includes/class-msm-importer.php';
        }

        $active_plugin = MSM_Common::get_active_seo_plugin();
        $meta_map      = MSM_Compat::get_meta_map($active_plugin);

        foreach ($rows as $row) {
            $results['processed']++;

            if ($is_image_mode) {
                // --- IMAGE LOGIC ---
                $filename = $row['filename'];
                $alt      = $row['alt_text'];

                $img_id = MSM_Images::find_image_by_filename($filename);

                if ($img_id) {
                    MSM_Images::update_image_alt($img_id, $alt);
                    $results['updated']++;
                    $results['log'][] = "🖼️ Image Updated: " . $filename;
                } else {
                    $results['failed']++;
                    $results['log'][] = "❌ Image Not Found: " . $filename;
                }
            } else {
                // --- POST LOGIC (Original) ---
                $new_post_id = MSM_Matcher::find_matching_post_id($row);

                if (! $new_post_id) {
                    $results['failed']++;
                    $results['log'][] = "❌ Post Not Found: " . ($row['post_title'] ?? 'Unknown');
                    continue;
                }

                $success = MSM_Importer::update_post_seo($new_post_id, $row, $meta_map, $active_plugin);

                if ($success) {
                    $results['updated']++;
                    $results['log'][] = "✅ SEO Updated: " . ($row['post_title'] ?? 'Post');
                } else {
                    $results['log'][] = "⚠️ No Changes: " . ($row['post_title'] ?? 'Post');
                }
            }
        }

        wp_send_json_success($results);
    }
}

// Initialize the batch handler
new MSM_Batch();
