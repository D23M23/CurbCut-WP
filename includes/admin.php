<?php
/**
 * Admin settings page for Curbcut WP.
 *
 * @package CurbcutWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Option helpers ────────────────────────────────────────────────────────────

/**
 * Returns plugin options merged with defaults.
 *
 * @return array
 */
function oawp_get_options() {
	$defaults = [
		'enabled'        => true,
		'button_position' => 'bottom-right',
		'accent_color'   => '#1a73e8',
		'skip_target_id' => 'content',
	];
	$saved = get_option( 'oawp_options', [] );
	return wp_parse_args( $saved, $defaults );
}

// ── Admin registration ────────────────────────────────────────────────────────

/**
 * Register the admin menu item.
 */
function oawp_admin_menu() {
	add_options_page(
		__( 'Curb Cut', 'curb-cut' ),
		__( 'Accessibility', 'curb-cut' ),
		'manage_options',
		'curbcut-wp',
		'oawp_admin_page'
	);
}
add_action( 'admin_menu', 'oawp_admin_menu' );

/**
 * Register settings and fields.
 */
function oawp_register_settings() {
	register_setting(
		'oawp_options_group',
		'oawp_options',
		[ 'sanitize_callback' => 'oawp_sanitize_options' ]
	);

	add_settings_section(
		'oawp_general',
		__( 'General Settings', 'curbcut-wp' ),
		'__return_false',
		'curbcut-wp'
	);

	add_settings_field(
		'oawp_enabled',
		__( 'Enable Plugin', 'curbcut-wp' ),
		'oawp_field_enabled',
		'curbcut-wp',
		'oawp_general'
	);

	add_settings_field(
		'oawp_button_position',
		__( 'Button Position', 'curbcut-wp' ),
		'oawp_field_button_position',
		'curbcut-wp',
		'oawp_general'
	);

	add_settings_field(
		'oawp_accent_color',
		__( 'Accent Color', 'curbcut-wp' ),
		'oawp_field_accent_color',
		'curbcut-wp',
		'oawp_general'
	);

	add_settings_field(
		'oawp_skip_target_id',
		__( '"Skip to Content" Target ID', 'curbcut-wp' ),
		'oawp_field_skip_target',
		'curbcut-wp',
		'oawp_general'
	);
}
add_action( 'admin_init', 'oawp_register_settings' );

// ── Field renderers ───────────────────────────────────────────────────────────

function oawp_field_enabled() {
	$options = oawp_get_options();
	echo '<label><input type="checkbox" name="oawp_options[enabled]" value="1" ' . checked( ! empty( $options['enabled'] ), true, false ) . '> ';
	esc_html_e( 'Show the accessibility overlay on the front end', 'curbcut-wp' );
	echo '</label>';
}

