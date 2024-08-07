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
* @subpackage AgileStoreLocator/elements/category
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Category {


    /**
    * [Get the all store categories for vc]
    * @since  4.8.21
    * @return [type]          [description]
    */
    public static function get_all_categories( $addon = null) {

        global $wpdb;

        $ASL_PREFIX   = ASL_PREFIX;
        $categories   = [];
        $orde_by      = " `category_name` ;";
        $where_clause = "`lang` = ''";

        //  Get the results
        $results    = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}categories WHERE {$where_clause} ORDER BY {$orde_by}");

        //  Loop over
        if($addon) {

            foreach ($results as $key => $value) {

                if ($addon === 'asl_vc') {

                    $categories[$value->category_name] =  $value->id;

                } elseif ($addon === 'asl_ele') {

                    $categories[$value->id] = $value->category_name;

                } else {

                     $categories[$value->category_name] =  $value->id;
                }

            }

            return $categories;
        }


        return $results;
    }


    /**
     * [get_category_by_id Return the Category by id]
     * @param  [type] $category_id [description]
     * @return [type]              [description]
     */
    public static function get_category_by_id($category_id) {

    }

    /**
     * [get_categories Get all the categories of a language]
     * @param  [type] $lang          [description]
     * @param  string $category_name [description]
     * @param  [type] $ids           [description]
     * @return [type]                [description]
     */
    public static function get_categories($lang, $category_name = 'category_name', $ids = null) {

        global $wpdb;
        
        $ids_clause = '';


        //  If Ids are provided
        if($ids) {

            //  Filter the numbers
            $ids = explode(',', $ids);
            $ids = array_map( 'absint', $ids );
            $ids = implode(',', $ids);

            $ids_clause = "AND id IN ($ids)";
        }

        //  Get the results
        $cats    = $wpdb->get_results("SELECT `id`,`category_name` as $category_name, `icon`, `ordr` FROM ".ASL_PREFIX."categories WHERE lang = '$lang' $ids_clause ORDER BY category_name ASC");

        //  Loop & filter
        if($cats) {

            foreach($cats as $cat) {

                $cat->$category_name =  esc_attr($cat->$category_name);
            }
        }

        return $cats;
    }

    /**
      * [get_parents Get all the parent categories]
      * @param  string $lang [description]
      * @return [type]       [description]
      */
    public static function get_parent($lang = '', $parent_id = NULL) {


        global $wpdb;

        $ASL_PREFIX   = ASL_PREFIX;
        
        $where_clause = "`lang` = '$lang'";
        $where_clause .= $parent_id ? " AND `id` = $parent_id" : "";
        
        //  Get the results
        $results    = $wpdb->get_row("SELECT * FROM {$ASL_PREFIX}categories WHERE {$where_clause}");
         
        return $results;
    }

    /**
      * [add_category Return category insert_id]
      * @param  [type] $name [description]
      * @param  [type] $lang [description]
      * @return [type]       [description]
      */
      public static function add_category(array $data) {
        global $wpdb;

        $wpdb->insert(
            ASL_PREFIX.'categories',
            $data,
            array('%s','%d','%s','%s','%d')
        );

        return $wpdb->insert_id;
     }




    /**
      * [get_category_by_name Return the category by name]
      * @param  [type] $name [description]
      * @param  [type] $lang [description]
      * @return [type]       [description]
    */
    public static function get_category_by_name($name, $parent_id = 0, $lang = '') {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM ".ASL_PREFIX."categories WHERE lang = '$lang' AND parent_id = $parent_id AND category_name = %s", $name));
    }


    /**
    * [get_app_categories Get all the categories in child/parent hirarachy]
    * @param  [type] $lang          [description]
    * @param  string $filter_clause [description]
    * @return [type]                [description]
    */
    public static function get_app_categories($lang, $filter_clause = '') {

        global $wpdb;

        $all_categories = array();

        //  Get the data
        $wpdb->show_errors = false;
        $results = $wpdb->get_results("SELECT id, category_name as name, icon, ordr, parent_id FROM " . ASL_PREFIX . "categories WHERE lang = '$lang' " . $filter_clause . ' ORDER BY parent_id ASC');
        if (!count($results) && strpos($wpdb->last_error, 'parent_id') !== false) {
            \AgileStoreLocator\Activator::add_cat_parent_id();
        }

        //  Has child categories
        $has_childs     = false;

        // Organize categories into a hierarchical structure
        foreach ($results as $_result) {
            
            $category = (object) array(
                'id' => $_result->id,
                'name' => esc_attr($_result->name),
                'icon' => $_result->icon,
                'ordr' => $_result->ordr,
                'children' => array() // Initialize an array to store child categories
            );

            if ($_result->parent_id) {
                
                // If the category has a parent, ensure the parent category is initialized
                if (!isset($all_categories[$_result->parent_id])) {
                    $all_categories[$_result->parent_id] = (object) array('children' => array());
                }
                
                // Add the current category as a child to the parent category
                $all_categories[$_result->parent_id]->children[] = $category;

                $has_childs = true;
            } 
            else {
                // If the category has no parent, add it directly to the main array
                $all_categories[$_result->id] = $category;
            }
        }

        return [$all_categories, $has_childs];
    }

}
