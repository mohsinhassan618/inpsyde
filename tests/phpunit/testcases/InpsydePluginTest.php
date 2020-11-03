<?php

namespace PluginTest\inpsyde;
use \Plugin\inpsyde\InpsydeTaskPlugin;
use \Brain\Monkey\Functions;
use \Brain\Monkey\Filters;

class InpsydePluginTest extends \inpsydePluginTestCase {


    public function test_init_hooks() {


        //Setup
            // We expect plugin_dir_path to be called during bootstrap
            Functions\expect( 'plugin_dir_path' )
                ->once()
                ->withAnyArgs()
                ->andReturn(PLUGIN_ABSPATH);

            // We expect plugin_dir_url to be called
            Functions\expect( 'plugin_dir_url' )
                ->once()
                ->withAnyArgs()
                ->andReturn( 'http://inpsyde.local/wp-content/inpsyde-task-plugin' );

            Functions\expect('register_activation_hook')
                ->once()
                ->andReturnNull();

            Filters\expectAdded('show_admin_bar')->with(\Mockery::type('Closure'));



        //Fire
        $InpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $InpsydeTaskPlugin->init();

        //Tests
        $this->assertNotFalse(has_action('rest_api_init',[$InpsydeTaskPlugin,'register_rest_route']));
        $this->assertNotFalse(has_action('rest_api_init',[$InpsydeTaskPlugin,'register_rest_route']));
        $this->assertNotFalse(has_action('wp_enqueue_scripts',[$InpsydeTaskPlugin,'enqueue_resources']));
        $this->assertNotFalse(has_filter('query_vars',[$InpsydeTaskPlugin,'inpsyde_add_query_var']));
        $this->assertNotFalse(has_filter('template_include',[$InpsydeTaskPlugin,'inpsyde_load_template']));
    }

    public function test_query_args_added(){

        //Setup
        $return_data = array(
            'inpsyde',
            'inpsyde_user'
        );

        //Fire and Test
        $InpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $this->assertEquals($return_data,$InpsydeTaskPlugin->inpsyde_add_query_var(array()));
    }

    public function test_data_retrieved_from_cache_for_all_users(){

        // Setup - prepare data and mock functions
        $data = $this->prepare_users_data_received_from_cache();
        $this->mock_wp_functions_used_in_retrieving_data_from_cache($data);

        //Test
        $this->expectOutputString(json_encode($data));

        //Fire
        $InpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $InpsydeTaskPlugin->get_cached_data();

    }

    public function test_data_retrieved_from_cache_for_single_user(){

        // Setup - prepare data and mock functions
        $data = $this->prepare_users_data_received_from_cache();
        $this->mock_wp_functions_used_in_retrieving_data_from_cache($data);

        //Test
        $this->expectOutputString(json_encode($data[4]));

        //Fire
        $InpsydeTaskPlugin = InpsydeTaskPlugin::get_instance();
        $InpsydeTaskPlugin->get_cached_data(5);

    }

    public function mock_wp_functions_used_in_retrieving_data_from_cache($data){
        // We expect get_transient to be called and return Data Stored from API
        Functions\expect( 'get_transient' )
            ->once()
            ->withAnyArgs()
            ->andReturn($data);

        // We expect wp_send_json_success to be called and return the user data
        Functions\expect( 'wp_send_json_success' )
            ->zeroOrMoreTimes()
            ->withAnyArgs()
            ->andReturnUsing(function ($arg1){
                echo json_encode($arg1);
            });

        // We expect wp_send_json_error to be called
        Functions\expect( 'wp_send_json_error' )
            ->zeroOrMoreTimes()
            ->withAnyArgs()
            ->andReturnUsing(function ($arg1){
                echo json_encode($arg1);
            });

    }

    public function prepare_users_data_received_from_cache(){
        $data = file_get_contents(__DIR__ .'/users_data.json');
        return json_decode($data);
    }

}
