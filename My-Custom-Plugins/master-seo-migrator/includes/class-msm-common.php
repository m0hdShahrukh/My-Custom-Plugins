<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MSM_Common {

    /**
     * Get all public post types (Posts, Pages, Products, etc.)
     * Excludes attachments and system types.
     */
    public static function get_migratable_post_types() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $valid_types = array();

        foreach ( $post_types as $slug => $type ) {
            if ( $slug !== 'attachment' ) {
                $valid_types[ $slug ] = $type->labels->name;
            }
        }
        return $valid_types;
    }

    /**
     * Check which SEO plugin is active
     */
    public static function get_active_seo_plugin() {
        if ( defined( 'WPSEO_VERSION' ) ) {
            return 'yoast';
        }
        if ( defined( 'RANK_MATH_VERSION' ) ) {
            return 'rankmath';
        }
        if ( defined( 'AIOSEO_VERSION' ) ) {
            return 'aioseo';
        }
        return 'none';
    }

    /**
     * Sanitize text for CSV (prevent excel errors)
     */
    public static function clean_csv_field( $string ) {
        // Remove line breaks and tabs to keep CSV strict
        $string = preg_replace( "/[\r\n\t]+/", " ", $string );
        return trim( $string );
    }
}