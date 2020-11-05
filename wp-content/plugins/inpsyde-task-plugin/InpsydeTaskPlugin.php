<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace InpsydePlugins;

class InpsydeTaskPlugin
{

    /**
     * The unique instance of the plugin.
     * @var singleton object
     */
    private static $pluginInstance;
    private $pluginUri;
    private $pluginDir;
    private $pluginSlug;
    private $pluginTextDomain;
    private $apiUrl = 'https://jsonplaceholder.typicode.com/users';
    private $isInpsydeEndpoint = false;
    private $removeDefaultThemeStyle = true;
    private $cacheApiResponse = false;

    /**
     * Constructor
     */
    private function __construct()
    {
    }

    /**
     * Gets an instance of our plugin.
     * @return object
     */
    public static function instance()
    {
        if (null === self::$pluginInstance) {
            self::$pluginInstance = new self();
        }

        return self::$pluginInstance;
    }

    public function init()
    {
        //
        $this->pluginDir = plugin_dir_path(__FILE__);
        $this->pluginUri = plugin_dir_url(__FILE__);
        $this->pluginSlug = 'inpsyde-task-plugin';
        $this->pluginTextDomain = 'inpsyde-task-plugin';

        register_activation_hook(__FILE__, [$this, 'inpsydeActivationSetup']);

        add_action('init', [$this, 'inpsydeAddEndPoint']);
        add_action('rest_api_init', [$this, 'registerRestRoute']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueResources']);

        add_filter('query_vars', [$this, 'inpsydeAddQueryVare']);
        add_filter('template_include', [$this, 'inpsydeLoadTemplate'], -1);
        add_filter('show_admin_bar', function () {
            return false;
        });
    }

    public function inpsydeAddQueryVare($vars)
    {
        $vars[] = 'inpsyde';
        $vars[] = 'inpsyde_user';
        return $vars;
    }

    public function inpsydeLoadTemplate($template)
    {
        $this->isInpsydeEndpoint = get_query_var('inpsyde');
        if ($this->isInpsydeEndpoint) {
            if ($this->removeDefaultThemeStyle) {
                $this->removeTwentyTwentyStyles();
            }
            return $this->pluginDir . 'template/index.php';
        }
        return $template;
    }

    public function removeTwentyTwentyStyles()
    {
        if (($this->removeDefaultThemeStyle == true) && (wp_get_theme()->get_template() == 'twentytwenty')) {
            remove_action('wp_enqueue_scripts', 'twentytwenty_register_scripts');
            remove_action('wp_enqueue_scripts', 'twentytwenty_register_styles');
        }
    }

    public function inpsydeActivationSetup()
    {
        $this->inpsydeAddEndPoint();
        flush_rewrite_rules();
    }

    public function inpsydeAddEndPoint()
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

    public function registerRestRoute()
    {
        register_rest_route('inpsyde/v1', '/users', [
            'methods' => 'GET',
            'callback' => [$this, 'inpsydeRestRouteCallbackUsers'],
        ]);

        register_rest_route('inpsyde/v1', '/users/(?P<id>[\d]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'inpsydeRestRouteCallbackUsers'],
        ]);
    }

    public function inpsydeRestRouteCallbackUsers($data = null)
    {
        $single_id = isset($data['id']) ? $data['id'] : null;
        $response_code = 0;

        $this->cacheApiResponse = apply_filters('inpsyde_cache_api_response', $this->cacheApiResponse);
        if ($this->cacheApiResponse) {
            $this->getCachedData($single_id);
        }

        try {
            $users_data = false;
            $response = wp_remote_get($this->apiUrl, ['timeout' => 20]);
            $response_code = wp_remote_retrieve_response_code($response);
            if (!is_wp_error($response) && $response_code == 200) {
                $body = wp_remote_retrieve_body($response);
                $users_data = json_decode($body);
                if ($this->cacheApiResponse) {
                    set_transient('typicode_api_response', $users_data, 12 * HOUR_IN_SECONDS);
                }

                if ($single_id) {
                    $single_result = $this->sortSingleUser($users_data, $single_id);
                    ($single_result != false) ? wp_send_json_success($single_result) : wp_send_json_error(['message' => 'User does not exist']);
                } else {
                    wp_send_json_success($users_data, 200);
                }
            }
        } catch (\Exception $exObj) {
            wp_send_json_error(['message' => $exObj->getMessage()]);
        }

        wp_send_json_error(
            ['message' => 'Unable to connect to the API server.', 'status_code' => $response_code]
        );
    }

    public function getCachedData($id = null)
    {
        $single_id = $id;
        $cached_data = get_transient('typicode_api_response');

        if (($cached_data != false) && is_array($cached_data)) {
            if ($single_id) {
                $single_result = $this->sortSingleUser($cached_data, $single_id);
                if ($single_result != false) {
                    wp_send_json_success($single_result);
                } else {
                    wp_send_json_error(['message' => 'User does not exist']);
                }
            } else {
                wp_send_json_success($cached_data);
            }
        }
    }

    public function sortSingleUser($json, $id)
    {
        foreach ($json as $value) {
            if ($value->id == $id) {
                return (array)$value;
            }
        }
        return false;
    }

    public function enqueueResources()
    {
        if ($this->isInpsydeEndpoint) {
            wp_register_script('vue-app-js', $this->pluginUri . 'vue-app/public/scripts.js', [], false, true);
            wp_localize_script('vue-app-js', 'wp_rest_api', [
                'home_url' => home_url(),
                'base_url' => rest_url('/wp/v2/'),
                'inpsyde_user_api' => rest_url('/inpsyde/v1/'),
                'site_name' => get_bloginfo('name'),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
            wp_enqueue_script('vue-app-js');

            wp_register_style('vue-app-css', $this->pluginUri . 'vue-app/public/styles.css');
            wp_enqueue_style('vue-app-css');

            wp_enqueue_style('data-table-css', 'https://cdn.datatables.net/1.10.22/css/jquery.dataTables.css');
        }
    }
}
