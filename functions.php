<?php
/*
Author: basanatakumar
Version: 1.0.1
Author URI: http://crayonux.com/
*/

define( 'SEQUENCE_PLUGIN_URL',        	WP_PLUGIN_URL . '/wp-sequence-slider/' );
define( 'SEQUENCE_IMAGES_DIR',  		SEQUENCE_PLUGIN_DIR . 'images' );

/*
 *	Register New sequence_slider
 *
 */
 // hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_slider_taxonomies', 0 );

// create two taxonomies, genres and writers for the post type "book"
function create_slider_taxonomies() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Slider Category', 'taxonomy general name' ),
		'singular_name'     => _x( 'Slider Categories', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Slider Category' ),
		'all_items'         => __( 'All Slider Category' ),
		'parent_item'       => __( 'Parent Slider Category' ),
		'parent_item_colon' => __( 'Parent Slider Category:' ),
		'edit_item'         => __( 'Edit Slider Category' ),
		'update_item'       => __( 'Update Slider Category' ),
		'add_new_item'      => __( 'Add New Slider Category' ),
		'new_item_name'     => __( 'New Slider Category Name' ),
		'menu_name'         => __( 'Slider Category' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'slider-category' ),
	);

	register_taxonomy( 'slider-category', array( 'sequence-slider' ), $args );
}

function sequence_slider_init() {
  $labels = array(
    'name'               => 'Sequence Slider',
    'singular_name'      => 'Sequence Slider',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Slider',
    'edit_item'          => 'Edit Slider',
    'new_item'           => 'New Slider',
    'all_items'          => 'All Sliders',
    'view_item'          => 'View Slider',
    'search_items'       => 'Search Sliders',
    'not_found'          => 'No Sliders found',
    'not_found_in_trash' => 'No Sliders found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => 'Sequence Slider'
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'sequence-slider' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => null,
	'taxonomies' => array('slider-category'),
	// 'sequence_slider_meta_box' => 'add_sequence_slider_metaboxes',
    'supports'           => array( 'title', 'thumbnail', 'excerpt' )
  );

  register_post_type( 'sequence-slider', $args );
  
}
add_action( 'init', 'sequence_slider_init' );

// added post meta to slider custom post type
add_action( 'add_meta_boxes', 'add_slider_metaboxes' );
function add_slider_metaboxes() {
	add_meta_box('sqn_hide_title_excerpt', 'Hide Title and Excerpt', 'sqn_hide_title_excerpt', 'sequence-slider', 'normal');
	add_meta_box('sqn_slider_link', 'Slider link and text', 'sqn_slider_link', 'sequence-slider', 'normal');
}

// The slider  Metabox
function sqn_hide_title_excerpt($post)
{
	$remove_title_excerpt = get_post_meta($post->ID, '_display_title_excerpt', true);
	
	if($remove_title_excerpt){
	    echo '<input type="checkbox"  name="_display_title_excerpt" checked value="_display_title_excerpt">Hide Title and Excerpt from Slider<br>';
	} else{
		echo '<input type="checkbox"  name="_display_title_excerpt" value="_display_title_excerpt">Hide Title and Excerpt from Slider<br>';
	}
}


function sqn_slider_link($post) 
{
    // Get the location data if its already been entered
    $slidermeta_link = get_post_meta($post->ID, '_link', true);
	$slidermeta_text = get_post_meta($post->ID, '_text', true);
	$display_link_text = get_post_meta($post->ID, '_display_link_text', true);
	$link_to_featured_image = get_post_meta($post->ID, '_link_to_featured_image', true);
	
    // Echo out the field
    echo '<input type="text" name="_link" value="' . $slidermeta_link . '"  /><br/><br/>';
	echo '<input type="text" name="_text" value="' . $slidermeta_text . '"/> <br/><br/>';
	
	if($display_link_text){
	    echo '<input type="checkbox"  name="_display_link_text" checked value="_display_link_text">Hide Slider Link and Text<br>';
	} else{
		echo '<input type="checkbox"  name="_display_link_text" value="_display_link_text">Hide Slider Link and Text<br>';
	}
	
	if($link_to_featured_image){
	    echo '<input type="checkbox"  name="_link_to_featured_image" checked value="_link_to_featured_image">Link to Slider Image<br>';
	} else{
		echo '<input type="checkbox"  name="_link_to_featured_image" value="_link_to_featured_image">Link to Slider Image<br>';
	}
}




