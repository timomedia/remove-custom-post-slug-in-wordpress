<?php
// remove product_cat slug custom post type
add_filter('request', 'timo_remove_status_slug', 1, 1 );
function timo_remove_status_slug($query){
	$tax_name = 'product_cat'; // specify you taxonomy name here, it can be also 'category' or 'post_tag'
	// Request for child terms differs, we should make an additional check
	if( $query['attachment'] ) :
		$include_children = true;
		$name = $query['attachment'];
	else:
		$include_children = false;
		$name = $query['name'];
	endif;
	$term = get_term_by('slug', $name, $tax_name); // get the current term to make sure it exists
	if (isset($name) && $term && !is_wp_error($term)): // check it here
		if( $include_children ) {
			unset($query['attachment']);
			$parent = $term->parent;
			while( $parent ) {
				$parent_term = get_term( $parent, $tax_name);
				$name = $parent_term->slug . '/' . $name;
				$parent = $parent_term->parent;
			}
		} else {
			unset($query['name']);
		}
		switch( $tax_name ):
			case 'category':{
				$query['category_name'] = $name; // for categories
				break;
			}
			case 'post_tag':{
				$query['tag'] = $name; // for post tags
				break;
			}
			default:{
				$query[$tax_name] = $name; // for another taxonomies
				break;
			}
		endswitch;
	endif;
	return $query; 
}
add_filter( 'term_link', 'timo_rudr_term_permalink', 10, 3 );
function timo_rudr_term_permalink( $url, $term, $taxonomy ){
	$taxonomy_name = 'product_cat'; // your taxonomy name here
	$taxonomy_slug = 'product-category'; // the taxonomy slug can be different with the taxonomy name (like 'post_tag' and 'tag' )
 
	// exit the function if taxonomy slug is not in URL
	if ( strpos($url, $taxonomy_slug) === FALSE || $taxonomy != $taxonomy_name ) return $url;
	$url = str_replace('/' . $taxonomy_slug, '', $url);
	return $url;
}

//remove custom post slug (product, your custom post slug ....)
function timo_gp_remove_cpt_slug( $post_link, $post, $leavename ) {
    if ( ('portfolio' == $post->post_type || 'product' == $post->post_type) && 'publish' == $post->post_status ) {
    $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link ); //enter your custom_post in if function
        return $post_link;
    }
    return $post_link;
}

add_filter( 'post_type_link', 'timo_gp_remove_cpt_slug', 10, 3 );
function timo_gp_add_cpt_post_names_to_main_query( $query ) {
	// Bail if this is not the main query.
	if ( ! $query->is_main_query() ) {
		return;
	}
	// Bail if this query doesn't match our very specific rewrite rule.
	if ( ! isset( $query->query['page'] ) || 2 !== count( $query->query ) ) {
		return;
	}
	// Bail if we're not querying based on the post name.
	if ( empty( $query->query['name'] ) ) {
		return;
	}
	// Add CPT to the list of post types WP will include when it queries based on the post name.
	$query->set( 'post_type', array( 'post', 'page', 'portfolio', 'product' ) );
}
add_action( 'pre_get_posts', 'timo_gp_add_cpt_post_names_to_main_query' );
?>
