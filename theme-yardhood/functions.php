<?php
/**
 * Yardhood child theme.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'yh-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=DM+Sans:wght@400;500;700&display=swap', array(), null );
	wp_enqueue_style( 'yardhood', get_stylesheet_directory_uri() . '/assets/yardhood.css', array(), filemtime( get_stylesheet_directory() . '/assets/yardhood.css' ) );
}, 200 );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<meta name="theme-color" content="#3B1E35">' . "\n";
}, 1 );

add_filter( 'body_class', function ( $c ) {
	$c[] = 'yardhood';
	if ( is_front_page() ) {
		$c[] = 'yh-cover-page';
	}
	if ( is_page_template() || is_post_type_archive( array( 'yh_event', 'yh_story' ) ) || is_singular( array( 'yh_event', 'yh_story' ) ) || is_front_page() ) {
		$c[] = 'yh-magazine';
	}
	return $c;
} );

/** Fallback so templates still render if the plugin is off. */
if ( ! function_exists( 'yh_img' ) ) {
	function yh_img( $name ) {
		return '';
	}
}

/** Small helpers for templates. */
function yh_t_kicker( $text ) {
	return '<span class="yh-kicker">' . esc_html( $text ) . '</span>';
}

function yh_t_event_card( $post, $variant = 'row' ) {
	$id    = $post->ID;
	$start = function_exists( 'yh_event_start' ) ? yh_event_start( $id ) : null;
	$lane  = get_post_meta( $id, '_yh_lane', true );
	$conf  = function_exists( 'yh_is_confirmed' ) && yh_is_confirmed( $id );
	$note  = get_post_meta( $id, '_yh_note', true );
	ob_start();
	?>
	<a class="yh-listing" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
		<span class="yh-listing-when">
			<?php if ( $start ) : ?>
				<span class="yh-listing-day"><?php echo esc_html( $start->format( 'D' ) ); ?></span>
				<span class="yh-listing-date"><?php echo esc_html( $start->format( 'M j' ) ); ?></span>
				<span class="yh-listing-time"><?php echo esc_html( $start->format( 'g:i a' ) ); ?> ET</span>
			<?php else : ?>
				<span class="yh-listing-day">TBA</span>
			<?php endif; ?>
		</span>
		<span class="yh-listing-body">
			<span class="yh-listing-lane"><?php echo esc_html( function_exists( 'yh_lane_label' ) ? yh_lane_label( $lane ) : '' ); ?><?php if ( ! $conf ) : ?> <em class="yh-badge-draft">Not confirmed yet</em><?php endif; ?></span>
			<span class="yh-listing-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
			<?php if ( $note ) : ?><span class="yh-listing-note"><?php echo esc_html( $note ); ?></span><?php endif; ?>
		</span>
		<span class="yh-listing-go" aria-hidden="true">&rarr;</span>
	</a>
	<?php
	return ob_get_clean();
}

/* ---------- Sign-in screen ---------- */
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style( 'yh-login-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@400;500;700&display=swap', array(), null );
	$css = file_get_contents( get_stylesheet_directory() . '/assets/login.css' );
	$img = function_exists( 'yh_img' ) ? yh_img( 'movie-screen' ) : '';
	wp_add_inline_style( 'login', str_replace( 'IMG_URL', esc_url( $img ), $css ) );
}, 50 );
add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );
add_filter( 'login_headertext', function () {
	return 'YARDHOOD';
} );
add_filter( 'login_message', function ( $m ) {
	return '<span class="yh-login-tag">Welcome back to the yard</span>' . $m;
} );

/* ---------- Register page intro ---------- */
add_action( 'bp_before_register_page', function () {
	?>
	<div class="yh-reg-intro">
		<img src="<?php echo esc_url( yh_img( 'cover' ) ); ?>" alt="" loading="eager">
		<div class="yh-reg-intro-text">
			<span class="yh-masthead-mini">YARDHOOD</span>
			<span class="yh-kicker" style="margin-top:8px">Join free</span>
			<p>Movie nights, real conversations, and people worth knowing. Pull up a chair.</p>
			<ul>
				<li>RSVP to watch parties and live rooms</li>
				<li>Join The Yard and find your people in The Hood</li>
				<li>Share your story, your plate and your work</li>
			</ul>
			<p class="yh-small" style="color:#D9CBBB">Already a member? <a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>" style="color:#E3C57A">Sign in</a></p>
		</div>
	</div>
	<?php
} );

/* Terms agreement line under the signup button. */
add_action( 'bp_after_registration_submit_buttons', function () {
	echo '<p class="yh-small" style="margin-top:12px">By joining you agree to the <a href="' . esc_url( home_url( '/terms-of-use/' ) ) . '">Terms of Use</a>, <a href="' . esc_url( home_url( '/community-guidelines/' ) ) . '">Community Guidelines</a> and <a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '">Privacy Policy</a>.</p>';
} );

