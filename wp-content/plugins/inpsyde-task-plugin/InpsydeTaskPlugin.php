<?php
/*
Plugin Name: Inpsyde Task Plugin
Description:
Version: 1.0
Author: Mohsin Hassan
License: GPLv2 or later
*/
namespace Plugin\inpsyde;


if(!class_exists('\Plugin\inpsyde\InpsydeTaskPlugin')){

    class InpsydeTaskPlugin {


        /**
         * The unique instance of the plugin.
         * @var singleton object
         */
        private static $plugin_instance;
        public  $plugin_uri;
        public  $plugin_dir;
        public  $plugin_name;
        public  $plugin_slug;
        public  $plugin_text_domain;
        public  $plugin_class_name_space;
        public  $api_url = 'https://jsonplaceholder.typicode.com/users';
        private $remove_default_theme_style = true;
        private $cache_api_response = true;
        public  $is_inpsyde_endpoint = false;




        /**
         * Gets an instance of our plugin.
         * @return object
         */
        public static function get_instance()
        {
            if (null === self::$plugin_instance) {
                self::$plugin_instance = new self();
            }

            return self::$plugin_instance;
        }



        /**
         * Constructor
         */
        private function __construct()
        {

        }

        public function init(){
            //
            $this->plugin_dir                   = plugin_dir_path(__FILE__);
            $this->plugin_uri                   = plugin_dir_url(__FILE__);
            $this->plugin_slug                  = 'inpsyde-task-plugin';
            $this->plugin_name                  = 'Inpsyde Task Plugin';
            $this->plugin_class_name_space      = 'plugin\inpsyde';
            $this->plugin_text_domain           = 'inpsyde-task-plugin';




            register_activation_hook( __FILE__, array ( $this, 'inpsyde_activation_setup') );


            add_action('init', array($this,'inpsyde_add_end_point'));
            add_action('rest_api_init',array($this,'register_rest_route'));
            add_action('wp_enqueue_scripts',array($this,'enqueue_resources'));

            add_filter('query_vars',array($this,'inpsyde_add_query_var'));
            add_filter('template_include',array($this,'inpsyde_load_template'),-1 );
            add_filter( 'show_admin_bar', function (){ return false;});

        }


        public function inpsyde_add_end_point()
        {

            add_rewrite_rule(
                '^inpsyde/?$',
                'index.php?inpsyde=true',
                'top'
            );
            add_rewrite_rule(
                '^inpsyde/user/([\d]+)/?$',
                'index.php?inpsyde=true&inpsyde_user=$matches[1]',
                'top'
            );

        }

        public function inpsyde_add_query_var($vars)
        {
            $vars[] = 'inpsyde';
            $vars[] = 'inpsyde_user';
            return $vars;
        }


        public function inpsyde_load_template($template)
        {
            $this->is_inpsyde_endpoint = get_query_var('inpsyde');
            if( $this->is_inpsyde_endpoint )
            {
                if($this->remove_default_theme_style) {
                    $this->remove_twenty_twenty_styles();
                }
                return $this->plugin_dir . 'template/index.php';
            }
            return $template;

        }


        public function inpsyde_activation_setup()
        {
            $this->inpsyde_add_end_point();
            flush_rewrite_rules();
        }

        public function remove_twenty_twenty_styles(){

            if( ($this->remove_default_theme_style == true) && (wp_get_theme()->get_template() == 'twentytwenty') ){
                remove_action('wp_enqueue_scripts', 'twentytwenty_register_scripts');
                remove_action('wp_enqueue_scripts', 'twentytwenty_register_styles' );
            }
        }


        public function register_rest_route(){
            register_rest_route( 'inpsyde/v1', '/users', array(
                'methods' => 'GET',
                'callback' => array($this,'inpsyde_rest_route_callback_users'),
            ) );

            register_rest_route( 'inpsyde/v1', '/users/(?P<id>[\d]+)', array(
                'methods' => 'GET',
                'callback' => array($this,'inpsyde_rest_route_callback_users'),
            ) );
        }

        public function inpsyde_rest_route_callback_users($data = null){

            $single_id = isset($data['id']) ? $data['id'] : null;
            $response_code = 0;

            $this->cache_api_response = apply_filters('inpsyde_cache_api_response',$this->cache_api_response);
            if($this->cache_api_response){
                $this->get_cached_data($single_id);
            }


            try {

                $users_data    = false;
                $response      = wp_remote_get( $this->api_url,array( 'timeout'=> 20) );
                $response_code = wp_remote_retrieve_response_code( $response );
                if( !is_wp_error( $response ) && $response_code == 200 ){

                    $body = wp_remote_retrieve_body( $response );
                    $users_data = json_decode($body);
                    if($this->cache_api_response)set_transient('typicode_api_response',$users_data,12 * HOUR_IN_SECONDS);

                    if($single_id){
                        $single_result = $this->sort_single_user($users_data,$single_id);
                        ($single_result != false) ? wp_send_json_success($single_result) : wp_send_json_error(array('message' => 'User does not exist'));
                    }else{
                        wp_send_json_success($users_data,200);
                    }
                }
            } catch ( \Exception $ex ) {
                wp_send_json_error(array('message' => $ex->getMessage()));
            }

            wp_send_json_error(
                array('message' => 'Unable to connect to the API server.','status_code' => $response_code)
            );
        }

        public function get_cached_data($id=null){

            $single_id = $id;
            $cached_data = get_transient('typicode_api_response');

            if( ($cached_data != false) && is_array($cached_data) ){

                if($single_id){

                    $single_result = $this->sort_single_user($cached_data,$single_id);
                    if($single_result != false) {
                        wp_send_json_success($single_result);
                    } else{
                        wp_send_json_error(array('message' => 'User does not exist'));
                    }
                }else{
                    wp_send_json_success($cached_data);
                }
            }
        }

        public function sort_single_user($json,$id){

            foreach ($json as $value){
                if($value->id == $id){
                    return (array) $value;
                }
            }
            return false;
        }

        public function enqueue_resources(){

            if($this->is_inpsyde_endpoint){
                wp_register_script('vue-app-js',$this->plugin_uri . 'vue-app/public/scripts.js',array(),false,true);
                wp_localize_script('vue-app-js','wp_rest_api',[
                    'home_url'          =>   home_url(),
                    'base_url'          =>   rest_url('/wp/v2/'),
                    'inpsyde_user_api'  =>   rest_url('/inpsyde/v1/'),
                    'site_name'         =>   get_bloginfo('name'),
                    'nonce'             =>   wp_create_nonce('wp_rest')
                ]);
                wp_enqueue_script('vue-app-js');


                wp_register_style('vue-app-css',$this->plugin_uri . 'vue-app/public/styles.css');
                wp_enqueue_style('vue-app-css');


                wp_enqueue_style('data-table-css','https://cdn.datatables.net/1.10.22/css/jquery.dataTables.css');


            }

        }


    }//InpsydeTaskPlugin

}//class_exists

if(! defined('INPSYDE_PHPUNIT')  ) {
    $inpsyde_plugin_obj =  \Plugin\inpsyde\InpsydeTaskPlugin::get_instance();
    $inpsyde_plugin_obj->init();

}

