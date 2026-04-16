/**
 * OpenAccess WP — Front-End Accessibility Engine
 *
 * Architecture:
 *  - Zero dependencies (Vanilla JS / ES6).
 *  - Settings persisted to LocalStorage under key "oawp_settings".
 *  - All visual changes via CSS classes on <html> or CSS custom properties on :root.
 *  - Mutually exclusive features handled via EXCLUSIVE_GROUPS.
 *  - Easter egg: Konami code (↑↑↓↓←→←→BA) triggers Windows XP mode.
 *
 * @package OpenAccessWP
 * @since   1.0.0
 */

( function () {
	'use strict';

	// ── Constants ──────────────────────────────────────────────────────────────

	const STORAGE_KEY = 'oawp_settings';

	const FEATURE_CLASS = {
		seizureSafe:       'oawp-seizure-safe',
		stopAnimations:    'oawp-stop-animations',
		pauseVideos:       'oawp-pause-videos',
		dyslexicFont:      'oawp-dyslexic-font',
		readingGuide:      null,
		highContrastDark:  'oawp-hc-dark',
		highContrastLight: 'oawp-hc-light',
		monochrome:        'oawp-monochrome',
		invertColors:      'oawp-invert',
		eyeSafe:           'oawp-eye-safe',
		blueLightFilter:   'oawp-blue-light',
		rainbowText:       'oawp-rainbow-text',
		focusEnhancer:     'oawp-focus-enhancer',
		bigCursor:         'oawp-big-cursor',
		cursorCrosshair:   'oawp-cursor-crosshair',
		cursorLines:       null,
		highlightLinks:    'oawp-highlight-links',
		bigTargets:        'oawp-big-targets',
		readAloud:         null,
		readingMode:       null,
	};

	// Preset profiles — each entry is a partial settings object applied on top of DEFAULTS.
	const PROFILES = {
		dyslexia: {
			dyslexicFont:  true,
			lineHeight:    2.0,
			letterSpacing: 2,
			fontSize:      120,
		},
		lowVision: {
			bigCursor:     true,
			focusEnhancer: true,
			highlightLinks: true,
			bigTargets:    true,
			zoom:          150,
		},
		seizureSafe: {
			seizureSafe:    true,
			stopAnimations: true,
			pauseVideos:    true,
		},
		focus: {
			readingGuide:  true,
			highlightLinks: true,
			focusEnhancer: true,
			lineHeight:    2.0,
		},
	};

	const EXCLUSIVE_GROUPS = [
		[ 'highContrastDark', 'highContrastLight', 'monochrome', 'invertColors' ],
	];

	const DEFAULTS = {
		seizureSafe:       false,
		stopAnimations:    false,
		pauseVideos:       false,
		dyslexicFont:      false,
		readingGuide:      false,
		highContrastDark:  false,
		highContrastLight: false,
		monochrome:        false,
		invertColors:      false,
		eyeSafe:           false,
		blueLightFilter:   false,
		rainbowText:       false,
		focusEnhancer:     false,
		bigCursor:         false,
		cursorCrosshair:   false,
		cursorLines:       false,
		highlightLinks:    false,
		bigTargets:        false,
		fontSize:          100,
		zoom:              100,
		lineHeight:        1.5,
		letterSpacing:     0,
		readAloud:         false,
		readingMode:       false,
	};

	const SLIDER_BOUNDS = {
		fontSize:      { min: 80,  max: 200 },
		zoom:          { min: 50,  max: 200 },
		lineHeight:    { min: 1,   max: 3   },
		letterSpacing: { min: 0,   max: 10  },
	};

	// Slider metadata for fill-gradient and label updates
	const SLIDER_META = [
		{ id: 'oawp-font-size',      key: 'fontSize',      suffix: '%',  labelId: 'oawp-font-size-val'      },
		{ id: 'oawp-zoom',           key: 'zoom',          suffix: '%',  labelId: 'oawp-zoom-val'           },
		{ id: 'oawp-line-height',    key: 'lineHeight',    suffix: '',   labelId: 'oawp-line-height-val'    },
		{ id: 'oawp-letter-spacing', key: 'letterSpacing', suffix: 'px', labelId: 'oawp-letter-spacing-val' },
	];

	// ── Security constants ─────────────────────────────────────────────────────

	const HEX_COLOR_RE    = /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/;
	const VALID_POSITIONS = [ 'bottom-right', 'bottom-left', 'top-right', 'top-left' ];

	// ── State ──────────────────────────────────────────────────────────────────

	let settings     = Object.assign( {}, DEFAULTS );
	let panelOpen    = false;
	let activeProfile = null;

	// ── DOM refs ───────────────────────────────────────────────────────────────

	let root, toggleBtn, panel, closeBtn, resetBtn, readingGuide;
	let crosshairH = null, crosshairV = null, crosshairHandler = null;

	// ── i18n ──────────────────────────────────────────────────────────────────

	const i18n       = ( window.oawpConfig && window.oawpConfig.i18n ) || {};
	const LABEL_OPEN  = i18n.openMenu  || 'Open accessibility menu';
	const LABEL_CLOSE = i18n.closeMenu || 'Close accessibility menu';

	// ── LocalStorage ───────────────────────────────────────────────────────────

	function saveSettings() {
		try {
			localStorage.setItem( STORAGE_KEY, JSON.stringify( settings ) );
		} catch ( e ) { /* blocked in privacy mode — fail silently */ }
	}

	function loadSettings() {
		try {
			const raw = localStorage.getItem( STORAGE_KEY );
			if ( raw ) {
				const parsed = JSON.parse( raw );
				// Whitelist keys to prevent prototype pollution
				const safe = {};
				Object.keys( DEFAULTS ).forEach( function ( key ) {
					if ( Object.prototype.hasOwnProperty.call( parsed, key ) ) {
						safe[ key ] = parsed[ key ];
					}
				} );
				// Coerce boolean keys to actual Boolean (prevents truthy objects from
				// tampered localStorage from bypassing strict equality checks).
				Object.keys( DEFAULTS ).forEach( function ( key ) {
					if ( typeof DEFAULTS[ key ] === 'boolean' && key in safe ) {
						safe[ key ] = !! safe[ key ];
					}
				} );
				// Clamp numeric (slider) values to defined bounds
				Object.keys( SLIDER_BOUNDS ).forEach( function ( key ) {
					if ( key in safe ) {
						const n = parseFloat( safe[ key ] );
						const b = SLIDER_BOUNDS[ key ];
						safe[ key ] = isNaN( n ) ? DEFAULTS[ key ] : Math.min( b.max, Math.max( b.min, n ) );
					}
				} );
				settings = Object.assign( {}, DEFAULTS, safe );
			}
		} catch ( e ) {
			settings = Object.assign( {}, DEFAULTS );
		}
	}

	// ── CSS variable helpers ───────────────────────────────────────────────────

	function setCSSVar( name, value ) {
		document.documentElement.style.setProperty( name, value );
	}

	// ── Slider fill gradient ───────────────────────────────────────────────────

	/**
	 * Updates the CSS custom property that drives the slider's filled-track
	 * gradient so the portion to the left of the thumb is accent-coloured.
	 */
	function updateSliderFill( slider ) {
		const min = parseFloat( slider.min );
		const max = parseFloat( slider.max );
		const val = parseFloat( slider.value );
		const pct = ( ( val - min ) / ( max - min ) ) * 100;
		slider.style.setProperty( '--oawp-slider-pct', pct + '%' );
	}

	// ── Feature: Text Size ────────────────────────────────────────────────────

	/**
	 * Scales text by adjusting `html { font-size }`. This affects rem-based and
	 * em-based text sizes without touching layout, images, or spacing.
	 *
	 * Themes that use px units or CSS custom properties for font sizes will not be
	 * affected — this is intentional. This slider is explicitly "Text Size" (WCAG
	 * 1.4.4 — Resize Text). For full page layout zoom use the Page Zoom slider.
	 */
	function applyFontSize( value ) {
		const n = Number( value );
		if ( ! isFinite( n ) ) return;
		let styleEl = document.getElementById( 'oawp-font-size-style' );
		if ( ! styleEl ) {
			styleEl    = document.createElement( 'style' );
			styleEl.id = 'oawp-font-size-style';
			document.head.appendChild( styleEl );
		}

		if ( n === DEFAULTS.fontSize ) {
			styleEl.textContent = '';
		} else {
			styleEl.textContent =
				'html { font-size: ' + n + '% !important; }\n' +
				'body { font-size: 1em !important; }';
		}
		value = n;

		const label  = document.getElementById( 'oawp-font-size-val' );
		const slider = document.getElementById( 'oawp-font-size' );
		if ( label )  label.textContent = value + '%';
		if ( slider ) {
			slider.value = value;
			slider.setAttribute( 'aria-valuenow', value );
			slider.setAttribute( 'aria-valuetext', value + '%' );
			updateSliderFill( slider );
		}
	}

	// ── Feature: Page Zoom ────────────────────────────────────────────────────

	/**
	 * Universal full-page zoom using the CSS `zoom` property.
	 *
	 * Unlike font-size, `zoom` scales EVERYTHING — px, rem, em, clamp(),
	 * CSS custom properties, images, spacing — regardless of unit. Supported
	 * natively by Chrome, Safari, Edge (always) and Firefox 126+ (May 2024).
	 *
	 * Fixed-position plugin elements (panel, toggle, reading guide, XP taskbar)
	 * are counter-zoomed (1 / scale) so they remain at their intended size.
	 *
	 * No fallback is provided for Firefox < 126 — the slider simply has no
	 * effect on those browsers, which is safe and non-breaking.
	 */
	function applyZoom( value ) {
		const n = Number( value );
		if ( ! isFinite( n ) || n === 0 ) return;
		value = n;
		let styleEl = document.getElementById( 'oawp-zoom-style' );
		if ( ! styleEl ) {
			styleEl    = document.createElement( 'style' );
			styleEl.id = 'oawp-zoom-style';
			document.head.appendChild( styleEl );
		}

		if ( value === DEFAULTS.zoom ) {
			styleEl.textContent = '';
		} else {
			const scale        = ( value / 100 ).toFixed( 5 );
			const counterScale = ( 100 / value ).toFixed( 5 );
			// All plugin-owned fixed elements that must NOT be zoomed with the page.
			const ownUI = '#oawp-root, #oawp-reading-guide, #oawp-xp-taskbar, #oawp-xp-balloon';

			styleEl.textContent = [
				'body { zoom: ' + scale + '; }',
				ownUI + ' { zoom: ' + counterScale + '; }',
			].join( '\n' );
		}

		const label  = document.getElementById( 'oawp-zoom-val' );
		const slider = document.getElementById( 'oawp-zoom' );
		if ( label )  label.textContent = value + '%';
		if ( slider ) {
			slider.value = value;
			slider.setAttribute( 'aria-valuenow', value );
			slider.setAttribute( 'aria-valuetext', value + '%' );
			updateSliderFill( slider );
		}
	}

	// ── Feature: Line Height ───────────────────────────────────────────────────

	function applyLineHeight( value ) {
		setCSSVar( '--oawp-line-height', value );
		const label  = document.getElementById( 'oawp-line-height-val' );
		const slider = document.getElementById( 'oawp-line-height' );
		if ( label )  label.textContent = parseFloat( value ).toFixed( 2 );
		if ( slider ) {
			slider.value = value;
			slider.setAttribute( 'aria-valuenow', value );
			slider.setAttribute( 'aria-valuetext', value );
			updateSliderFill( slider );
		}
		toggleSpacingClass();
	}

	// ── Feature: Letter Spacing ────────────────────────────────────────────────

	function applyLetterSpacing( value ) {
		setCSSVar( '--oawp-letter-spacing', value + 'px' );
		const label  = document.getElementById( 'oawp-letter-spacing-val' );
		const slider = document.getElementById( 'oawp-letter-spacing' );
		if ( label )  label.textContent = value + 'px';
		if ( slider ) {
			slider.value = value;
			slider.setAttribute( 'aria-valuenow', value );
			slider.setAttribute( 'aria-valuetext', value + 'px' );
			updateSliderFill( slider );
		}
		toggleSpacingClass();
	}

	function toggleSpacingClass() {
		const needsClass =
			parseFloat( settings.lineHeight )   !== parseFloat( DEFAULTS.lineHeight ) ||
			parseFloat( settings.letterSpacing ) !== parseFloat( DEFAULTS.letterSpacing );
		document.documentElement.classList.toggle( 'oawp-custom-spacing', needsClass );
	}

	// ── Feature: Reading Guide ─────────────────────────────────────────────────

	let readingGuideHandler = null;

	function enableReadingGuide() {
		if ( ! readingGuide ) return;
		readingGuide.hidden = false;
		readingGuideHandler = function ( e ) {
			readingGuide.style.top = e.clientY + 'px';
		};
		document.addEventListener( 'mousemove', readingGuideHandler );
	}

	function disableReadingGuide() {
		if ( ! readingGuide ) return;
		readingGuide.hidden = true;
		if ( readingGuideHandler ) {
			document.removeEventListener( 'mousemove', readingGuideHandler );
			readingGuideHandler = null;
		}
	}

	// ── Feature: Cursor Crosshair Lines ───────────────────────────────────────

	function enableCursorLines() {
		if ( crosshairH ) return;
		crosshairH = document.createElement( 'div' );
		crosshairH.id = 'oawp-crosshair-h';
		crosshairH.setAttribute( 'aria-hidden', 'true' );
		crosshairV = document.createElement( 'div' );
		crosshairV.id = 'oawp-crosshair-v';
		crosshairV.setAttribute( 'aria-hidden', 'true' );
		document.body.appendChild( crosshairH );
		document.body.appendChild( crosshairV );
		crosshairHandler = function ( e ) {
			crosshairH.style.top  = e.clientY + 'px';
			crosshairV.style.left = e.clientX + 'px';
		};
		document.addEventListener( 'mousemove', crosshairHandler );
	}

	function disableCursorLines() {
		if ( crosshairH ) { crosshairH.remove(); crosshairH = null; }
		if ( crosshairV ) { crosshairV.remove(); crosshairV = null; }
		if ( crosshairHandler ) {
			document.removeEventListener( 'mousemove', crosshairHandler );
			crosshairHandler = null;
		}
	}

	// ── Feature: Reading Mode (distraction-free reader view) ──────────────────

	let readerOverlay = null;

	function enableReadingMode() {
		if ( readerOverlay ) return;
		const selectors = [
			'article',
			'[role="main"]',
			'main',
			'.entry-content',
			'.post-content',
			'.article-content',
			'.page-content',
			'#content',
			'#main-content',
			'#primary',
			'.content',
		];
		let source = null;
		for ( let i = 0; i < selectors.length; i++ ) {
			const el = document.querySelector( selectors[ i ] );
			if ( el && ! el.closest( '#oawp-root' ) && ( el.innerText || '' ).trim().length > 100 ) {
				source = el;
				break;
			}
		}

		readerOverlay = document.createElement( 'div' );
		readerOverlay.id = 'oawp-reader-overlay';
		readerOverlay.setAttribute( 'role', 'region' );
		readerOverlay.setAttribute( 'aria-label', 'Reading mode' );

		const readerHeader = document.createElement( 'div' );
		readerHeader.id = 'oawp-reader-header';

		const exitBtn = document.createElement( 'button' );
		exitBtn.id = 'oawp-reader-close';
		exitBtn.setAttribute( 'aria-label', 'Exit reading mode' );
		exitBtn.innerHTML = '&#10005; Exit Reading Mode';
		exitBtn.addEventListener( 'click', function () {
			settings.readingMode = false;
			applyFeature( 'readingMode', false );
			updateToggleButton( 'readingMode', false );
			saveSettings();
		} );
		readerHeader.appendChild( exitBtn );
		readerOverlay.appendChild( readerHeader );

		const readerContent = document.createElement( 'div' );
		readerContent.id = 'oawp-reader-content';
		if ( source ) {
			readerContent.appendChild( source.cloneNode( true ) );
		} else {
			const msg = document.createElement( 'p' );
			msg.style.color = '#64748b';
			msg.textContent = 'No article content was detected on this page.';
			readerContent.appendChild( msg );
		}
		readerOverlay.appendChild( readerContent );
		document.body.appendChild( readerOverlay );
		exitBtn.focus();
	}

	function disableReadingMode() {
		if ( readerOverlay ) { readerOverlay.remove(); readerOverlay = null; }
	}

	// ── Feature: Seizure Safe — JS mousemove blocker ──────────────────────────

	/**
	 * CSS can stop CSS animations/transitions, but JavaScript-driven effects
	 * (parallax, mouse-follow, tilt.js, GSAP, etc.) listen to `mousemove` and
	 * update element styles directly — CSS cannot touch those.
	 *
	 * When Seizure Safe mode is active we register a capturing `mousemove`
	 * listener on `window`.  Capture phase runs top-down before any bubble-phase
	 * handler on child elements.  Calling stopImmediatePropagation() here
	 * prevents every other `mousemove` handler on the page from firing.
	 *
	 * We manually forward the reading-guide position inside this interceptor
	 * so that feature still works even while the blocker is active.
	 */
	let mousemoveBlocker = null;

	function enableMousemoveBlock() {
		if ( mousemoveBlocker ) return;
		mousemoveBlocker = function ( e ) {
			// Keep our own reading guide alive (its listener is on document,
			// which would never see the event once we stop propagation here).
			if ( settings.readingGuide && readingGuide && ! readingGuide.hidden ) {
				readingGuide.style.top = e.clientY + 'px';
			}
			// Keep cursor crosshair lines alive too.
			if ( settings.cursorLines && crosshairH && crosshairV ) {
				crosshairH.style.top  = e.clientY + 'px';
				crosshairV.style.left = e.clientX + 'px';
			}
			// Block every other mousemove handler on the page.
			e.stopImmediatePropagation();
		};
		// passive:true — we don't call preventDefault, so the browser can still
		// process pointer hit-testing, :hover CSS states, and native cursor updates.
		window.addEventListener( 'mousemove', mousemoveBlocker, { capture: true, passive: true } );
	}

	function disableMousemoveBlock() {
		if ( ! mousemoveBlocker ) return;
		window.removeEventListener( 'mousemove', mousemoveBlocker, { capture: true } );
		mousemoveBlocker = null;
	}

	// ── Feature: Read Aloud (Text-to-Speech) ──────────────────────────────────

	/**
	 * Uses the browser's built-in Web Speech API (SpeechSynthesis).
	 * When active, hovering over a text element outlines it; clicking reads it aloud.
	 * The click is intercepted in capture phase so links/buttons don't navigate.
	 */

	const TTS_SUPPORTED = 'speechSynthesis' in window;
	const TTS_SELECTOR  = 'p, h1, h2, h3, h4, h5, h6, li, td, th, blockquote, figcaption, label, dt, dd, a, button';

	let ttsHoverHandler = null;
	let ttsLeaveHandler = null;
	let ttsClickHandler = null;

	function enableReadAloud() {
		if ( ! TTS_SUPPORTED ) return;
		document.documentElement.classList.add( 'oawp-read-aloud-mode' );

		ttsHoverHandler = function ( e ) {
			const el = e.target.closest( TTS_SELECTOR );
			// Clear any previous hover highlight
			document.querySelectorAll( '.oawp-tts-hover' ).forEach( function ( n ) {
				n.classList.remove( 'oawp-tts-hover' );
			} );
			if ( el && ! el.closest( '#oawp-root' ) ) {
				el.classList.add( 'oawp-tts-hover' );
			}
		};

		ttsLeaveHandler = function ( e ) {
			if ( e.target.classList ) e.target.classList.remove( 'oawp-tts-hover' );
		};

		ttsClickHandler = function ( e ) {
			const el = e.target.closest( TTS_SELECTOR );
			if ( ! el || el.closest( '#oawp-root' ) ) return;

			// Prevent navigation / form submission
			e.preventDefault();
			e.stopPropagation();

			// Clear previous speaking highlight
			document.querySelectorAll( '.oawp-tts-speaking' ).forEach( function ( n ) {
				n.classList.remove( 'oawp-tts-speaking' );
			} );

			const text = ( el.innerText || el.textContent || '' ).trim();
			if ( ! text ) return;

			window.speechSynthesis.cancel();
			el.classList.add( 'oawp-tts-speaking' );

			const utterance = new SpeechSynthesisUtterance( text );
			utterance.onend = utterance.onerror = function () {
				el.classList.remove( 'oawp-tts-speaking' );
			};
			window.speechSynthesis.speak( utterance );
		};

		document.addEventListener( 'mouseover', ttsHoverHandler );
		document.addEventListener( 'mouseout',  ttsLeaveHandler );
		document.addEventListener( 'click',     ttsClickHandler, { capture: true } );
	}

	function disableReadAloud() {
		document.documentElement.classList.remove( 'oawp-read-aloud-mode' );
		if ( TTS_SUPPORTED ) window.speechSynthesis.cancel();

		document.querySelectorAll( '.oawp-tts-hover, .oawp-tts-speaking' ).forEach( function ( n ) {
			n.classList.remove( 'oawp-tts-hover', 'oawp-tts-speaking' );
		} );

		if ( ttsHoverHandler ) { document.removeEventListener( 'mouseover', ttsHoverHandler ); ttsHoverHandler = null; }
		if ( ttsLeaveHandler ) { document.removeEventListener( 'mouseout',  ttsLeaveHandler ); ttsLeaveHandler = null; }
		if ( ttsClickHandler ) { document.removeEventListener( 'click',     ttsClickHandler, { capture: true } ); ttsClickHandler = null; }
	}

	// ── Preset Profiles ────────────────────────────────────────────────────────

	/**
	 * Applies a named profile by resetting to DEFAULTS then layering the profile's
	 * partial settings on top.  Manual changes after applying a profile are allowed
	 * and simply clear the "active" indicator on the profile buttons.
	 */
	function applyProfile( name ) {
		const profile = PROFILES[ name ];
		if ( ! profile ) return;

		// ── Reset state ──────────────────────────────────────────────────────────
		settings = Object.assign( {}, DEFAULTS );
		Object.values( FEATURE_CLASS ).forEach( function ( cls ) {
			if ( cls ) document.documentElement.classList.remove( cls );
		} );
		document.documentElement.classList.remove( 'oawp-custom-spacing', 'oawp-read-aloud-mode' );
		const fontSt = document.getElementById( 'oawp-font-size-style' );
		if ( fontSt ) fontSt.textContent = '';
		const zoomSt = document.getElementById( 'oawp-zoom-style' );
		if ( zoomSt ) zoomSt.textContent = '';
		disableReadingGuide();
		disableMousemoveBlock();
		releaseVideos();
		disableReadAloud();
		disableCursorLines();
		disableReadingMode();

		// ── Apply profile overrides ───────────────────────────────────────────────
		Object.keys( profile ).forEach( function ( key ) {
			settings[ key ] = profile[ key ];
		} );

		// Handle exclusive contrast groups
		Object.keys( profile ).forEach( function ( key ) {
			resolveExclusive( key, profile[ key ] );
		} );

		// ── Push to DOM ──────────────────────────────────────────────────────────
		applyAllSettings();
		syncAllButtons();
		syncSliders();

		activeProfile = name;
		updateProfileButtons();
		saveSettings();
	}

	function updateProfileButtons() {
		document.querySelectorAll( '.oawp-profile-btn' ).forEach( function ( btn ) {
			const isActive = btn.dataset.profile === activeProfile;
			btn.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
		} );
	}

	/** Called whenever the user manually changes a toggle or slider. */
	function clearActiveProfile() {
		if ( activeProfile === null ) return;
		activeProfile = null;
		updateProfileButtons();
	}

	// ── Feature: Pause Videos ─────────────────────────────────────────────────

	let pausedVideos = [];

	function pauseAllVideos() {
		const videos = Array.from( document.querySelectorAll( 'video' ) );
		pausedVideos = [];
		videos.forEach( function ( vid ) {
			if ( ! vid.paused ) { vid.pause(); pausedVideos.push( vid ); }
			vid.setAttribute( 'data-oawp-paused', '1' );
		} );
	}

	function releaseVideos() {
		pausedVideos.forEach( function ( vid ) { vid.removeAttribute( 'data-oawp-paused' ); } );
		pausedVideos = [];
	}

	// ── Feature: OpenDyslexic font — lazy loader ───────────────────────────────

	let dyslexicFontLoaded = false;

	function ensureDyslexicFont() {
		if ( dyslexicFontLoaded ) return;
		dyslexicFontLoaded = true;
		const link       = document.createElement( 'link' );
		link.rel         = 'stylesheet';
		link.crossOrigin = 'anonymous';
		link.integrity   = 'sha256-4T0/YPsJfqSU3xjAjWiMnf5v5mvJh73BGZS6T0HGJQ=';
		link.href        = 'https://cdn.jsdelivr.net/npm/open-dyslexic@1.0.3/open-dyslexic-regular.min.css';
		document.head.appendChild( link );
	}

	// ── Exclusive groups ───────────────────────────────────────────────────────

	function resolveExclusive( featureName, newValue ) {
		if ( ! newValue ) return;
		EXCLUSIVE_GROUPS.forEach( function ( group ) {
			if ( group.indexOf( featureName ) !== -1 ) {
				group.forEach( function ( sibling ) {
					if ( sibling !== featureName && settings[ sibling ] ) {
						settings[ sibling ] = false;
						applyFeature( sibling, false );
						updateToggleButton( sibling, false );
					}
				} );
			}
		} );
	}

	// ── Core apply function ────────────────────────────────────────────────────

	function applyFeature( name, value ) {
		const cssClass = FEATURE_CLASS[ name ];

		if ( typeof value === 'boolean' ) {
			if ( cssClass ) {
				document.documentElement.classList.toggle( cssClass, value );
			}
			if ( name === 'dyslexicFont'  && value ) ensureDyslexicFont();
			if ( name === 'pauseVideos'   ) { value ? pauseAllVideos()      : releaseVideos();          }
			if ( name === 'readingGuide'  ) { value ? enableReadingGuide()  : disableReadingGuide();    }
			if ( name === 'readAloud'     ) { value ? enableReadAloud()     : disableReadAloud();       }
			if ( name === 'cursorLines'   ) { value ? enableCursorLines()   : disableCursorLines();     }
			if ( name === 'readingMode'   ) { value ? enableReadingMode()   : disableReadingMode();     }
			// Seizure Safe & Stop Animations: block JS-driven mousemove effects (parallax, tilt, etc.)
			// Only disable the blocker when BOTH features are off — either one active keeps it running.
			if ( name === 'seizureSafe' || name === 'stopAnimations' ) {
				const eitherActive = settings.seizureSafe || settings.stopAnimations;
				eitherActive ? enableMousemoveBlock() : disableMousemoveBlock();
			}
			return;
		}

		if ( name === 'fontSize' )      applyFontSize( value );
		if ( name === 'zoom' )          applyZoom( value );
		if ( name === 'lineHeight' )    applyLineHeight( value );
		if ( name === 'letterSpacing' ) applyLetterSpacing( value );
	}

	function applyAllSettings() {
		Object.keys( settings ).forEach( function ( key ) {
			applyFeature( key, settings[ key ] );
		} );
	}

	// ── UI helpers ─────────────────────────────────────────────────────────────

	function updateToggleButton( featureName, value ) {
		// Use dataset equality instead of CSS selector string-building to avoid
		// any possibility of selector-injection via an unexpected featureName value.
		document.querySelectorAll( '.oawp-toggle-btn[data-feature]' ).forEach( function ( btn ) {
			if ( btn.dataset.feature === featureName ) {
				btn.setAttribute( 'aria-pressed', value ? 'true' : 'false' );
			}
		} );
	}

	function syncAllButtons() {
		Object.keys( FEATURE_CLASS ).forEach( function ( key ) {
			updateToggleButton( key, !! settings[ key ] );
		} );
	}

	function syncSliders() {
		SLIDER_META.forEach( function ( s ) {
			const slider = document.getElementById( s.id );
			const label  = document.getElementById( s.labelId );
			const val    = settings[ s.key ];
			if ( slider ) { slider.value = val; updateSliderFill( slider ); }
			if ( label )  label.textContent = val + s.suffix;
		} );
	}

	// ── Panel open/close ───────────────────────────────────────────────────────

	function openPanel() {
		panelOpen = true;
		panel.hidden = false;
		toggleBtn.setAttribute( 'aria-expanded', 'true' );
		toggleBtn.setAttribute( 'aria-label', LABEL_CLOSE );
		closeBtn.focus();
	}

	function closePanel() {
		panelOpen = false;
		panel.hidden = true;
		toggleBtn.setAttribute( 'aria-expanded', 'false' );
		toggleBtn.setAttribute( 'aria-label', LABEL_OPEN );
		toggleBtn.focus();
	}

	// ── Reset ──────────────────────────────────────────────────────────────────

	function resetAll() {
		settings = Object.assign( {}, DEFAULTS );
		Object.values( FEATURE_CLASS ).forEach( function ( cls ) {
			if ( cls ) document.documentElement.classList.remove( cls );
		} );
		document.documentElement.classList.remove( 'oawp-custom-spacing' );
		const fontStyleEl = document.getElementById( 'oawp-font-size-style' );
		if ( fontStyleEl ) fontStyleEl.textContent = '';
		const zoomStyleEl = document.getElementById( 'oawp-zoom-style' );
		if ( zoomStyleEl ) zoomStyleEl.textContent = '';
		disableReadingGuide();
		disableMousemoveBlock();
		releaseVideos();
		disableReadAloud();
		disableCursorLines();
		disableReadingMode();
		activeProfile = null;
		updateProfileButtons();
		applyAllSettings();
		syncAllButtons();
		syncSliders();
		saveSettings();
	}

	// ── Accent color & button position (from PHP config) ──────────────────────

	function applyAccentColor() {
		const config = window.oawpConfig || {};
		const color  = config.accentColor || '';
		if ( HEX_COLOR_RE.test( color ) ) {
			document.documentElement.style.setProperty( '--oawp-accent', color );
		}
	}

	function applyButtonPosition() {
		const config  = window.oawpConfig || {};
		const pos     = config.buttonPosition || '';
		const safePos = VALID_POSITIONS.indexOf( pos ) !== -1 ? pos : 'bottom-right';
		root.className = 'pos-' + safePos;
	}

	// ═══════════════════════════════════════════════════════════════════════════
	//  EASTER EGG — Windows XP Mode
	//  Trigger: Konami code  ↑ ↑ ↓ ↓ ← → ← → B A
	// ═══════════════════════════════════════════════════════════════════════════

	const KONAMI = [
		'ArrowUp','ArrowUp','ArrowDown','ArrowDown',
		'ArrowLeft','ArrowRight','ArrowLeft','ArrowRight',
		'b','a',
	];

	let konamiIndex  = 0;
	let xpModeActive = false;
	let xpClockTimer = null;

	function updateXPClock() {
		const clockEl = document.getElementById( 'oawp-xp-clock' );
		if ( ! clockEl ) return;
		const now = new Date();
		let hours = now.getHours();
		const mins = String( now.getMinutes() ).padStart( 2, '0' );
		const ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12 || 12;
		clockEl.textContent = hours + ':' + mins + ' ' + ampm;
	}

	function activateXP() {
		if ( xpModeActive ) return;
		xpModeActive = true;
		document.documentElement.classList.add( 'oawp-xp-mode' );

		// Update toggle button text to say "Start"
		const svgEl = toggleBtn.querySelector( 'svg' );
		if ( svgEl ) svgEl.style.display = 'none';
		if ( ! toggleBtn.querySelector( '.oawp-xp-start-label' ) ) {
			const label = document.createElement( 'span' );
			label.className = 'oawp-xp-start-label';
			label.textContent = 'start';
			toggleBtn.appendChild( label );
		}

		// Start the taskbar clock
		updateXPClock();
		xpClockTimer = setInterval( updateXPClock, 30000 );

		// Show the notification balloon, then auto-dismiss after 6 s
		const balloon = document.getElementById( 'oawp-xp-balloon' );
		if ( balloon ) {
			balloon.style.display = 'block';
			setTimeout( function () {
				if ( balloon ) balloon.style.display = 'none';
			}, 6000 );
		}
	}

	function deactivateXP() {
		if ( ! xpModeActive ) return;
		xpModeActive = false;
		document.documentElement.classList.remove( 'oawp-xp-mode' );

		const svgEl = toggleBtn.querySelector( 'svg' );
		if ( svgEl ) svgEl.style.display = '';
		const startLabel = toggleBtn.querySelector( '.oawp-xp-start-label' );
		if ( startLabel ) startLabel.remove();

		clearInterval( xpClockTimer );
		xpClockTimer = null;
	}

	function handleKonami( e ) {
		// Normalise: lowercase single-char keys so B/b and A/a both match
		const key = e.key.length === 1 ? e.key.toLowerCase() : e.key;
		if ( key === KONAMI[ konamiIndex ] ) {
			konamiIndex++;
			if ( konamiIndex === KONAMI.length ) {
				konamiIndex = 0;
				xpModeActive ? deactivateXP() : activateXP();
			}
		} else {
			// Allow restarting the sequence if first key matches
			konamiIndex = ( key === KONAMI[ 0 ] ) ? 1 : 0;
		}
	}

	// ═══════════════════════════════════════════════════════════════════════════

	// ── Event listeners ────────────────────────────────────────────────────────

	function bindEvents() {
		toggleBtn.addEventListener( 'click', function () {
			panelOpen ? closePanel() : openPanel();
		} );

		closeBtn.addEventListener( 'click', closePanel );
		resetBtn.addEventListener( 'click', resetAll );

		// Toggle buttons + Profile buttons (shared click handler)
		panel.addEventListener( 'click', function ( e ) {
			// ── Profile preset ───────────────────────────────────────────────────
			const profileBtn = e.target.closest( '.oawp-profile-btn' );
			if ( profileBtn ) {
				const name = profileBtn.dataset.profile;
				if ( activeProfile === name ) {
					// Second click on the same profile = reset everything
					resetAll();
				} else {
					applyProfile( name );
				}
				return;
			}

			// ── Feature toggle ───────────────────────────────────────────────────
			const btn = e.target.closest( '.oawp-toggle-btn' );
			if ( ! btn ) return;
			const feature = btn.dataset.feature;
			if ( ! ( feature in settings ) ) return;

			const newValue = ! settings[ feature ];
			resolveExclusive( feature, newValue );
			settings[ feature ] = newValue;
			applyFeature( feature, newValue );
			updateToggleButton( feature, newValue );
			clearActiveProfile(); // manual change deactivates profile highlight
			saveSettings();
		} );

		// Sliders
		panel.addEventListener( 'input', function ( e ) {
			const slider = e.target.closest( 'input[type="range"]' );
			if ( ! slider ) return;
			const feature = slider.dataset.feature;
			if ( ! ( feature in settings ) ) return;

			const value = parseFloat( slider.value );
			settings[ feature ] = value;
			applyFeature( feature, value );
			updateSliderFill( slider );
			clearActiveProfile(); // manual change deactivates profile highlight
			saveSettings();
		} );

		// Escape closes panel
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && panelOpen ) closePanel();
		} );

		// Konami code listener — capture phase so site handlers can't swallow the keys
		document.addEventListener( 'keydown', handleKonami, true );

		// Focus trap inside panel
		panel.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'Tab' ) return;
			const focusable = panel.querySelectorAll(
				'button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
			);
			const first = focusable[ 0 ];
			const last  = focusable[ focusable.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault(); last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault(); first.focus();
			}
		} );

		// MutationObserver for dynamically added videos
		if ( window.MutationObserver ) {
			const observer = new MutationObserver( function ( mutations ) {
				if ( ! settings.pauseVideos ) return;
				mutations.forEach( function ( mutation ) {
					mutation.addedNodes.forEach( function ( node ) {
						if ( node.nodeType !== 1 ) return;
						const vids = node.tagName === 'VIDEO'
							? [ node ]
							: Array.from( node.querySelectorAll( 'video' ) );
						vids.forEach( function ( vid ) {
							vid.pause();
							vid.setAttribute( 'data-oawp-paused', '1' );
						} );
					} );
				} );
			} );
			observer.observe( document.body, { childList: true, subtree: true } );
		}
	}

	// ── Boot ───────────────────────────────────────────────────────────────────

	function init() {
		root         = document.getElementById( 'oawp-root' );
		toggleBtn    = document.getElementById( 'oawp-toggle' );
		panel        = document.getElementById( 'oawp-panel' );
		closeBtn     = document.getElementById( 'oawp-close' );
		resetBtn     = document.getElementById( 'oawp-reset' );
		readingGuide = document.getElementById( 'oawp-reading-guide' );

		if ( ! root || ! toggleBtn || ! panel ) return;

		loadSettings();
		applyAccentColor();
		applyButtonPosition();
		applyAllSettings();
		syncAllButtons();
		syncSliders();
		bindEvents();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
