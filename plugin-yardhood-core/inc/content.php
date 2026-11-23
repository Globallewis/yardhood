<?php
/**
 * Page copy for policy and info pages. Edited in one place, pushed by Tools > Yardhood setup.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_operator() {
	return 'Tripath Property Group, LLC';
}

function yh_contact_email() {
	return get_option( 'yh_contact_email', 'hello@yardhood.com' );
}

function yh_updated_line() {
	return '<p class="yh-small"><em>Last updated November 22, 2026.</em></p>';
}

function yh_copy_story_permissions() {
	$mail = esc_html( yh_contact_email() );
	return yh_updated_line() . <<<HTML
<p>Yardhood features real people: artists, makers, business owners, workers and neighbors. This page explains what happens when you send us your story, photos, artwork or video ("your submission"), what you keep, and what you allow Yardhood to do. Yardhood is operated by Tripath Property Group, LLC.</p>

<h3>You keep your rights</h3>
<p>You keep the copyright and ownership of your story, your work and your likeness. Sending a submission does not transfer ownership to Yardhood. You are free to share, sell or publish your own work anywhere else.</p>

<h3>What you allow Yardhood to do</h3>
<p>When you check the required boxes on the form, you give Yardhood a non-exclusive, worldwide, royalty-free license to:</p>
<ul>
<li>Edit your submission into a feature. That includes shortening, arranging, correcting spelling and grammar, writing headlines and captions, choosing quotes you actually said, and cropping or resizing images, as long as the edits do not change the meaning of what you shared.</li>
<li>Publish the feature on yardhood.com, in Yardhood member spaces, in newsletters and in the Yardhood app.</li>
<li>Keep the feature in Yardhood's back issues and archive, including issue covers and contents pages that show it.</li>
<li>Show your name, the name of your work or business, your city and the links you gave us alongside the feature.</li>
</ul>
<p>Only if you check the matching optional box, you also allow Yardhood to:</p>
<ul>
<li>Publish the video you sent with your feature.</li>
<li>Share short excerpts, quotes, photos or clips on Yardhood's social media to promote your feature.</li>
<li>Show your Top Three on your feature.</li>
</ul>

<h3>What Yardhood will not do without asking you first</h3>
<ul>
<li>Sell your work or your image, or license it to anyone else.</li>
<li>Use your submission in paid advertising for a sponsor.</li>
<li>Put your work in a printed book, anthology or product. That would need a separate written agreement with you.</li>
<li>Credit AI-generated art as yours, or credit your work to anyone else.</li>
</ul>

<h3>Your promises to us</h3>
<ul>
<li>You are 18 or older.</li>
<li>The submission is yours, or you have permission to share every part of it, including music, photos and artwork in it.</li>
<li>Anyone clearly shown or named in your photos or video has agreed to be included.</li>
<li>Your submission is true to the best of your knowledge and does not break any law or anyone else's rights.</li>
</ul>
<p>If a claim is made because something in your submission was not yours to share, you agree to work with us to resolve it, and Yardhood may remove the material.</p>

<h3>Selection and editing</h3>
<p>Sending a submission does not guarantee it will be published. Yardhood decides what to feature, when, and in what form. Before a feature goes live, an editor will reach out to you by email. We do not pay for submissions unless we agree to that with you in writing.</p>

<h3>Taking something down</h3>
<p>If you want your feature changed or removed, email <a href="mailto:{$mail}">{$mail}</a>. We review every request and aim to respond within 10 business days. We can remove a feature from the live site, but we cannot pull back social posts others have already shared, and a copy may stay in back issues and private records where removal is not practical.</p>

<h3>Interviews and recordings</h3>
<p>If you join a Zoom interview, we will tell you before recording starts. Recorded interviews are covered by these same permissions. You can ask to stop recording at any time.</p>

<h3>Changes to these permissions</h3>
<p>Yardhood may update this page as the magazine grows, for example to add new places where features appear. Changes apply to submissions sent after the update. If a change would affect a feature that is already published in a way you did not agree to, we will ask you first. The date at the top shows the latest version.</p>

<h3>Questions</h3>
<p>Email <a href="mailto:{$mail}">{$mail}</a>.</p>
HTML;
}

function yh_copy_terms() {
	$mail = esc_html( yh_contact_email() );
	return yh_updated_line() . <<<HTML
<p>Welcome to Yardhood, the community culture magazine at yardhood.com. These terms are the rules for using the site, the member community and paid hosting plans. Yardhood is operated by Tripath Property Group, LLC ("Yardhood", "we", "us"). By creating an account or using the site, you agree to these terms and to the <a href="/community-guidelines/">Community Guidelines</a>.</p>

<h3>Who can join</h3>
<p>Yardhood is for adults 18 and older. You agree to give accurate information, keep your login private, and use one personal account. You are responsible for what happens under your account.</p>

<h3>Your content</h3>
<p>You own what you post: posts, comments, photos, videos and group content. You give Yardhood a non-exclusive, worldwide, royalty-free license to host, store, display and share it inside Yardhood so the community can work (for example, showing your post in a group or in the activity feed). This license ends when you delete the content or your account, except for copies other members already shared, content kept for legal or safety reasons, and backups that are cleared on a normal schedule. Features and stories you send through Submit Your Story are covered by the <a href="/story-permissions/">Story Permissions</a>.</p>
<p>Only post what you have the right to post. Do not share copies of movies, shows or music. Watch parties work because everybody brings their own copy.</p>

<h3>Our content and brand</h3>
<p>The Yardhood name, masthead, design, photography and editorial content belong to Yardhood or its licensors. You may share links to Yardhood pages. Please do not copy or reuse our design or content without permission.</p>

<h3>Community rules and moderation</h3>
<p>Everyone follows the Community Guidelines. Yardhood may remove content, limit features, pause or close accounts or groups that break the rules or put members at risk, with or without notice. You can report anything that feels wrong using the report buttons or by emailing <a href="mailto:{$mail}">{$mail}</a>.</p>

<h3>Live rooms and recordings</h3>
<p>Some events use Zoom or other outside tools, which have their own terms. Reaction rooms and interviews are only recorded when that is announced in advance, and you can always join without being recorded. Do not record or screenshot other members without their permission.</p>

<h3>Hosting plans and billing</h3>
<p>Joining Yardhood and taking part is free. Members who host a group can choose a paid hosting plan. Plans are billed through Stripe on behalf of Tripath Property Group, LLC.</p>
<ul>
<li><strong>Free first month.</strong> When a plan offers a free first month, your card is saved at sign-up and the first charge happens one month later unless you cancel before then.</li>
<li><strong>Renewals.</strong> Plans renew automatically each month at the listed price until you cancel.</li>
<li><strong>Cancelling.</strong> You can cancel anytime from your account page. Cancelling stops future charges. Your group stays active until the end of the period you already paid for.</li>
<li><strong>Refunds.</strong> Payments are not refunded for partial months, except where the law requires it or we make an error.</li>
<li><strong>Price changes.</strong> We will give at least 30 days notice by email before a price change affects your plan.</li>
<li><strong>Your group.</strong> Hosts are responsible for their group's content, members and any money they collect from their own members, including taxes. Yardhood may pause a group that breaks these terms.</li>
</ul>

<h3>Links and other services</h3>
<p>Yardhood links to other sites, including partner brands like <a href="https://legacyplaza.co" target="_blank" rel="noopener">legacyplaza.co</a> and streaming services. We are not responsible for other sites or what they offer.</p>

<h3>No guarantees</h3>
<p>We work to keep Yardhood running well, but the site is provided as it is, without promises that it will always be available or free of errors. Events and schedules can change. To the fullest extent the law allows, Yardhood and Tripath Property Group, LLC are not liable for indirect or consequential losses, and our total liability to you is limited to the amount you paid us in the 12 months before the claim.</p>

<h3>Ending your account</h3>
<p>You can delete your account anytime from your account settings. We may close accounts that break these terms. Sections about content licenses you already granted, payments owed and limits of liability continue after an account closes.</p>

<h3>Changes to these terms</h3>
<p>We may update these terms as Yardhood grows. If a change is significant, we will tell members by email or with a notice on the site before it takes effect. Continuing to use Yardhood after that means you accept the updated terms.</p>

<h3>Law and contact</h3>
<p>These terms are governed by the laws of the State of Georgia, USA. Questions: <a href="mailto:{$mail}">{$mail}</a>.</p>
HTML;
}

function yh_copy_privacy() {
	$mail = esc_html( yh_contact_email() );
	return yh_updated_line() . <<<HTML
<p>This policy explains what Yardhood collects, why, and the choices you have. Yardhood is operated by Tripath Property Group, LLC.</p>

<h3>What we collect</h3>
<ul>
<li><strong>Account details:</strong> your name, email, username, password (stored encrypted) and anything you add to your profile.</li>
<li><strong>What you share:</strong> posts, comments, photos, group activity, RSVPs, and stories you submit.</li>
<li><strong>Event activity:</strong> which events you RSVP to, whether you join the room, and whether you agreed to be on camera. We use this to run events and count attendance.</li>
<li><strong>Payments:</strong> if you buy a hosting plan, Stripe processes your card. We see your plan, billing status and the last four digits of your card, never the full number.</li>
<li><strong>Technical data:</strong> basic information your browser sends, like IP address, device type and pages visited, plus cookies that keep you signed in.</li>
</ul>

<h3>How we use it</h3>
<ul>
<li>To run your account, groups, events and the magazine.</li>
<li>To send account emails, event reminders and replies to your messages.</li>
<li>To keep the community safe, review reports and prevent abuse.</li>
<li>To understand which features people use, using totals rather than individual profiles.</li>
</ul>
<p>We do not sell your personal information. If Yardhood works with sponsors, we share only overall numbers, like total attendance, never your profile or contact details.</p>

<h3>Who we share with</h3>
<p>We use trusted services to run the site: Hostinger (hosting), Stripe (payments), Zoom (live rooms), email delivery providers and Google Fonts (the site's typefaces). They only get what they need to provide their service. We may share information if the law requires it or to protect someone's safety.</p>

<h3>What other members see</h3>
<p>Your profile name, photo and public posts are visible to other members. Posts in private groups are visible to that group's members. Published features are public on yardhood.com.</p>

<h3>Your choices</h3>
<ul>
<li>Edit or delete your profile and posts anytime.</li>
<li>Change email notification settings in your account.</li>
<li>Delete your account from your settings, or email us to request a copy or deletion of your data.</li>
</ul>

<h3>Keeping data</h3>
<p>We keep your information while your account is open. After you delete your account, we remove your personal information within a reasonable time, except records we must keep for payments, legal or safety reasons.</p>

<h3>Adults only</h3>
<p>Yardhood is for people 18 and older. We do not knowingly collect information from anyone younger. If you believe someone under 18 has joined, tell us and we will remove the account.</p>

<h3>Changes and contact</h3>
<p>We may update this policy and will note the date at the top. Questions or requests: <a href="mailto:{$mail}">{$mail}</a>.</p>
HTML;
}

function yh_copy_hosting() {
	$mail = esc_html( yh_contact_email() );
	return <<<HTML
<p class="yh-lede-dark">Yardhood has room for your people. Here is how hosting works, what each plan is for, and how to make a group people want to come back to.</p>

<h3>How groups work here</h3>
<p>Every member can join the official spaces in The Yard for free: Movie Nights, The Cookout, Sunday Dinner, Artist Corner, People Worth Knowing and Real Skills For Life. Hosts start their own groups in The Hood. Your group gets its own page, discussion feed, photos, member list, and events in the Yard Guide when you run something live.</p>

<h3>The plans</h3>
<p><strong>Community Host · \$10 a month, first month free.</strong> Start and run a free group for the people you organize: a book club, a city chapter, a crew of creatives, a support circle, a fan room. Members join free. You can cancel anytime.</p>
<p><strong>Paid Group Host · \$99 a month.</strong> For hosts who charge their own members, like a coach, creator or teacher running a paid community. Includes a paid membership option for your group and help setting it up. Available by request.</p>
<p><strong>Large Rooms · \$499 a month.</strong> For hosts who regularly bring 50 or more people into live rooms and events, like conferences, big watch parties or organizations. Includes larger live room setup and planning support. Available by request.</p>
<p>To ask about the \$99 or \$499 plans, email <a href="mailto:{$mail}">{$mail}</a> or use the request form on <a href="/the-hood/#host">The Hood</a>.</p>

<h3>Ideas that work well</h3>
<ul>
<li>A family group to plan the reunion, share old photos, and keep everybody in touch between holidays.</li>
<li>Virtual game nights: spades, trivia, Pictionary or a standing Friday game room.</li>
<li>A watch group for one show, with a weekly thread and a monthly live night.</li>
<li>A city or neighborhood group that plans meetups and shares local finds.</li>
<li>A creative circle that shares work in progress and gives feedback.</li>
<li>A career room where people in one field trade tips and How I Got Here stories.</li>
<li>A cooking or Sunday Dinner group that swaps recipes and plates.</li>
<li>A wellness or accountability circle with weekly check-ins.</li>
</ul>

<h3>What a good host does</h3>
<ul>
<li>Writes a clear one-line description of who the group is for.</li>
<li>Posts a welcome and a weekly prompt so new people have something to answer.</li>
<li>Keeps the room kind and follows the <a href="/community-guidelines/">Community Guidelines</a>.</li>
<li>Uses the report tools and asks Yardhood for help when something goes wrong.</li>
</ul>

<h3>What groups cannot be used for</h3>
<ul>
<li>Sharing or streaming copies of movies, shows or music.</li>
<li>Spam, pyramid schemes, or pressure selling to members.</li>
<li>Adult content, harassment, hate, or targeting anyone.</li>
<li>Collecting members' personal information for outside use.</li>
<li>Anything aimed at people under 18. Yardhood is an adult community.</li>
</ul>

<h3>Billing</h3>
<p>Plans are billed monthly through Stripe and renew until you cancel. See the <a href="/terms-of-use/">Terms of Use</a> for cancellation and refunds.</p>
HTML;
}
