<?php
/**
 * Plugin Name:       Age When Published
 * Description:       Provides a block that shows an age as at the post's publish date, based on a date of birth defined on the Settings page.
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           2.0
 * Author:            Andrew Viney
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       age-when-published
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 */
function create_block_age_when_published_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_age_when_published_block_init' );


/**
 * Add a settings page for the plugin.
 */
function age_when_published_register_settings_page() {
	add_options_page(
		'Age When Published Settings',
		'Age When Published',
		'manage_options',
		'age-when-published',
		'age_when_published_render_settings_page'
	);
}
add_action( 'admin_menu', 'age_when_published_register_settings_page' );

/**
 * Render the settings page.
 */
function age_when_published_render_settings_page() {
	?>
	<div class="wrap">
		<h1>Age When Published Settings</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'age_when_published_settings' );
			do_settings_sections( 'age-when-published' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Register settings for the plugin.
 */
function age_when_published_register_settings() {
	register_setting( 'age_when_published_settings', 'age_when_published_dob' );

	add_settings_section(
		'age_when_published_section',
		'Settings',
		null,
		'age-when-published'
	);

	add_settings_field(
		'age_when_published_dob',
		'Date of Birth',
		'age_when_published_dob_field_callback',
		'age-when-published',
		'age_when_published_section'
	);
}
add_action( 'admin_init', 'age_when_published_register_settings' );

/**
 * Render the date of birth field.
 */
function age_when_published_dob_field_callback() {
	$dob = get_option( 'age_when_published_dob', '' );
	echo '<input type="date" id="age_when_published_dob" name="age_when_published_dob" value="' . esc_attr( $dob ) . '">';
}

/**
 * Calculate the age based on the post date and date of birth.
 *
 * @param string $post_date The post date.
 * @return string The calculated age.
 */
function calculate_age_when_published( $post_date ) {
	$dob = get_option( 'age_when_published_dob', '' );

	if ( ! $dob ) {
		return 'Date of birth not set';
	}

	$post_date = new DateTime( $post_date );
	$dob       = new DateTime( $dob );
	$interval  = $dob->diff( $post_date );

	$days   = $interval->days;
	$weeks  = floor( $days / 7 );
	$months = $interval->m + ( $interval->y * 12 );
	$years  = $interval->y;

	$age = '';

	if ( $days < 14 ) {
		$age = sprintf( '%d %s', $days, _n( 'day', 'days', $days, 'age-when-published' ) );
	} elseif ( $days < 56 ) {
		$age = sprintf( '%d %s', $weeks, _n( 'week', 'weeks', $weeks, 'age-when-published' ) );
	} elseif ( $days < 365 ) {
		$age = sprintf( '%d %s', $months, _n( 'month', 'months', $months, 'age-when-published' ) );
	} elseif ( $years < 3 ) {
		$age = sprintf(
			'%d %s, %d %s',
			$years,
			_n( 'year', 'years', $years, 'age-when-published' ),
			$interval->m,
			_n( 'month', 'months', $interval->m, 'age-when-published' )
		);
	} else {
		$age = sprintf( '%d %s', $years, _n( 'year', 'years', $years, 'age-when-published' ) );
	}

	if ( $post_date < $dob ) {
		$age .= ' to go';
	} else {
		$age .= ' old';
	}

	return $age;
}
