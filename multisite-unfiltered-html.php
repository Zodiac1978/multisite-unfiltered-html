<?php
/**
 * Plugin Name:       Multisite Unfiltered HTML
 * Description:       Grants unfiltered_html to selected users or roles in a WordPress multisite.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Torsten Landsiedel
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       multisite-unfiltered-html
 * Network:           true
 *
 * @package Multisite_Unfiltered_HTML
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-multisite-unfiltered-html.php';

Multisite_Unfiltered_HTML::init();
