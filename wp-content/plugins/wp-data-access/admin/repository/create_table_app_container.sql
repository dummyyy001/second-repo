CREATE TABLE {wp_prefix}wpda_app_container{wpda_postfix}
(cnt_id			bigint unsigned		not null	auto_increment
,cnt_dbs		varchar(128)		not null
,cnt_tbl		varchar(128)		not null
,cnt_cls		longtext			not null
,cnt_title		varchar(100)		not null
,app_id			bigint unsigned		not null
,cnt_seq_nr		smallint unsigned	not null
,cnt_table		longtext
,cnt_form		longtext
,cnt_relation   longtext
,primary key (cnt_id)
) {wpda_collate};
