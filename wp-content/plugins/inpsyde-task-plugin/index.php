<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
Plugin Name: Inpsyde Task Plugin
Description: Inpsyde Task for WordPress developer.
Version: 1.0
Author: Mohsin Hassan
License: GPLv2 or later
*/

require_once __DIR__ . './InpsydeTaskPlugin.php';

if (! defined('INPSYDE_PHPUNIT')) {
    $inpsydePluginObj =  \InpsydePlugins\InpsydeTaskPlugin::instance();
    $inpsydePluginObj->init();
}
