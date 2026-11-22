<?php
/**
 * The Yard Guide: TV-guide style listings, grouped by day.
 */
get_header();
$lanes   = function_exists( 'yh_lanes' ) ? yh_lanes() : array();
$current = isset( $_GET['lane'] ) ? sanitize_key( $_GET['lane'] ) : '';
$past    = ! empty( $_GET['past'] );
$base    = get_post_type_archive_link( 'yh_event' );
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-plum yh-page-top yh-guide">
		<div class="yh-container">
			<div class="yh-guide-head">
				<div>
					<?php echo yh_t_kicker( 'The Winter Issue' ); // phpcs:ignore ?>
					<h1 class="yh-h1">The Yard <span class="yh-gold">Guide</span></h1>
					<p class="yh-sub">Watch parties, cookouts, live rooms and conversations. All times Eastern.</p>
				</div>
			</div>
			<nav class="yh-chips" aria-label="Filter by lane">
				<a class="yh-chip<?php echo ! $current && ! $past ? ' is-on' : ''; ?>" href="<?php echo esc_url( $base ); ?>">Everything</a>
				<?php foreach ( $lanes as $k => $l ) : ?>
					<a class="yh-chip<?php echo $current === $k ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'lane', $k, $base ) ); ?>"><?php echo esc_html( $l['label'] ); ?></a>
				<?php endforeach; ?>
				<a class="yh-chip<?php echo $past ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'past', 1, $base ) ); ?>">Past</a>
			</nav>

			<?php if ( have_posts() ) : ?>
				<?php
				$last_day = '';
				while ( have_posts() ) :
					the_post();
					$start = function_exists( 'yh_event_start' ) ? yh_event_start( get_the_ID() ) : null;
					$day   = $start ? $start->format( 'l, F j' ) : 'Date to be announced';
					if ( $day !== $last_day ) {
						if ( $last_day ) {
							echo '</div>';
						}
						echo '<h2 class="yh-guide-day">' . esc_html( $day ) . '</h2><div class="yh-listings">';
						$last_day = $day;
					}
					echo yh_t_event_card( get_post() ); // phpcs:ignore
				endwhile;
				echo '</div>';
				?>
			<?php else : ?>
				<div class="yh-empty-block">
					<p class="yh-empty"><?php echo $past ? 'No past events yet.' : 'The winter lineup posts here soon.'; ?></p>
					<?php if ( ! is_user_logged_in() ) : ?>
						<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( yh_join_url( $base ) ); ?>">Join free to get the first look</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();
