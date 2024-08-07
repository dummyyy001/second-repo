CREATE TABLE {wp_prefix}wpda_app_apps{wpda_postfix}
(app_id			bigint unsigned		not null
,app_id_detail	bigint unsigned		not null
,seq_nr			smallint unsigned	not null
,primary key (app_id, app_id_detail, seq_nr)
) {wpda_collate};