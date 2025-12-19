<?php
if (! defined('ABSPATH')) exit;

class MSM_Exporter
{

    public function handle_export()
    {
        // 1. Verify Security (Double check)
        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized user');
        }

        // CLEAR BUFFER to prevent "Headers Sent" error
        if (ob_get_level()) {
            ob_end_clean();
        }

        // 2. Setup Headers for Download
        $filename = 'Master-SEO-Export-' . date('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add BOM for Excel compatibility (Fixes weird characters)
        fwrite($output, "\xEF\xBB\xBF");

        // 3. Define Master CSV Columns
        $headers = array(
            'old_id',           // Original Post ID
            'post_type',        // Product, Page, Post
            'post_slug',        // For URL matching
            'post_title',       // For fuzzy matching
            'full_permalink',   // For exact URL matching
            'seo_title',        // Neutral Data
            'seo_desc',         // Neutral Data
            'seo_canonical',    // Neutral Data
            'seo_robots',       // Neutral Data
            'focus_keyword'     // Neutral Data
        );
        fputcsv($output, $headers);

        // 4. Detect Plugin & Get Keys
        $active_plugin = MSM_Common::get_active_seo_plugin();
        $meta_map      = MSM_Compat::get_meta_map($active_plugin);

        // 5. Get All Posts (Chunked for memory safety)
        $post_types = array_keys(MSM_Common::get_migratable_post_types());

        // We grab 500 posts at a time to avoid crashing on large sites
        $paged = 1;
        $posts_per_batch = 500;

        do {
            $args = array(
                'post_type'      => $post_types,
                'post_status'    => array('publish', 'draft', 'private', 'future'),
                'posts_per_page' => $posts_per_batch,
                'paged'          => $paged
            );

            $posts = get_posts($args);

            foreach ($posts as $post) {

                // Fetch Meta Data dynamically based on the active plugin
                $seo_title      = get_post_meta($post->ID, $meta_map['seo_title'], true);
                $seo_desc       = get_post_meta($post->ID, $meta_map['seo_desc'], true);
                $seo_canonical  = get_post_meta($post->ID, $meta_map['seo_canonical'], true);
                $seo_robots     = get_post_meta($post->ID, $meta_map['seo_robots'], true);
                $focus_kw       = get_post_meta($post->ID, $meta_map['focus_keyword'], true);

                // RankMath stores robots as an array, we need to convert to string
                if (is_array($seo_robots)) {
                    $seo_robots = implode(', ', $seo_robots);
                }

                $row = array(
                    $post->ID,
                    $post->post_type,
                    $post->post_name,
                    MSM_Common::clean_csv_field($post->post_title),
                    get_permalink($post->ID),
                    MSM_Common::clean_csv_field($seo_title),
                    MSM_Common::clean_csv_field($seo_desc),
                    $seo_canonical,
                    $seo_robots,
                    MSM_Common::clean_csv_field($focus_kw)
                );

                fputcsv($output, $row);
            }

            // Stop loop if we ran out of posts
            if (count($posts) < $posts_per_batch) {
                break;
            }
            $paged++;
        } while (true);

        fclose($output);
        exit;
    }
    public function handle_image_export()
    {
        if (! current_user_can('manage_options')) wp_die('Unauthorized');
        if (ob_get_level()) ob_end_clean();

        $filename = 'Master-Image-Alt-Export-' . date('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // BOM

        // Headers specific for Images
        fputcsv($output, array('image_id', 'filename', 'alt_text'));

        // Get all Images
        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        );

        $images = get_posts($args);

        foreach ($images as $image) {
            $file_path = get_post_meta($image->ID, '_wp_attached_file', true);
            $alt_text  = get_post_meta($image->ID, '_wp_attachment_image_alt', true);

            // We only need the basename (logo.png) not the full path (2023/logo.png)
            // This makes matching easier on the new site.
            $clean_filename = basename($file_path);

            fputcsv($output, array(
                $image->ID,
                $clean_filename,
                MSM_Common::clean_csv_field($alt_text)
            ));
        }

        fclose($output);
        exit;
    }
}
