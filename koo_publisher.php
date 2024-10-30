<?php
/**
 * Koo Publisher
 * Plugin Name: Koo Publisher
 * Plugin URI:
 * Description: Koo Pusblisher description
 * Version: 1.0.0
 * Author: Koo India
 * Author URI:https://www.kooapp.com/
 * Licence: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

class koo_publisher
{
    private $plugin_name;

    public function __construct()
    {
        require_once(dirname(__FILE__) . '/includes/koo_publisher_settings.php');
        require_once(dirname(__FILE__) . '/includes/koo_api.php');

        $this->plugin_name = plugin_basename(__FILE__);

        add_filter("plugin_action_links_$this->plugin_name", array($this, 'koo_publisher_add_action_links'));

        // capture post publish event
        add_action('transition_post_status', array($this, 'koo_publisher_send_new_post'), 10, 3);

        // show post publish status
    	add_action('admin_notices', array($this, 'koo_publish_message'));

        // show plugin status notice in edit pages
        add_action('admin_notices', array($this, 'koo_enabled_message'));

        // register oEmbed
        add_action('init', array($this, 'register_koo_oembed'));

        //block editor sidebar
        add_action('init', array($this, 'sidebar_plugin_register'));

        add_action('enqueue_block_editor_assets', array($this, 'sidebar_plugin_script_enqueue'));

        // save post meta
        add_action('init', function() {
            register_meta('post', 'koo_publish_custom_meta', [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'boolean'
            ]);
        });

    }

    // register Koo oEmbed provider
    function register_koo_oembed() {
        wp_oembed_add_provider('https://*.kooapp.com/koo/*', 'https://embed.kooapp.com/services/oembed', false);
        wp_oembed_add_provider('http://*.kooapp.com/koo/*', 'https://embed.kooapp.com/services/oembed', false);

    }

    // register sidebar script
    function sidebar_plugin_register() {
	    wp_register_script(
		    'index-js',
		    plugins_url('/build/index.js', __FILE__),
		    array(
                'wp-plugins',
                'wp-edit-post',
                'wp-element'
            )
	    );
    }

    // load sidebar script
    function sidebar_plugin_script_enqueue(){
	    wp_enqueue_script('index-js');
    }

    public function koo_enabled_message()
    {
        global $pagenow;
        // show publish status only in post edit page
        if ($pagenow == 'edit.php') {

            if ($this->koo_publisher_get_enable_status() == 'no') {
        ?>
                <div class="notice notice-warning is-dismissible">
                    <p>Koo publisher is disabled, please enable to post to Koo.</p>
                </div>
            <?php
            } else {
            ?>
                <div class="notice notice-success is-dismissible">
                    <p>Koo publisher is enabled.</p>
                </div>
            <?php
            }
        }
    }

    // only handle success and failure,
    // no api specific error code/ message for user
    public function koo_publish_message()
    {
        global $pagenow;
        // show status only in edit pages
        if ($pagenow == 'edit.php') {

        $koo_publish_message_option = get_option('koo_publish_message_option');

            if (isset($koo_publish_message_option) && !empty($koo_publish_message_option)) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                            echo ($koo_publish_message_option == 'success') ? 'Successfully posted to Koo!' : 'Failed to post to Koo!';
                            delete_option('koo_publish_message_option');
                        ?>
                    </p>
                </div>
            <?php
            }
        }
    }

    // capture only publish state, ignore rest
    public function koo_publisher_send_new_post($new_status, $old_status, $post)
    {
        // check global enable status
        if ($this->koo_publisher_get_enable_status() == 'yes') {
            // enable editor toggle
            add_post_meta($post->ID, 'koo_publish_custom_meta', 1, true);
        }

        // check editor toggle
        $is_post_pubish_enablled = get_post_meta($post->ID, 'koo_publish_custom_meta', true);

        if(!empty($is_post_pubish_enablled) && $is_post_pubish_enablled == 1) {

            // do nothing for autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (wp_is_post_autosave($post)) {
                return;
            }

            if (wp_is_post_revision($post)) {
                return;
            }

            // post to koo only on publish
            if ('publish' === $new_status /* && 'publish' !== $old_status */ && get_post_type($post) === 'post') {
                $result = $this->koo_posting($post);
                add_option('koo_publish_message_option', $result);
            }
        }
    }

    // Koo publish API call
    public function koo_posting($post)
    {
        $arr = array(
            'key' => $this->koo_publisher_get_api_key(),
            'title' => $post->post_title,
            'url' => get_permalink($post)
        );

        $koo_api = new koo_api();
        return $koo_api->post_to_koo(
          $arr
        );
    }

    public function koo_publisher_add_action_links($links)
    {
        $setting_link = '<a href="' . admin_url('admin.php?page=koo-publisher-settings') . '">Settings</a>';
        array_push($links, $setting_link);

        return $links;
    }

    // fetch API key from db
    public function koo_publisher_get_api_key()
    {
        $koo_publisher_settings_options = get_option('koo_publisher_settings_option_name'); // Array of All Options
        $koo_api_key_0 = $koo_publisher_settings_options['koo_api_key_0']; // Koo API Key
        return $koo_api_key_0;
    }

    public function koo_publisher_get_enable_status()
    {
        $koo_publisher_settings_options = get_option('koo_publisher_settings_option_name'); // Array of All Options
        $enable_koo_publish_1 = $koo_publisher_settings_options['enable_koo_publish_1']; // Enable Koo Publish
        return $enable_koo_publish_1;
    }

}

$koo = new koo_publisher;

