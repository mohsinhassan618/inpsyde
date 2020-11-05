<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace InpsydePHPUnit\TestCases;

use \Plugin\inpsyde\InpsydeTaskPlugin;
use \Brain\Monkey\Functions;
use \Brain\Monkey\Filters;
use InpsydePHPUnit\Inc\InpsydePluginTestCase as TestCase;

class InpsydePluginTest extends TestCase
{

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
        $inpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $inpsydeTaskPlugin->init();

        //Tests
        $this->assertNotFalse(
            has_action(
                'rest_api_init',
                [$inpsydeTaskPlugin, 'register_rest_route']
            )
        );
        $this->assertNotFalse(
            has_action(
                'rest_api_init',
                [$inpsydeTaskPlugin, 'register_rest_route']
            )
        );
        $this->assertNotFalse(
            has_action(
                'wp_enqueue_scripts',
                [$inpsydeTaskPlugin, 'enqueue_resources']
            )
        );
        $this->assertNotFalse(
            has_filter(
                'query_vars',
                [$inpsydeTaskPlugin, 'inpsyde_add_query_var']
            )
        );
        $this->assertNotFalse(
            has_filter(
                'template_include',
                [$inpsydeTaskPlugin, 'inpsyde_load_template']
            )
        );
    }

    public function testQueryArgsAdded()
    {
        //Setup
        $returnData = [
            'inpsyde',
            'inpsyde_user',
        ];

        //Fire and Test
        $inpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $this->assertEquals(
            $returnData,
            $inpsydeTaskPlugin->inpsyde_add_query_var([])
        );
    }

    public function testDataRetrievedFromCacheForAllUsers()
    {
        // Setup - prepare data and mock functions
        $data = $this->prepareUsersDataSupposedToReceiveFromCache();
        $this->mockWpFunctionsUsedInRetrievingDataFromCache($data);

        //Test
        $this->expectOutputString(json_encode($data));

        //Fire
        $inpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $inpsydeTaskPlugin->get_cached_data();
    }

    public function testDataRetrievedFromCacheForSingleUser()
    {
        // Setup - prepare data and mock functions
        $data = $this->prepareUsersDataSupposedToReceiveFromCache();
        $this->mockWpFunctionsUsedInRetrievingDataFromCache($data);

        //Test
        $this->expectOutputString(json_encode($data[4]));

        //Fire
        $inpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $inpsydeTaskPlugin->get_cached_data(5);
    }

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

    public function prepareUsersDataSupposedToReceiveFromCache(): array
    {
        $data = file_get_contents(__DIR__ .'/users_data.json');
        return json_decode($data);
    }
}
