<?php declare(strict_types=1); # -*- coding: utf-8 -*-

// Define test environment
const INPSYDE_PHPUNIT = true ;

// Define fake ABSPATH
if (! defined('ABSPATH')) {
    define('ABSPATH', sys_get_temp_dir());
}

// Define fake PLUGIN_ABSPATH
if (! defined('PLUGIN_ABSPATH')) {
    define('PLUGIN_ABSPATH', sys_get_temp_dir() . '/wp-content/plugins/inpsyde-task-plugin');
}
