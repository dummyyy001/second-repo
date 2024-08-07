<?php

namespace AgileStoreLocator\Vendors;


/**
 *
 * This class defines all the codes of the Google Matrix API with the Agile Store Locator
 *
 * @link       https://agilelogix.com
 * @since      4.8.24
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/includes\vendors
 * @author     Your Name <support@agilelogix.com>
 */
class Matrix {

    public function __construct() {

    }

    
    public static function get_nearest_stores($origins, $destinations) {

        $unit = \AgileStoreLocator\Helper::get_configs('distance_unit');
        $unit = $unit == 'KM' ? 'metric' : 'imperial';
        
        $api_key = \AgileStoreLocator\Helper::get_configs('server_key');


        $origins = is_array($origins) ? implode('%7C', $origins) : $origins;
        $destinations = is_array($destinations) ? implode('%7C', $destinations) : $destinations;
        
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=" . $unit . "&origins=" . $origins . "&destinations=" . $destinations . "&key=" . $api_key;

        $curl_session = curl_init(); 
        curl_setopt($curl_session, CURLOPT_URL, $url);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_HEADER, false); 
        $response = curl_exec($curl_session);
        curl_close($curl_session);

        return $response;
    }
}