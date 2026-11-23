<?php
/**
 * Perspectives section front (also used for topic pages).
 */
get_header();
$term   = get_queried_object();
$on_tax = $term instanceof WP_Term && 'yh_pillar' === $term->taxonomy;
$write  = yh_page_url( 'write-a-perspective' );
$wlink  = $on_tax ? add_query_arg( 'pillar', $term->slug, $write ) : $write;
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top yh-front">
		<div class="yh-container">
			<header class="yh-front-head">
				<span class="yh-front-kick">The Opinion Pages · Winter Issue</span>
				<h1 class="yh-front-title"><?php echo $on_tax ? esc_html( $term->name ) : 'Perspectives'; ?></h1>
				<p class="yh-front-dek"><?php echo $on_tax ? 'Members and columnists on ' . esc_html( strtolower( $term->name ) ) . '.' : 'Members and columnists make their case on the things that shape a life.'; ?></p>
			</header>
			<?php echo yh_t_topic_index( $on_tax ? $term->term_id : 0 ); // phpcs:ignore ?>
			<?php yh_t_section_front( $on_tax ? 'Nobody has written on this topic yet. It could be you.' : 'The first Perspectives are being written now.', $wlink, 'Write a perspective' ); ?>
		</div>
	</section>
	<section class="yh-section yh-plum">
		<div class="yh-container yh-split">
			<div>
				<h2 class="yh-h2">Got something to say?</h2>
				<p>Pick a topic, give it a headline, and make your case. An editor reads every piece.</p>
			</div>
			<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( $wlink ); ?>">Write a perspective</a>
		</div>
	</section>
</div>
<?php
get_footer();
