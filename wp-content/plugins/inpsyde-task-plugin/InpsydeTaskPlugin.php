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
    public static function instance():object
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

    public function inpsydeAddQueryVare(array $vars):array
    {
        $vars[] = 'inpsyde';
        $vars[] = 'inpsyde_user';
        return $vars;
    }

    public function inpsydeLoadTemplate(string $template):string
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
        if (($this->removeDefaultThemeStyle === true) &&
            ((string) wp_get_theme()->get_template() === 'twentytwenty')
        ) {
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

    public function inpsydeRestRouteCallbackUsers(object $data = null)
    {
        $singleId = isset($data['id']) ? (int) $data['id'] : null;
        $responseCode = 0;

        $this->cacheApiResponse = apply_filters('inpsyde_cache_api_response', $this->cacheApiResponse);
        if ($this->cacheApiResponse) {
            $this->cachedData($singleId);
        }

        try {
            $this->sendApiRequest($singleId);
        } catch (\Exception $exObj) {
            wp_send_json_error(
                ['message' => $exObj->getMessage()]
            );
        }

        wp_send_json_error(
            ['message' => 'Unable to connect to the API server.', 'status_code' => $responseCode]
        );
    }

    public function sendApiRequest(int $singleId = null)
    {
        $usersData = false;
        $response = wp_remote_get($this->apiUrl, ['timeout' => 20]);
        $responseCode = wp_remote_retrieve_response_code($response);
        if (!is_wp_error($response) && ((int) $responseCode === 200)) {
            $body = wp_remote_retrieve_body($response);
            $usersData = json_decode($body);
            if ($this->cacheApiResponse) {
                set_transient('typicode_api_response', $usersData, 12 * HOUR_IN_SECONDS);
            }

            $this->sendSingleOrAllResults($usersData, $singleId);
        }
    }

    public function cachedData(int $id = null)
    {
        $cachedData = get_transient('typicode_api_response');

        if (($cachedData !== false) && is_array($cachedData)) {
            $this->sendSingleOrAllResults($cachedData, $id);
        }
    }

    
    public function sendSingleOrAllResults(array $data, int $id = null)
    {
        if ($id !== null) {
            $singleResult = $this->sortSingleUser($data, (int) $id);
            $this->sendSingleResult($singleResult);
            return;
        }

        $this->sendAllResults($data);
    }

    public function sendSingleResult(array $singleResult)
    {
        if (empty($singleResult)) {
            wp_send_json_error(
                ['message' => 'User does not exist']
            );
        }

        wp_send_json_success($singleResult);
    }

    public function sendAllResults(array $allUsersData)
    {
        wp_send_json_success(
            $allUsersData
        );
    }

    public function sortSingleUser(array $json, int $id):array
    {
        foreach ($json as $value) {
            if ($value->id === $id) {
                return (array)$value;
            }
        }
        return [];
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