add_action( 'save_post', 'sqn_meta_box_save' );
function sqn_meta_box_save( $post_id )
{
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;
	
	// Probably a good idea to make sure your data is set
	if( isset( $_POST['_link'] ) ){
		update_post_meta( $post_id, '_link', wp_kses( esc_url( $_POST['_link'] ), $allowed ) );
	}
	if( isset( $_POST['_text'] ) ){
		update_post_meta( $post_id, '_text', wp_kses( $_POST['_text'], $allowed ) );
	}
	
	if( isset( $_POST['_display_title_excerpt'] ) ){
		update_post_meta( $post_id, '_display_title_excerpt', true );
	}
	else{
		update_post_meta( $post_id, '_display_title_excerpt', false );
	}
	
	if( isset( $_POST['_display_link_text'] ) ){
		update_post_meta( $post_id, '_display_link_text', true );
	    //var_dump($_POST['_display_link_text']);
		//exit;
	}
	else{
	   update_post_meta( $post_id, '_display_link_text', false );
	   //var_dump($_POST);
	   //exit;
	}
	
	if( isset( $_POST['_link_to_featured_image'] ) ){
		update_post_meta( $post_id, '_link_to_featured_image', true );
	}
	else{
		update_post_meta( $post_id, '_link_to_featured_image', false );
	}
}


//end post meta

function sequence_slider_display( $atts=null ) {

   extract( shortcode_atts( array(
	   'limit' => null,
		'slider_category' => null,
    ), $atts ) );
	
	$args = array(
		'post_type'=> 'sequence-slider',
		'posts_per_page'    => $limit,
		'slider-category' => $slider_category,
	);
	
	// The Query
	$the_query = new WP_Query( $args );
	
	$html = null;

	// The Loop
	if ( $the_query->have_posts() ) {
		    //var_dump($the_query);
		 	$html .= '<div class="sequence-theme">';
			$html .= '<div class="sequence">';
			$html .= '<img class="sequence-prev" src="'. SEQUENCE_PLUGIN_URL .'images/bt-prev.png" alt="Previous Frame" />';
			$html .= '<img class="sequence-next" src="'. SEQUENCE_PLUGIN_URL .'images/bt-next.png" alt="Next Frame" />';
			$html .= '<ul class="sequence-canvas">';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				//echo "<pre>";
				//print_r($the_query);
				$html .= '<li class="animate-in">';
				
				if(get_post_meta( $the_query->post->ID, '_display_title_excerpt', true ) != true){
				    $html .= '<h3 class="title">' . get_the_title() .'</h3>';
				    $html .= '<h4 class="subtitle">' . get_the_excerpt();
				}
				
				if(get_post_meta( $the_query->post->ID, '_display_link_text', true ) != true){
				    $html .= '<a class="slider_link" href="' .get_post_meta( $the_query->post->ID, '_link', true ).'">' . get_post_meta( $the_query->post->ID, '_text', true ) .'</a></h4>';
				}
				
				if(get_post_meta( $the_query->post->ID, '_link_to_featured_image', true ) == true){
					$html .= '<a class="model" href="' . get_post_meta( $the_query->post->ID, '_link', true ).'">';
				    $html .= get_the_post_thumbnail($the_query->post->ID, 'full' ,array('class' => 'model')); 
					$html .= '</a>';
				} else {
					$html .= get_the_post_thumbnail($the_query->post->ID, 'full' ,array('class' => 'model')); 
				}
				
				$html .= '</li>';
			}
			$html .= '</ul>';
			//$html .= '<ul class="sequence-pagination"></ul>';
			$html .= '</div></div>';
	} else {
		// no posts found
	}
	/* Restore original Post Data */
	wp_reset_postdata();
	
    return $html;
}
add_shortcode( 'sequence_slider', 'sequence_slider_display' );

