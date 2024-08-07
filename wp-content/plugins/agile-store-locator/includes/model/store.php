<?php

namespace AgileStoreLocator\Model;


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the Stores database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/store
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Store {


  /**
   * [get_searchable_columns Return the list of searchable columns]
   * @return [type] [description]
   */
  public static function get_searchable_columns() {

    return ['id', 'title', 'lat', 'lng', 'street', 'city', 'state', 'postal_code', 'country', 'email', 'phone', 'fax', 'website', 'is_disabled', 'category', 'marker_id', 'logo_id', 'created_on', 'pending'];
  }

  /**
   * [get_last_ts Return the last timestamp of updated_on or created_on]
   * @return [type] [description]
   */
  public static function get_last_ts() {

    global $wpdb;

    $ASL_PREFIX = ASL_PREFIX;
    
    $max_create = $wpdb->get_var("SELECT MAX(created_on) FROM `{$ASL_PREFIX}stores`");

    $max_update = $wpdb->get_var("SELECT MAX(updated_on) FROM `{$ASL_PREFIX}stores`");

    return ($max_create > $max_update)? $max_create: $max_update;
  }

  /**
   * [get_all_fields Return all the fields]
   * @return [type] [description]
   */
  public static function get_all_fields() {
    $default_columns = self::get_searchable_columns();
    // Values to remove
    $to_remove = array('id');
    // Remove values from the original array
    $default_columns = array_diff($default_columns, $to_remove);
    $custom_fields = \AgileStoreLocator\Helper::get_custom_fields();
    if($custom_fields) {
      $custom_fields   = array_keys($custom_fields);
      $default_columns = array_merge($default_columns, $custom_fields);
    }
    return $default_columns;
  }

  /**
   * [get_stores Get the stores by the given clause]
   * @param  array   $where_clause [description]
   * @param  [type]  $limit        [description]
   * @param  integer $offset       [description]
   * @return [type]                [description]
   */
  public static function get_stores($where_clause = [], $limit = 10000, $offset = 0, $user_id = null, $show_disabled = true) {
    
    global $wpdb;
    
    $ASL_PREFIX         = ASL_PREFIX;
    $category_clause    = '';
    $join_clause        = '';
    $where_query        = '';
    
    //  Validate the allowed clauses
    foreach($where_clause as $cl_key => $cl_value) {
      if(!in_array($cl_key, ['category', 'countries', 'state', 'city', 'country','pending', 'lang', 'meta'])) {
        unset($where_clause[$cl_key]);
      }
    }

    //  Countries Clause
    if(isset($where_clause['countries'])) {
      
      $countries = $where_clause['countries'];
      
      $countries = str_replace(', ', ',', $countries);
      $countries = explode(',', $countries);
      array_walk($countries, function(&$x) {$x = "'$x'";});
      $where_query .= " AND {$ASL_PREFIX}countries.`country` IN (".implode(',', $countries).")";
      unset($where_clause['countries']);
    }

    //  Cities Clause
    if(isset($where_clause['city'])) {
      
      $city   = $where_clause['city'];
      
      $city   = str_replace(', ', ',', $city);
      $city   = explode(',', $city);
      array_walk($city, function(&$x) {$x = "'$x'";});
      $where_query .= " AND `city` IN (".implode(',', $city).")";
      unset($where_clause['city']);
    }


    //  Categories Clause
    if(isset($where_clause['category'])) {
      
      $categories       = $where_clause['category'];
      
      $the_categories   = array_map('intval', explode(',', $categories));
      $the_categories   = implode(',', $the_categories);
      
      $category_clause  = " AND {$ASL_PREFIX}stores_categories.`category_id` IN (".$the_categories.")";
      
      unset($where_clause['category']);
    }

    //  Pending Clause
    if(isset($where_clause['pending'])) {
      
      if ($where_clause['pending'] == 2) {
        $where_query .= ' AND  (s.`pending` IS NULL OR s.`pending` = 0)';
      }
      else if ($where_clause['pending'] == 1) {
        $where_query .= ' AND s.`pending` = 1';
      }
      /*else if ($where_clause['pending'] == 0) {
        $pending_clause = '';
      }*/

      unset($where_clause['pending']);
    }



    // Meta Where Clause
    if (isset($where_clause['meta'])) {
      
      $meta_key = $where_clause['meta']['key'];
      $meta_val = $where_clause['meta']['value'];

      // Validating $meta_key and $meta_val
      //if (in_array($meta_key, ['shipping_id', 'shipping_method']) && is_numeric($meta_val)) {
      if (preg_match('/^shipping_id_\d+$/', $meta_key) && is_numeric($meta_val)) {
        
        // Build the JOIN and WHERE clauses
        $join_clause .= " LEFT JOIN {$ASL_PREFIX}stores_meta m ON s.id = m.store_id AND m.option_name = '$meta_key'";
        $where_query .= " AND m.option_value = '$meta_val'";
      }

      unset($where_clause['meta']);
    }

    //  Get the ddl fields
    $ddl_fields  = \AgileStoreLocator\Model\Attribute::get_fields();
    
    // ddl_fields in the query
    $ddl_fields_str = implode(', ', array_map(function($f) { return "`$f`";}, $ddl_fields));
    
    //  Get the stores
    $query   = "SELECT s.`id`, `title`,  `description`, CONCAT_WS(', ', IF(LENGTH(`street`),`street`,NULL), `city`, IF(LENGTH(`state`),`state`,NULL), IF(LENGTH(`postal_code`),`postal_code`,NULL), {$ASL_PREFIX}countries.country) as address, `street`,  `city`,  `state`, `postal_code`, `lat`,`lng`,`phone`,  `fax`,`email`,`website`,`logo_id`,{$ASL_PREFIX}storelogos.`path`,`marker_id`,`description_2`,`open_hours`, `ordr`, $ddl_fields_str, `custom`,`slug`, {$ASL_PREFIX}countries.`country` , `s`.`created_on`, `s`.`updated_on`, `s`.`pending`
          FROM {$ASL_PREFIX}stores as s
          LEFT JOIN {$ASL_PREFIX}storelogos ON logo_id = {$ASL_PREFIX}storelogos.id
          LEFT JOIN {$ASL_PREFIX}countries ON s.`country` = {$ASL_PREFIX}countries.id
          LEFT JOIN {$ASL_PREFIX}stores_categories ON s.`id` = {$ASL_PREFIX}stores_categories.store_id $join_clause
          WHERE s.`id` $category_clause".$where_query;
    
    //  When we have user ID add it in the clause
    if($user_id) {
      //  Clean it must be integer
      $user_id = intval($user_id);
      $query  .= " AND s.`id` IN (SELECT store_id FROM `{$ASL_PREFIX}stores_meta` WHERE option_name = 'store_owner' AND  option_value = $user_id)";
    }
    
    //  Show the disabled Stores
    if($show_disabled) {
      $query  .= " AND (is_disabled is NULL || is_disabled = 0) ";
    }
    
    //  Add the category clause
    $str_clause     = '';
    $clause_params  = [];
    
    //  Must be an array
    if(!is_array($where_clause))
      $where_clause = [];
    

    //  Language Clause
    if(isset($where_clause['lang'])) {
        
      // For all the languages
      if($where_clause['lang'] == '*') {
        unset($where_clause['lang']);
      }
    }
    else {
      $where_clause['lang'] = '';
    }
    
    // loop over the clauses
    foreach($where_clause as $k => $value) {
      $str_clause      .= " AND {$k} = %s";
      $clause_params[]  = $value;
    }
    
    //  Add the clause
    $query .= $str_clause;
    $limit  = intval($limit);
    
    
    //  Prepare the limit clause
    $limit_clause = intval($offset).', '.intval($limit);
    $query .= " GROUP BY s.`id` ORDER BY `title` LIMIT $limit_clause;";
    
    //  Get the results
    $stores = $wpdb->get_results($wpdb->prepare($query, $clause_params));

    if($stores) {

      //   Loop over the Data
      foreach($stores as $store) {

        //  Clean the Store
        $store = \AgileStoreLocator\Helper::sanitize_store($store);
      }
    }
    
    return $stores;
  }



  /**
   * [get_store Get the store by the store id or where clause]
   * @param  [type] $origLat [description]
   * @param  [type] $origLon [description]
   * @param  [type] $dist    [description]
   * @return [type]          [description]
   */
  public static function get_store($store_id, $_where_clause = null) {

    global $wpdb;
    
    $ASL_PREFIX   = ASL_PREFIX;

    $store_id     = ($store_id)? intval($store_id): '';

    $where_clause = ($_where_clause)? $_where_clause: "s.`id` = {$store_id}";

    // ddl_fields in the query
    $ddl_fields_str = \AgileStoreLocator\Model\Attribute::sql_query_fields();

    //  Query the get the closest store
    $query   = "SELECT s.`id`, `title`, `is_disabled`, `brand`, `special` ,{$ASL_PREFIX}countries.`country` as 'country_name', {$ASL_PREFIX}countries.`id` as 'country', `description`, `street`,  `city`,  `state`, `postal_code`, `lat`,`lng`,`phone`,  `fax`,`email`,`website`,`logo_id`,{$ASL_PREFIX}storelogos.`path`,`marker_id`,`description_2`,`open_hours`, `ordr`,$ddl_fields_str, `custom`,`slug`,GROUP_CONCAT({$ASL_PREFIX}stores_categories.`category_id`) AS category FROM {$ASL_PREFIX}stores as s 
          LEFT JOIN {$ASL_PREFIX}storelogos ON logo_id = {$ASL_PREFIX}storelogos.id
          LEFT JOIN {$ASL_PREFIX}countries ON s.`country` = {$ASL_PREFIX}countries.id
          LEFT JOIN {$ASL_PREFIX}stores_categories ON s.`id` = {$ASL_PREFIX}stores_categories.store_id
          WHERE ".$where_clause;

    $result = $wpdb->get_results($query);

    //  When we have a store in result
    if($result) {

      // Sanitize the store
      $store = \AgileStoreLocator\Helper::sanitize_store($result[0]);

      return $store;
    }

    return null;
  }
  /**
   * [delete_store Delete a Store]
   * @param  [type] $store_id [description]
   * @return [type]           [description]
   */
  public static function delete_store($store_id) {

    global $wpdb;

    $ASL_PREFIX = ASL_PREFIX;

    // Delete Meta
    $wpdb->delete("{$ASL_PREFIX}stores_meta", array('store_id' => $store_id));

    //  Delete the store
    return $wpdb->delete("{$ASL_PREFIX}stores", array('id' => $store_id));
  }

  /**
   * [count_branches Count the branches of the store]
   * @param  [type]  $store_id [description]
   * @return boolean           [description]
   */
  public static function count_branches($store_id) {


    global $wpdb;

    $ASL_PREFIX = ASL_PREFIX;

    return $wpdb->get_var($wpdb->prepare("SELECT count(*) as c FROM {$ASL_PREFIX}stores_meta WHERE option_name = 'p_id' AND option_value = %d", [$store_id]));   
  }


  /**
   * [assignBranches Assign the branches to the store]
   * @param  [type] $parent_id [description]
   * @param  [type] $branches  [description]
   * @return [type]            [description]
   */
  public static function assignBranches($parent_id, $branches) {

    //  Make sure it is an array
    if(!is_array($branches)) {
      $branches = explode(',', $branches);
    }


    //  Loop to add
    foreach($branches as $b) {

      // Update Meta for branch
      if($b && is_numeric($b))
        \AgileStoreLocator\Helper::set_option($b, 'p_id', $parent_id);
    }

    return;
  }

  
      /**
   * [ Get all the stores metas by the given clause]
   * @param  [type]  $store_id        [description]
   * @return [type]                [description]
   */
  public static function get_stores_meta($store_id) {

    global $wpdb;

    $prefix = ASL_PREFIX;
    $table = $prefix.'stores_meta';
    

    // Get store meta by store id
    $get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE `option_name`= 'p_id' AND `store_id`= $store_id "));
    return $get_meta;
  }


  /**
     * [stores_to_enable_by_schedule Get those stores that will be started by now]
     * @return [type] [description]
     */
  public static function stores_to_enable_by_schedule() {

    global $wpdb;

    $prefix = ASL_PREFIX;

    // Get store ids is scheduled
    $schedule_store_ids = $wpdb->get_results("SELECT store_id FROM {$prefix}stores_meta WHERE option_name = 's_date' AND option_value > DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i') AND option_value != '' AND is_exec != 1");

    // Store Ids
    $schedulee_ids = wp_list_pluck( $schedule_store_ids, 'store_id' ); 
    $schedulee_ids = implode(',', $schedulee_ids);


    // Check Store ids avaiable
    if (!empty($schedule_store_ids)) {
        
        // Update store status
        $wpdb->query("UPDATE {$prefix}stores SET is_disabled = 1 WHERE id IN ($schedulee_ids)");
        
    }


    // Get store ids that is  started 
    $enable_store_ids = $wpdb->get_results("SELECT store_id FROM {$prefix}stores_meta WHERE option_name = 's_date' AND option_value < DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i')  AND is_exec != 1");

    // Check store is avaiable?
    if (!empty($enable_store_ids)) {

        foreach ($enable_store_ids as $key => $enable_store_id) {

            // Check Store is enable / Disable
            $store_status = \AgileStoreLocator\Helper::get_option($enable_store_id->store_id, 'is_scheduled');

            if ($store_status == 1) {
              
              // update store and store meta
              $wpdb->query("UPDATE {$prefix}stores SET is_disabled = 1 WHERE id = $enable_store_id->store_id");
              $wpdb->query("UPDATE {$prefix}stores_meta SET is_exec = 1 WHERE option_name = 's_date' AND store_id = $enable_store_id->store_id");
            } 
            else {

              // update store and store meta
              $wpdb->query("UPDATE {$prefix}stores SET is_disabled = 0 WHERE id = $enable_store_id->store_id");
              $wpdb->query("UPDATE {$prefix}stores_meta SET is_exec = 1 WHERE option_name = 's_date' AND store_id = $enable_store_id->store_id ");
            }

          
        }

    }

  
  }

    /**
     * [stores_to_disable_by_schedule Get those stores that will be stop by now]
     * @return [type] [description]
     */
    public static function stores_to_disable_by_schedule() {

       global $wpdb;

       $prefix = ASL_PREFIX;

       // Get all stores for disable
       $disable_store_ids = $wpdb->get_results("SELECT store_id FROM {$prefix}stores_meta WHERE option_name = 'e_date' AND option_value < DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i') AND is_exec != 1 AND option_value != ''");

       // Store Ids
       $disable_ids = wp_list_pluck( $disable_store_ids, 'store_id' ); 
       $disable_ids = implode(',', $disable_ids);

       // Check Store ids avaiable
      if (!empty($disable_store_ids)) {
           
            // Update store and store meta table
           $wpdb->query("UPDATE {$prefix}stores SET is_disabled = 1 WHERE id IN ($disable_ids)");
           $wpdb->query("UPDATE {$prefix}stores_meta SET is_exec = 1 WHERE option_name = 'e_date' AND store_id IN ($disable_ids) ");
           
       }

     }



}

