<?php
/**
 * Template Name: The Hood (member communities)
 */
get_header();
$groups = array();
if ( function_exists( 'groups_get_groups' ) ) {
	$res    = groups_get_groups( array( 'group_type' => 'member', 'per_page' => 24, 'type' => 'active' ) );
	$groups = isset( $res['groups'] ) ? $res['groups'] : array();
}
?>
<div class="yh-wrap-full">
	<section class="yh-feature-hero yh-feature-hero-short">
		<img src="<?php echo esc_url( yh_img( 'hood-street' ) ); ?>" alt="Neighbors talking on a city sidewalk beside a colorful mural." fetchpriority="high">
		<span class="yh-feature-scrim" aria-hidden="true"></span>
		<div class="yh-container yh-feature-hero-text">
			<?php echo yh_t_kicker( 'Member communities' ); // phpcs:ignore ?>
			<h1 class="yh-h1">The Hood</h1>
			<p class="yh-lede">The people, places and ideas in our community. Groups started by members, for the folks they want around the table.</p>
		</div>
	</section>

	<section class="yh-section yh-cream">
		<div class="yh-container">
			<h2 class="yh-h2">Member groups</h2>
			<?php if ( $groups ) : ?>
				<div class="yh-cards">
					<?php foreach ( $groups as $g ) : ?>
						<a class="yh-card" href="<?php echo esc_url( bp_get_group_permalink( $g ) ); ?>">
							<span class="yh-card-kick">Member group</span>
							<span class="yh-card-title"><?php echo esc_html( $g->name ); ?></span>
							<span class="yh-card-dek"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $g->description ), 18 ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="yh-empty">The first member groups open soon: family groups, reunions, game nights, book clubs, city crews. Want to start one? Start below.</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="yh-section yh-plum" id="host">
		<div class="yh-container yh-host">
			<div>
				<?php echo yh_t_kicker( 'Host your people here' ); // phpcs:ignore ?>
				<h2 class="yh-h2">Bring your community to the yard.</h2>
				<?php
				$hs = isset( $_GET['host_status'] ) ? sanitize_key( $_GET['host_status'] ) : '';
				if ( 'active' === $hs ) {
					echo '<div class="yh-notice yh-notice-ok" style="color:#3B1E35;background:#F6EFE4">You are a Yardhood host. Your first month is free. Start your group below.</div>';
				} elseif ( 'pending' === $hs || 'check' === $hs ) {
					echo '<div class="yh-notice yh-notice-ok" style="color:#3B1E35;background:#F6EFE4">Thank you. We got your sign-up and will switch on hosting for you shortly.</div>';
				}
				?>
				<div class="yh-plan">
					<div class="yh-plan-top"><span class="yh-plan-name">Community Host</span><span class="yh-plan-price">$10<small>/mo</small></span></div>
					<p class="yh-plan-free">First month free</p>
					<ul class="yh-plan-list">
						<li>Start and run your own group in The Hood</li>
						<li>Members join free</li>
						<li>Group page, discussion, photos and events in the Yard Guide</li>
						<li>Cancel anytime</li>
					</ul>
					<?php
					$checkout = function_exists( 'yh_host_checkout_url' ) ? yh_host_checkout_url() : '';
					if ( function_exists( 'yh_user_is_host' ) && yh_user_is_host() && function_exists( 'bp_get_groups_directory_permalink' ) ) :
						?>
						<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( trailingslashit( bp_get_groups_directory_permalink() ) . 'create/' ); ?>">Start your group</a>
					<?php elseif ( $checkout ) : ?>
						<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( is_user_logged_in() ? $checkout : yh_join_url( $checkout ) ); ?>">Start hosting free</a>
					<?php else : ?>
						<p class="yh-note">Host sign-up opens soon. Send a request and we will set your group up with you.</p>
					<?php endif; ?>
				</div>
				<p class="yh-small yh-more-plans">Charging your own members, or running rooms of 50+? <a href="<?php echo esc_url( yh_page_url( 'hosting' ) ); ?>">See how hosting works and the larger plans</a>.</p>
			</div>
			<div class="yh-host-form">
				<h3 class="yh-form-title">Questions first?</h3>
				<p class="yh-help">Tell us about your group and we will get back to you.</p>
				<?php echo do_shortcode( '[yardhood_host_request]' ); ?>
			</div>
		</div>
	</section>
</div>
<?php
get_footer();
