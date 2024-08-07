<?php

namespace AgileStoreLocator\Vendors;


/**
 *
 * This class defines all the codes of the Yoast SEO with the Agile Store Locator
 *
 * @link       https://agilelogix.com
 * @since      4.8.24
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/includes\vendors
 * @author     Your Name <support@agilelogix.com>
 */
class Yoast {
  
  /**
   * [$asl_slug]
   * @var string
   */
  public $asl_slug = 'stores';
  
  /**
   * [__construct description]
   */
  public function __construct() {

  }


  /**
   * [register_hook Register the hook to get it working]
   * @return [type] [description]
   */
  public function register_hook() {

    $rewrite_config = \AgileStoreLocator\Helper::get_configs(['rewrite_slug', 'rewrite_id']);


    if(isset($rewrite_config['rewrite_slug']) && isset($rewrite_config['rewrite_id']) && $rewrite_config['rewrite_slug'] && $rewrite_config['rewrite_id']) {

      // $this->rewrite_slug = $rewrite_config['rewrite_slug'];
      /* $this->store_slug = get_query_var('sl-store', false);
      if ($this->store_slug) {
        $this->store_details = \AgileStoreLocator\Model\Store::get_store('', "slug = '{$this->store_slug}'");
      } */

      add_filter( 'wpseo_sitemap_index', [$this,'add_stores_sitemap'] );
      add_filter( 'wpseo_canonical', [\AgileStoreLocator\Schema\Slug::class, 'update_canonical_tag'] );
      add_filter( 'wpseo_opengraph_url', [$this, 'change_opengraph_url'] );
      add_filter( 'wpseo_title', [$this, 'update_title'] );
      add_filter( 'wpseo_opengraph_title', [$this, 'update_title'] );
      add_filter( 'wpseo_metadesc', [$this, 'add_meta_description_by_store_slug'] );
      add_filter( 'wpseo_opengraph_desc', [$this, 'add_meta_description_by_store_slug'] );
      add_action( 'init', [$this,'init_yoast_sitemap_actions'] );
      add_action( 'init', [$this,'register_stores_sitemap'], 99 );
      add_action( 'init', [$this,'init_yoast_do_sitemap_actions'] );
    }
  }

  /**
   * [update_title updates store details page title]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type] $smp [description]
   */
  public function update_title($title) {
    $store_uri = get_query_var('sl-store', false);

    if ($store_uri) {
      $store_details = \AgileStoreLocator\Model\Store::get_store('', "slug = '$store_uri'");
      $title = $store_details->title . ' - ' . $title;
    }

    return $title;
  }


    /**
   * [add_meta_description_by_store_slug adds meta description]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type] $smp [description]
   */
  public function add_meta_description_by_store_slug($desc) {
    $store_uri = get_query_var('sl-store', false);

    if ($store_uri) {
      $store_details = \AgileStoreLocator\Model\Store::get_store('', "slug = '$store_uri'");
      if (isset($store_details->description) && $store_details->description) {
        return $store_details->description;
      }
    }

    return $desc;
  }
    /**
   * [change_opengraph_url adds URL]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type] $url [description]
   */
  public function change_opengraph_url( $url ) {

      $url = $url.get_query_var('sl-store', false);

      return $url;
  }

  /**
   * [add_stores_sitemap Create a new custom yoast seo sitemap]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type] $smp [description]
   */
  public function add_stores_sitemap(){
     
    global $wpseo_sitemaps;
    
    $date = \AgileStoreLocator\Model\Store::get_last_ts();
    //$date = $wpseo_sitemaps->get_last_modified('asl_stores');
    //$date = $wpseo_sitemaps->get_last_modified('asl_stores');

    if($date) {
      $date = date('Y-m-d' , strtotime($date)) . 'T' . date('H:i:s' , strtotime($date)) . strval('+00:00');
    }

    $smp ='';

    $smp .= '<sitemap>' . "\n";
    $smp .= '<loc>' . home_url() .'/'.$this->asl_slug.'-sitemap.xml</loc>' . "\n";
    $smp .= '<lastmod>' . htmlspecialchars( $date ) . '</lastmod>' . "\n";
    $smp .= '</sitemap>' . "\n";


    return $smp;
  }


