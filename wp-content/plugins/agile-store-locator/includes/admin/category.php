<?php

namespace AgileStoreLocator\Admin;


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

use AgileStoreLocator\Admin\Base;

/**
 * The category manager functionality of the admin.
 *
 * @link       https://agilestorelocator.com
 * @since      4.7.32
 *
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/Admin/Category
 */

class Category extends Base {


  /**
   * [__construct description]
   */
  public function __construct() {
    
    parent::__construct();
  }
  
  ////////////////////////////////
  /////////ALL Category Methods //
  ////////////////////////////////
  
  /**
   * [add_category Add Category Method]
   */
  public function add_category() {

    global $wpdb;

    $response = new \stdclass();
    $response->success = false;

    //  Forms Data
    $form_data = stripslashes_deep($_REQUEST['data']);

    //  The Order ID
    $order_id  = (isset($form_data['ordr']) && is_numeric($form_data['ordr']))? $form_data['ordr']: '0';

    //  Parameters to Save
    $data_params = array(
      'parent_id' => $this->clean_input($form_data['parent_id']),
      'category_name' => $this->clean_input($form_data['category_name']),
      'ordr'          => $order_id
    );

    //  lang
    $data_params['lang']    = $this->lang;


    //  Upload the Category Icon File
    $upload_result  = $this->_file_uploader($_FILES["files"], 'svg');

    //  Validate the Upload Success
    if(isset($upload_result['success']) && $upload_result['success']) {

      $file_name    = $upload_result['file_name'];

      //  Add the newly uploaded file
      $data_params['icon'] = $file_name;
    }
    else {

      $response->msg      = ($upload_result['error'])? $upload_result['error']: esc_attr__('Error! Failed to upload the image.','asl_locator');
      return $this->send_response($response);
    }
    
    
    //  Insert the Category Record
    if($wpdb->insert(ASL_PREFIX.'categories', $data_params , array('%s','%s','%s'))) {
        
      $response->msg = esc_attr__("Category added successfully",'asl_locator');
      $response->data = $data_params;
      $response->success = true;
    }
    else {
      
      $response->msg = esc_attr__('Error occurred while saving record','asl_locator');//$form_data
    }

    return $this->send_response($response);
  }

  /**
   * [delete_category delete category/categories]
   * @return [type] [description]
   */
  public function delete_category() {

    global $wpdb;

    $response  = new \stdclass();
    $response->success = false;

    $multiple = isset($_REQUEST['multiple'])? $_REQUEST['multiple']: null;
    $delete_sql;$cResults;

    if($multiple) {

      $item_ids      = implode(",", array_map( 'intval', $_POST['item_ids'] ));
      $delete_sql    = "DELETE FROM ".ASL_PREFIX."categories WHERE id IN (".$item_ids.")";
      $cResults      = $wpdb->get_results("SELECT * FROM ".ASL_PREFIX."categories WHERE id IN (".$item_ids.")");
    }
    else {

      $category_id   = intval($_REQUEST['category_id']);
      $delete_sql    = "DELETE FROM ".ASL_PREFIX."categories WHERE id = ".$category_id;
      $cResults      = $wpdb->get_results("SELECT * FROM ".ASL_PREFIX."categories WHERE id = ".$category_id );
    }


    if(count($cResults) != 0) {
      
      if($wpdb->query($delete_sql))
      {
          $response->success = true;
          foreach($cResults as $c) {

            $inputFileName = ASL_UPLOAD_DIR.'icon/'.sanitize_file_name($c->icon);
          
            if(file_exists($inputFileName) && $c->icon != 'default.png') {  
                  
              unlink($inputFileName);
            }
          }             
      }
      else
      {
        $response->error = esc_attr__('Error occurred while deleting record','asl_locator');//$form_data
        $response->msg   = $wpdb->show_errors();
      }
    }
    else
    {
      $response->error = esc_attr__('Error occurred while deleting record','asl_locator');
    }

    if($response->success)
      $response->msg = ($multiple)?__('Categories deleted successfully.','asl_locator'): esc_attr__('Category deleted successfully.','asl_locator');
    
    return $this->send_response($response);
  }


