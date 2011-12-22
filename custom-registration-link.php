<?php
/*
Plugin Name: Custom Registration Link
Plugin URI: http://www.gabsoftware.com/products/scripts/custom-registration-link/
Description: Let you customize the registration link.
Author: Hautclocq Gabriel
Author URI: http://www.gabsoftware.com/
Version: 1.0.0
Tags: registration, link, customize, custom, spam, spammer, change, modify, register, login
License: ISC
*/

// Security check. We do not want to be able to access our plugin directly.
if( !defined( 'WP_PLUGIN_DIR') )
{
	die( "There is nothing to see here." );
}

/*
 * global variables for our plugin
 */

// Absolute path of the plugin from the server view
// (eg: "/home/gabriel/public_html/blog/wp-content/plugins/custom-registration-link")
$crl_plugin_dir = WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__) );

// Public URL of the plugin
// (eg: "/blog/wp-content/plugins/custom-registration-link")
$crl_plugin_url = WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__) );

/* Constants */
define( 'CRL_TEXTDOMAIN', 'custom-registration-link' );
define( 'CRL_DEFAULT_NEW_ACTION', 'join_us' );


/*
 * Beginning of our plugin class
 */
class CustomRegistrationLink
{
	protected $new_action;

	/*
	 * Our plugin constructor
	 */
	function __construct()
	{
		// Place your add_actions and add_filters here
		add_action( 'init', array( &$this, 'init_callback' ), 0 );
		add_filter( 'register', array( &$this, 'register_callback' ) );
		add_action( 'login_init', array( &$this, 'login_init_callback' ) );
	} // end of constructor

	protected function load_options()
	{
		//load the new_action option
		if( ( $tmp = $this->get_option( 'new_action' ) ) === FALSE )
		{
			$this->set_option( 'new_action', CRL_DEFAULT_NEW_ACTION );
			$tmp = CRL_DEFAULT_NEW_ACTION;
		}
		$this->new_action = $tmp;
	}
	
	// Returns the value of the specified option
	protected function get_option( $name = NULL )
	{
		//retrieve the current options array
		$options = get_option( 'crl_options' );

		//add the options array if it does not exist
		if( $options === FALSE )
		{
			add_option( 'crl_options', array() );
		}

		//return the options array if name is null,
		// or the specified option in the options array,
		// or FALSE otherwise
		if( is_null( $name ) )
		{
			return get_option( 'crl_options' );
		}
		elseif( isset( $options[$name] ) )
		{
			return $options[$name];
		}
		else
		{
			return FALSE;
		}
	}

	// Sets the value of the specified option
	protected function set_option( $name, $value )
	{
		$options = $this->get_option( NULL );
		if( $options === FALSE )
		{
			$options = Array();
		}
		$options[$name] = $value;

		return update_option('crl_options', $options );
	}

	public function init_callback()
	{
	
		// Load the plugin localization (.mo files) located
		// in the plugin "lang" subdirectory
		if( function_exists( 'load_plugin_textdomain' ) )
		{
			load_plugin_textdomain(
				CRL_TEXTDOMAIN,
				false,
				$crl_plugin_dir . '/lang'
			);
		}
	
		//load the plugin options
		$this->load_options();
		
		//Do the administrative stuff only if we are in the administration area
		if( is_admin() )
		{
			if( current_user_can( 'manage_options' ) )
			{
				//Add admin actions
				add_action( 'admin_init', array( &$this, 'admin_init_callback' ) );
				add_action( 'admin_menu', array( &$this, 'add_page_admin_callback' ) );
			}
		}
		
	}
	
	/*
	 * Registers our admin page, admin menu and admin CSS/Javascript
	 */
	public function add_page_admin_callback()
	{
		//We add our options page into the Settings section
		$options_page_handle = add_submenu_page(
			'options-general.php',
			__('Custom Registration Link options', CRL_TEXTDOMAIN),
			__('Custom Registration Link', CRL_TEXTDOMAIN),
			'manage_options',
			'crl_options_page_id',
			array( &$this, 'options_page_callback' )
		);

		//specify that we want to alter the links in the plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'filter_plugin_actions_callback' ), 10, 2 );
	}
	
	//add a Configure link in the plugins page
	public function filter_plugin_actions_callback( $links, $file )
	{
		$settings_link = '<a href="options-general.php?page=crl_options_page_id">' . __( 'Configure', CRL_TEXTDOMAIN ) . '</a>';
		array_unshift( $links, $settings_link ); // add the configure link before other links
		return $links;
	}
	
