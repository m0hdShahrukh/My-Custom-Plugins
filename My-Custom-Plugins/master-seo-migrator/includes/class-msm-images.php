<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MSM_Images {

    /**
     * Finds an attachment ID by searching for the filename.
     * This matches 'my-image.jpg' even if it's organized in /2023/09/ folders.
     */
    public static function find_image_by_filename( $filename ) {
        global $wpdb;

        // Clean the filename (remove paths if they exist in the CSV)
        $filename = basename( $filename );

        // Search the _wp_attached_file meta key
        // We use LIKE because the DB stores '2023/09/filename.jpg'
        $query = $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
            '%' . $wpdb->esc_like( $filename )
        );

        return $wpdb->get_var( $query );
    }

    /**
     * Updates the Alt Text
     */
    public static function update_image_alt( $attachment_id, $alt_text ) {
        // Sanitize
        $alt_text = sanitize_text_field( $alt_text );
        
        // Update
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
        return true;
    }
}