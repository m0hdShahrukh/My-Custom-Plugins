<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MSM_Matcher {

    /**
     * Try to find a post on the current site that matches the CSV row data.
     * * @param array $row The CSV row data
     * @return int|false The NEW Post ID or false if not found
     */
    public static function find_matching_post_id( $row ) {
        
        $slug = isset($row['post_slug']) ? sanitize_text_field($row['post_slug']) : '';
        $type = isset($row['post_type']) ? sanitize_text_field($row['post_type']) : 'post';
        $title = isset($row['post_title']) ? sanitize_text_field($row['post_title']) : '';

        // Strategy 1: Exact Slug & Post Type Match (Most Accurate)
        // We use a direct DB query for speed and to bypass some filter caching issues
        if ( ! empty( $slug ) ) {
            $args = array(
                'name'           => $slug,
                'post_type'      => $type,
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids' // We only need the ID
            );
            
            $posts = get_posts( $args );
            
            if ( ! empty( $posts ) ) {
                return $posts[0]; // Found it!
            }
        }

        // Strategy 2: Match by Exact Title (Fallback)
        // Useful if the slug changed slightly (e.g., "about-us" vs "about")
        if ( ! empty( $title ) ) {
            $post = get_page_by_title( $title, OBJECT, $type );
            if ( $post ) {
                return $post->ID;
            }
        }

        // Strategy 3: Loose Slug Match (Desperation Move)
        // If Type is 'post', maybe it was moved to 'page'? (Optional, but safe to skip for strictness)
        
        return false; // No match found
    }
}