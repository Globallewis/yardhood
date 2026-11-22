<?php
/**
 * Film leader countdown (3, 2, 1, then the Movie Nights title card).
 * Adapted from the founder's leader code to the Yardhood palette and type.
 */
if ( ! function_exists( 'yh_film_leader' ) ) {
	function yh_film_leader( $subtitle = 'Pull up a chair · The film starts soon' ) {
		static $n = 0;
		$n++;
		$id = 'yhl' . $n;
		ob_start();
		?>
		<div class="yh-leader" id="<?php echo esc_attr( $id ); ?>" role="img" aria-label="Movie Nights countdown: 3, 2, 1">
			<svg viewBox="0 0 1600 900" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<defs>
					<linearGradient id="<?php echo esc_attr( $id ); ?>L" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#4A2840"/><stop offset="100%" stop-color="#2C1527"/></linearGradient>
					<linearGradient id="<?php echo esc_attr( $id ); ?>R" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#5A3350"/><stop offset="100%" stop-color="#3B1E35"/></linearGradient>
					<linearGradient id="<?php echo esc_attr( $id ); ?>S" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#C79A3A" stop-opacity=".34"/><stop offset="100%" stop-color="#C79A3A" stop-opacity="0"/></linearGradient>
					<filter id="<?php echo esc_attr( $id ); ?>G" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="8" result="b"/><feComposite in="SourceGraphic" in2="b" operator="over"/></filter>
				</defs>
				<rect x="0" y="0" width="800" height="900" fill="url(#<?php echo esc_attr( $id ); ?>L)"/>
				<rect x="800" y="0" width="800" height="900" fill="url(#<?php echo esc_attr( $id ); ?>R)"/>
				<g fill="#1E0F1B" opacity=".65">
					<?php for ( $i = 0; $i < 8; $i++ ) : $y = 45 + $i * 110; ?>
						<rect x="28" y="<?php echo (int) $y; ?>" width="34" height="52" rx="8"/><rect x="1538" y="<?php echo (int) $y; ?>" width="34" height="52" rx="8"/>
					<?php endfor; ?>
				</g>
				<rect x="96" y="54" width="1408" height="792" rx="18" fill="none" stroke="#F6EFE4" stroke-opacity=".22" stroke-width="2"/>
				<g class="yh-leader-target">
					<g class="yh-leader-sweep" transform="rotate(0 800 450)">
						<path d="M 800 450 L 800 85 A 365 365 0 0 1 1116 267 Z" fill="url(#<?php echo esc_attr( $id ); ?>S)"/>
						<line x1="800" y1="450" x2="800" y2="85" stroke="#C79A3A" stroke-width="4" stroke-opacity=".85"/>
					</g>
					<circle cx="800" cy="450" r="365" fill="none" stroke="#F6EFE4" stroke-width="4" stroke-opacity=".85"/>
					<circle cx="800" cy="450" r="325" fill="none" stroke="#F6EFE4" stroke-width="2.5" stroke-opacity=".6"/>
					<circle cx="800" cy="450" r="245" fill="none" stroke="#C79A3A" stroke-width="1.5" stroke-dasharray="10 10" stroke-opacity=".55"/>
					<line x1="96" y1="450" x2="1504" y2="450" stroke="#1E0F1B" stroke-width="3" stroke-opacity=".6"/>
					<line x1="800" y1="54" x2="800" y2="846" stroke="#1E0F1B" stroke-width="3" stroke-opacity=".6"/>
					<g stroke="#C79A3A" stroke-width="2" stroke-opacity=".7" fill="none">
						<path d="M 140 125 L 140 95 L 170 95"/><path d="M 1460 125 L 1460 95 L 1430 95"/><path d="M 140 775 L 140 805 L 170 805"/><path d="M 1460 775 L 1460 805 L 1430 805"/>
					</g>
					<text class="yh-leader-num" x="800" y="468" text-anchor="middle" dominant-baseline="central" fill="#C79A3A" filter="url(#<?php echo esc_attr( $id ); ?>G)" font-family="'Playfair Display', Georgia, serif" font-weight="700" font-size="370">3</text>
				</g>
				<g class="yh-leader-card" opacity="0">
					<rect x="96" y="54" width="1408" height="792" rx="18" fill="#1E0F1B" fill-opacity=".88"/>
					<path d="M 780 268 L 800 248 L 820 268 L 800 288 Z" fill="#C79A3A"/>
					<text x="800" y="365" text-anchor="middle" fill="#C79A3A" font-family="'DM Sans', sans-serif" font-size="22" font-weight="700" letter-spacing="10">YARDHOOD PRESENTS</text>
					<text x="800" y="470" text-anchor="middle" fill="#F6EFE4" font-family="'Playfair Display', Georgia, serif" font-size="104" font-weight="600" font-style="italic">Movie Nights</text>
					<line x1="620" y1="518" x2="980" y2="518" stroke="#C79A3A" stroke-width="1.5" stroke-opacity=".75"/>
					<text x="800" y="575" text-anchor="middle" fill="#F6EFE4" fill-opacity=".82" font-family="'Playfair Display', Georgia, serif" font-style="italic" font-size="34"><?php echo esc_html( $subtitle ); ?></text>
				</g>
			</svg>
		</div>
		<script>
		(function(){
			var root=document.getElementById('<?php echo esc_js( $id ); ?>'); if(!root) return;
			var sweep=root.querySelector('.yh-leader-sweep'),num=root.querySelector('.yh-leader-num'),target=root.querySelector('.yh-leader-target'),card=root.querySelector('.yh-leader-card');
			if(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches){target.setAttribute('opacity','.15');card.setAttribute('opacity','1');return;}
			var start=null,visible=true;
			if('IntersectionObserver' in window){new IntersectionObserver(function(e){visible=e[0].isIntersecting;}).observe(root);}
			function tick(ts){ if(!start) start=ts; if(visible){ var el=(ts-start)%5600;
				if(el<3000){var step=Math.floor(el/1000),prog=(el%1000)/1000; target.setAttribute('opacity','1'); card.setAttribute('opacity','0'); num.textContent=String(3-step); sweep.setAttribute('transform','rotate('+(prog*360).toFixed(1)+' 800 450)');}
				else{target.setAttribute('opacity','.15'); card.setAttribute('opacity','1');} }
				requestAnimationFrame(tick);}
			requestAnimationFrame(tick);
		})();
		</script>
		<?php
		return ob_get_clean();
	}
}