/* ---------- Magazine footer ---------- */
add_action( 'buddyboss_theme_before_footer', function () {
	$links = array(
		'The Yard'             => '/the-yard/',
		'The Hood'             => '/the-hood/',
		'Yard Guide'           => '/yard-guide/',
		'Stories'              => '/stories/',
		'Submit Your Story'    => '/submit-your-story/',
		'Hosting'              => '/hosting/',
	);
	$fine = array(
		'Community Guidelines' => '/community-guidelines/',
		'Story Permissions'    => '/story-permissions/',
		'Terms of Use'         => '/terms-of-use/',
		'Privacy Policy'       => '/privacy-policy/',
	);
	echo '<footer class="yh-footer"><div class="yh-container yh-footer-grid"><div><span class="yh-footer-mast">YARDHOOD</span><span class="yh-footer-tag">The Community Culture Magazine</span></div><nav aria-label="Footer"><ul>';
	foreach ( $links as $t => $u ) {
		echo '<li><a href="' . esc_url( home_url( $u ) ) . '">' . esc_html( $t ) . '</a></li>';
	}
	echo '</ul><ul class="yh-footer-fine">';
	foreach ( $fine as $t => $u ) {
		echo '<li><a href="' . esc_url( home_url( $u ) ) . '">' . esc_html( $t ) . '</a></li>';
	}
	echo '</ul></nav></div></footer>';
} );

/* Friendlier header button labels. */
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( 'buddyboss-theme' === $domain || 'buddyboss' === $domain ) {
		if ( 'Sign up' === $text ) {
			return 'Join free';
		}
		if ( 'Create an Account' === $text ) {
			return 'Join Yardhood';
		}
	}
	return $t;
}, 10, 3 );

/* ---------- Motion: reveals and cover countdown ---------- */
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('yh-js');</script>\n";
}, 2 );
add_action( 'wp_footer', function () {
	?>
	<script>
	(function(){
		var sel='.yh-toc-item,.yh-door,.yh-listing,.yh-tile,.yh-banner,.yh-step,.yh-story-card,.yh-card,.yh-plan,.yh-section-head,.yh-editor-letter,.yh-contents li';
		var els=document.querySelectorAll(sel);
		if(!('IntersectionObserver' in window)){return;}
		var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('is-in');io.unobserve(e.target);}});},{rootMargin:'0px 0px -8% 0px'});
		els.forEach(function(el,i){el.classList.add('yh-reveal');el.style.transitionDelay=(i%4)*70+'ms';io.observe(el);});
		var n=document.querySelector('.yh-next');
		if(n){var t=parseInt(n.getAttribute('data-start'),10)*1000,c=n.querySelector('.yh-next-c');
			var tick=function(){var d=t-Date.now();if(d<=0){c.textContent='Happening now';return;}
				var dd=Math.floor(d/864e5),hh=Math.floor(d%864e5/36e5),mm=Math.floor(d%36e5/6e4),ss=Math.floor(d%6e4/1e3);
				c.textContent=(dd?dd+'d ':'')+hh+'h '+mm+'m '+(dd?'':ss+'s');};
			tick();setInterval(tick,1000);}
	})();
	</script>
	<?php
}, 50 );

/* ---------- Site icon and social share card ---------- */
add_action( 'wp_head', function () {
	if ( ! function_exists( 'yh_img' ) ) {
		return;
	}
	$icon = yh_img( 'house' );
	echo '<link rel="icon" href="' . esc_url( $icon ) . '" type="image/webp">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $icon ) . '">' . "\n";
	$title = is_front_page() ? 'Yardhood | The Community Culture Magazine' : wp_get_document_title();
	$desc  = 'Movie nights, real conversations, and people worth knowing. Join free.';
	$img   = yh_img( 'cover' );
	if ( is_singular() && has_post_thumbnail() ) {
		$img = get_the_post_thumbnail_url( null, 'large' );
	} elseif ( is_singular( 'yh_event' ) ) {
		$img = yh_img( 'movie-screen' );
	}
	if ( is_singular() && has_excerpt() ) {
		$desc = wp_strip_all_tags( get_the_excerpt() );
	}
	printf( '<meta property="og:site_name" content="Yardhood"><meta property="og:title" content="%1$s"><meta property="og:description" content="%2$s"><meta property="og:image" content="%3$s"><meta property="og:url" content="%4$s"><meta name="twitter:card" content="summary_large_image"><meta name="description" content="%2$s">' . "\n", esc_attr( $title ), esc_attr( $desc ), esc_url( $img ), esc_url( home_url( add_query_arg( array() ) ) ) );
}, 3 );
