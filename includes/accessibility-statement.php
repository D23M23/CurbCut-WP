<?php
/**
 * Accessibility Statement shortcode for Curb Cut.
 *
 * Usage: [curb_cut_statement]
 * Optional attributes:
 *   site_name     — defaults to get_bloginfo('name')
 *   contact_email — shown in the Contact section
 *   contact_url   — URL for the contact form / page
 *   updated       — date string, e.g. "April 2026"
 *   lang          — "en" (default) or "es" for Spanish
 *
 * @package CurbcutWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the shortcode.
 */
function oawp_register_statement_shortcode() {
	add_shortcode( 'curb_cut_statement', 'oawp_render_statement' );
}
add_action( 'init', 'oawp_register_statement_shortcode' );

/**
 * Render the accessibility statement.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function oawp_render_statement( $atts ) {
	$atts = shortcode_atts(
		[
			'site_name'     => get_bloginfo( 'name' ),
			'contact_email' => '',
			'contact_url'   => '',
			'updated'       => date( 'F Y' ),
			'lang'          => 'en',
		],
		$atts,
		'curb_cut_statement'
	);

	$site        = esc_html( $atts['site_name'] );
	$email       = sanitize_email( $atts['contact_email'] );
	$contact_url = esc_url( $atts['contact_url'] );
	$updated     = esc_html( $atts['updated'] );
	$spanish     = ( 'es' === $atts['lang'] );

	ob_start();
	?>
	<div class="curb-cut-statement" lang="<?php echo $spanish ? 'es' : 'en'; ?>">

	<?php if ( $spanish ) : ?>

		<h2>Declaración de Accesibilidad</h2>

		<h3>Nuestro Compromiso</h3>
		<p><?php echo esc_html( $site ); ?> está comprometido a garantizar la accesibilidad digital para personas con discapacidades. Trabajamos continuamente para mejorar la experiencia de usuario y cumplir con los estándares de accesibilidad web.</p>

		<h3>Estándares que Seguimos</h3>
		<p>Buscamos cumplir con las <strong>Pautas de Accesibilidad para el Contenido Web (WCAG) 2.1 en el Nivel AA</strong>, así como con las directrices de accesibilidad de <strong>CA.gov</strong>.</p>

		<h3>Cómo Ayuda Curb Cut</h3>
		<p>Este sitio utiliza el complemento de accesibilidad <strong>Curb Cut</strong>, que ofrece ajustes en tiempo real del lado del cliente. Toda la configuración se guarda localmente en su navegador. No se recopilan ni transmiten datos personales.</p>
		<p>Las funciones disponibles incluyen:</p>
		<ul>
			<li><strong>Seguridad ante convulsiones y movimiento</strong> — modo seguro para epilepsia, detención de animaciones y ocultación de vídeos</li>
			<li><strong>Personalización visual</strong> — tamaño de texto, zoom, interlineado, espaciado entre letras y fuente OpenDyslexic</li>
			<li><strong>Color y contraste</strong> — modos de alto contraste oscuro/claro, escala de grises, inversión de colores, tonos cálidos seguros para los ojos y filtro de luz azul</li>
			<li><strong>Ayudas de navegación</strong> — enlace "Saltar al contenido", anillo de enfoque mejorado, cursor grande, cursor en cruz, líneas de cursor y resaltado de enlaces</li>
			<li><strong>Ayudas de lectura</strong> — guía de lectura, modo de lectura y lectura en voz alta mediante el motor de voz del navegador</li>
			<li><strong>Perfiles rápidos</strong> — Dislexia, Baja visión, Modo seguro ante convulsiones y TDAH/Enfoque</li>
		</ul>

		<h3>Navegación por Teclado</h3>
		<p>Este sitio admite navegación completa por teclado. Use <kbd>Tab</kbd> para moverse entre elementos, <kbd>Enter</kbd> o <kbd>Espacio</kbd> para activar controles y <kbd>Escape</kbd> para cerrar menús. Un enlace "Saltar al contenido principal" está disponible como primer elemento en cada página.</p>

		<h3>Lectores de Pantalla</h3>
		<p>Este sitio usa HTML semántico y atributos ARIA para ser compatible con lectores de pantalla, incluidos JAWS, NVDA, VoiceOver y TalkBack.</p>

		<h3>Compatibilidad con Navegadores</h3>
		<p>Curb Cut es compatible con Chrome, Firefox, Safari, Edge y Opera.</p>

		<h3>Limitaciones Conocidas</h3>
		<p>A pesar de nuestros esfuerzos, algunos contenidos de terceros (vídeos integrados, mapas y widgets de redes sociales) pueden no cumplir plenamente con WCAG 2.1 AA. Trabajamos continuamente para identificar y mejorar alternativas accesibles.</p>

		<h3>Comentarios y Contacto</h3>
		<?php oawp_statement_contact( $email, $contact_url, true ); ?>
		<p><small>Declaración actualizada: <?php echo esc_html( $updated ); ?></small></p>

	<?php else : ?>

		<h2>Accessibility Statement</h2>

		<h3>Our Commitment</h3>
		<p><?php echo esc_html( $site ); ?> is committed to ensuring digital accessibility for people with disabilities. We continually work to improve the user experience for everyone and to meet internationally recognised accessibility standards.</p>

		<h3>Standards We Follow</h3>
		<p>We aim to conform to the <strong>Web Content Accessibility Guidelines (WCAG) 2.1 at Level AA</strong>, as well as <strong>CA.gov</strong> accessibility guidelines. These guidelines explain how to make web content accessible to people with a wide range of disabilities, including visual, auditory, physical, speech, cognitive, language, learning, and neurological disabilities.</p>

		<h3>How Curb Cut Helps</h3>
		<p>This website uses the <strong>Curb Cut</strong> accessibility plugin, which provides real-time, client-side adjustments to the page layout and appearance. All settings are stored locally in your browser (LocalStorage). <strong>No personal data is ever collected or transmitted.</strong></p>
		<p>Features available to you include:</p>
		<ul>
			<li><strong>Motion &amp; Seizure Safety</strong> — Seizure-safe mode, stop all animations, and hide videos (including YouTube and Vimeo embeds)</li>
			<li><strong>Visual Customization</strong> — Text size, page zoom, line spacing, letter spacing, and dyslexia-friendly font (OpenDyslexic)</li>
			<li><strong>Color &amp; Contrast</strong> — High contrast dark/light, monochrome, color inversion, eye-safe warm tones, and blue light filter</li>
			<li><strong>Navigation &amp; Motor Aids</strong> — Skip to content link, enhanced focus ring, large cursor, crosshair cursor, cursor crosshair lines, and highlight links</li>
			<li><strong>Reading Aids</strong> — Reading guide bar, distraction-free reading mode, and read-aloud text-to-speech using your browser's built-in speech engine</li>
			<li><strong>Quick Profiles</strong> — One-tap profiles for Dyslexia, Low Vision, Seizure Safe, and ADHD / Focus</li>
		</ul>

		<h3>Keyboard Navigation</h3>
		<p>This website supports full keyboard navigation. You can use <kbd>Tab</kbd> to move between interactive elements, <kbd>Enter</kbd> or <kbd>Space</kbd> to activate buttons and links, <kbd>Escape</kbd> to close menus, and arrow keys to adjust sliders. A <em>Skip to main content</em> link is available as the first element on every page.</p>

		<h3>Screen Reader Support</h3>
		<p>This website uses semantic HTML and ARIA (Accessible Rich Internet Applications) attributes to support screen readers including <strong>JAWS</strong>, <strong>NVDA</strong>, <strong>VoiceOver</strong> (macOS &amp; iOS), and <strong>TalkBack</strong> (Android).</p>

		<h3>Browser Compatibility</h3>
		<p>Curb Cut supports all modern browsers, including Chrome, Firefox, Safari, Edge, and Opera on both Windows and macOS.</p>

		<h3>Known Limitations</h3>
		<p>Despite our best efforts, some third-party content — including embedded videos, maps, and social media widgets — may not fully conform to WCAG 2.1 AA. We are continually working to identify and improve accessible alternatives for these areas.</p>

		<h3>Feedback &amp; Contact</h3>
		<?php oawp_statement_contact( $email, $contact_url, false ); ?>
		<p><small>This statement was last updated: <?php echo esc_html( $updated ); ?></small></p>

	<?php endif; ?>

	</div><!-- .curb-cut-statement -->
	<?php
	return ob_get_clean();
}

/**
 * Output the contact block inside the statement.
 *
 * @param string $email   Contact email address (may be empty).
 * @param string $url     Contact page URL (may be empty).
 * @param bool   $spanish Use Spanish copy.
 */
function oawp_statement_contact( $email, $url, $spanish ) {
	if ( $spanish ) {
		echo '<p>Agradecemos sus comentarios sobre la accesibilidad de nuestro sitio web. Si encuentra algún obstáculo o tiene sugerencias de mejora, póngase en contacto con nosotros:</p>';
	} else {
		echo '<p>We welcome feedback on the accessibility of our website. If you experience any barriers or have suggestions for improvement, please contact us:</p>';
	}

	if ( $email ) {
		echo '<p><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
	}
	if ( $url ) {
		$label = $spanish ? 'Formulario de contacto' : 'Contact form';
		echo '<p><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></p>';
	}
	if ( ! $email && ! $url ) {
		$placeholder = $spanish
			? '<em>[Añada aquí su información de contacto]</em>'
			: '<em>[Add your contact information here]</em>';
		echo '<p>' . $placeholder . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
