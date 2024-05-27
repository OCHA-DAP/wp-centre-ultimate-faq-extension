<?php
   /*
   Plugin Name: API Sort extension for Ultimate FAQ
   Plugin URI: https://centre.humdata.org
   description: Ultimate FAQ plugin doesn't apply sorting to for "ufaq" posts on the REST API. This plugin enables it through these params: orderby=meta_value_num, meta_key=ufaq_order and per_page=1000
   Version: 1.2
   Author: https://centre.humdata.org
   Author URI: https://centre.humdata.org
   License: GPL2
   */

add_filter('rest_endpoints', function ($routes) {
    // I'm modifying multiple types here, you won't need the loop if you're just doing posts
    foreach (['ufaq'] as $type) {
        if (!($route =& $routes['/wp/v2/' . $type])) {
            continue;
        }

        // Allow ordering by my meta value
        $route[0]['args']['orderby']['enum'][] = 'meta_value_num';

        // Allow only the meta keys that I want
        $route[0]['args']['meta_key'] = array(
            'description'       => 'The meta key to query.',
            'type'              => 'string',
            'enum'              => ['ufaq_order'],
            'validate_callback' => 'rest_validate_request_arg',
        );
    }

    return $routes;
}, 10, 1);

$ufaq_post = 'ufaq';
add_filter("rest_{$ufaq_post}_query", function ($args, $request) { 	    
	if ($key = $request->get_param('meta_key')) { 	        
		$args['meta_key'] = $key; 	    
	} 	    
	return $args; 	
}, 10, 2);

add_filter("rest_{$ufaq_post}_collection_params", function($params) {
    $params['per_page']['maximum'] = 1000;
    return $params;
}, 10, 3);

/**
 * HDX Extra Links inside strattic paths
 */

function list_faq_categories() {
    $categories = get_terms(array('taxonomy' => 'ufaq-category', 'hide_empty' => false, 'parent' => 0));
    $result = array();

    foreach ($categories as $category) {
        // Array to hold sub-category IDs
        $sub_cat_ids = array();

        $result[] = '/custom-ufaq-category/'.$category->term_id;
        // Fetch sub-categories
        $sub_categories = get_terms(array('taxonomy' => 'ufaq-category', 'hide_empty' => false, 'parent' => $category->term_id));
        foreach ($sub_categories as $sub_cat) {
            $sub_cat_ids[] = $sub_cat->term_id;
        }

        if (!empty($sub_cat_ids)) {
            // Prepare API URL for sub-categories
            $sub_cat_ids_string = implode(',', $sub_cat_ids);
            $custom_api_url = '/custom-ufaq-list/' . $sub_cat_ids_string;
            $result[] = $custom_api_url;
        }
    }

    return $result;
}

add_filter(
   'strattic_paths',
    function ($paths) {
        $faq_urls = list_faq_categories();
        // do NOT end these with slash!
        $custompaths = array_merge(
            [
                '/custom-ufaq-category'
            ],
            $faq_urls,
            [
                '/wp-content/themes/uncode-child/style.css.map',
                '/wp-content/themes/uncode-child/js/humdata-footer.js.map',
                '/wp-content/plugins/enhanced-tooltipglossary/assets/js/tooltip.min.js',
                '/wp-content/plugins/enhanced-tooltipglossary/assets/css/tooltip.min.css',
                '/wp-content/plugins/enhanced-tooltipglossary/assets/js/modernizr.min.js',
                '/wp-admin/js/password-strength-meter.min.js'
            ]
        );
        foreach ($custompaths as $custompath) {
            $paths[] = array(
                'path' => $custompath,
                'priority' => 8,
                'quick_publish' => true
            );
            $paths[] = array(
                'path' => $custompath."/",
                'priority' => 8,
                'quick_publish' => true
            );
        }
        return $paths;
    }
);


?>
