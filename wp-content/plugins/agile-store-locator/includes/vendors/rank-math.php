<?php
// namespace AgileStoreLocator\Vendors;

namespace RankMath\Sitemap\Providers;

defined( 'ABSPATH' ) || exit;

/**
 * Implement the MathRank sitemap 
 */
class ASLRankMath implements Provider {


	/**
	 * [get_asl_slug Get the asl slug]
	 * @return [type] [description]
	 */
	public function get_asl_slug(){
		
		$slug = \AgileStoreLocator\Helper::get_configs('rewrite_slug');

		if (!empty($slug)) {
			return $slug;
		}

		return 'stores';
	}
	

	/**
	 * [handles_type description]
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public function handles_type( $type ) {
		$this->get_asl_slug();

		return $this->get_asl_slug()  === $type;
	}


	/**
	 * [get_index_links Add the main stores node in the sitemap]
	 * @param  [type] $max_entries [description]
	 * @return [type]              [description]
	 */
	public function get_index_links( $max_entries ) {
		

		return [
			[
				'loc'     => \RankMath\Sitemap\Router::get_base_url( $this->get_asl_slug().'-sitemap.xml' ),
				'lastmod' => '',
			]
		];
	}

	/**
	 * [get_sitemap_links Add the sitemap links]
	 * @param  [type] $type         [description]
	 * @param  [type] $max_entries  [description]
	 * @param  [type] $current_page [description]
	 * @return [type]               [description]
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ) {


	  $post_type = 'asl_stores';
	  $link_urls = array();

	  //	Get all the languages
		$stores = \AgileStoreLocator\Model\Store::get_stores(['lang' => '*']);

		$output = '';
		
		if($stores) {

			$chf 		= 'weekly';
			$pri 		= 1.0;


	    $page_url = apply_filters( 'wpml_home_url', home_url('/'));

	    // replace the double slash
	    $page_url = preg_replace('#(?<!:)/+#im', '/', $page_url);


	    //  must have a slash in the end
	    if(substr($page_url, -1) != '/') {
	      $page_url = $page_url.'/';
	    }

	    //  Get the detail page
	    $detail_page = $this->get_asl_slug();

	    //	 Loop over the stores
			foreach ($stores as $store) {
				
				$url = array();

				$url['mod'] = ($store->updated_on)? $store->updated_on: $store->created_on;
				$url['loc'] = $page_url.$detail_page.'/'.$store->slug.'/';

				if (!empty($url)) {
					$link_urls[] = $url;
				}
			}
		}

		$links     = $link_urls;


		return $links;
	}
	/**
	 * [update_page_title_by_store_slug for updating <title> as store title]
	 * @since  4.9.8 [<description>]
	 * @param  $title [description]
	 */
	public  static  function update_page_title_by_store_slug( $title ) {
		
		$store_uri = get_query_var('sl-store', false);

	  if ($store_uri) {
	    $store_details = \AgileStoreLocator\Model\Store::get_store('', "slug = '$store_uri'");
	    $title = $title.$store_details->title;
	  }

		return $title;
	}

}

