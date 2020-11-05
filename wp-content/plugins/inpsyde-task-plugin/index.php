<?php
/*
Plugin Name: Inpsyde Task Plugin
Description:
Version: 1.0
Author: Mohsin Hassan
License: GPLv2 or later
*/

require_once __DIR__ . './InpsydeTaskPlugin.php';

if(! defined('INPSYDE_PHPUNIT')  ) {
    $inpsydePluginObj =  \InpsydePlugins\InpsydeTaskPlugin::instance();
    $inpsydePluginObj->init();
}
