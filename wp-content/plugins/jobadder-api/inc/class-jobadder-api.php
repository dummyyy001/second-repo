<?php
/**
 * Jobadder API Class
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || die( 'You are not allowed to access.' ); // Terminate if accessed directly

class BH2OJAA {
    /**
	 * Client ID.
	 *
	 * @var string
	 */
	protected $client_id = '';
    
    /**
	 * Client Secret.
	 *
	 * @var string
	 */
	protected $client_secret = '';

	/**
	 * Board ID
	 * 
	 * @var int
	 */
	protected $board_id = 0;
    
	/**
	 * Base URL.
	 *
	 * @var string
	 */
    public $base_url = 'https://au5api.jobadder.com/v2';
    
	/**
	 * Authorization URL.
	 *
	 * @var string
	 */
	public $auth_url = 'https://id.jobadder.com/connect/authorize';
    
    /**
	 * Token URL.
	 *
	 * @var string
	 */
	public $token_url = 'https://id.jobadder.com/connect/token';
    
    /**
	 * Constructor.
	 */
	public function __construct() {
    	$this->client_id 		= get_option( 'bh2ojaa_options' )['bh2ojaa_options_client_id'];
        $this->client_secret 	= get_option( 'bh2ojaa_options' )['bh2ojaa_options_client_secret'];
        // $this->board_id 		= get_option( 'bh2ojaa_options' )['bh2ojaa_options_board_id'];
		$this->board_id 		= "1000"; // Family Doctor Job Board id is 1000
	}
    
    /**
     * Get authorize ready url
     *
     * @param string $redirect_url
     *
     * @return string The url
     */
    public function get_auth_ready_url( $redirect_url ) {
    	return add_query_arg( array(
            'response_type' => 'code',
            'client_id' 	=> $this->client_id,
            'scope'			=> 'read write offline_access',
            'redirect_uri'	=> $redirect_url
        ), $this->auth_url );
    }

	/**
     * Get access token
     *
     * @param string $auth_code
     * @param string $redirect_url
     *
     * @return string The response data
     */
    public function get_access_token( $auth_code, $redirect_url ) {
    	$response = wp_remote_post( $this->token_url, array(
			'body' => array(
				'client_id' 	=> $this->client_id,
				'client_secret'	=> $this->client_secret,
				'grant_type' 	=> 'authorization_code',
				'code'			=> $auth_code,
				'redirect_uri'	=> $redirect_url
			)
        ) );

		return $response['body'];
    }

	/**
     * Get access token by refresh token
     *
     * @param string $refresh_token
     *
     * @return string The response data
     */
    public function get_access_token_by_refresh_token( $refresh_token ) {
    	$response = wp_remote_post( $this->token_url, array(
			'body' => array(
				'client_id' 	=> $this->client_id,
				'client_secret'	=> $this->client_secret,
				'grant_type' 	=> 'refresh_token',
				'refresh_token'	=> $refresh_token
			)
        ) );

		return $response['body'];
    }

	/**
	 * Get jobs
	 * 
	 * @param array $args
	 * 
	 * @return string The response data
	 */
	public function get_jobs( $args = array() ) {
		$args 		= array_merge( array(
			'access_token' => get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token']
		), $args );

		$params = array();

		foreach ( $args as $key => $value ) {
			$params[] = $key . '=' . $value;
		}

		if ( count( $params ) > 0 ) {
			$params = '?' . implode( '&', $params );
		} else {
			$params = '';
		}

		$response 	= wp_remote_get( $this->base_url . '/jobs' . $params );

		return $response['body'];
	}

	/**
	 * Get a job
	 * 
	 * @param int $job_id
	 * 
	 * @return string The response data
	 */
	public function get_job( $job_id ) {
		$args 		= array(
			'access_token' => get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token']
		);

		$params = array();

		foreach ( $args as $key => $value ) {
			$params[] = $key . '=' . $value;
		}

		if ( count( $params ) > 0 ) {
			$params = '?' . implode( '&', $params );
		} else {
			$params = '';
		}

		$response 	= wp_remote_get( $this->base_url . '/jobs/' . $job_id . $params );

		return $response['body'];
	}
	
	/**
	 * Get count total job ads
	 * 
	 * @param array $args
	 * 
	 * @return string|WP_Error The response data
	 */
	 public function get_total_job_ads( $args = array()) {
		$access_token=get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token'];
		
		$args 		= array_merge( array(
			'access_token' => $access_token,
			'limit' => 1
		), $args );

		$params = array();

		foreach ( $args as $key => $value ) {
			$params[] = $key . '=' . $value;
		}
	
		
		if ( count( $params ) > 0 ) {
			$params = '?' . implode( '&', $params );
		} else {
			$params = '';
		}

		
		$base_url=$this->base_url . '/jobboards/' . $this->board_id . '/ads' . $params;
		$response 	= wp_remote_get( $base_url );
		
		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			/*echo "<pre>";
			print_r(json_decode($response['body']));
			echo "</pre>";*/
			return json_decode($response['body'])->totalCount;
		}
	}
	 
	/**
	 * Get job ads
	 * 
	 * @param array $args
	 * 
	 * @return string|WP_Error The response data
	 */
	public function get_job_ads( $args = array(), $offset=0, $limit=100 ) {
				
		$args 		= array_merge( array(
			'access_token' => get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token'],
			'limit' => $limit,
			'offset' => $offset
		), $args );

		$params = array();

		foreach ( $args as $key => $value ) {
			$params[] = $key . '=' . $value;
		}
	
		
		if ( count( $params ) > 0 ) {
			$params = '?' . implode( '&', $params );
		} else {
			$params = '';
		}

		
		$base_url=$this->base_url . '/jobboards/' . $this->board_id . '/ads' . $params;
		$response 	= wp_remote_get( $base_url );
		
		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			return $response['body'];
		}
	}

	/**
	 * Get a job ad
	 * 
	 * @param int $ad_id
	 * 
	 * @return string The response data
	 */
	public function get_job_ad( $ad_id ) {
		$args 		= array(
			'access_token' => get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token']
		);

		$params = array();

		foreach ( $args as $key => $value ) {
			$params[] = $key . '=' . $value;
		}

		if ( count( $params ) > 0 ) {
			$params = '?' . implode( '&', $params );
		} else {
			$params = '';
		}

		$response 	= wp_remote_get( $this->base_url . '/jobboards/' . $this->board_id . '/ads/' . $ad_id . $params );
		return $response['body'];
	}

	/**
	 * Apply for a job
	 * 
	 * @param int $ad_id
	 * @param array $data
	 * 
	 * @return string The response data
	 */
	public function apply_for_job( $ad_id, $data ) {
		
		$args 		= array_merge( array(
			'access_token' => get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token']
		), $data );

		// print_r($args);
		// print_r("-----");
		// print_r(json_encode($data,true));
		
		$response 	= wp_remote_post( $this->base_url . '/jobboards/' . $this->board_id . '/ads/' . $ad_id . '/applications', 
		array(
			'body' =>json_encode($data,true),
			'headers' => array(
				'Authorization' => "Bearer ". get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token'],
				'Content-Type' => 'application/json'
			) ,
        ));
		
		return $response['body'];
	}

	/**
	 * Submit document in a job application
	 * 
	 * @param int $ad_id
	 * @param int $application_id
	 * @param string $attachment_type
	 * @param array $data
	 * 
	 * @return string The response data
	 */
	public function submit_documents_for_job_application( $ad_id, $application_id, $attachment_type, $data ) {

		$url = $this->base_url . '/jobboards/' . $this->board_id . '/ads/' . $ad_id . '/applications/' . $application_id . '/' . $attachment_type;
		
		$file_data = new CURLFile($data['tmp_name'], $data['type'], $data['name']); 

		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => $file_data)); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '. get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token'],
			'Content-Type: multipart/form-data',
		)); 

		$response = curl_exec($curl); 
		
		curl_close($curl); 
		
		return $response;
		
	}

	public function submit_documents_for_job_application_covernote( $ad_id, $application_id, $attachment_type_cover, $data_cover ) {
		 
		$response 	= wp_remote_post( $this->base_url . '/jobboards/' . $this->board_id . '/ads/' . $ad_id . '/applications/' . $application_id . '/' . $attachment_type_cover, array(
			'body' => $data_cover,
			'headers' => array(
				'Authorization' => "Bearer ". get_option( 'bh2ojaa_options' )['bh2ojaa_options_access_token'],
				'Content-Type' => 'multipart/form-data'
			) ,
        ) );

		return $response['body'];
		
	}
}
