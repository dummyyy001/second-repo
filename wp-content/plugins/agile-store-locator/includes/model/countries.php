<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the countries database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/countries
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Countries {


    /**
    * [Get the all countries]
    * @since  4.8.21
    * @return [type]          [description]
    */
  public  static function get_all_countries() {
   
    global $wpdb;

    $ASL_PREFIX   = ASL_PREFIX;
    
    //  Get the results
    $results = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}countries ORDER BY country");

    return $results;
 }

}
