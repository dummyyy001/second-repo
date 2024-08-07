<?php
/**
 * Jobadder Batch Class
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || die( 'You are not allowed to access.' ); // Terminate if accessed directly

if ( class_exists( 'WP_Batch' ) ) {

	/**
	 * Class BH2OAJAA_Batch
	 */
	class BH2OAJAA_Batch extends WP_Batch {

		/**
		 * Unique identifier of each batch
         * 
		 * @var string
		 */
		public $id = 'jobadder_job_ads';

		/**
		 * Describe the batch
         * 
		 * @var string
		 */
		public $title = 'Jobadder Job Ads';

		/**
		 * To setup the batch data use the push() method to add WP_Batch_Item instances to the queue.
		 *
		 * Note: If the operation of obtaining data is expensive, cache it to avoid slowdowns.
		 *
		 * @return void
		 */
		public function setup() {
			$ads = bh2ojaa_get_jobadder_job_ads();
		/*	echo "<pre>";
			print_r($ads);
			echo "</pre>";
			die;*/
			if ( !is_wp_error( $ads ) ) {
			//	foreach ( $ads->items as $ad ) {
					foreach ( $ads as $ad ) {
					$this->push( new WP_Batch_Item( $ad->adId, array( 'ad_id' => $ad->adId ) ) );
				}
			}
		}

		/**
		 * Handles processing of batch item. One at a time.
		 *
		 * In order to work it correctly you must return values as follows:
		 *
		 * - TRUE - If the item was processed successfully.
		 * - WP_Error instance - If there was an error. Add message to display it in the admin area.
		 *
		 * @param WP_Batch_Item $item
		 *
		 * @return bool|\WP_Error
		 */
		public function process( $item ) {
			// Retrieve the custom data
			$api_ad_id	= $item->get_value( 'ad_id' );
			$ad			= bh2ojaa_get_jobadder_job_ad( $api_ad_id );
            $ad_id 		= bh2ojaa_insert_job_ad( $ad );

            if ( is_wp_error( $ad_id ) ) return $ad_id;

            return true;
		}

		/**
		 * Called when specific process is finished (all items were processed).
		 * This method can be overriden in the process class.
		 * @return void
		 */
		public function finish() {
			$delay 		= 300;
			$recurrence	= 'five-min';
			$hook 		= 'bh2ojaa_refresh_job_ads';

			if ( !wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time() + $delay, $recurrence, $hook );
			} else {
				wp_clear_scheduled_hook( $hook );
				wp_reschedule_event( time() + $delay, $recurrence, $hook );
			}
		}

	}
}