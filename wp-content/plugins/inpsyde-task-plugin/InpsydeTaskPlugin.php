<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace InpsydePlugins;

class InpsydeTaskPlugin
{

    /**
     * The unique instance of the plugin.
     * @var singleton object
     */
    private static $pluginInstance;

    /**
     * Plugin root URL
     * @var string
     */
    private $pluginUri;

    /**
     * Plugin root directory
     * @var string
     */
    private $pluginDir;

    /**
     * Plugin
     * @var string
     */
    private $pluginSlug;

    /**
     * Plugin text domain
     * @var string
     */
    private $pluginTextDomain;

    /**
     * Api Url
     * @var string
     */
    private $apiUrl = 'https://jsonplaceholder.typicode.com/users';

    /**
     * Flag to indicate the end point
     * @var bool
     */
    private $isInpsydeEndpoint = false;

    /**
     * Flag to remove the default theme style
     * @var bool
     */
    private $removeDefaultThemeStyle = true;

    /**
     * Flag to store the Api response
     * @var bool
     */
    private $cacheApiResponse = true;

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

    /**
     * initialize all the hooks
     */
    public function init()
    {
        //
        $this->pluginDir = plugin_dir_path(__FILE__);
        $this->pluginUri = plugin_dir_url(__FILE__);
        $this->pluginSlug = 'inpsyde-task-plugin';
        $this->pluginTextDomain = 'inpsyde-task-plugin';

        register_activation_hook(__DIR__.'/index.php', [$this, 'inpsydeActivationSetup']);

        add_action('init', [$this, 'inpsydeAddEndPoint']);
        add_action('rest_api_init', [$this, 'registerRestRoute']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueResources']);

        add_filter('query_vars', [$this, 'inpsydeAddQueryVare']);
        add_filter('template_include', [$this, 'inpsydeLoadTemplate'], -1);
        add_filter('wp_list_pages', [$this, 'addInpsydeLink'], 10, 3);
        add_filter('show_admin_bar', function () {
            return false;
        });
    }

    /**
     * Add Query Vars for Inpsyde endpoints
     * @param array $vars
     * @return array
     */
    public function inpsydeAddQueryVare(array $vars):array
    {
        $vars[] = 'inpsyde';
        $vars[] = 'inpsyde_user';
        return $vars;
    }

    /**
     * Call to Remove the default Theme style and load the Inpsyde template
     * @param string $template
     * @return string
     */
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

    /**
     * remove the default theme styles
     */
    public function removeTwentyTwentyStyles()
    {
        if (($this->removeDefaultThemeStyle === true) &&
            ((string) wp_get_theme()->get_template() === 'twentytwenty')
        ) {
            remove_action('wp_enqueue_scripts', 'twentytwenty_register_scripts');
            remove_action('wp_enqueue_scripts', 'twentytwenty_register_styles');
        }
    }

    /**
     * Add the link to Inpsyde endpoint link default theme navigation
     * @param string $output
     * @return string
     */
    public function addInpsydeLink(string $output):string
    {
        $inpsydeLink = home_url() . '/inpsyde';
        $html = "<li class='page_item page-item-2'><a href='$inpsydeLink'>Inpsyde Task</a></li>";
        return $output . $html;
    }

    /**
     * Add the endpoints and rewrite rules
     */
    public function inpsydeActivationSetup()
    {
        $this->updatePermalinkStructure();
        $this->inpsydeAddEndPoint();
        flush_rewrite_rules();
    }

    /**
     * Update the permalink structure
     */
    public function updatePermalinkStructure()
    {
        if (get_option('permalink_structure') !== '/%postname%/') {
            update_option('permalink_structure', '/%postname%/');
        }
    }

    /**
     * add the inpsyde endpoints
     */
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

    /**
     * Register the rest routes for users retrieved from APi
     */
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

    /**
     * Call back function for rest routes for all users and single users
     * @param object|null $data
     */

    public function inpsydeRestRouteCallbackUsers(object $data = null)
    {
        $singleId = isset($data['id']) ? (int) $data['id'] : null;
        $responseCode = 0;

        /**
         * Filters flag to cache Api response
         * @param bool $this->cacheApiResponse
         */
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

    /**
     * Send the request to external API to get the users data
     * @param int|null $singleId
     */
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

    /**
     * Used to get the cached users data
     * @param int|null $id
     */
    public function cachedData(int $id = null)
    {
        $cachedData = get_transient('typicode_api_response');

        if (($cachedData !== false) && is_array($cachedData)) {
            $this->sendSingleOrAllResults($cachedData, $id);
        }
    }

    /**
     * used to get the data for single users or all users
     * @param array $data
     * @param int|null $id
     */
    public function sendSingleOrAllResults(array $data, int $id = null)
    {
        if ($id !== null) {
            $singleResult = $this->sortSingleUser($data, (int) $id);
            $this->sendSingleResult($singleResult);
            return;
        }

        $this->sendAllResults($data);
    }

    /**
     * Send the response for single user via rest route
     * @param array $singleResult
     */
    public function sendSingleResult(array $singleResult)
    {
        if (empty($singleResult)) {
            wp_send_json_error(
                ['message' => 'User does not exist']
            );
        }

        wp_send_json_success($singleResult);
    }

    /**
     * Send the response for all users via rest route
     * @param array $allUsersData
     */
    public function sendAllResults(array $allUsersData)
    {
        wp_send_json_success(
            $allUsersData
        );
    }

    /**
     * used to sort the single user out of Json data received from API
     * @param array $json
     * @param int $id
     * @return array
     */
    public function sortSingleUser(array $json, int $id):array
    {
        foreach ($json as $value) {
            if ($value->id === $id) {
                return (array)$value;
            }
        }
        return [];
    }

    /**
     * enqueue the resources
     */
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
