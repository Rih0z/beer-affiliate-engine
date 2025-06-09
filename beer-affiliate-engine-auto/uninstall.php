<?php
/**
 * Uninstall Beer Affiliate Engine
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('beer_affiliate_programs');
delete_option('beer_affiliate_version');