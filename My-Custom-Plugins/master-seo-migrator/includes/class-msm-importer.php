<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MSM_Importer {

    public static function update_post_seo( $post_id, $row, $meta_map, $plugin ) {
        $updated = false;

        // 1. Update Title
        if ( ! empty( $row['seo_title'] ) && ! empty( $meta_map['seo_title'] ) ) {
            update_post_meta( $post_id, $meta_map['seo_title'], $row['seo_title'] );
            $updated = true;
        }

        // 2. Update Description
        if ( ! empty( $row['seo_desc'] ) && ! empty( $meta_map['seo_desc'] ) ) {
            update_post_meta( $post_id, $meta_map['seo_desc'], $row['seo_desc'] );
            $updated = true;
        }

        // 3. Update Canonical
        if ( ! empty( $row['seo_canonical'] ) && ! empty( $meta_map['seo_canonical'] ) ) {
            update_post_meta( $post_id, $meta_map['seo_canonical'], $row['seo_canonical'] );
            $updated = true;
        }

        // 4. Update Focus Keyword
        if ( ! empty( $row['focus_keyword'] ) && ! empty( $meta_map['focus_keyword'] ) ) {
            update_post_meta( $post_id, $meta_map['focus_keyword'], $row['focus_keyword'] );
            $updated = true;
        }

        // 5. Update Robots (Complex handling for different plugins)
        if ( ! empty( $row['seo_robots'] ) && ! empty( $meta_map['seo_robots'] ) ) {
            $robots_val = $row['seo_robots'];
            
            // Yoast expects: 1 (noindex), 2 (nofollow), 0 (index)
            // RankMath expects: array('noindex', 'nofollow')
            
            if ( $plugin === 'yoast' ) {
                // Determine Yoast value based on string content
                if ( strpos( $robots_val, 'noindex' ) !== false ) {
                    update_post_meta( $post_id, $meta_map['seo_robots'], 1 );
                } else {
                    update_post_meta( $post_id, $meta_map['seo_robots'], 0 );
                }
            } elseif ( $plugin === 'rankmath' ) {
                $robots_array = explode( ',', $robots_val );
                $robots_array = array_map( 'trim', $robots_array );
                update_post_meta( $post_id, $meta_map['seo_robots'], $robots_array );
            }
            
            $updated = true;
        }

        return $updated;
    }
}