  /**
   * [update_category update category with icon]
   * @return [type] [description]
   */
  public function update_category() {

    global $wpdb;

    $response  = new \stdclass();
    $response->success = false;

    $data        = stripslashes_deep($_REQUEST['data']);
    
    //  The Order ID
    $order_id    = (isset($data['ordr']) && is_numeric($data['ordr']))? $data['ordr']: '0';
      
    //  Parameters to Save
    $data_params = array('category_name' => $this->clean_input($data['category_name']), 'parent_id' => $this->clean_input($data['parent_id']), 'ordr' => $order_id);



    // Have Icon to Update?
    if($data['action'] == "notsame") {

      //  Upload the Icon File
      $upload_result  = $this->_file_uploader($_FILES["files"], 'svg');

      //  Validate the Upload Success
      if(isset($upload_result['success']) && $upload_result['success']) {

        $file_name    = $upload_result['file_name'];

        //  Add the newly uploaded file
        $data_params['icon'] = $file_name;

        //  Delete the old icon if exist
        $old_icon     = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".ASL_PREFIX."categories WHERE id = %d", $data['category_id']));

        //  Delete the old file, if exist
        if (file_exists(ASL_UPLOAD_DIR.'svg/'.$old_icon[0]->icon)) { 
          unlink(ASL_UPLOAD_DIR.'svg/'.sanitize_file_name($old_icon[0]->icon));
        }
      }
      else {

        $response->msg      = ($upload_result['error'])? $upload_result['error']: esc_attr__('Error! Failed to upload the image.','asl_locator');
        return $this->send_response($response);
      }

    }
    
      
    $wpdb->update(ASL_PREFIX."categories", $data_params, array('id' => $data['category_id']));
    $response->msg      = esc_attr__('Category updated successfully.','asl_locator');
    $response->post  = $data;
    $response->success  = true;
        
    return $this->send_response($response);
  }


  /**
   * [get_category_by_id get category by id]
   * @return [type] [description]
   */
  public function get_category_by_id() {

    global $wpdb;

    $response  = new \stdclass();
    $response->success = false;

    $category_id = isset($_REQUEST['category_id'])? intval($_REQUEST['category_id']) : 0;
    
    $response->item    = $wpdb->get_row( "SELECT * FROM ".ASL_PREFIX."categories WHERE id = $category_id");

    $response->item->parent = $response->item->parent_id ? \AgileStoreLocator\Model\Category::get_parent('', $response->item->parent_id) : null;

    if($response->item) {

      $response->success = true;
    }
    else {
      $response->error = esc_attr__('Error occurred while geting record','asl_locator');//$form_data

    }
    return $this->send_response($response);
  }


