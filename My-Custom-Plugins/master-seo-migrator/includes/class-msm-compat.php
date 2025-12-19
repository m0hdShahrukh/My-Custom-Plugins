<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MSM_Compat {

    /**
     * Returns the 'Master Key' map.
     * The array keys are our internal standard.
     * The values are the meta keys used by specific plugins.
     */
    public static function get_meta_map( $plugin ) {
        $map = array(
            'seo_title'       => '',
            'seo_desc'        => '',
            'seo_canonical'   => '',
            'seo_robots'      => '',
            'seo_image'       => '',
            'focus_keyword'   => '',
        );

        switch ( $plugin ) {
            case 'yoast':
                $map['seo_title']       = '_yoast_wpseo_title';
                $map['seo_desc']        = '_yoast_wpseo_metadesc';
                $map['seo_canonical']   = '_yoast_wpseo_canonical';
                $map['seo_robots']      = '_yoast_wpseo_meta-robots-noindex'; // 1=noindex, 2=nofollow
                $map['seo_image']       = '_yoast_wpseo_opengraph-image';
                $map['focus_keyword']   = '_yoast_wpseo_focuskw';
                break;

            case 'rankmath':
                $map['seo_title']       = 'rank_math_title';
                $map['seo_desc']        = 'rank_math_description';
                $map['seo_canonical']   = 'rank_math_canonical_url';
                $map['seo_robots']      = 'rank_math_robots'; // Array of values
                $map['seo_image']       = 'rank_math_facebook_image';
                $map['focus_keyword']   = 'rank_math_focus_keyword';
                break;

            case 'aioseo':
                $map['seo_title']       = '_aioseop_title';
                $map['seo_desc']        = '_aioseop_description';
                $map['seo_canonical']   = '_aioseop_canonical_url';
                $map['seo_robots']      = '_aioseop_noindex'; // Boolean
                $map['seo_image']       = '_aioseop_opengraph_image';
                $map['focus_keyword']   = '_aioseop_keywords';
                break;
        }

        return $map;
    }
}