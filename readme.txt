=== Multisite Unfiltered HTML ===
Contributors: zodiac1978
Tags: multisite, capabilities, unfiltered html, roles, users
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows super admins to grant the unfiltered_html capability to specific users or roles.

== Description ==

In a multisite network, WordPress denies the unfiltered_html capability to all
users who are not super admins. This plugin lets super admins make controlled
exceptions:

* Grant access to individual users across the network from their user profiles.
* Grant access by role for each site under Settings > Unfiltered HTML.
* Show a notice and disable the profile checkbox when a role already grants the
  capability.
* Always respect DISALLOW_UNFILTERED_HTML.

The role-based setting is intentionally site-specific because WordPress
Multisite defines roles separately for each site.

== Installation ==

1. Upload the `multisite-unfiltered-html` folder to `/wp-content/plugins/`.
2. Preferably network-activate the plugin.
3. Grant access to roles under Settings > Unfiltered HTML, or edit individual
   users' profiles as a super admin.

== Security ==

The unfiltered_html capability allows users to save potentially unsafe HTML,
including scripts and iframes. Grant it only to trusted users.

== Changelog ==

= 1.0.0 =
* Initial release.
