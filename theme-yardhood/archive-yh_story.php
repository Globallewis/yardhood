<?php
/**
 * Stories section front: lead feature, then picture cards. Also used for story types and series.
 */
get_header();
$types = get_terms( array( 'taxonomy' => 'yh_story_type', 'hide_empty' => false ) );
$term  = get_queried_object();
$on    = $term instanceof WP_Term ? $term->term_id : 0;
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top yh-front">
		<div class="yh-container">
			<header class="yh-front-head">
				<span class="yh-front-kick">Culture · People · Purpose</span>
				<h1 class="yh-front-title"><?php echo $on ? esc_html( $term->name ) : 'Stories Worth Knowing'; ?></h1>
				<p class="yh-front-dek">Artists, makers, business owners and neighbors on where they started and what they are building.</p>
			</header>
			<?php if ( $types && ! is_wp_error( $types ) ) : ?>
				<nav class="yh-topic-index" aria-label="Story types">
					<span class="yh-topic-label">Sections</span>
					<a class="<?php echo $on ? '' : 'is-on'; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'yh_story' ) ); ?>">All</a>
					<?php foreach ( $types as $t ) : ?>
						<a class="<?php echo $on === $t->term_id ? 'is-on' : ''; ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<?php yh_t_section_front( 'The first features are being written now.', yh_page_url( 'submit-your-story' ), 'Submit your story' ); ?>
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