  /**
   * [get_categories GET the Categories]
   * @return [type] [description]
   */
  public function get_categories() {

    global $wpdb;
    $start = isset( $_REQUEST['iDisplayStart'])?$_REQUEST['iDisplayStart']:0;
    $start = isset( $_REQUEST['iDisplayStart'])?$_REQUEST['iDisplayStart']:0;
    $params  = isset($_REQUEST)?$_REQUEST:null;   
    
    if (isset( $_REQUEST['parent_id'])) $parent_id = $_REQUEST['parent_id'];

    $acolumns = array(
      'id','id','category_name','ordr','icon','created_on', 'parent_id'
    );

    $columnsFull = $acolumns;

    $clause = array();

    if(isset($_REQUEST['filter'])) {

      foreach($_REQUEST['filter'] as $key => $value) {

        if(!$key || $key  == 'undefined' || empty($key))
          continue;
        
        if(!$value || $value  == 'undefined' || empty($value))
          continue;

        if($value != '') {

          //  Clean it
          $value    = sanitize_text_field($value);
          $key      = sanitize_text_field($key);

          $clause[] = "$key like '%{$value}%'";
        }
      }
    } 
    
    //  Add the lang Filter
    $clause[] = "lang = '{$this->lang}'";
    
    //iDisplayStart::Limit per page
    $sLimit = "";
    if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' )
    {
      $sLimit = "LIMIT ".intval( $_REQUEST['iDisplayStart'] ).", ".
        intval( $_REQUEST['iDisplayLength'] );
    }

    /*
     * Ordering
     */
    $sOrder = "";
    if ( isset( $_REQUEST['iSortCol_0'] ) )
    {
      $sOrder = "ORDER BY  ";

      for ( $i=0 ; $i < intval( $_REQUEST['iSortingCols'] ) ; $i++ )
      {
        if (isset($_REQUEST['iSortCol_'.$i]))
        {
          $sort_dir = (isset($_REQUEST['sSortDir_0']) && $_REQUEST['sSortDir_0'] == 'asc')? 'ASC': 'DESC';
          $sOrder .= "`".$acolumns[ intval( $_REQUEST['iSortCol_'.$i] )  ]."` ".$sort_dir;
          break;
        }
      }
      

      //$sOrder = substr_replace( $sOrder, "", -2 );
      if ( $sOrder == "ORDER BY" )
      {
        $sOrder = "";
      }
    }


    $sWhere = implode(' AND ', $clause);
    
    if($sWhere != '')$sWhere = ' WHERE '.$sWhere;
    
    $fields = implode(',', $columnsFull);
    
    ###get the fields###
    $sql =  "SELECT $fields FROM ".ASL_PREFIX."categories";

    $sqlCount = "SELECT count(*) 'count' FROM ".ASL_PREFIX."categories";

    // Get all categories which are not children of any other
    $parent_categories = $wpdb->get_results("SELECT `id`, `category_name` FROM ".ASL_PREFIX."categories WHERE `parent_id` = 0");
    if (!count($parent_categories) && strpos($wpdb->last_error, 'parent_id') !== false) {
      \AgileStoreLocator\Activator::add_cat_parent_id();
    }
    /*
     * SQL queries
     * Get data to display
     */
    $sQuery = "{$sql} {$sWhere} {$sOrder} {$sLimit}";
    $data_output  = $wpdb->get_results($sQuery);
      
    $error_status = $wpdb->last_error;

    /* Data set length after filtering */
    $sQuery = "{$sqlCount} {$sWhere}";
    $r = $wpdb->get_results($sQuery);
    $iFilteredTotal = $r[0]->count;
    
    $iTotal = $iFilteredTotal;

      /*
     * Output
     */
    $sEcho = isset($_REQUEST['sEcho'])?intval($_REQUEST['sEcho']):1;
    $output = array(
      "sEcho" => intval($_REQUEST['sEcho']),
      "error" => $error_status,
      "iTotalRecords" => $iTotal,
      "iTotalDisplayRecords" => $iFilteredTotal,
      "parent_categories" => $parent_categories,
      "aaData" => array()
    );
    
    foreach($data_output as $aRow) {
        
      $row = $aRow;

      $row->parent_name = '';
      
      //  When we have a parent
      if ($row->parent_id) {
        
        $parent_key       = array_search($row->parent_id, array_column($parent_categories, 'id'));
        $row->parent_name = esc_attr($parent_categories[$parent_key]->category_name);
      }

      $row->icon    = "<img  src='".ASL_UPLOAD_URL."svg/".$row->icon."' alt=''  style='width:20px'/>"; 
      $row->action  = '<div class="edit-options"><a data-id="'.$row->id.'" title="Edit" class="edit_category"><svg width="14" height="14"><use xlink:href="#i-edit"></use></svg></a><a title="Delete" data-id="'.$row->id.'" class="delete_category g-trash"><svg width="14" height="14"><use xlink:href="#i-trash"></use></svg></a></div>';
      $row->check   = '<div class="custom-control custom-checkbox"><input type="checkbox" data-id="'.$row->id.'" class="custom-control-input" id="asl-chk-'.$row->id.'"><label class="custom-control-label" for="asl-chk-'.$row->id.'"></label></div>';
        
      //  Clean it
      $row->category_name = esc_attr($row->category_name);

      $output['aaData'][] = $row;
    }

    return $this->send_response($output);
  }
  
}