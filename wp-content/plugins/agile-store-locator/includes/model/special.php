<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the categories database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/Special
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Special {

    /**
    * [Get the all store categories for vc]
    * @since  4.8.21
    * @return [type]          [description]
    */
  public  static function get_all_specials( $addon = null) {
   
    global $wpdb;

    $ASL_PREFIX   = ASL_PREFIX;
    
    $categories   = [];
    
    $orde_by      = " `name` ;";
    $where_clause = "`lang` = ''";
    
    //  Get the results
    $results = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}specials WHERE {$where_clause} ORDER BY {$orde_by}");

    
    return $results;
 }

}
