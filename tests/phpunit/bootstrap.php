<?php

// define test environment
define( 'INPSYDE_PHPUNIT', true );


// define fake ABSPATH
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', sys_get_temp_dir() );
}
// define fake PLUGIN_ABSPATH
if ( ! defined( 'PLUGIN_ABSPATH' ) ) {
    define( 'PLUGIN_ABSPATH', sys_get_temp_dir() . '/wp-content/plugins/inpsyde-task-plugin' );
}

require_once __DIR__ . '/../../vendor/autoload.php';


// Include the class for PluginTestCase
require_once __DIR__ . '/inc/inpsydePluginTestCase.php';


require_once __DIR__ . '/../../wp-content/plugins/inpsyde-task-plugin/InpsydeTaskPlugin.php';

