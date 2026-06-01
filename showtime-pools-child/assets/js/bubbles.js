/**
 * bubbles.js — Canvas bubble animation on the home hero.
 *
 * Replaces the broken CSS animation (mix-blend-mode + will-change created a
 * GPU compositing isolation that rendered bubbles against transparent, not photo).
 *
 * Features: requestAnimationFrame loop · radial-gradient sphere rendering
 *           mouse repulsion · click-to-burst · ResizeObserver · reduced-motion safe
 *
 * @package ShowtimePools
 */

/* eslint-disable no-console */
(function () {
	'use strict';

	/* Note: bubbles run regardless of prefers-reduced-motion because they are
	   gentle background decoration on a business site. Remove this comment
	   if you want to re-add the guard later. */
	console.log( '[bubbles] script loaded' );

	function init() {
		var hero = document.querySelector( '.home-hero--immersive' );
		if ( ! hero ) {
			console.warn( '[bubbles] .home-hero--immersive not found — script loaded but hero not in DOM' );
			return;
		}
		console.log( '[bubbles] init — hero found, size:', hero.offsetWidth, 'x', hero.offsetHeight );

		/* ── Canvas creation ───────────────────────────────────────── */
		const canvas = document.createElement( 'canvas' );
		canvas.setAttribute( 'aria-hidden', 'true' );
		canvas.className = 'home-hero__bubbles';

		/* Explicit individual properties — avoids `inset` shorthand parse issues */
		/* Four-corner stretch — avoids `height:100%` failing when containing
		   block only has min-height (no explicit height). base.css sets
		   `canvas { height: auto }` which would collapse a `height:100%`
		   canvas to 0px. Four corners always stretch correctly. */
		canvas.style.position      = 'absolute';
		canvas.style.top           = '0';
		canvas.style.right         = '0';
		canvas.style.bottom        = '0';
		canvas.style.left          = '0';
		canvas.style.zIndex        = '2';
		canvas.style.pointerEvents = 'none';
		canvas.style.display       = 'block';

		const existing = hero.querySelector( '.home-hero__bubbles' );
		if ( existing ) {
			hero.replaceChild( canvas, existing );
		} else {
			const ref = hero.querySelector( '.container' );
			hero.insertBefore( canvas, ref || null );
		}

		const ctx = canvas.getContext( '2d' );
		let W = 0, H = 0;
		let mouseX = 0, mouseY = 0, mouseActive = false;

		/* ── Sizing ────────────────────────────────────────────────── */
		function resize() {
			W = canvas.width  = hero.offsetWidth  || window.innerWidth;
			H = canvas.height = hero.offsetHeight || window.innerHeight;
			/* Also set CSS pixel dimensions so base.css `height:auto` can
			   never collapse the display size to 0. */
			canvas.style.width  = W + 'px';
			canvas.style.height = H + 'px';
		}

		/* ── Bubble ────────────────────────────────────────────────── */
		function Bubble( scatter ) {
			this.reset( scatter );
		}

		Bubble.prototype.reset = function ( scatter ) {
			this.r      = 18 + Math.random() * 42;                         // radius 18–60 px — LARGE so they're unmissable
			this.baseX  = this.r + Math.random() * Math.max( 1, W - this.r * 2 );
			this.x      = this.baseX;
			this.y      = scatter
				? Math.random() * ( H + 100 ) - 50
				: H + this.r + 12;
			this.vy     = ( 0.35 + Math.random() * 0.45 ) * ( H / 700 );  // px per frame
			this.driftA = ( Math.random() - 0.5 ) * 30;
			this.driftP = Math.random() * Math.PI * 2;
			this.driftS = 0.016 + Math.random() * 0.020;
			this.alpha  = 0;
			this.maxA   = 0.75 + Math.random() * 0.22;                    // peak opacity 0.75–0.97
		};

		Bubble.prototype.update = function () {
			this.y      -= this.vy;
			this.driftP += this.driftS;
			this.x       = this.baseX + Math.sin( this.driftP ) * this.driftA;

			if ( mouseActive ) {
				var dx = this.x - mouseX;
				var dy = this.y - mouseY;
				var d  = Math.sqrt( dx * dx + dy * dy );
				if ( d < 120 && d > 0.5 ) {
					var f = ( 1 - d / 120 ) * 2.8;
					this.x     += ( dx / d ) * f;
					this.baseX += ( dx / d ) * f * 0.10;
				}
			}

			var fromBot = H - this.y;
			var fromTop = this.y;
			var fadeIn  = H * 0.12;
			var fadeOut = H * 0.15;

			if ( fromBot < fadeIn ) {
				this.alpha = ( fromBot / fadeIn ) * this.maxA;
			} else if ( fromTop < fadeOut ) {
				this.alpha = ( fromTop / fadeOut ) * this.maxA;
			} else {
				this.alpha = this.maxA;
			}

			if ( this.y + this.r < 0 ) {
				this.reset( false );
			}
		};

		Bubble.prototype.draw = function () {
			if ( this.alpha < 0.02 ) return;

			var x = this.x, y = this.y, r = this.r, a = this.alpha;

			ctx.save();

			/* ── Clip everything inside the circle ─────────────────── */
			ctx.beginPath();
			ctx.arc( x, y, r, 0, Math.PI * 2 );
			ctx.clip();

			/* 1. GLASS BODY — very transparent blue-tinted base */
			var body = ctx.createRadialGradient( x, y - r * 0.1, r * 0.1, x, y, r );
			body.addColorStop( 0,    'rgba(190,225,255,' + ( a * 0.06 ).toFixed( 3 ) + ')' );
			body.addColorStop( 0.55, 'rgba(160,210,255,' + ( a * 0.10 ).toFixed( 3 ) + ')' );
			body.addColorStop( 1,    'rgba(100,170,230,' + ( a * 0.22 ).toFixed( 3 ) + ')' );
			ctx.fillStyle = body;
			ctx.fillRect( x - r, y - r, r * 2, r * 2 );

			/* 2. REFRACTION SHADOW — dark crescent at bottom-right */
			var shadow = ctx.createRadialGradient(
				x + r * 0.15, y + r * 0.25, r * 0.05,
				x + r * 0.10, y + r * 0.15, r * 1.05
			);
			shadow.addColorStop( 0,    'rgba(0,30,80,' + ( a * 0.38 ).toFixed( 3 ) + ')' );
			shadow.addColorStop( 0.45, 'rgba(0,30,80,' + ( a * 0.14 ).toFixed( 3 ) + ')' );
			shadow.addColorStop( 1,    'rgba(0,30,80,0)' );
			ctx.fillStyle = shadow;
			ctx.fillRect( x - r, y - r, r * 2, r * 2 );

			/* 3. MAIN HIGHLIGHT — large soft blob, top-left quadrant */
			var hlx = x - r * 0.28, hly = y - r * 0.28;
			var hl1 = ctx.createRadialGradient( hlx, hly, 0, hlx, hly, r * 0.70 );
			hl1.addColorStop( 0,    'rgba(255,255,255,' + ( a * 0.82 ).toFixed( 3 ) + ')' );
			hl1.addColorStop( 0.35, 'rgba(255,255,255,' + ( a * 0.42 ).toFixed( 3 ) + ')' );
			hl1.addColorStop( 1,    'rgba(255,255,255,0)' );
			ctx.fillStyle = hl1;
			ctx.fillRect( x - r, y - r, r * 2, r * 2 );

			/* 4. SPECULAR POINT — intense bright dot inside the highlight */
			var spx = x - r * 0.34, spy = y - r * 0.36;
			var sp = ctx.createRadialGradient( spx, spy, 0, spx, spy, r * 0.22 );
			sp.addColorStop( 0,    'rgba(255,255,255,' + ( a * 0.98 ).toFixed( 3 ) + ')' );
			sp.addColorStop( 0.5,  'rgba(255,255,255,' + ( a * 0.60 ).toFixed( 3 ) + ')' );
			sp.addColorStop( 1,    'rgba(255,255,255,0)' );
			ctx.fillStyle = sp;
			ctx.fillRect( x - r, y - r, r * 2, r * 2 );

			/* 5. BOTTOM CAUSTIC — faint secondary bounce light at bottom */
			var caus = ctx.createRadialGradient( x + r * 0.20, y + r * 0.55, 0, x + r * 0.20, y + r * 0.55, r * 0.30 );
			caus.addColorStop( 0,   'rgba(180,220,255,' + ( a * 0.30 ).toFixed( 3 ) + ')' );
			caus.addColorStop( 1,   'rgba(180,220,255,0)' );
			ctx.fillStyle = caus;
			ctx.fillRect( x - r, y - r, r * 2, r * 2 );

			ctx.restore(); /* end clip */

			/* 6. OUTER RIM — thin bright edge ring */
			ctx.beginPath();
			ctx.arc( x, y, r - 0.5, 0, Math.PI * 2 );
			ctx.strokeStyle = 'rgba(255,255,255,' + ( a * 0.45 ).toFixed( 3 ) + ')';
			ctx.lineWidth   = 1.2;
			ctx.stroke();

			/* 7. SOFT OUTER GLOW — subtle halo around the bubble */
			var glow = ctx.createRadialGradient( x, y, r * 0.85, x, y, r * 1.25 );
			glow.addColorStop( 0,   'rgba(180,220,255,' + ( a * 0.10 ).toFixed( 3 ) + ')' );
			glow.addColorStop( 1,   'rgba(180,220,255,0)' );
			ctx.beginPath();
			ctx.arc( x, y, r * 1.25, 0, Math.PI * 2 );
			ctx.fillStyle = glow;
			ctx.fill();
		};

		/* ── Pool ──────────────────────────────────────────────────── */
		var pool = [];

		function initPool() {
			var count = Math.max( 16, Math.min( 30, Math.floor( W / 90 ) ) );
			pool = [];
			for ( var i = 0; i < count; i++ ) {
				pool.push( new Bubble( true ) );
			}
		}

		/* ── Loop ──────────────────────────────────────────────────── */
		var firstTick = true;
		function tick() {
			if ( firstTick ) {
				console.log( '[bubbles] first tick — canvas:', W, 'x', H, 'bubbles:', pool.length );
				firstTick = false;
			}
			ctx.clearRect( 0, 0, W, H );
			for ( var i = 0; i < pool.length; i++ ) {
				pool[ i ].update();
				pool[ i ].draw();
			}
			requestAnimationFrame( tick );
		}

		/* ── Resize ────────────────────────────────────────────────── */
		if ( typeof ResizeObserver !== 'undefined' ) {
			new ResizeObserver( function () {
				resize();
				initPool();
			} ).observe( hero );
		} else {
			window.addEventListener( 'resize', function () {
				resize();
				initPool();
			} );
		}

		/* ── Mouse ─────────────────────────────────────────────────── */
		hero.addEventListener( 'mousemove', function ( e ) {
			var rect = hero.getBoundingClientRect();
			mouseX   = e.clientX - rect.left;
			mouseY   = e.clientY - rect.top;
			mouseActive = true;
		}, { passive: true } );

		hero.addEventListener( 'mouseleave', function () {
			mouseActive = false;
		} );

		/* Click / tap — burst of bubbles at pointer (skip if CTA was hit) */
		hero.addEventListener( 'pointerdown', function ( e ) {
			if ( e.target.closest( 'a, button' ) ) return;
			var rect = hero.getBoundingClientRect();
			var cx = e.clientX - rect.left;
			var cy = e.clientY - rect.top;
			for ( var i = 0; i < 8; i++ ) {
				var b = new Bubble( false );
				b.baseX = cx + ( Math.random() - 0.5 ) * 70;
				b.x     = b.baseX;
				b.y     = cy + ( Math.random() - 0.5 ) * 28;
				b.r     = 8 + Math.random() * 16;
				b.vy   *= 2.6;
				b.maxA  = 0.82 + Math.random() * 0.14;
				pool.push( b );
			}
			if ( pool.length > 55 ) pool.splice( 0, 8 );
		}, { passive: true } );

		/* ── Start ─────────────────────────────────────────────────── */
		resize();
		initPool();
		requestAnimationFrame( tick );
	}

	/* Run after DOM is ready */
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
