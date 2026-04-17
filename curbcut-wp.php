<?php
/**
 * Plugin Name:       Curb Cut
 * Plugin URI:        https://github.com/D23M23/CurbCut-WP
 * Description:       Level the web for all users. A real-time accessibility overlay providing WCAG 2.1 AA and CA.gov compliant features: motion safety, visual customization, contrast profiles, and navigation aids.
 * Version:           2.0.0
 * Author:            Curbcut WP Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       curb-cut
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package CurbcutWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OAWP_VERSION', '2.0.0' );
define( 'OAWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OAWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once OAWP_PLUGIN_DIR . 'includes/admin.php';
require_once OAWP_PLUGIN_DIR . 'includes/accessibility-statement.php';

/**
 * Enqueue front-end assets.
 */
function oawp_enqueue_assets() {
	$options = oawp_get_options();

	if ( ! $options['enabled'] ) {
		return;
	}

	wp_enqueue_style(
		'curbcut-wp',
		OAWP_PLUGIN_URL . 'assets/css/overlay.css',
		[],
		OAWP_VERSION
	);

	wp_enqueue_script(
		'curbcut-wp',
		OAWP_PLUGIN_URL . 'assets/js/curbcut.js',
		[],
		OAWP_VERSION,
		true // Load in footer.
	);

	// Pass PHP options and translatable strings to JS.
	wp_localize_script(
		'curbcut-wp',
		'oawpConfig',
		[
			'buttonPosition' => esc_js( $options['button_position'] ),
			'accentColor'    => esc_js( $options['accent_color'] ),
			'skipTargetId'   => esc_js( $options['skip_target_id'] ),
			'i18n'           => [
				'openMenu'  => esc_js( __( 'Open accessibility menu', 'curbcut-wp' ) ),
				'closeMenu' => esc_js( __( 'Close accessibility menu', 'curbcut-wp' ) ),
			],
		]
	);
}
add_action( 'wp_enqueue_scripts', 'oawp_enqueue_assets' );

/**
 * Helper: render a single toggle-switch button.
 *
 * @param string $feature JS feature key (data-feature attribute).
 * @param string $icon    HTML entity / emoji for the icon.
 * @param string $label   Translated button label.
 * @param string $desc    Optional short description shown below the label.
 */
function oawp_toggle_btn( $feature, $icon, $label, $desc = '' ) {
	?>
	<div class="oawp-control">
		<button class="oawp-toggle-btn" data-feature="<?php echo esc_attr( $feature ); ?>" aria-pressed="false">
			<span class="oawp-switch" aria-hidden="true"><span class="oawp-switch-thumb"></span></span>
			<span class="oawp-btn-icon" aria-hidden="true"><?php echo wp_kses( $icon, [] ); ?></span>
			<span class="oawp-btn-text">
				<span class="oawp-btn-label"><?php echo esc_html( $label ); ?></span>
				<?php if ( $desc ) : ?>
				<span class="oawp-btn-desc"><?php echo esc_html( $desc ); ?></span>
				<?php endif; ?>
			</span>
		</button>
	</div>
	<?php
}

/**
 * Output the overlay HTML in the footer.
 */
