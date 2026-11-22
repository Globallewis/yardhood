<?php
/**
 * Stories: editorial features (Artist Stories, How I Got Here, Entrepreneurs, Community).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_story_types() {
	return array(
		'artist-creative' => 'Artist & Creative',
		'career'          => 'How I Got Here',
		'entrepreneur'    => 'Entrepreneur',
		'community'       => 'Community',
	);
}

function yh_story_series() {
	return array( 'The Art of Becoming', 'People Worth Knowing', 'Behind the Craft', 'Around the Hood', 'The Game They Don\'t Teach You', 'Before the World Knows', 'Show Us Your Plate' );
}

function yh_register_stories_cpt() {
	register_post_type( 'yh_story', array(
		'labels'        => array(
			'name'          => 'Stories',
			'singular_name' => 'Story',
			'add_new_item'  => 'Add story',
			'edit_item'     => 'Edit story',
			'all_items'     => 'All stories',
		),
		'public'        => true,
		'has_archive'   => 'stories',
		'rewrite'       => array( 'slug' => 'stories', 'with_front' => false ),
		'menu_icon'     => 'dashicons-format-quote',
		'menu_position' => 22,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'show_in_rest'  => true,
	) );
	register_taxonomy( 'yh_story_type', 'yh_story', array(
		'label'             => 'Story type',
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'story-type', 'with_front' => false ),
	) );
	register_taxonomy( 'yh_series', 'yh_story', array(
		'label'             => 'Series label',
		'hierarchical'      => false,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'series', 'with_front' => false ),
	) );
}
add_action( 'init', 'yh_register_stories_cpt' );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'yh_story_details', 'Feature details', 'yh_story_metabox', 'yh_story', 'normal', 'high' );
	add_meta_box( 'yh_story_submission', 'Original submission and permissions', 'yh_story_submission_box', 'yh_story', 'normal', 'default' );
} );

function yh_story_fields() {
	return array(
		'_yh_name'        => array( 'Name (as they want it shown)', 'text' ),
		'_yh_discipline'  => array( 'Discipline or field', 'text' ),
		'_yh_city'        => array( 'City', 'text' ),
		'_yh_dek'         => array( 'Dek (one or two sentences under the headline)', 'textarea' ),
		'_yh_pull'        => array( 'Pull quote (only words they actually said)', 'textarea' ),
		'_yh_video'       => array( 'Video link (YouTube, Vimeo, Zoom recording page)', 'url' ),
		'_yh_top1'        => array( 'Top Three: first', 'text' ),
		'_yh_top2'        => array( 'Top Three: second', 'text' ),
		'_yh_top3'        => array( 'Top Three: third', 'text' ),
		'_yh_photo_credit'=> array( 'Portrait credit', 'text' ),
	);
}

function yh_story_metabox( $post ) {
	wp_nonce_field( 'yh_story_save', 'yh_story_nonce' );
	echo '<style>.yh-f{display:grid;grid-template-columns:220px 1fr;gap:10px 16px;align-items:start}.yh-f input,.yh-f textarea{width:100%}</style><div class="yh-f">';
	foreach ( yh_story_fields() as $k => $f ) {
		$v = get_post_meta( $post->ID, $k, true );
		echo '<label for="' . esc_attr( $k ) . '">' . esc_html( $f[0] ) . '</label>';
		if ( 'textarea' === $f[1] ) {
			echo '<textarea id="' . esc_attr( $k ) . '" name="' . esc_attr( $k ) . '" rows="2">' . esc_textarea( $v ) . '</textarea>';
		} else {
			echo '<input id="' . esc_attr( $k ) . '" type="' . esc_attr( $f[1] ) . '" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
		}
	}
	$show = get_post_meta( $post->ID, '_yh_top_publish', true );
	echo '<span>Show Top Three</span><label><input type="checkbox" name="_yh_top_publish" value="1" ' . checked( $show, '1', false ) . '> They opted in to showing their Top Three</label>';
	echo '</div><p style="color:#666">Use the featured image for the portrait. Add selected work as images in the article body. AI-made art never gets an artist credit.</p>';
}

function yh_story_submission_box( $post ) {
	$sub = get_post_meta( $post->ID, '_yh_submission', true );
	if ( ! $sub || ! is_array( $sub ) ) {
		echo '<p>This story was not sent through the Submit Your Story form.</p>';
		return;
	}
	echo '<table class="widefat striped"><tbody>';
	foreach ( $sub as $k => $v ) {
		if ( is_array( $v ) ) {
			$v = implode( ', ', $v );
		}
		echo '<tr><th style="width:220px">' . esc_html( $k ) . '</th><td>' . nl2br( esc_html( (string) $v ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
}

add_action( 'save_post_yh_story', function ( $post_id ) {
	if ( ! isset( $_POST['yh_story_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yh_story_nonce'] ) ), 'yh_story_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( yh_story_fields() as $k => $f ) {
		if ( ! isset( $_POST[ $k ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $k ] );
		$val = 'url' === $f[1] ? esc_url_raw( $raw ) : ( 'textarea' === $f[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw ) );
		update_post_meta( $post_id, $k, $val );
	}
	update_post_meta( $post_id, '_yh_top_publish', empty( $_POST['_yh_top_publish'] ) ? '0' : '1' );
} );

/** The Top Three as one understated line, only when opted in. */
function yh_top_three_line( $post_id ) {
	if ( '1' !== get_post_meta( $post_id, '_yh_top_publish', true ) ) {
		return '';
	}
	$items = array_filter( array_map( 'trim', array(
		get_post_meta( $post_id, '_yh_top1', true ),
		get_post_meta( $post_id, '_yh_top2', true ),
		get_post_meta( $post_id, '_yh_top3', true ),
	) ) );
	if ( count( $items ) < 1 ) {
		return '';
	}
	return '<p class="yh-top3"><span class="yh-top3-label">The Top Three</span> ' . implode( ' <span aria-hidden="true">·</span> ', array_map( 'esc_html', $items ) ) . '</p>';
}

function yh_read_time( $post_id ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) round( $words / 230 ) ) . ' min read';
}

/* Admin: pending review count in the menu. */
add_action( 'admin_menu', function () {
	global $menu;
	$count = wp_count_posts( 'yh_story' );
	$n     = isset( $count->pending ) ? (int) $count->pending : 0;
	if ( ! $n ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=yh_story' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>';
		}
	}
}, 99 );
