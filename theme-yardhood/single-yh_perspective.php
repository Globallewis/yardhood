<?php
/**
 * A Perspective: two-part headline, byline with member or columnist label, essay, bio.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$sub   = get_post_meta( $id, '_yh_subtitle', true );
	$bio   = get_post_meta( $id, '_yh_bio', true );
	$pt    = get_the_terms( $id, 'yh_pillar' );
	$pill  = $pt && ! is_wp_error( $pt ) ? $pt[0] : null;
	$write = yh_page_url( 'write-a-perspective' );
	?>
	<article class="yh-wrap-full yh-article yh-persp">
		<header class="yh-article-head yh-plum">
			<div class="yh-container yh-persp-head">
				<?php echo yh_t_kicker( 'Perspectives' . ( $pill ? ' · ' . $pill->name : '' ) ); // phpcs:ignore ?>
				<h1 class="yh-h1"><?php the_title(); ?></h1>
				<?php if ( $sub ) : ?><p class="yh-lede"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
				<p class="yh-byline">By <?php echo esc_html( yh_perspective_byline( $id ) ); ?> <span aria-hidden="true">·</span> <?php echo esc_html( yh_perspective_label( $id ) ); ?> <span aria-hidden="true">·</span> <?php echo esc_html( yh_read_time( $id ) ); ?></p>
			</div>
		</header>
		<div class="yh-cream yh-section">
			<div class="yh-container yh-article-body">
				<div class="yh-prose yh-dropcap"><?php the_content(); ?></div>
				<?php if ( $bio ) : ?><p class="yh-persp-bio"><?php echo esc_html( $bio ); ?></p><?php endif; ?>
				<p class="yh-help yh-persp-note">Perspectives are the views of their writers, not Yardhood.</p>
				<?php if ( $pill ) : ?><p><a class="yh-chip" href="<?php echo esc_url( get_term_link( $pill ) ); ?>">More on <?php echo esc_html( $pill->name ); ?></a></p><?php endif; ?>
			</div>
		</div>
		<section class="yh-section yh-plum">
			<div class="yh-container yh-split">
				<div>
					<h2 class="yh-h2">See it differently?</h2>
					<p>Write your own take. An editor reads every piece.</p>
				</div>
				<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( $pill ? add_query_arg( 'pillar', $pill->slug, $write ) : $write ); ?>">Write a perspective</a>
			</div>
		</section>
	</article>
	<?php
endwhile;
get_footer();
