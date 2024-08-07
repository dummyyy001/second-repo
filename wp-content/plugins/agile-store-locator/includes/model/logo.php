<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the logos database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/marker
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Logo {


    /**
    * [Get the all logos]
    * @since  4.8.21
    * @return [type]          [description]
    */
  public  static function get_all_logos() {
   
    global $wpdb;

    $ASL_PREFIX   = ASL_PREFIX;
    
    //  Get the results
    $results = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}storelogos ORDER BY name");

    foreach ($results as $key => $result) {
      
      $result->url = ASL_UPLOAD_URL. 'Logo/' . $result->path;

    }

    return $results;
 }

    /**
    * [Get Store logo]
    * @since  4.8.21
    * @return [type]          [description]
    */
  public  static function get_store_logo_url($logo_id) {
   
    global $wpdb;

    $ASL_PREFIX   = ASL_PREFIX;
    $logo_url     = '';
    $logo  = $wpdb->get_results( "SELECT `id` as `value`, `name` as `text`, `path` as `imageSrc`  FROM ".ASL_PREFIX."storelogos WHERE id = $logo_id ORDER BY name");

    if(isset($logo[0])){

      $logo_url = ASL_UPLOAD_URL. 'Logo/' . $logo[0]->imageSrc;

    } 

    return $logo_url;
 }

}
