<?php
/**
 * Perspectives: member essays and columnists, browsable by pillar.
 * Also used for pillar pages (essays and stories together).
 */
get_header();
$term    = get_queried_object();
$on_tax  = $term instanceof WP_Term && 'yh_pillar' === $term->taxonomy;
$pillars = function_exists( 'yh_pillar_terms' ) ? yh_pillar_terms() : array();
$write   = yh_page_url( 'write-a-perspective' );
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top">
		<div class="yh-container">
			<?php echo yh_t_kicker( $on_tax ? 'The 12 Pillars' : 'Member essays and columns' ); // phpcs:ignore ?>
			<?php if ( $on_tax ) : ?>
				<h1 class="yh-h1"><?php echo esc_html( $term->name ); ?></h1>
				<p class="yh-sub">Perspectives and stories on <?php echo esc_html( strtolower( $term->name ) ); ?>, from people who live it.</p>
			<?php else : ?>
				<h1 class="yh-h1">Perspec<span class="yh-gold-text">tives</span></h1>
				<p class="yh-sub">Members make their case on the things that shape a life. Different voices, one table.</p>
			<?php endif; ?>
			<?php if ( $pillars ) : ?>
				<nav class="yh-chips yh-chips-dark yh-pillar-chips" aria-label="Pillars">
					<a class="yh-chip<?php echo $on_tax ? '' : ' is-on'; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'yh_perspective' ) ); ?>">All</a>
					<?php foreach ( $pillars as $t ) : ?>
						<a class="yh-chip<?php echo ( $on_tax && $term->term_id === $t->term_id ) ? ' is-on' : ''; ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="yh-persp-list">
					<?php
					while ( have_posts() ) :
						the_post();
						$pid   = get_the_ID();
						$is_st = 'yh_story' === get_post_type();
						$pt    = get_the_terms( $pid, 'yh_pillar' );
						$kick  = $is_st ? 'Story' : yh_perspective_label( $pid );
						$sub   = $is_st ? get_post_meta( $pid, '_yh_dek', true ) : get_post_meta( $pid, '_yh_subtitle', true );
						$by    = $is_st ? get_post_meta( $pid, '_yh_name', true ) : yh_perspective_byline( $pid );
						?>
						<a class="yh-persp-item" href="<?php the_permalink(); ?>">
							<span class="yh-card-kick"><?php echo esc_html( $kick . ( $pt && ! is_wp_error( $pt ) && ! $on_tax ? ' · ' . $pt[0]->name : '' ) ); ?></span>
							<span class="yh-persp-title"><?php the_title(); ?></span>
							<?php if ( $sub ) : ?><span class="yh-persp-sub"><?php echo esc_html( $sub ); ?></span><?php endif; ?>
							<?php if ( $by ) : ?><span class="yh-story-by">By <?php echo esc_html( $by ); ?> · <?php echo esc_html( yh_read_time( $pid ) ); ?></span><?php endif; ?>
						</a>
					<?php endwhile; ?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<div class="yh-empty-block yh-empty-cream">
					<p class="yh-empty"><?php echo $on_tax ? 'Nobody has written on this pillar yet. It could be you.' : 'The first Perspectives are being written now.'; ?></p>
					<a class="yh-btn yh-btn-plum" href="<?php echo esc_url( $on_tax ? add_query_arg( 'pillar', $term->slug, $write ) : $write ); ?>">Write a perspective</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<section class="yh-section yh-plum">
		<div class="yh-container yh-split">
			<div>
				<h2 class="yh-h2">Got something to say?</h2>
				<p>Pick a pillar, give it a headline, and make your case. An editor reads every piece.</p>
			</div>
			<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( $on_tax ? add_query_arg( 'pillar', $term->slug, $write ) : $write ); ?>">Write a perspective</a>
		</div>
	</section>
</div>
<?php
get_footer();
