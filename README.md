# Multisite Unfiltered HTML

[![Build status](https://github.com/zodiac1978/multisite-unfiltered-html/actions/workflows/ci.yml/badge.svg)](https://github.com/zodiac1978/multisite-unfiltered-html/actions/workflows/ci.yml)

Allows super admins to grant the `unfiltered_html` capability to selected users or roles in a WordPress multisite.

## What does Multisite Unfiltered HTML do?

WordPress denies the `unfiltered_html` capability to users who are not super admins on a multisite network. This plugin lets super admins make controlled exceptions.

Individual grants are configured in a user's profile and apply across the network. Role-based grants are configured separately for each site under **Settings > Unfiltered HTML**, because WordPress Multisite defines roles per site.

When one of a user's roles already grants the capability, the profile screen explains this and disables the individual checkbox. The plugin always respects the `DISALLOW_UNFILTERED_HTML` constant.

## Security

The `unfiltered_html` capability allows users to save potentially unsafe HTML, including scripts and iframes. Grant it only to trusted users.

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- A WordPress multisite network

