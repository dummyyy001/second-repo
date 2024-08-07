<?php

namespace AgileStoreLocator\Vendors;


/**
 *
 * This class is responsible to get the INFO of latest plugin
 *
 * @link       https://agilelogix.com
 * @since      4.8.24
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/includes/vendors
 * @author     Your Name <support@agilelogix.com>
 */

class Updater {

  public $updater_url = 'https://lc.agilestorelocator.com/api/asl.json';
  
  /**
   * Register all of the hooks related to updater
   * of the plugin.
   *
   * @since    1.0.0
   * @access   public
   */
  public function __construct()
  {
    // Check for updates
    add_filter('pre_set_site_transient_update_plugins', [$this, 'plugin_auto_update_check']  );

    // Display changelog in update details.
    add_filter( 'plugins_api', [$this, 'asl_get_plugin_information'], 10, 3 );
  }

  
  /**
   * [plugin_auto_update_check for checking updates]
   * @return [type] [description]
   */
  public function plugin_auto_update_check($transient) {
        
    if (empty($transient->checked)) {
      return $transient;
    }

    // Fetch update information from your update JSON file
    $update_info_url  = $this->updater_url;

    $response         = wp_safe_remote_get($update_info_url);

    if (!is_wp_error($response)) {
        
        $update_info    = json_decode(wp_remote_retrieve_body($response));

        $latest_version     = $update_info->latest_version;

        if ($update_info && version_compare(ASL_CVERSION, $update_info->latest_version, '<')) {

            $transient->response[ASL_BASE_PATH.'/'.ASL_PLUGIN.'.php'] = (object) array(
              'id'                => ASL_PLUGIN,
              'slug'              => ASL_PLUGIN,
              'new_version'       => $latest_version,
              'url'               => 'https://agilestorelocator.com/',
              //'package'           => $download_link,
              'plugin'            => ASL_BASE_PATH.'/'.ASL_PLUGIN.'.php',
              //'upgrade_notice'    => $changelog,
              'tested'            => '6.3.3',
              'requires_php'      => '7.0',
              'icons'             => array('1x' => 'http://plugins.svn.wordpress.org/agile-store-locator/assets/icon-128x128.png'),
              'banners'           => array('1x' => 'http://plugins.svn.wordpress.org/agile-store-locator/assets/banner-1544x500.jpg'),
              'banners_rtl'       => array('1x' => 'http://plugins.svn.wordpress.org/agile-store-locator/assets/banner-1544x500.jpg'),

            );
        }
    }


    return $transient;
  }


  /**
   * [asl_get_plugin_information Updates information on the "View version x.x details"]
   * @param  [type] $_data   [description]
   * @param  string $_action [description]
   * @param  [type] $_args   [description]
   * @return [type]          [description]
   */
  public function asl_get_plugin_information( $_data, $_action = '', $_args = null ) {

     if ( 'plugin_information' !== $_action ) {
      return $_data;
    }

    $plugin_slug = ASL_PLUGIN;

    if ( $plugin_slug === $_args->slug ) {
      
      $info = new \stdClass();

      $plugin_name    = asl_esc_lbl('plugin_name');
      $info->name     = $plugin_name;
      $info->slug     = $plugin_slug;
      $info->version  = ASL_CVERSION;
      $info->author   = '<a href="https:/agilelogix.com">AGILELOGIX</a>';
      $info->homepage = 'https://agilestorelocator.com/';
      $info->sections = array();

      // Add banner image URL
      $banner_url    = 'https://ps.w.org/agile-store-locator/assets/banner-772x250.jpg'; // Replace with actual banner image URL

      $info->banners = array(
          'low'  => $banner_url,
          'high' => $banner_url
      );
      
      // Parse the changelogs.txt file
      $plugin_changelog = file_get_contents(ASL_PLUGIN_PATH.'changelogs.txt');      
      
      // Add changelog
      $info->sections['ChangeLogs'] = $this->present_changelogs($plugin_changelog);



      $_data = $info;
    }

    return $_data;
  }


  /**
   * [present_changelogs Beautify the changelogs]
   * @param  [type] $changelogs [description]
   * @return [type]             [description]
   */
  public function present_changelogs($changelog_content) {


    return '<ul>
                <li><a target="__blank" href="https://agilestorelocator.com/wiki/automatic-updates/">How to enable auto-updates?</a></li>
                <li><a target="__blank" href="https://agilestorelocator.com/wiki/upgrade-plugin-newer-version/">How to update manually?</a></li>
                <li><a target="__blank" href="https://agilestorelocator.com/wiki/">Documentation</a></li>
            </ul><h4>Change Logs</h4><pre style="border:0px !important;padding: 0px;">'.$changelog_content.'</pre>';
  }

}