function oawp_field_button_position() {
	$options   = oawp_get_options();
	$current   = $options['button_position'];
	$positions = [
		'bottom-right' => __( 'Bottom Right (default)', 'curbcut-wp' ),
		'bottom-left'  => __( 'Bottom Left', 'curbcut-wp' ),
		'top-right'    => __( 'Top Right', 'curbcut-wp' ),
		'top-left'     => __( 'Top Left', 'curbcut-wp' ),
	];
	echo '<select name="oawp_options[button_position]">';
	foreach ( $positions as $value => $label ) {
		$selected = selected( $current, $value, false );
		// Do NOT wrap $selected in esc_attr() — selected() already returns a safe
		// attribute string (' selected="selected"'). esc_attr() would encode the
		// inner quotes and produce malformed HTML.
		echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}

function oawp_field_accent_color() {
	$options = oawp_get_options();
	$color   = esc_attr( $options['accent_color'] );
	echo '<input type="color" name="oawp_options[accent_color]" value="' . $color . '">';
	echo '<p class="description">' . esc_html__( 'Used for the toggle button background.', 'curbcut-wp' ) . '</p>';
}

function oawp_field_skip_target() {
	$options = oawp_get_options();
	$id      = esc_attr( $options['skip_target_id'] );
	echo '<input type="text" name="oawp_options[skip_target_id]" value="' . $id . '" class="regular-text">';
	echo '<p class="description">' . esc_html__( 'The HTML id of your main content element (e.g. "content", "main", "primary").', 'curbcut-wp' ) . '</p>';
}

// ── Sanitization ──────────────────────────────────────────────────────────────

/**
 * Sanitize saved options.
 *
 * @param array $raw Raw POST data.
 * @return array
 */
function oawp_sanitize_options( $raw ) {
	return [
		'enabled'         => ! empty( $raw['enabled'] ),
		'button_position' => in_array( $raw['button_position'] ?? '', [ 'bottom-right', 'bottom-left', 'top-right', 'top-left' ], true )
			? $raw['button_position']
			: 'bottom-right',
		'accent_color'    => sanitize_hex_color( $raw['accent_color'] ?? '#1a73e8' ) ?: '#1a73e8',
		// sanitize_html_class() is for CSS selectors (lowercases, strips uppercase).
		// HTML IDs allow [a-zA-Z0-9_-]; use a targeted regex instead.
		'skip_target_id'  => preg_replace( '/[^a-zA-Z0-9_-]/', '', $raw['skip_target_id'] ?? 'content' ) ?: 'content',
	];
}

// ── Admin page HTML ───────────────────────────────────────────────────────────

/**
 * Render the admin settings page.
 */
function oawp_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Curb Cut — Accessibility Settings', 'curb-cut' ); ?></h1>
		<p><?php esc_html_e( 'Level the web for all users. Configure the front-end accessibility overlay. All features are stored client-side in LocalStorage; no personal data is transmitted.', 'curb-cut' ); ?></p>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'oawp_options_group' );
			do_settings_sections( 'curbcut-wp' );
			submit_button( __( 'Save Settings', 'curbcut-wp' ) );
			?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Compliance Reference', 'curbcut-wp' ); ?></h2>
		<table class="widefat striped" style="max-width:700px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Requirement', 'curbcut-wp' ); ?></th>
					<th><?php esc_html_e( 'Feature', 'curbcut-wp' ); ?></th>
					<th><?php esc_html_e( 'Status', 'curbcut-wp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td>Pause, Stop, Hide (WCAG 2.2.2)</td><td><?php esc_html_e( 'Seizure Safe / Stop Animations', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
				<tr><td>Bypass Blocks (WCAG 2.4.1)</td><td><?php esc_html_e( 'Skip to Content Link', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
				<tr><td>Focus Visible (WCAG 2.4.7)</td><td><?php esc_html_e( 'Enhanced Focus Ring', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
				<tr><td>Contrast (Minimum) (WCAG 1.4.3)</td><td><?php esc_html_e( 'High Contrast Modes', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
				<tr><td>Resize Text (WCAG 1.4.4)</td><td><?php esc_html_e( 'Text Size Slider (up to 200%)', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
				<tr><td>Target Size (WCAG 2.5.5)</td><td><?php esc_html_e( 'Larger Click Targets', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
				<tr><td>Non-Text Contrast (WCAG 1.4.11)</td><td><?php esc_html_e( 'Monochrome / Invert Modes', 'curbcut-wp' ); ?></td><td>&#9989;</td></tr>
			</tbody>
		</table>

		<hr>
		<h2><?php esc_html_e( 'Testing Resources', 'curbcut-wp' ); ?></h2>
		<ul>
			<li><a href="https://wave.webaim.org/" target="_blank" rel="noopener noreferrer">WAVE Accessibility Evaluator</a></li>
			<li><a href="https://www.deque.com/axe/" target="_blank" rel="noopener noreferrer">axe DevTools</a></li>
			<li><a href="https://www.w3.org/WAI/WCAG21/quickref/" target="_blank" rel="noopener noreferrer">WCAG 2.1 Quick Reference</a></li>
		</ul>
	</div>
	<?php
}
