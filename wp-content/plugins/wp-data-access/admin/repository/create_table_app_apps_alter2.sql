ALTER TABLE {wp_prefix}wpda_app_apps{wpda_postfix}
    ADD FOREIGN KEY (app_id_detail)
	REFERENCES wp_wpda_app(app_id);