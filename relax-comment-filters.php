<?php
/**
 * Plugin Name: Relax Comment Filters
 * Description: Forces comments to go through the more liberal post HTML filters, rather than the restrictive comment filters.
 * Version:     1.0.0
 * Author:      required
 * Author URI:  https://required.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Required\RelaxCommentFilters
 */

namespace Required\RelaxCommentFilters;

// Default KSES runs just prior, on the same priority.
add_action( 'init', __NAMESPACE__ . '\kses_init' );
add_action( 'set_current_user', __NAMESPACE__ . '\kses_init' );

/**
 * Initializes filters if current user can't unfiltered HTML.
 *
 * @since 1.0.0
 */
function kses_init() {
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		kses_init_filters();
	}
}

/**
 * Changes KSES filter for comment conntent.
 *
 * @since 1.0.0
 */
function kses_init_filters() {
	remove_filter( 'pre_comment_content', 'wp_filter_kses' );
	add_filter( 'pre_comment_content', 'wp_filter_post_kses' );
}
