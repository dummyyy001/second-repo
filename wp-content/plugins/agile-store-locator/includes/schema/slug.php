<?php

namespace AgileStoreLocator\Schema;


class Slug {



  /**
   * [slugify Create Slug]
   * @since  4.8.21         [<description>]
   * @param  [type] $store  [description]
   * @return [type]         [description]
   */
  public static function slugify($store, $custom_fields) {

    global $wpdb;

    //  All the fields for the slug
    $all_fields   = ($custom_fields && is_array($custom_fields))? array_merge($custom_fields, $store): $store;

    //  Slug Attributes
    $slug_fields  = \AgileStoreLocator\Helper::get_setting('slug_attr_ddl');

    //  Default values
    if(!$slug_fields) {
      $slug_fields = 'title,city';
    }

    //  Exploded in array
    $slug_fields  = explode(',', $slug_fields);
    
      
    $slug_value  = [];

    //  Make Slug String
    foreach ($slug_fields as $slug_chunk) {

      if(isset($all_fields[$slug_chunk]) && $all_fields[$slug_chunk]) {

        $slug_value[] = $all_fields[$slug_chunk];
      }
    }

    //  When slug data fields are empty, make it title and city
    if(empty($slug_value)) {
      $slug_value[] = $all_fields['title'];
      $slug_value[] = $all_fields['city'];
    }


    $slug_value   = implode('-', $slug_value);

    //  Filter the string
    $slug_value   = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug_value), '-'));

    $count_slug   = self::count_slug($slug_value);

    if($count_slug > 0) {


        $slug_value  .= '-'.$count_slug ;

        return $slug_value; 

    }


    return preg_replace('/-+/', '-', $slug_value);
  }


  /**
   * [check slug already exist or not]
   * @since  4.8.21 [<description>]
   * @param  array   $slug [description]
   */
  public static function check_slug($slug='') {

    global $wpdb;  

    $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".ASL_PREFIX."stores WHERE slug =  %s", $slug));
    return $results;

  }


  /**
   * [update canonical for store details page]
   * @since  4.8.33 [<description>]
   * @param  $url [description]
   */
  public static function update_canonical_tag($url) {

		$store_details_slug = \AgileStoreLocator\Helper::get_configs('rewrite_slug');
		$store_uri = get_query_var('sl-store', false);

		if ($store_uri) {
			if (!empty($store_details_slug) && !is_null($store_details_slug)) {
				$url = site_url("$store_details_slug/$store_uri/");
			} else {
				$url .= $store_uri . '/';
			}
		}

		return $url;

  }


  /**
   * [update_title_by_store_slug for updating <title> as store title]
   * @since  4.9.8 [<description>]
   * @param  $title [description]
   */
  public static function update_title_by_store_slug($title) {

		$store_uri = get_query_var('sl-store', false);

    if ($store_uri) {
      $store_details = \AgileStoreLocator\Model\Store::get_store('', "slug = '$store_uri'");
      $title['title'] = $store_details->title;
    }


    return $title;
  }


  /**
   * [add_meta_description_by_store_slug for adding meta description as store description]
   * @since  4.9.8 [<description>]
   * @param  $title [description]
   */
  public static function add_meta_description_by_store_slug() {
		
    $store_uri = get_query_var('sl-store', false);

    if ($store_uri) {
      
      $store_details = \AgileStoreLocator\Model\Store::get_store('', "slug = '$store_uri'");
      
      if (isset($store_details->description) && $store_details->description) {
        
        \AgileStoreLocator\Helper::add_content_to_head( '<meta name="description" content="' . $store_details->description . '">' . PHP_EOL );
      }
    }

  }


  /**
   * [check slug already exist get next count]
   * @since  4.8.21 [<description>]
   * @param  array   $slug [description]
   */
  public static function count_slug($slug='') {

   global $wpdb;

   $results = $wpdb->get_var("SELECT COUNT(*) AS counter FROM ".ASL_PREFIX."stores WHERE  `slug`  LIKE '$slug%'");
   return $results;

  }


}