<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              github.com/havok89/wordpress-gatsby-build-plugin
 * @since             1.0.0
 * @package           Build_Gatsby
 *
 * @wordpress-plugin
 * Plugin Name:       Build Gatsby
 * Plugin URI:        github.com/havok89/wordpress-gatsby-build-plugin
 * Description:       This plugin is used to trigger a github build action for Gatsby that you should have set up
 * Version:           1.0.0
 * Author:            Havok89
 * Author URI:        github.com/havok89
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       build-gatsby
 * Domain Path:       /languages
 */

add_action('admin_menu', 'build_gatsby_setup_menu');
add_action( 'admin_init', 'git_api_settings_init' );

function build_gatsby_setup_menu(){
        add_menu_page( 'Build Gatsby Page', 'Build Gatsby', 'manage_options', 'build-gatsby', 'build_button_page' );
}


function git_api_settings_init() {
  register_setting( 'build_gatsby', 'gatsby_build_settings' );
  
  add_settings_section(
      'build_gatsby_section',
      __( 'GIT API settings', 'wordpress' ),
      'build_gatsby_section_callback',
      'build_gatsby'
  );

  add_settings_field(
      'git_api_token_field',
      __( 'GIT Auth Token', 'wordpress' ),
      'git_api_token_field_render',
      'build_gatsby',
      'build_gatsby_section'
  );

  add_settings_field(
      'git_api_repo_field',
      __( 'GIT Repo (username/reponame)', 'wordpress' ),
      'git_api_repo_field_render',
      'build_gatsby',
      'build_gatsby_section'
  );
}

function build_button_page() {
  $options = get_option( 'gatsby_build_settings' );
  // General check for user permissions.
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient pilchards to access this page.')    );
  }

  if(($options['git_api_token_field'] != "") && ($options['git_api_repo_field'] != "")){
    // Start building the page
    echo '<h2>Trigger a build?</h2>';

    // Check whether the button has been pressed AND also check the nonce
    if (isset($_POST['confirm_build']) && check_admin_referer('confirm_build_clicked')) {
      // the button has been pressed AND we've passed the security check
      echo '<p>A build has been queued</p>';
      $response  = build_init();
      printf( '<pre>%s</pre>', print_r( $response , true ) );
    } else {
      echo '<form action="admin.php?page=build-gatsby" method="post">';
        wp_nonce_field('confirm_build_clicked');
        echo '<input type="hidden" value="true" name="confirm_build" />';
        submit_button('Rebuild!');
      echo '</form>';
      echo '<hr>';
    }
  }

  echo "<form action='options.php' method='post'>";
    settings_fields( 'build_gatsby' );
    do_settings_sections( 'build_gatsby' );
    submit_button();
  echo "</form>";

}

function git_api_token_field_render() {
  $options = get_option( 'gatsby_build_settings' );
  echo "<input type='text' name='gatsby_build_settings[git_api_token_field]' value='".$options['git_api_token_field']."'>";
}

function git_api_repo_field_render() {
  $options = get_option( 'gatsby_build_settings' );
  echo "<input type='text' name='gatsby_build_settings[git_api_repo_field]' value='".$options['git_api_repo_field']."'>";
}

function build_gatsby_section_callback() {
  echo __( 'Please fill in the settings', 'wordpress' );
}
 
function build_init(){
  $options = get_option( 'gatsby_build_settings' );

  $agent = 'WordPress Build Gatsby';
  $data = array(
    'event_type' => 'WordPress triggered build'
  );

  $payload = json_encode($data); 

  $c = curl_init();
  curl_setopt($c, CURLOPT_USERAGENT, $agent);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: token '.$options['git_api_token_field'], 'Accept: application/vnd.github.everest-preview+json', 'Content-Type: application/json'));
  curl_setopt($c, CURLOPT_URL, 'https://api.github.com/repos/'.$options['git_api_repo_field'].'/dispatches');
  curl_setopt($c, CURLOPT_POSTFIELDS, $payload);
  $output = curl_exec($c);
  curl_close($c);
  return $output;
}