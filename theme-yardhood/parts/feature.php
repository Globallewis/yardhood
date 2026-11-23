<?php
/**
 * Magazine feature cards shared by Perspectives, Stories and Your Yard.
 */

/** Photo for a piece: its own image, else an issue photo that fits its topic or type. */
function yh_t_feature_img( $id ) {
	if ( has_post_thumbnail( $id ) ) {
		return get_the_post_thumbnail_url( $id, 'large' );
	}
	$map = array(
		'family' => 'dinner-table', 'self' => 'library', 'purpose' => 'artist-studio', 'money' => 'hood-street',
		'community' => 'cookout', 'health' => 'plate', 'spirituality' => 'movie-screen', 'mind' => 'library',
		'creativity' => 'artist-studio', 'environment' => 'hood-street', 'legacy' => 'dinner-table', 'mastery' => 'cookout',
		'artist-creative' => 'artist-studio', 'career' => 'library', 'entrepreneur' => 'hood-street',
	);
	foreach ( array( 'yh_pillar', 'yh_story_type' ) as $tax ) {
		$t = get_the_terms( $id, $tax );
		if ( $t && ! is_wp_error( $t ) && isset( $map[ $t[0]->slug ] ) ) {
			return yh_img( $map[ $t[0]->slug ] );
		}
	}
	return yh_img( 'cookout' );
}

/** Label, topic, headline pieces for any story or perspective. */
function yh_t_feature_bits( $id ) {
	$is_story = 'yh_story' === get_post_type( $id );
	$topic    = get_the_terms( $id, 'yh_pillar' );
	$type     = get_the_terms( $id, 'yh_story_type' );
	$section  = $is_story ? ( $type && ! is_wp_error( $type ) ? $type[0]->name : 'Story' ) : ( function_exists( 'yh_perspective_label' ) ? yh_perspective_label( $id ) : 'Perspective' );
	$by       = $is_story ? get_post_meta( $id, '_yh_name', true ) : ( function_exists( 'yh_perspective_byline' ) ? yh_perspective_byline( $id ) : '' );
	$sub      = $is_story ? get_post_meta( $id, '_yh_dek', true ) : get_post_meta( $id, '_yh_subtitle', true );
	return array(
		'label' => $section,
		'topic' => $topic && ! is_wp_error( $topic ) ? $topic[0]->name : '',
		'by'    => $by,
		'sub'   => $sub,
		'img'   => yh_t_feature_img( $id ),
		'time'  => function_exists( 'yh_read_time' ) ? yh_read_time( $id ) : '',
	);
}

function yh_t_feature_lead( $post ) {
	$id  = $post->ID;
	$b   = yh_t_feature_bits( $id );
	$ex  = wp_trim_words( wp_strip_all_tags( preg_replace( '#<h3>.*?</h3>#s', '', $post->post_content ) ), 42, '…' );
	ob_start();
	?>
	<a class="yh-lead" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
		<span class="yh-lead-img"><img src="<?php echo esc_url( $b['img'] ); ?>" alt="" loading="eager"><span class="yh-fc-tag"><?php echo esc_html( $b['label'] ); ?></span></span>
		<span class="yh-lead-text">
			<?php if ( $b['topic'] ) : ?><span class="yh-card-kick"><?php echo esc_html( $b['topic'] ); ?></span><?php endif; ?>
			<span class="yh-lead-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
			<?php if ( $b['sub'] ) : ?><span class="yh-lead-sub"><?php echo esc_html( $b['sub'] ); ?></span><?php endif; ?>
			<span class="yh-lead-ex"><?php echo esc_html( $ex ); ?></span>
			<span class="yh-fc-by"><?php echo esc_html( trim( ( $b['by'] ? 'By ' . $b['by'] : '' ) . ( $b['time'] ? ' · ' . $b['time'] : '' ), ' ·' ) ); ?></span>
			<span class="yh-lead-go">Read it <span aria-hidden="true">&rarr;</span></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}

function yh_t_feature_card( $post ) {
	$id = $post->ID;
	$b  = yh_t_feature_bits( $id );
	ob_start();
	?>
	<a class="yh-fcard" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
		<span class="yh-fc-img"><img src="<?php echo esc_url( $b['img'] ); ?>" alt="" loading="lazy"><span class="yh-fc-tag"><?php echo esc_html( $b['label'] ); ?></span></span>
		<?php if ( $b['topic'] ) : ?><span class="yh-card-kick"><?php echo esc_html( $b['topic'] ); ?></span><?php endif; ?>
		<span class="yh-fc-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
		<?php if ( $b['sub'] ) : ?><span class="yh-fc-sub"><?php echo esc_html( $b['sub'] ); ?></span><?php endif; ?>
		<?php if ( $b['by'] ) : ?><span class="yh-fc-by">By <?php echo esc_html( $b['by'] ); ?></span><?php endif; ?>
	</a>
	<?php
	return ob_get_clean();
}

/** Section front: lead piece, then cards. Uses the main query. */
function yh_t_section_front( $empty_text, $empty_link, $empty_label ) {
	if ( ! have_posts() ) {
		echo '<div class="yh-empty-block yh-empty-cream"><p class="yh-empty">' . esc_html( $empty_text ) . '</p><a class="yh-btn yh-btn-plum" href="' . esc_url( $empty_link ) . '">' . esc_html( $empty_label ) . '</a></div>';
		return;
	}
	$i = 0;
	$cards = '';
	while ( have_posts() ) {
		the_post();
		if ( 0 === $i && ! is_paged() ) {
			echo yh_t_feature_lead( get_post() ); // phpcs:ignore
		} else {
			$cards .= yh_t_feature_card( get_post() );
		}
		$i++;
	}
	if ( $cards ) {
		echo '<h2 class="yh-more-head">More in this section</h2><div class="yh-fcards">' . $cards . '</div>'; // phpcs:ignore
	}
	the_posts_pagination();
}

/** Topic index line, magazine style. */
function yh_t_topic_index( $current = 0 ) {
	$terms = function_exists( 'yh_pillar_terms' ) ? yh_pillar_terms() : array();
	if ( ! $terms ) {
		return '';
	}
	$out = '<nav class="yh-topic-index" aria-label="Topics"><span class="yh-topic-label">Topics</span>';
	$out .= '<a class="' . ( $current ? '' : 'is-on' ) . '" href="' . esc_url( get_post_type_archive_link( 'yh_perspective' ) ) . '">All</a>';
	foreach ( $terms as $t ) {
		$out .= '<a class="' . ( $current === $t->term_id ? 'is-on' : '' ) . '" href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
	}
	return $out . '</nav>';
}
