<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the markers database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/marker
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Marker {


    /**
    * [Get the all markers]
    * @since  4.8.21
    * @return [type]          [description]
    */
  public  static function get_all_markers() {
   
    global $wpdb;

    $ASL_PREFIX   = ASL_PREFIX;
    
    //  Get the results
    $results = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}markers ORDER BY marker_name");


    foreach ($results as $key => $result) {
      
      $result->url = ASL_UPLOAD_URL. 'icon/' . $result->icon;

    }

    return $results;
 }

}
