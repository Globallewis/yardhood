<?php
/**
 * Article page pieces: photo hero, side rail (writer, reactions, share), responses, more to read.
 */

function yh_t_article_hero( $id, $kicker, $sub, $byline ) {
	$img = function_exists( 'yh_t_feature_img' ) ? yh_t_feature_img( $id ) : '';
	ob_start();
	?>
	<header class="yh-ahero">
		<?php if ( $img ) : ?><img class="yh-ahero-img" src="<?php echo esc_url( $img ); ?>" alt="" fetchpriority="high"><?php endif; ?>
		<span class="yh-ahero-scrim" aria-hidden="true"></span>
		<div class="yh-container yh-ahero-text">
			<?php echo yh_t_kicker( $kicker ); // phpcs:ignore ?>
			<h1 class="yh-ahero-title"><?php echo esc_html( get_the_title( $id ) ); ?></h1>
			<?php if ( $sub ) : ?><p class="yh-ahero-sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			<p class="yh-ahero-by"><?php echo esc_html( $byline ); ?></p>
		</div>
	</header>
	<?php
	return ob_get_clean();
}

function yh_t_reactions( $id ) {
	if ( ! function_exists( 'yh_reactions' ) ) {
		return '';
	}
	$counts = yh_reaction_counts( $id );
	$mine   = yh_user_reactions( $id );
	$in     = is_user_logged_in();
	ob_start();
	?>
	<div class="yh-react" data-id="<?php echo esc_attr( $id ); ?>" data-n="<?php echo esc_attr( wp_create_nonce( 'yh_react' ) ); ?>" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
		<?php foreach ( yh_reactions() as $k => $label ) : ?>
			<?php if ( $in ) : ?>
				<button type="button" class="yh-react-b<?php echo in_array( $k, $mine, true ) ? ' is-on' : ''; ?>" data-k="<?php echo esc_attr( $k ); ?>" aria-pressed="<?php echo in_array( $k, $mine, true ) ? 'true' : 'false'; ?>"><span class="yh-react-l"><?php echo esc_html( $label ); ?></span><span class="yh-react-n"><?php echo $counts[ $k ] ? (int) $counts[ $k ] : ''; ?></span></button>
			<?php else : ?>
				<a class="yh-react-b" href="<?php echo esc_url( function_exists( 'yh_join_url' ) ? yh_join_url( get_permalink( $id ) ) : wp_registration_url() ); ?>"><span class="yh-react-l"><?php echo esc_html( $label ); ?></span><span class="yh-react-n"><?php echo $counts[ $k ] ? (int) $counts[ $k ] : ''; ?></span></a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

function yh_t_article_rail( $id, $who, $label, $bio, $topic, $respond_url, $respond_label ) {
	$n = (int) get_comments_number( $id );
	ob_start();
	?>
	<aside class="yh-rail">
		<div class="yh-rail-box">
			<span class="yh-rail-k"><?php echo esc_html( $label ); ?></span>
			<span class="yh-rail-name"><?php echo esc_html( $who ); ?></span>
			<?php if ( $bio ) : ?><p class="yh-rail-bio"><?php echo esc_html( $bio ); ?></p><?php endif; ?>
		</div>
		<div class="yh-rail-box">
			<span class="yh-rail-k">Your reaction</span>
			<?php echo yh_t_reactions( $id ); // phpcs:ignore ?>
		</div>
		<div class="yh-rail-box">
			<a class="yh-rail-link" href="#responses"><?php echo $n ? esc_html( $n . ( 1 === $n ? ' response' : ' responses' ) ) : 'Be the first to respond'; ?> <span aria-hidden="true">&darr;</span></a>
			<button type="button" class="yh-rail-link yh-share" data-url="<?php echo esc_url( get_permalink( $id ) ); ?>" data-title="<?php echo esc_attr( get_the_title( $id ) ); ?>">Share this piece</button>
			<?php if ( $topic ) : ?><a class="yh-rail-link" href="<?php echo esc_url( get_term_link( $topic ) ); ?>">More on <?php echo esc_html( $topic->name ); ?></a><?php endif; ?>
			<a class="yh-btn yh-btn-plum yh-rail-cta" href="<?php echo esc_url( $respond_url ); ?>"><?php echo esc_html( $respond_label ); ?></a>
		</div>
	</aside>
	<?php
	return ob_get_clean();
}

function yh_t_responses( $id ) {
	$comments = get_comments( array( 'post_id' => $id, 'status' => 'approve', 'order' => 'ASC' ) );
	ob_start();
	?>
	<section id="responses" class="yh-responses">
		<h2 class="yh-resp-head">Responses <?php if ( $comments ) : ?><span><?php echo count( $comments ); ?></span><?php endif; ?></h2>
		<?php if ( $comments ) : ?>
			<ol class="yh-resp-list">
				<?php foreach ( $comments as $c ) : ?>
					<li class="yh-resp" id="comment-<?php echo (int) $c->comment_ID; ?>">
						<span class="yh-resp-av"><?php echo get_avatar( $c, 44 ); ?></span>
						<div>
							<p class="yh-resp-who"><?php echo esc_html( $c->comment_author ); ?> <span><?php echo esc_html( human_time_diff( strtotime( $c->comment_date_gmt . ' UTC' ) ) ); ?> ago</span></p>
							<div class="yh-resp-text"><?php echo wp_kses_post( wpautop( $c->comment_content ) ); ?></div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
		<?php if ( is_user_logged_in() ) : ?>
			<?php
			comment_form( array(
				'title_reply'          => $comments ? 'Add your response' : 'Start the conversation',
				'title_reply_before'   => '<h3 class="yh-resp-formhead">',
				'title_reply_after'    => '</h3>',
				'label_submit'         => 'Post response',
				'class_submit'         => 'yh-btn yh-btn-gold',
				'comment_field'        => '<p class="comment-form-comment"><label for="comment" class="screen-reader-text">Your response</label><textarea id="comment" name="comment" rows="4" maxlength="3000" required placeholder="What did this bring up for you?"></textarea></p>',
				'logged_in_as'         => '',
				'comment_notes_before' => '',
			), $id );
			?>
		<?php else : ?>
			<p class="yh-resp-join"><a class="yh-btn yh-btn-gold" href="<?php echo esc_url( function_exists( 'yh_join_url' ) ? yh_join_url( get_permalink( $id ) . '#responses' ) : wp_registration_url() ); ?>">Join free to respond</a> <a href="<?php echo esc_url( wp_login_url( get_permalink( $id ) . '#responses' ) ); ?>">Sign in</a></p>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}

function yh_t_more_reads( $id, $types ) {
	$q = get_posts( array( 'post_type' => $types, 'posts_per_page' => 3, 'post__not_in' => array( $id ), 'post_status' => 'publish' ) );
	if ( ! $q || ! function_exists( 'yh_t_feature_card' ) ) {
		return '';
	}
	$out = '<section class="yh-section yh-cream yh-more-reads"><div class="yh-container"><h2 class="yh-more-head">Keep reading</h2><div class="yh-fcards">';
	foreach ( $q as $p ) {
		$out .= yh_t_feature_card( $p );
	}
	return $out . '</div></div></section>';
}

/* Reactions and share buttons. */
add_action( 'wp_footer', function () {
	if ( ! is_singular( array( 'yh_perspective', 'yh_story' ) ) ) {
		return;
	}
	?>
	<script>
	(function(){
		document.querySelectorAll('.yh-react button').forEach(function(b){b.addEventListener('click',function(){
			var w=b.closest('.yh-react'),f=new FormData();f.append('action','yh_react');f.append('n',w.dataset.n);f.append('id',w.dataset.id);f.append('k',b.dataset.k);
			b.disabled=true;fetch(w.dataset.ajax,{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){
				b.disabled=false;if(!j.success)return;b.classList.toggle('is-on',j.data.on);b.setAttribute('aria-pressed',j.data.on?'true':'false');b.querySelector('.yh-react-n').textContent=j.data.n||'';
			}).catch(function(){b.disabled=false;});
		});});
		document.querySelectorAll('.yh-share').forEach(function(b){b.addEventListener('click',function(){
			var u=b.dataset.url,t=b.dataset.title;
			if(navigator.share){navigator.share({title:t,url:u}).catch(function(){});return;}
			navigator.clipboard&&navigator.clipboard.writeText(u).then(function(){b.textContent='Link copied';setTimeout(function(){b.textContent='Share this piece';},2000);});
		});});
	})();
	</script>
	<?php
}, 60 );
