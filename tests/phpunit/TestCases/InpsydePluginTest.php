<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace InpsydePHPUnit\TestCases;

use InpsydePlugins\InpsydeTaskPlugin;
use \Brain\Monkey\Functions;
use \Brain\Monkey\Filters;
use InpsydePHPUnit\Inc\InpsydePluginTestCase as TestCase;

class InpsydePluginTest extends TestCase
{

    /**
     * Test the plugin hooks init function
     */
    public function testInitHooks()
    {
        //Setup
            // We expect plugin_dir_path to be called during bootstrap
            Functions\expect('plugin_dir_path')
                ->once()
                ->withAnyArgs()
                ->andReturn(PLUGIN_ABSPATH);

            // We expect plugin_dir_url to be called
            Functions\expect('plugin_dir_url')
                ->once()
                ->withAnyArgs()
                ->andReturn('http://inpsyde.local/wp-content/inpsyde-task-plugin');

            Functions\expect('register_activation_hook')
                ->once()
                ->andReturnNull();

            Filters\expectAdded('show_admin_bar')->with(\Mockery::type('Closure'));

        //Fire
        $inpsydeTaskPlugin = InpsydeTaskPlugin::instance();
        $inpsydeTaskPlugin->init();

        //Tests
        $this->assertNotFalse(
            has_action(
                'rest_api_init',
                [$inpsydeTaskPlugin, 'registerRestRoute']
            )
        );
        $this->assertNotFalse(
            has_action(
                'rest_api_init',
                [$inpsydeTaskPlugin, 'registerRestRoute']
            )
        );
        $this->assertNotFalse(
            has_action(
                'wp_enqueue_scripts',
                [$inpsydeTaskPlugin, 'enqueueResources']
            )
        );
        $this->assertNotFalse(
            has_filter(
                'query_vars',
                [$inpsydeTaskPlugin, 'inpsydeAddQueryVare']
            )
        );
        $this->assertNotFalse(
            has_filter(
                'template_include',
                [$inpsydeTaskPlugin, 'inpsydeLoadTemplate']
            )
        );
    }

    /**
     * Test the Query Args for endpoint is added successfully
     */
    public function testQueryArgsAdded()
    {
        //Setup
        $returnData = [
            'inpsyde',
            'inpsyde_user',
        ];

        //Fire and Test
        $inpsydeTaskPlugin = InpsydeTaskPlugin::instance();
        $this->assertEquals(
            $returnData,
            $inpsydeTaskPlugin->inpsydeAddQueryVare([])
        );
    }

    /**
     * Test to retrieved the data from cache all users
     */
    public function testDataRetrievedFromCacheForAllUsers()
    {
        // Setup - prepare data and mock functions
        $data = $this->prepareUsersDataSupposedToReceiveFromCache();
        $this->mockWpFunctionsUsedInRetrievingDataFromCache($data);

        //Test
        $this->expectOutputString(json_encode($data));

        //Fire
        $inpsydeTaskPlugin = InpsydeTaskPlugin::instance();
        $inpsydeTaskPlugin->cachedData();
    }

    /**
     * Test to retrieved the data from cache single users
     */
    public function testDataRetrievedFromCacheForSingleUser()
    {
        // Setup - prepare data and mock functions
        $data = $this->prepareUsersDataSupposedToReceiveFromCache();
        $this->mockWpFunctionsUsedInRetrievingDataFromCache($data);

        //Test
        $this->expectOutputString(json_encode($data[4]));

        //Fire
        $inpsydeTaskPlugin = InpsydeTaskPlugin::instance();
        $inpsydeTaskPlugin->cachedData(5);
    }

    /**
     * Mock WP function used to get users data from cache
     * @param array $data
     */
    public function mockWpFunctionsUsedInRetrievingDataFromCache(array  $data)
    {
        // We expect get_transient to be called and return Data Stored from API
        Functions\expect('get_transient')
            ->once()
            ->withAnyArgs()
            ->andReturn($data);

        // We expect wp_send_json_success to be called and return the user data
        Functions\expect('wp_send_json_success')
            ->zeroOrMoreTimes()
            ->withAnyArgs()
            ->andReturnUsing(function (array $arg1) {
                echo json_encode($arg1);
            });

        // We expect wp_send_json_error to be called
        Functions\expect('wp_send_json_error')
            ->zeroOrMoreTimes()
            ->withAnyArgs()
            ->andReturnUsing(function (array $arg1) {
                echo json_encode($arg1);
            });
    }

    /**
     * Used to mock the get_transient function
     * @return array
     */
    public function prepareUsersDataSupposedToReceiveFromCache(): array
    {
        $data = file_get_contents(__DIR__ .'/users_data.json');
        return json_decode($data);
    }
}
