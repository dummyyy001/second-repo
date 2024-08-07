<?php

namespace WPDataAccess\Data_Apps;

class WPDA_Apps_List extends WPDA_Container {
    public function show() {
        if ( !current_user_can( 'manage_options' ) ) {
            if ( !is_admin() && !$this->send_feedback() ) {
                return;
            }
            $this->show_feedback( __( 'Not authorized', 'wp-data-access' ) );
            return;
        }
        if ( is_admin() ) {
            $this->show_on_backend();
        } else {
            $this->show_on_frontend();
        }
        $this->add_client();
    }

    private function show_on_backend() {
        ?>

			<div class="wrap wpda-apps-backend">
				<h1 class="wp-heading-inline">
					<?php 
        ?>
					Apps
				</h1>

				<?php 
        $this->show_on_frontend();
        ?>
			</div>

			<?php 
    }

    private function show_on_frontend() {
        ?>

			<div class="wpda-pp-container">
				<div class="pp-container-applist"></div>
			</div>

			<?php 
    }

}