	/*
	 * Content of the options page
	 */
	public function options_page_callback()
	{
		global $crl_plugin_dir;

		//Insufficient capabilities? Go away.
		if ( ! current_user_can( 'manage_options' ) )
		{
			die ( __( "You don't have sufficient privileges to display this page", CRL_TEXTDOMAIN ) );
		}
		?>

		<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>

			<h2><?php echo __('Custom Registration Link configuration', CRL_TEXTDOMAIN); ?></h2>

			<form method="post" action="options.php">

				<?php

				settings_fields( 'crl_options_group' );
				do_settings_sections( 'crl_options_page_id' );

				?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', CRL_TEXTDOMAIN ); ?>" />
				</p>
			</form>

		</div>

		<?php
	}
	
	/*
	 * Executed when Wordpress initialize the administration section.
	 * Inside we register our admin options.
	 */
	public function admin_init_callback()
	{
		//register option group
		register_setting( 'crl_options_group', 'crl_options', array( &$this, 'options_validate_callback' ) );

		//add sections
		add_settings_section('crl_options_section_general', __('General', CRL_TEXTDOMAIN), array( &$this, 'options_display_section_general_callback' ), 'crl_options_page_id');

		//section Appearance
		add_settings_field( 'crl_setting_new_action', sprintf( __( 'New action (default: %s)', CRL_TEXTDOMAIN ), CRL_DEFAULT_NEW_ACTION ), array( &$this, 'options_display_new_action_callback'    ) , 'crl_options_page_id', 'crl_options_section_general');
	}
	
	//display the General section
	public function options_display_section_general_callback()
	{
		echo '<p>' . __( 'General options for Custom Registration Link', CRL_TEXTDOMAIN ) . '</p>';
	}
	
	//display the deny message option field
	public function options_display_new_action_callback()
	{
		$options = $this->get_option( NULL );
		//set a default value if no value is set
		if( $options === FALSE || ! empty( $options['new_action'] ) )
		{
			$options['new_action'] = $this->new_action;
		}
		?>
		<input id='crl_setting_new_action' name='crl_options[new_action]' size='60' type='text' required='required' value='<?php echo $options['new_action']; ?>' />
		<?php
	}
	
	//validate the options
	public function options_validate_callback( $input )
	{
		//load the current options
		$newinput = $this->get_option( NULL );

		//validate the new action
		if( isset( $input['new_action'] ) )
		{
			$newinput['new_action'] = $input['new_action'];
		}

		return $newinput;
	}
	
	
	
	
	
	

	public function register_callback( $link )
	{
		if( ! is_user_logged_in() )
		{
			if ( get_option('users_can_register') )
			{
				$link = preg_replace( '#(wp-login.php\?action=)register#i', '${1}' . $this->new_action, $link );
			}
			else
			{
				$link = '';
			}
			return $link;
		}
		else
		{
			return $link;
		}
	}

	public function login_init_callback()
	{
		global $action;

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
		$errors = new WP_Error();

		if ( isset($_GET['key']) )
		$action = 'resetpass';

		// validate action so as to default to the login screen
		if ( !in_array($action, array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', $this->new_action, 'register', 'login'), true) && false === has_filter('login_form_' . $action) )
		{
			$action = 'login';
		}

		$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);

		switch( $action )
		{
			case 'register' :
				//Registration using 'register' action is now disabled!
				wp_redirect( site_url('wp-login.php?registration=disabled') );
				exit();

			case $this->new_action :
				//$action = 'register';

				if ( is_multisite() ) {
					// Multisite uses wp-signup.php
					wp_redirect( apply_filters( 'wp_signup_location', site_url('wp-signup.php') ) );
					exit;
				}

				if ( !get_option('users_can_register') ) {
					wp_redirect( site_url('wp-login.php?registration=disabled') );
					exit();
				}

				$user_login = '';
				$user_email = '';
				if ( $http_post ) {
					$user_login = $_POST['user_login'];
					$user_email = $_POST['user_email'];
					$errors = register_new_user($user_login, $user_email);
					if ( !is_wp_error($errors) ) {
						$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'wp-login.php?checkemail=registered';
						wp_safe_redirect( $redirect_to );
						exit();
					}
				}

				$redirect_to = apply_filters( 'registration_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
				login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site') . '</p>', $errors);
			?>

			<form name="registerform" id="registerform" action="<?php echo site_url('wp-login.php?action=' . $this->new_action, 'login_post') ?>" method="post">
				<p>
					<label><?php _e('Username') ?><br />
					<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" tabindex="10" /></label>
				</p>
				<p>
					<label><?php _e('E-mail') ?><br />
					<input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(stripslashes($user_email)); ?>" size="25" tabindex="20" /></label>
				</p>
			<?php do_action('register_form'); ?>
				<p id="reg_passmail"><?php _e('A password will be e-mailed to you.') ?></p>
				<br class="clear" />
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Register'); ?>" tabindex="100" /></p>
			</form>

			<p id="nav">
			<a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a> |
			<a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
			</p>

			<?php
			login_footer('user_login');

			//break;
			exit;
		}
	}

}

/*
 * Instanciate a new instance of CustomRegistrationLink
 */
new CustomRegistrationLink();

?>