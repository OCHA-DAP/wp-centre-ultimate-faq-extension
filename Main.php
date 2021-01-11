<?php
   /*
   Plugin Name: API Sort extension for Ultimate FAQ
   Plugin URI: https://centre.humdata.org
   description: Ultimate FAQ plugin doesn't apply sorting to for "ufaq" posts on the REST API. This plugin enables it through these params: orderby=meta_value_num, meta_key=ufaq_order and per_page=1000
   Version: 1.1
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

?>