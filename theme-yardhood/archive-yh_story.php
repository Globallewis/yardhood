<?php
/**
 * Stories: People Worth Knowing, How I Got Here, entrepreneurs and community.
 */
get_header();
$types = get_terms( array( 'taxonomy' => 'yh_story_type', 'hide_empty' => false ) );
$term  = get_queried_object();
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top">
		<div class="yh-container">
			<?php echo yh_t_kicker( 'Culture · People · Purpose' ); // phpcs:ignore ?>
			<h1 class="yh-h1">Stories <span class="yh-gold-text">Worth Knowing</span></h1>
			<p class="yh-sub">Artists, makers, business owners and neighbors on where they started and what they are building.</p>
			<?php if ( $types && ! is_wp_error( $types ) ) : ?>
				<nav class="yh-chips yh-chips-dark" aria-label="Story types">
					<a class="yh-chip<?php echo is_post_type_archive( 'yh_story' ) ? ' is-on' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'yh_story' ) ); ?>">All stories</a>
					<?php foreach ( $types as $t ) : ?>
						<a class="yh-chip<?php echo ( $term instanceof WP_Term && $term->term_id === $t->term_id ) ? ' is-on' : ''; ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="yh-story-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						$sid  = get_the_ID();
						$img  = has_post_thumbnail() ? get_the_post_thumbnail_url( $sid, 'large' ) : '';
						$name = get_post_meta( $sid, '_yh_name', true );
						$disc = get_post_meta( $sid, '_yh_discipline', true );
						$tt   = get_the_terms( $sid, 'yh_story_type' );
						?>
						<a class="yh-story-card" href="<?php the_permalink(); ?>">
							<span class="yh-story-img"><?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"><?php endif; ?></span>
							<span class="yh-card-kick"><?php echo esc_html( $tt && ! is_wp_error( $tt ) ? $tt[0]->name : 'Story' ); ?></span>
							<span class="yh-story-title"><?php the_title(); ?></span>
							<?php if ( $name ) : ?><span class="yh-story-by"><?php echo esc_html( $name . ( $disc ? ' · ' . $disc : '' ) ); ?></span><?php endif; ?>
						</a>
					<?php endwhile; ?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<div class="yh-empty-block yh-empty-cream">
					<p class="yh-empty">The first features are being written now.</p>
					<a class="yh-btn yh-btn-plum" href="<?php echo esc_url( yh_page_url( 'submit-your-story' ) ); ?>">Submit your story</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<section class="yh-section yh-plum">
		<div class="yh-container yh-split">
			<div>
				<h2 class="yh-h2">Got a story worth knowing?</h2>
				<p>Artists, makers, business owners and neighbors. Send a short video or answer a few questions.</p>
			</div>
			<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( yh_page_url( 'submit-your-story' ) ); ?>">Submit your story</a>
		</div>
	</section>
</div>
<?php
get_footer();
