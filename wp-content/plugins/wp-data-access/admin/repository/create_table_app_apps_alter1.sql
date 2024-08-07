ALTER TABLE {wp_prefix}wpda_app_apps{wpda_postfix}
    ADD FOREIGN KEY (app_id)
    REFERENCES wp_wpda_app(app_id);