function oawp_render_overlay() {
	$options = oawp_get_options();
	if ( ! $options['enabled'] ) {
		return;
	}
	?>
	<!-- Curbcut WP Overlay -->
	<div id="oawp-root" role="region" aria-label="<?php esc_attr_e( 'Accessibility Controls', 'curbcut-wp' ); ?>">

		<!-- Floating toggle button — wrapper locks the 60×60 shape against site CSS -->
		<div style="display:block;width:60px;height:60px;min-width:60px;min-height:60px;flex-shrink:0;padding:0;margin:0;overflow:visible;">
		<button
			id="oawp-toggle"
			aria-expanded="false"
			aria-controls="oawp-panel"
			aria-label="<?php esc_attr_e( 'Open accessibility menu', 'curbcut-wp' ); ?>"
			title="<?php esc_attr_e( 'Accessibility', 'curbcut-wp' ); ?>"
			style="width:60px;height:60px;min-width:60px;min-height:60px;padding:0;margin:0;border-radius:50%;box-sizing:border-box;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;"
		>
			<!-- Accessible Icon Project — accessibleicon.org — Public Domain -->
			<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"
				viewBox="190 -75 800 950" width="60" height="60" fill="currentColor">
				<path d="M833.556,367.574c-7.753-7.955-18.586-12.155-29.656-11.549l-133.981,7.458l73.733-83.975c10.504-11.962,13.505-27.908,9.444-42.157c-2.143-9.764-8.056-18.648-17.14-24.324c-0.279-0.199-176.247-102.423-176.247-102.423c-14.369-8.347-32.475-6.508-44.875,4.552l-85.958,76.676c-15.837,14.126-17.224,38.416-3.097,54.254c14.128,15.836,38.419,17.227,54.255,3.096l65.168-58.131l53.874,31.285l-95.096,108.305c-39.433,6.431-74.913,24.602-102.765,50.801l49.66,49.66c22.449-20.412,52.256-32.871,84.918-32.871c69.667,0,126.346,56.68,126.346,126.348c0,32.662-12.459,62.467-32.869,84.916l49.657,49.66c33.08-35.166,53.382-82.484,53.382-134.576c0-31.035-7.205-60.384-20.016-86.482l51.861-2.889l-12.616,154.75c-1.725,21.152,14.027,39.695,35.18,41.422c1.059,0.086,2.116,0.127,3.163,0.127c19.806,0,36.621-15.219,38.257-35.306l16.193-198.685C845.235,386.445,841.305,375.527,833.556,367.574z"/>
				<path d="M762.384,202.965c35.523,0,64.317-28.797,64.317-64.322c0-35.523-28.794-64.323-64.317-64.323c-35.527,0-64.323,28.8-64.323,64.323C698.061,174.168,726.856,202.965,762.384,202.965z"/>
				<path d="M535.794,650.926c-69.668,0-126.348-56.68-126.348-126.348c0-26.256,8.056-50.66,21.817-70.887l-50.196-50.195c-26.155,33.377-41.791,75.393-41.791,121.082c0,108.535,87.983,196.517,196.518,196.517c45.691,0,87.703-15.636,121.079-41.792l-50.195-50.193C586.452,642.867,562.048,650.926,535.794,650.926z"/>
			</svg>
		</button>
		</div><!-- /.oawp-toggle-wrap -->

		<!-- Side panel -->
		<div
			id="oawp-panel"
			role="dialog"
			aria-modal="false"
			aria-label="<?php esc_attr_e( 'Accessibility settings', 'curbcut-wp' ); ?>"
			hidden
		>

			<!-- Panel header -->
			<div id="oawp-panel-header">
				<div class="oawp-header-icon" aria-hidden="true">
					<!-- Accessible Icon Project — accessibleicon.org — Public Domain -->
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="190 -75 800 950" width="22" height="22" fill="currentColor">
						<path d="M833.556,367.574c-7.753-7.955-18.586-12.155-29.656-11.549l-133.981,7.458l73.733-83.975c10.504-11.962,13.505-27.908,9.444-42.157c-2.143-9.764-8.056-18.648-17.14-24.324c-0.279-0.199-176.247-102.423-176.247-102.423c-14.369-8.347-32.475-6.508-44.875,4.552l-85.958,76.676c-15.837,14.126-17.224,38.416-3.097,54.254c14.128,15.836,38.419,17.227,54.255,3.096l65.168-58.131l53.874,31.285l-95.096,108.305c-39.433,6.431-74.913,24.602-102.765,50.801l49.66,49.66c22.449-20.412,52.256-32.871,84.918-32.871c69.667,0,126.346,56.68,126.346,126.348c0,32.662-12.459,62.467-32.869,84.916l49.657,49.66c33.08-35.166,53.382-82.484,53.382-134.576c0-31.035-7.205-60.384-20.016-86.482l51.861-2.889l-12.616,154.75c-1.725,21.152,14.027,39.695,35.18,41.422c1.059,0.086,2.116,0.127,3.163,0.127c19.806,0,36.621-15.219,38.257-35.306l16.193-198.685C845.235,386.445,841.305,375.527,833.556,367.574z"/>
						<path d="M762.384,202.965c35.523,0,64.317-28.797,64.317-64.322c0-35.523-28.794-64.323-64.317-64.323c-35.527,0-64.323,28.8-64.323,64.323C698.061,174.168,726.856,202.965,762.384,202.965z"/>
						<path d="M535.794,650.926c-69.668,0-126.348-56.68-126.348-126.348c0-26.256,8.056-50.66,21.817-70.887l-50.196-50.195c-26.155,33.377-41.791,75.393-41.791,121.082c0,108.535,87.983,196.517,196.518,196.517c45.691,0,87.703-15.636,121.079-41.792l-50.195-50.193C586.452,642.867,562.048,650.926,535.794,650.926z"/>
					</svg>
				</div>
				<div class="oawp-header-text">
					<span id="oawp-panel-title"><?php esc_html_e( 'Accessibility', 'curbcut-wp' ); ?></span>
				</div>
				<div class="oawp-header-actions">
					<button
						id="oawp-reset"
						class="oawp-header-btn"
						title="<?php esc_attr_e( 'Reset all settings', 'curbcut-wp' ); ?>"
						aria-label="<?php esc_attr_e( 'Reset all accessibility settings', 'curbcut-wp' ); ?>"
					>&#8635;</button>
					<button
						id="oawp-close"
						class="oawp-header-btn"
						aria-label="<?php esc_attr_e( 'Close accessibility menu', 'curbcut-wp' ); ?>"
					>&#10005;</button>
				</div>
			</div>

			<!-- Panel body -->
			<div id="oawp-panel-body">

				<!-- Quick Profiles -->
				<div id="oawp-profiles">
					<div class="oawp-profiles-header">
						<span class="oawp-section-dot" aria-hidden="true"></span>
						<span class="oawp-profiles-label"><?php esc_html_e( 'Quick Profiles', 'curbcut-wp' ); ?></span>
					</div>
					<div class="oawp-profiles-grid" role="group" aria-label="<?php esc_attr_e( 'Accessibility profiles', 'curbcut-wp' ); ?>">
						<button class="oawp-profile-btn" data-profile="dyslexia" aria-pressed="false">
							<span class="oawp-profile-icon" aria-hidden="true">&#128214;</span>
							<span class="oawp-profile-label"><?php esc_html_e( 'Dyslexia', 'curbcut-wp' ); ?></span>
						</button>
						<button class="oawp-profile-btn" data-profile="lowVision" aria-pressed="false">
							<span class="oawp-profile-icon" aria-hidden="true">&#128065;</span>
							<span class="oawp-profile-label"><?php esc_html_e( 'Low Vision', 'curbcut-wp' ); ?></span>
						</button>
						<button class="oawp-profile-btn" data-profile="seizureSafe" aria-pressed="false">
							<span class="oawp-profile-icon" aria-hidden="true">&#9889;</span>
							<span class="oawp-profile-label"><?php esc_html_e( 'Seizure Safe', 'curbcut-wp' ); ?></span>
						</button>
						<button class="oawp-profile-btn" data-profile="focus" aria-pressed="false">
							<span class="oawp-profile-icon" aria-hidden="true">&#127919;</span>
							<span class="oawp-profile-label"><?php esc_html_e( 'ADHD / Focus', 'curbcut-wp' ); ?></span>
						</button>
					</div>
				</div>

				<!-- ── Section 1: Motion & Seizure Safety ────────────────── -->
				<section class="oawp-section" aria-labelledby="oawp-sec-motion">
					<div class="oawp-section-header">
						<span class="oawp-section-dot" aria-hidden="true"></span>
						<h3 id="oawp-sec-motion" class="oawp-section-title"><?php esc_html_e( 'Motion & Seizure Safety', 'curbcut-wp' ); ?></h3>
					</div>
					<?php oawp_toggle_btn( 'seizureSafe',    '&#9889;',        __( 'Seizure Safe Mode',   'curbcut-wp' ), __( 'Stops flashing animations and motion that may trigger seizures.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'stopAnimations', '&#9646;&#9646;', __( 'Stop All Animations', 'curbcut-wp' ), __( 'Freezes CSS animations, transitions, and auto-playing content.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'pauseVideos',    '&#9654;',        __( 'Pause / Hide Videos', 'curbcut-wp' ), __( 'Hides all video content including YouTube and Vimeo embeds.', 'curbcut-wp' ) ); ?>
				</section>

				<!-- ── Section 2: Visual Customization ───────────────────── -->
				<section class="oawp-section" aria-labelledby="oawp-sec-visual">
					<div class="oawp-section-header">
						<span class="oawp-section-dot" aria-hidden="true"></span>
						<h3 id="oawp-sec-visual" class="oawp-section-title"><?php esc_html_e( 'Visual Customization', 'curbcut-wp' ); ?></h3>
					</div>

					<div class="oawp-control oawp-slider-control">
						<div class="oawp-slider-label">
							<label for="oawp-font-size"><?php esc_html_e( 'Text Size', 'curbcut-wp' ); ?></label>
							<span id="oawp-font-size-val" class="oawp-slider-val" aria-live="polite">100%</span>
						</div>
						<input type="range" id="oawp-font-size" class="oawp-slider" data-feature="fontSize"
							min="80" max="200" step="10" value="100"
							aria-valuemin="80" aria-valuemax="200" aria-valuenow="100" aria-valuetext="100%">
					</div>

					<div class="oawp-control oawp-slider-control">
						<div class="oawp-slider-label">
							<label for="oawp-zoom"><?php esc_html_e( 'Page Zoom', 'curbcut-wp' ); ?></label>
							<span id="oawp-zoom-val" class="oawp-slider-val" aria-live="polite">100%</span>
						</div>
						<input type="range" id="oawp-zoom" class="oawp-slider" data-feature="zoom"
							min="50" max="200" step="10" value="100"
							aria-valuemin="50" aria-valuemax="200" aria-valuenow="100" aria-valuetext="100%">
					</div>

					<div class="oawp-control oawp-slider-control">
						<div class="oawp-slider-label">
							<label for="oawp-line-height"><?php esc_html_e( 'Line Spacing', 'curbcut-wp' ); ?></label>
							<span id="oawp-line-height-val" class="oawp-slider-val" aria-live="polite">1.5</span>
						</div>
						<input type="range" id="oawp-line-height" class="oawp-slider" data-feature="lineHeight"
							min="1" max="3" step="0.25" value="1.5"
							aria-valuemin="1" aria-valuemax="3" aria-valuenow="1.5" aria-valuetext="1.5">
					</div>

					<div class="oawp-control oawp-slider-control">
						<div class="oawp-slider-label">
							<label for="oawp-letter-spacing"><?php esc_html_e( 'Letter Spacing', 'curbcut-wp' ); ?></label>
							<span id="oawp-letter-spacing-val" class="oawp-slider-val" aria-live="polite">0px</span>
						</div>
						<input type="range" id="oawp-letter-spacing" class="oawp-slider" data-feature="letterSpacing"
							min="0" max="10" step="1" value="0"
							aria-valuemin="0" aria-valuemax="10" aria-valuenow="0" aria-valuetext="0px">
					</div>

					<?php oawp_toggle_btn( 'dyslexicFont',  '&#128196;', __( 'Dyslexia-Friendly Font', 'curbcut-wp' ), __( 'Switches to OpenDyslexic, designed to improve text readability.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'readingGuide',  '&#8212;',   __( 'Reading Guide',          'curbcut-wp' ), __( 'Adds a highlight bar that follows your cursor line by line.', 'curbcut-wp' ) ); ?>
				</section>

				<!-- ── Section 3: Contrast & Color ───────────────────────── -->
				<section class="oawp-section" aria-labelledby="oawp-sec-contrast">
					<div class="oawp-section-header">
						<span class="oawp-section-dot" aria-hidden="true"></span>
						<h3 id="oawp-sec-contrast" class="oawp-section-title"><?php esc_html_e( 'Contrast & Color', 'curbcut-wp' ); ?></h3>
					</div>
					<?php oawp_toggle_btn( 'highContrastDark',  '&#9790;', __( 'High Contrast (Dark)',  'curbcut-wp' ), __( 'Black background with yellow text for maximum contrast.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'highContrastLight', '&#9788;', __( 'High Contrast (Light)', 'curbcut-wp' ), __( 'White background with pure black text for strong contrast.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'monochrome',        '&#9680;', __( 'Monochrome',            'curbcut-wp' ), __( 'Removes all color — useful for reducing visual distraction.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'invertColors',      '&#9683;', __( 'Invert Colors',         'curbcut-wp' ), __( 'Reverses all page colors — helpful for light-sensitive users.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'eyeSafe',           '&#127774;', __( 'Eye Safe (Warm)',       'curbcut-wp' ), __( 'Shifts to warm amber tones to reduce eye strain in low light.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'blueLightFilter',   '&#128161;', __( 'Blue Light Filter',    'curbcut-wp' ), __( 'Reduces blue light emission to protect eyes during evening use.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'rainbowText',       '&#127752;', __( 'Rainbow Text',         'curbcut-wp' ), __( 'Adds animated color to text — for fun and positive engagement.', 'curbcut-wp' ) ); ?>
				</section>

				<!-- ── Section 4: Navigation & Motor Aids ────────────────── -->
				<section class="oawp-section" aria-labelledby="oawp-sec-nav">
					<div class="oawp-section-header">
						<span class="oawp-section-dot" aria-hidden="true"></span>
						<h3 id="oawp-sec-nav" class="oawp-section-title"><?php esc_html_e( 'Navigation & Motor Aids', 'curbcut-wp' ); ?></h3>
					</div>
					<?php oawp_toggle_btn( 'focusEnhancer',  '&#9641;',   __( 'Enhanced Focus Ring',   'curbcut-wp' ), __( 'Adds a bold visible outline to the focused element for keyboard users.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'bigCursor',      '&#8630;',   __( 'Large Cursor',          'curbcut-wp' ), __( 'Enlarges the mouse cursor for easier tracking on screen.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'cursorCrosshair','&#10010;',  __( 'Crosshair Cursor',      'curbcut-wp' ), __( 'Changes cursor to a crosshair shape for precise positioning.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'cursorLines',    '&#43;',     __( 'Cursor Lines',          'curbcut-wp' ), __( 'Overlays screen-wide crosshair lines to track your cursor exactly.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'highlightLinks', '&#128279;', __( 'Highlight Links',       'curbcut-wp' ), __( 'Adds bold styling and outlines to all clickable links.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'bigTargets',     '&#8853;',   __( 'Larger Click Targets',  'curbcut-wp' ), __( 'Enlarges buttons and links to meet the 44×44 px minimum touch target.', 'curbcut-wp' ) ); ?>
				</section>

				<!-- Section 5: Reading Aids -->
				<section class="oawp-section" aria-labelledby="oawp-sec-reading">
					<div class="oawp-section-header">
						<span class="oawp-section-dot" aria-hidden="true"></span>
						<h3 id="oawp-sec-reading" class="oawp-section-title"><?php esc_html_e( 'Reading Aids', 'curbcut-wp' ); ?></h3>
					</div>
					<?php oawp_toggle_btn( 'readAloud',   '&#128266;', __( 'Read Aloud',    'curbcut-wp' ), __( 'Click any text to have it read aloud using your device\'s speech engine.', 'curbcut-wp' ) ); ?>
					<?php oawp_toggle_btn( 'readingMode', '&#128214;', __( 'Reading Mode',  'curbcut-wp' ), __( 'Strips page chrome and shows just the article in a clean, focused view.', 'curbcut-wp' ) ); ?>
				</section>

			</div><!-- /#oawp-panel-body -->

			<div id="oawp-panel-footer">
				<span class="oawp-wcag-badge">WCAG 2.1 AA</span>
				<span class="oawp-wcag-badge">CA.gov</span>
			</div>

		</div><!-- /#oawp-panel -->

		<!-- Reading guide (follows cursor) -->
		<div id="oawp-reading-guide" aria-hidden="true" hidden></div>

	</div><!-- /#oawp-root -->

	<!-- ── Easter Egg: Windows XP elements (hidden until Konami code) ──────── -->
	<div id="oawp-xp-taskbar" aria-hidden="true">
		<div id="oawp-xp-tray">
			<span id="oawp-xp-clock">12:00 PM</span>
		</div>
	</div>

	<div id="oawp-xp-balloon" role="status" aria-live="polite" aria-atomic="true">
		<strong>&#127987;&#65039; Curbcut WP</strong>
		<?php esc_html_e( 'Windows XP mode activated. Thank you for using our product.', 'curbcut-wp' ); ?>
	</div>
	<?php
}
add_action( 'wp_footer', 'oawp_render_overlay' );

/**
 * Inject "Skip to Content" link just after <body> opens.
 */
function oawp_skip_link() {
	$options   = oawp_get_options();
	$target_id = ! empty( $options['skip_target_id'] ) ? $options['skip_target_id'] : 'content';
	?>
	<a id="oawp-skip-link" href="#<?php echo esc_attr( $target_id ); ?>" class="oawp-skip-link">
		<?php esc_html_e( 'Skip to main content', 'curbcut-wp' ); ?>
	</a>
	<?php
}
add_action( 'wp_body_open', 'oawp_skip_link' );
