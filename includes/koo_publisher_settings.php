<?php

if (!defined('ABSPATH')) {
	exit;
}

class KooPublisherSettings
{
	private $koo_publisher_settings_options;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'koo_publisher_settings_add_plugin_page'));
		add_action('admin_init', array($this, 'koo_publisher_settings_page_init'));
	}

	public function koo_publisher_settings_add_plugin_page()
	{
		add_menu_page(
			'Koo Publisher Settings', // page_title
			'Koo Publisher Settings', // menu_title
			'manage_options', // capability
			'koo-publisher-settings', // menu_slug
			array($this, 'koo_publisher_settings_create_admin_page'), // function
			'dashicons-admin-settings', // icon_url
			80 // position
		);
	}

	// admin settings page
	public function koo_publisher_settings_create_admin_page()
	{
		$this->koo_publisher_settings_options = get_option('koo_publisher_settings_option_name'); ?>

		<div class="wrap">
			<h2>Koo Publisher Settings</h2>
			<p>API Configuration</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields('koo_publisher_settings_option_group');
				do_settings_sections('koo-publisher-settings-admin');
				submit_button();
				?>
			</form>
		</div>
<?php }

	public function koo_publisher_settings_page_init()
	{
		register_setting(
			'koo_publisher_settings_option_group', // option_group
			'koo_publisher_settings_option_name', // option_name
			array($this, 'koo_publisher_settings_sanitize') // sanitize_callback
		);

		add_settings_section(
			'koo_publisher_settings_setting_section', // id
			'Settings', // title
			array($this, 'koo_publisher_settings_section_info'), // callback
			'koo-publisher-settings-admin' // page
		);

		add_settings_field(
			'enable_koo_publish_1', // id
			'Auto publish to Koo', // title
			array( $this, 'enable_koo_publish_1_callback' ), // callback
			'koo-publisher-settings-admin', // page
			'koo_publisher_settings_setting_section' // section
		);

		add_settings_field(
			'koo_api_key_0', // id
			'API Key', // title
			array($this, 'koo_api_key_0_callback'), // callback
			'koo-publisher-settings-admin', // page
			'koo_publisher_settings_setting_section' // section
		);
	}

	public function koo_publisher_settings_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['koo_api_key_0'])) {
			$sanitary_values['koo_api_key_0'] = sanitize_text_field($input['koo_api_key_0']);
		}

		if ( isset( $input['enable_koo_publish_1'] ) ) {
			$sanitary_values['enable_koo_publish_1'] = $input['enable_koo_publish_1'];
		}

		return $sanitary_values;
	}

	public function koo_publisher_settings_section_info()
	{
	}

	public function enable_koo_publish_1_callback() {
		?> 
		<fieldset>
			<?php $checked = ( isset( $this->koo_publisher_settings_options['enable_koo_publish_1'] ) && $this->koo_publisher_settings_options['enable_koo_publish_1'] === 'yes' ) ? 'checked' : '' ; ?>
			<label for="enable_koo_publish_1-0">
				<input type="radio" name="koo_publisher_settings_option_name[enable_koo_publish_1]" id="enable_koo_publish_1-0" value="yes" <?php echo $checked; ?>>  Yes (All articles will be posted to Koo on publish)
			</label>
		<br>
		<?php $checked = ( isset( $this->koo_publisher_settings_options['enable_koo_publish_1'] ) && $this->koo_publisher_settings_options['enable_koo_publish_1'] === 'no' ) ? 'checked' : '' ; ?>
			<label for="enable_koo_publish_1-1">
				<input type="radio" name="koo_publisher_settings_option_name[enable_koo_publish_1]" id="enable_koo_publish_1-1" value="no" <?php echo $checked; ?>>  No (You can selectively choose article to post on Koo from the Koo publisher sidebar setting)
			</label>
		</fieldset> <?php
	}

	public function koo_api_key_0_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="koo_publisher_settings_option_name[koo_api_key_0]" id="koo_api_key_0" value="%s">
			<br/> <p>Please contact <a href="mailto:hello@kooapp.com"> hello@kooapp.com </a> for API key</p>',
			isset($this->koo_publisher_settings_options['koo_api_key_0']) ? esc_attr($this->koo_publisher_settings_options['koo_api_key_0']) : ''
		);
	}
}

if (is_admin())
	$koo_publisher_settings = new KooPublisherSettings();
