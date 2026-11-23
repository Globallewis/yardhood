<?php
/**
 * Launch drafts (Nov 22, 2026): editor's letter, one columnist essay, one story.
 * Created once as DRAFTS for Lynn's review. Nothing here publishes on its own.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', function () {
	if ( get_option( 'yh_launch_drafts_v1' ) || ! post_type_exists( 'yh_perspective' ) ) {
		return;
	}
	update_option( 'yh_launch_drafts_v1', 1 );
	$house = (int) get_option( 'yh_house_user', 0 );
	$house = $house ? $house : get_current_user_id();

	$letter = wp_insert_post( array(
		'post_type'    => 'yh_perspective',
		'post_status'  => 'draft',
		'post_author'  => $house,
		'post_title'   => 'Pull Up a Chair',
		'post_excerpt' => 'Why Yardhood exists, and what this first issue is for',
		'post_content' => '<p>Most of the places we gather online were built to keep us scrolling. You open an app to check one thing, and an hour later you cannot remember what it was. Yardhood started with a different question: what if a website felt more like a backyard on a good night?</p>
<p>You know the night. Somebody brought a speaker. The string lights are up. There is a movie playing on a sheet hung across the fence, and half the yard is arguing about whether Craig should have stayed on the porch. Nobody had to be invited twice.</p>
<p>That is the feeling we are building toward. Yardhood is a magazine you can walk into. The Yard is where we gather for movie nights, the Cookout and Sunday Dinner. The Hood is where you find your people and start your own circle. And these pages are where we tell the stories of the folks who make it worth showing up.</p>
<p>This Winter Issue is our first. The movie lineup runs through the holidays, starting December 11 with <em>Friday</em>, because of course it does. Stories and Perspectives are open now, and we want yours. Tell us how you got here, or make your case on something you care about.</p>
<p>Two promises. We read everything you send before it runs, and we will never sell your profile to anyone. We would like this to be a place where you can disagree with somebody and still pass them the potato salad.</p>
<p>Thank you for being early. The best seats are still open.</p>',
	) );
	if ( $letter && ! is_wp_error( $letter ) ) {
		wp_set_object_terms( $letter, 'community', 'yh_pillar' );
		update_post_meta( $letter, '_yh_subtitle', 'Why Yardhood exists, and what this first issue is for' );
		update_post_meta( $letter, '_yh_byline', 'The Editors' );
		update_post_meta( $letter, '_yh_label', 'Editor\'s Letter' );
		update_post_meta( $letter, '_yh_columnist', '0' );
	}

	$zola = wp_insert_post( array(
		'post_type'    => 'yh_perspective',
		'post_status'  => 'draft',
		'post_author'  => $house,
		'post_title'   => 'Generosity Is Not a Line of Credit',
		'post_excerpt' => 'How to show up for the holidays without paying for it in January',
		'post_content' => '<p>Every year around this time, somebody I love puts Christmas on a card they know they cannot pay off. They do it out of love. I have done it too. Then January comes, and the love shows up as a bill with interest on it.</p>
<p>So I want to say something plainly, because nobody says it at the table: you do not owe anyone a holiday that puts you in debt.</p>
<p>That is not stinginess. It is protection. The people who sell us the season are counting on one thing. They know we will feel more guilt about showing up empty-handed than about the statement that arrives in the mail later. Stores plan for that. Card companies plan for that. Usually the only person not planning is you.</p>
<p>Here is what I do now.</p>
<p><strong>I pick the number before the season picks it for me.</strong> Before the Black Friday ads hit my phone, I sit down with what I actually have after the bills are paid, and I choose a number I can walk away from without flinching. That number is the holiday. When it is spent, I am done.</p>
<p><strong>I give time where I cannot give money.</strong> Some of the gifts people still talk about at my grandmother\'s house cost nothing. A framed picture. A recipe written out by hand. A Saturday spent fixing somebody\'s gutter. Showing up is a gift, and so is effort.</p>
<p><strong>I say it out loud.</strong> This is the hard one. "I am keeping gifts small this year" is a complete sentence. You would be surprised how many people at your table are relieved to hear somebody say it first. Somebody has to go first. Let it be you.</p>
<p><strong>I read the fine print.</strong> Buy now, pay later looks like a favor until you miss one payment. Store cards that hand you a discount at the register often carry interest high enough to eat that discount if you keep a balance. Nobody gives away free money in December.</p>
<p>None of this means you stop being generous. It means your generosity cannot be used against you. Real generosity does not come with a payment plan you never agreed to.</p>
<p>So this year, set your number and guard it. Let the people who love you love you back without a receipt. I will see you at the table.</p>',
	) );
	if ( $zola && ! is_wp_error( $zola ) ) {
		wp_set_object_terms( $zola, 'money', 'yh_pillar' );
		update_post_meta( $zola, '_yh_subtitle', 'How to show up for the holidays without paying for it in January' );
		update_post_meta( $zola, '_yh_byline', 'Zola' );
		update_post_meta( $zola, '_yh_columnist', '1' );
		update_post_meta( $zola, '_yh_bio', 'Zola writes about money, safety and the systems we are taught not to question. She is a Yardhood columnist.' );
	}

	$story = wp_insert_post( array(
		'post_type'    => 'yh_story',
		'post_status'  => 'draft',
		'post_author'  => $house,
		'post_title'   => 'Calling the Cues',
		'post_excerpt' => 'She spent years making rooms work from behind the scenes, in theater and in counseling. Yardhood is the room she built.',
		'post_content' => '<h3>When did you realize what you were meant to do with your life?</h3>
<p>Honestly, I am still realizing it. But if I had to point to a moment, it would be sitting in the booth at the back of a dark theater with a headset on, calling cues. The audience never sees the stage manager. They just feel it when everything lands on time. I loved being the person who made the room work.</p>
<h3>The journey</h3>
<p>I was the first in my family to go to college, which meant nobody could hand me a map. I found theater sideways. Carol Mitchell-Leon introduced me to it and to stage management, and she connected me to my first professional job, at Theater in the Square in Marietta. Later I stage managed <em>Blue Door</em>. Theater taught me how people act when there is pressure, a deadline and a lot of feeling in one room. It turns out that describes most of life.</p>
<p>That curiosity about people took me into counseling. I earned my master\'s degree and became a Licensed Professional Counselor in Georgia. I also worked as a life skills worker with young adults in independent living programs, helping with the everyday things nobody had shown them, like budgets, first apartments and how to speak up for yourself in an office that was not built for you.</p>
<p>Both jobs kept teaching me the same lesson. People do better when they have somewhere to belong and someone who notices them. Most of us are carrying more than we say.</p>
<p>Yardhood comes out of all of it. The stage manager in me wanted a place that runs smoothly, so people can just show up. The counselor in me wanted a place where people are seen. And the first-generation kid who figured it out alone wanted a front porch where you can ask somebody, "How did you do it?"</p>
<h3>What do you want to leave behind?</h3>
<p>Rooms that work. Places where people can gather, learn something useful and leave a little more themselves than when they came. If Yardhood does that for even one block\'s worth of people, it was worth building.</p>',
	) );
	if ( $story && ! is_wp_error( $story ) ) {
		wp_set_object_terms( $story, 'career', 'yh_story_type' );
		wp_set_object_terms( $story, 'purpose', 'yh_pillar' );
		update_post_meta( $story, '_yh_name', 'Lynn Lewis' );
		update_post_meta( $story, '_yh_discipline', 'Founder, Yardhood' );
		update_post_meta( $story, '_yh_city', 'Atlanta' );
		update_post_meta( $story, '_yh_dek', 'She spent years making rooms work from behind the scenes, in theater and in counseling. Yardhood is the room she built.' );
		update_post_meta( $story, '_yh_pull', 'The audience never sees the stage manager. They just feel it when everything lands on time.' );
	}
	update_option( 'yh_launch_drafts_ids', array( 'letter' => $letter, 'zola' => $zola, 'story' => $story ) );
} );