  /**
   * [init_yoast_sitemap_actions Generates store's origin sitemap hook]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type]  [description]
   */
  public function init_yoast_sitemap_actions(){
    add_action( "wpseo_do_sitemap_asl_stores", [$this,'generate_stores_origin_combo_sitemap']);
  }

  /**
   * [generate_stores_origin_combo_sitemap Generates store's origin sitemap]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type]  [description]
   */
  public function generate_stores_origin_combo_sitemap(){

    global $wpdb;
    global $wp_query;
    global $wpseo_sitemaps;

    $post_type = 'asl_stores';

    wp_reset_query();

    $args = array(
      'posts_per_page'   => -1,
      'orderby'          => 'post_date',
      'order'            => 'DESC',
      'post_type'        => $post_type,
      'post_status'      => 'publish',
      'suppress_filters' => true
    );
    query_posts( $args );

    wp_reset_postdata();

    $posts_array = get_posts( $args );

    $output = '';
    if( !empty( $posts_array ) ) {

      $chf    = 'weekly';
      $pri    = 1.0;


      $page_url = apply_filters( 'wpml_home_url', home_url('/'));

      // replace the double slash
      $page_url = preg_replace('#(?<!:)/+#im', '/', $page_url);


      //  must have a slash in the end
      if(substr($page_url, -1) != '/') {
        $page_url = $page_url.'/';
      }

      //  Get the detail page
      $detail_page = \AgileStoreLocator\Helper::get_configs('rewrite_slug');

      foreach ( $posts_array as $p ) {

        $p->post_type   = $post_type;
        $p->post_status = 'publish';
        
        $url = array();

        if ( isset( $p->post_modified_gmt ) && $p->post_modified_gmt != '0000-00-00 00:00:00' && $p->post_modified_gmt > $p->post_date_gmt ) {
          $url['mod'] = $p->post_modified_gmt;
        } 
        else {
          if ( '0000-00-00 00:00:00' != $p->post_date_gmt ) {
            $url['mod'] = $p->post_date_gmt;
          } else {
            $url['mod'] = $p->post_date;
          }
        }   


        $url['loc'] = $page_url.$detail_page.'/'.$p->post_name.'/';
        $url['chf'] = $chf;
        $url['pri'] = $pri;

        if (!empty($url)) {

          $output .= $wpseo_sitemaps->renderer->sitemap_url( $url );
        }

      }
    }

    if ( empty( $output ) ) {
        $wpseo_sitemaps->bad_sitemap = true;
        return;
    }

    //Build the full sitemap
    $sitemap = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
    $sitemap .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ';
    $sitemap .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $sitemap .= $output . '</urlset>';

    //echo $sitemap;
    $wpseo_sitemaps->set_sitemap($sitemap);

  }

  /**
   * [generate_stores_origin_combo_sitemap]
   * On init, run the function that will register our new sitemap as well
   * as the function that will be used to generate the XML. This creates an
   * action that we can hook into built around the new
   * sitemap name - 'register_stores_sitemap'
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type]  [description]
   */
  
  public function register_stores_sitemap() {
    global $wpseo_sitemaps;
    $wpseo_sitemaps->register_sitemap( $this->asl_slug, [$this,'generate_stores_origin_combo_sitemap'] );
  }

  /**
   * [init_yoast_do_sitemap_actions]
   * @param  [type]  [description]
   * @param  [type]  [description]
   * @return [type]  [description]
   */
  public function init_yoast_do_sitemap_actions(){
    add_action( 'wp_seo_do_sitemap_our-'.$this->asl_slug, [$this,'generate_stores_origin_combo_sitemap'] );
  }

}
