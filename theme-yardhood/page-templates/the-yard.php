<?php
/**
 * Template Name: The Yard (official spaces)
 */
get_header();
$spaces = function_exists( 'yh_official_spaces' ) ? yh_official_spaces() : array();
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top">
		<div class="yh-container">
			<?php echo yh_t_kicker( 'Official spaces' ); // phpcs:ignore ?>
			<h1 class="yh-h1">The Yard</h1>
			<p class="yh-sub">Where the regular gatherings live. Join a space to see its threads, photos and upcoming rooms.</p>
			<div class="yh-tiles">
				<?php foreach ( $spaces as $s ) : ?>
					<a class="yh-tile" href="<?php echo esc_url( yh_space_url( $s['slug'] ) ); ?>" style="--tile-bg:<?php echo esc_attr( $s['bg'] ); ?>">
						<img src="<?php echo esc_url( yh_img( $s['img'] ) ); ?>" alt="" loading="lazy" style="object-position:<?php echo esc_attr( $s['pos'] ); ?>">
						<span class="yh-tile-scrim" aria-hidden="true"></span>
						<span class="yh-tile-text">
							<span class="yh-tile-kick"><?php echo esc_html( $s['kick'] ); ?></span>
							<span class="yh-tile-name"><?php echo esc_html( $s['name'] ); ?></span>
							<span class="yh-tile-dek"><?php echo esc_html( $s['dek'] ); ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<section class="yh-section yh-plum">
		<div class="yh-container yh-split">
			<div>
				<h2 class="yh-h2">Looking for a smaller circle?</h2>
				<p>Members run their own groups in The Hood: family groups and reunions, game nights, book clubs, cities, crafts, careers and more.</p>
			</div>
			<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( yh_page_url( 'the-hood' ) ); ?>">Explore the Hood</a>
		</div>
	</section>
</div>
<?php
get_footer();
