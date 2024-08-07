<?php

namespace WPDataAccess\API;

use stdClass;
use WPDataAccess\Connection\WPDADB;
use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
use WPDataAccess\Plugin_Table_Models\WPDA_App_Container_Model;
use WPDataAccess\Plugin_Table_Models\WPDA_App_Model;
use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
use WPDataAccess\WPDA;
class WPDA_Apps extends WPDA_API_Core {
    public function register_rest_routes() {
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/init', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_init'),
            'permission_callback' => '__return_true',
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/list', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_list'),
            'permission_callback' => '__return_true',
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/meta', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_meta'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/table/meta', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_table_meta'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'dbs' => $this->get_param( 'dbs' ),
                'tbl' => $this->get_param( 'tbl' ),
                'waa' => array(
                    'required'    => false,
                    'type'        => 'boolean',
                    'description' => __( 'With admin actions (to support table exports)', 'wp-data-access' ),
                ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/create', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_create'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_name'     => $this->get_param( 'app_name' ),
                'app_title'    => $this->get_param( 'app_title' ),
                'app_type'     => $this->get_param( 'app_type' ),
                'app_settings' => $this->get_param( 'app_settings' ),
                'app_dbs'      => $this->get_param( 'dbs' ),
                'app_tbl'      => $this->get_param( 'tbl' ),
                'app_cls'      => $this->get_param( 'app_cls' ),
                'app_table'    => array(
                    'required'          => true,
                    'type'              => 'string',
                    'description'       => __( 'Table settings', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/copy', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_copy'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/details', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_details'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
                'cnt_id' => $this->get_param( 'cnt_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/detailmeta', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_detail_meta'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'  => $this->get_param( 'app_id' ),
                'cnt_id'  => $this->get_param( 'cnt_id' ),
                'rel_tab' => $this->get_param( 'rel_tab' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/detailreorder', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_detail_reorder'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'      => $this->get_param( 'app_id' ),
                'cnt_id_from' => $this->get_param( 'cnt_id' ),
                'cnt_id_to'   => $this->get_param( 'cnt_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/relationship/create', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_relationship_create'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'       => $this->get_param( 'app_id' ),
                'app_title'    => $this->get_param( 'app_title' ),
                'app_dbs'      => $this->get_param( 'dbs' ),
                'app_tbl'      => $this->get_param( 'tbl' ),
                'app_cls'      => $this->get_param( 'app_cls' ),
                'app_relation' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'description'       => __( 'Table settings', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/relationship/update', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_relationship_update'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'       => $this->get_param( 'app_id' ),
                'app_cnt'      => $this->get_param( 'cnt_id' ),
                'app_title'    => $this->get_param( 'app_title' ),
                'app_dbs'      => $this->get_param( 'dbs' ),
                'app_tbl'      => $this->get_param( 'tbl' ),
                'app_cls'      => $this->get_param( 'app_cls' ),
                'app_relation' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'description'       => __( 'Table settings', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/relationship/delete', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_relationship_delete'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'cnt_id' => $this->get_param( 'cnt_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/save', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_save'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'          => $this->get_param( 'app_id' ),
                'app_name'        => $this->get_param( 'app_name' ),
                'app_title'       => $this->get_param( 'app_title' ),
                'app_type'        => $this->get_param( 'app_type' ),
                'app_settings'    => $this->get_param( 'app_settings' ),
                'app_add_to_menu' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'description'       => __( 'Add app to dashboard menu', 'wp-data-access' ),
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'app_dbs'         => $this->get_param( 'dbs' ),
                'app_tbl'         => $this->get_param( 'tbl' ),
                'app_cls'         => $this->get_param( 'app_cls' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/remove', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_remove'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/settings', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_settings'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'   => $this->get_param( 'app_id' ),
                'cnt_id'   => $this->get_param( 'cnt_id' ),
                'target'   => array(
                    'required'          => true,
                    'type'              => 'string',
                    'description'       => __( 'Setting target', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'settings' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'description'       => __( 'Settings - JSON string', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'theme'    => array(
                    'required'          => false,
                    'type'              => 'string',
                    'description'       => __( 'Theme settings - JSON string', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            ),
        ) );
        // DML
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/select', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_select'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'             => $this->get_param( 'app_id' ),
                'cnt_id'             => $this->get_param( 'cnt_id' ),
                'col'                => $this->get_param( 'cols' ),
                'page_index'         => $this->get_param( 'page_index' ),
                'page_size'          => $this->get_param( 'page_size' ),
                'search'             => $this->get_param( 'search' ),
                'search_columns'     => $this->get_param( 'search_columns' ),
                'search_column_fns'  => $this->get_param( 'search_column_fns' ),
                'search_column_lov'  => $this->get_param( 'search_column_lov' ),
                'md'                 => array(
                    'required'          => false,
                    'type'              => 'mixed',
                    'description'       => __( 'Master detail join conditions', 'wp-data-access' ),
                    'sanitize_callback' => function ( $param ) {
                        $columns = array();
                        foreach ( rest_sanitize_object( $param ) as $column_name => $value ) {
                            $columns[$this->sanitize_db_identifier( $column_name )] = sanitize_text_field( wp_unslash( $value ) );
                        }
                        return $columns;
                    },
                    'validate_callback' => function ( $param ) {
                        return is_array( $param );
                    },
                ),
                'sorting'            => $this->get_param( 'sorting' ),
                'row_count'          => $this->get_param( 'row_count' ),
                'row_count_estimate' => $this->get_param( 'row_count_estimate' ),
                'media'              => $this->get_param( 'media' ),
                'rel_tab'            => $this->get_param( 'rel_tab' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/get', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_get'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'  => $this->get_param( 'app_id' ),
                'cnt_id'  => $this->get_param( 'cnt_id' ),
                'key'     => $this->get_param( 'key' ),
                'media'   => $this->get_param( 'media' ),
                'rel_tab' => $this->get_param( 'rel_tab' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/insert', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_insert'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'   => $this->get_param( 'app_id' ),
                'cnt_id'   => $this->get_param( 'cnt_id' ),
                'val'      => $this->get_param( 'val' ),
                'join_tab' => $this->get_param( 'join_tab' ),
                'rel_tab'  => $this->get_param( 'rel_tab' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/update', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_update'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'   => $this->get_param( 'app_id' ),
                'cnt_id'   => $this->get_param( 'cnt_id' ),
                'key'      => $this->get_param( 'key' ),
                'val'      => $this->get_param( 'val' ),
                'join_tab' => $this->get_param( 'join_tab' ),
                'rel_tab'  => $this->get_param( 'rel_tab' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/delete', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_delete'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
                'cnt_id' => $this->get_param( 'cnt_id' ),
                'key'    => $this->get_param( 'key' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/lov', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_lov'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id'            => $this->get_param( 'app_id' ),
                'cnt_id'            => $this->get_param( 'cnt_id' ),
                'col'               => $this->get_param( 'col' ),
                'search'            => $this->get_param( 'search' ),
                'search_columns'    => $this->get_param( 'search_columns' ),
                'search_column_fns' => $this->get_param( 'search_column_fns' ),
                'search_column_lov' => $this->get_param( 'search_column_lov' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/lookup', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_lookup'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
                'cnt_id' => $this->get_param( 'cnt_id' ),
                'target' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'description'       => __( 'Target: table or form', 'wp-data-access' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'col'    => $this->get_param( 'col' ),
                'colk'   => $this->get_param( 'col' ),
                'colv'   => $this->get_param( 'col' ),
                'cold'   => $this->get_param( 'key' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/lookup/dbs', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_lookup_dbs'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
                'cnt_id' => $this->get_param( 'cnt_id' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/lookup/tbl', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_lookup_tbl'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
                'cnt_id' => $this->get_param( 'cnt_id' ),
                'dbs'    => $this->get_param( 'dbs' ),
            ),
        ) );
        register_rest_route( WPDA_API::WPDA_NAMESPACE, 'app/lookup/cls', array(
            'methods'             => array('POST'),
            'callback'            => array($this, 'app_lookup_cls'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'app_id' => $this->get_param( 'app_id' ),
                'cnt_id' => $this->get_param( 'cnt_id' ),
                'dbs'    => $this->get_param( 'dbs' ),
                'tbl'    => $this->get_param( 'tbl' ),
            ),
        ) );
    }

    private function get_app_columns( $columns ) {
        if ( !is_array( $columns ) ) {
            return false;
        }
        return array_map( function ( $value ) {
            return $value['columnName'];
        }, array_filter( $columns, function ( $column ) {
            return $column['isSelected'];
        } ) );
    }

    private function get_app_table_columns( $settings, $table_settings ) {
        if ( !isset( $settings['columns'] ) ) {
            return false;
        }
        $columns = $this->get_app_columns( $settings['columns'] );
        if ( false === $columns ) {
            return false;
        }
        $columns_available = array_flip( $columns );
        if ( !isset( $settings['table'] ) ) {
            return array_map( function () {
                return true;
            }, $columns_available );
        }
        if ( !is_array( $table_settings ) || !isset( $table_settings['columns'] ) ) {
            return false;
        }
        $table_columns = $table_settings['columns'];
        for ($i = 0; $i < count( $columns ); $i++) {
            if ( isset( $columns_available[$columns[$i]] ) ) {
                $column_name = $columns[$i];
                $columns_available[$columns[$i]] = count( array_filter( $table_columns, function ( $column ) use($columns, $column_name) {
                    if ( !isset( $column['columnName'], $column['queryable'] ) ) {
                        return false;
                    }
                    $queryable = $column['queryable'];
                    return $column_name === $column['columnName'] && true === $queryable;
                } ) ) > 0;
            }
        }
        return $columns_available;
    }

    private function get_app_form_columns( $settings ) {
        if ( !isset( $settings['columns'] ) ) {
            return false;
        }
        return $this->get_app_columns( $settings['columns'] );
    }

    public function app_copy( $request ) {
        $app_id = $request->get_param( 'app_id' );
        if ( !$this->main_app_access( $app_id, $msg ) ) {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            }
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        return $this->do_app_copy( $app_id );
    }

    public function app_details( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $dbs,
            $tbl,
            $msg,
            $settings
        ) ) {
            return $this->WPDA_Rest_Response( '', WPDA_App_Container_Model::select( $app_id, 1 ) );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_detail_meta( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $rel_tab = $request->get_param( 'rel_tab' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $dbs,
            $tbl,
            $msg,
            $settings
        ) ) {
            $container = WPDA_App_Container_Model::get_container( $cnt_id );
            if ( !isset( $container[0] ) ) {
                return $this->bad_request();
            }
            return $this->get_app_container_meta( $app_id, $container, $rel_tab );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_detail_reorder( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id_from = $request->get_param( 'cnt_id_from' );
        $cnt_id_to = $request->get_param( 'cnt_id_to' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id_from,
            'select',
            $dbs,
            $tbl,
            $msg,
            $settings
        ) ) {
            $container = WPDA_App_Container_Model::get_container( $cnt_id_from );
            if ( !isset( $container[0] ) ) {
                return $this->bad_request();
            }
            $container = WPDA_App_Container_Model::get_container( $cnt_id_to );
            if ( !isset( $container[0] ) ) {
                return $this->bad_request();
            }
            return $this->reorder_details( $cnt_id_from, $cnt_id_to );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_relationship_create( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        $app_id = $request->get_param( 'app_id' );
        $app_title = $request->get_param( 'app_title' );
        $app_dbs = $request->get_param( 'app_dbs' );
        $app_tbl = $request->get_param( 'app_tbl' );
        $app_cls = $request->get_param( 'app_cls' );
        $app_relation = $request->get_param( 'app_relation' );
        return $this->WPDA_Rest_Response( '', WPDA_App_Container_Model::create(
            $app_id,
            $app_dbs,
            $app_tbl,
            json_encode( $app_cls ),
            $app_title,
            1,
            null,
            $app_relation
        ) );
    }

    public function app_relationship_update( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        $app_id = $request->get_param( 'app_id' );
        $app_cnt = $request->get_param( 'app_cnt' );
        $app_title = $request->get_param( 'app_title' );
        $app_dbs = $request->get_param( 'app_dbs' );
        $app_tbl = $request->get_param( 'app_tbl' );
        $app_cls = $request->get_param( 'app_cls' );
        $app_relation = $request->get_param( 'app_relation' );
        return $this->WPDA_Rest_Response( '', WPDA_App_Container_Model::update(
            $app_id,
            $app_cnt,
            $app_dbs,
            $app_tbl,
            json_encode( $app_cls ),
            $app_title,
            1,
            null,
            $app_relation
        ) );
    }

    public function app_relationship_delete( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        $cnt_id = $request->get_param( 'cnt_id' );
        return $this->WPDA_Rest_Response( '', WPDA_App_Container_Model::delete_container( $cnt_id ) );
    }

    public function app_select( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $col = $request->get_param( 'col' );
        $page_index = $request->get_param( 'page_index' );
        $page_size = $request->get_param( 'page_size' );
        $search = $request->get_param( 'search' );
        $search_columns = $request->get_param( 'search_columns' );
        $search_column_fns = $request->get_param( 'search_column_fns' );
        $search_column_lov = $request->get_param( 'search_column_lov' );
        $md = $request->get_param( 'md' );
        $sorting = $request->get_param( 'sorting' );
        $row_count = $request->get_param( 'row_count' );
        $row_count_estimate = $request->get_param( 'row_count_estimate' );
        $media = $request->get_param( 'media' );
        $rel_tab = $request->get_param( 'rel_tab' );
        $default_where = '';
        $default_orderby = '';
        $lookups = array();
        $m2m_relationship = array();
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $dbs,
            $tbl,
            $msg,
            $settings
        ) ) {
            $container = WPDA_App_Container_Model::get_container( $cnt_id );
            if ( '1' === $rel_tab ) {
            } else {
                $table_settings = $settings['table'] ?? array();
                // Get default where clause
                if ( isset( $table_settings['table']['defaultWhere'] ) ) {
                    $default_where = $table_settings['table']['defaultWhere'];
                }
                // Get default order by
                if ( isset( $table_settings['table']['defaultOrderBy'] ) ) {
                    $default_orderby_db = $table_settings['table']['defaultOrderBy'];
                    if ( is_array( $default_orderby_db ) ) {
                        foreach ( $default_orderby_db as $orderby ) {
                            if ( isset( $orderby['columnName'], $orderby['order'] ) && '' !== trim( $orderby['columnName'] ) ) {
                                $default_orderby .= (( '' === $default_orderby ? 'order by ' : ',' )) . '`' . WPDA::remove_backticks( $orderby['columnName'] ) . '` ' . (( 'desc' === $orderby['order'] ? 'desc' : 'asc' ));
                            }
                        }
                    }
                }
            }
            $table_api = new WPDA_Table();
            return $table_api->select(
                $dbs,
                $tbl,
                $col,
                $page_index,
                $page_size,
                $search,
                $search_columns,
                $search_column_fns,
                $sorting,
                $row_count,
                $row_count_estimate,
                $media,
                $default_where,
                $default_orderby,
                $lookups,
                $md,
                $m2m_relationship
            );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    private function get_m2m_relationship( $relationship, $tbl, $cols ) {
        return array();
    }

    private function get_lookup_lov( $column_lookup, $search_value, $search_type ) {
        return null;
    }

    private function convert_relation_columns( $columns ) {
        return array_map( function ( $value ) {
            if ( true === $value['isSelected'] ) {
                return $value['columnName'];
            }
        }, $columns );
    }

    public function app_get( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $key = $request->get_param( 'key' );
        $media = $request->get_param( 'media' );
        $rel_tab = $request->get_param( 'rel_tab' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $dbs,
            $tbl,
            $msg,
            $settings
        ) ) {
            $column_names = $this->get_app_form_columns( $settings );
            if ( false === $column_names ) {
                return $this->invalid_app_settings();
            }
            $default_where = '';
            $table_api = new WPDA_Table();
            return $table_api->get(
                $dbs,
                $tbl,
                $key,
                $media,
                $column_names,
                $default_where
            );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_insert( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $val = $request->get_param( 'val' );
        $join_tab = $request->get_param( 'join_tab' );
        $rel_tab = $request->get_param( 'rel_tab' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'insert',
            $dbs,
            $tbl,
            $msg
        ) ) {
            $table_api = new WPDA_Table();
            return $table_api->insert( $dbs, $tbl, $val );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_update( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $key = $request->get_param( 'key' );
        $val = $request->get_param( 'val' );
        $join_tab = $request->get_param( 'join_tab' );
        $rel_tab = $request->get_param( 'rel_tab' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'update',
            $dbs,
            $tbl,
            $msg,
            $settings
        ) ) {
            $column_names = $this->get_app_form_columns( $settings );
            if ( false === $column_names ) {
                $column_names = array();
            }
            $table_api = new WPDA_Table();
            return $table_api->update(
                $dbs,
                $tbl,
                $key,
                $val,
                $column_names
            );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_delete( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $key = $request->get_param( 'key' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'delete',
            $dbs,
            $tbl,
            $msg
        ) ) {
            $table_api = new WPDA_Table();
            return $table_api->delete( $dbs, $tbl, $key );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_lov( $request ) {
        return $this->bad_request();
    }

    public function app_lookup( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $target = $request->get_param( 'target' );
        $col = $request->get_param( 'col' );
        $colk = $request->get_param( 'colk' );
        $colv = $request->get_param( 'colv' );
        $cold = $request->get_param( 'cold' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $dbs,
            $tbl,
            $msg
        ) ) {
            $container = WPDA_App_Container_Model::get_container( $cnt_id );
            if ( !isset( $container[0] ) ) {
                return $this->bad_request();
            }
            $default_where = '';
            $lookup = array();
            $lookup_dbs = "";
            $lookup_tbl = "";
            if ( 'form' === $target ) {
                // Handle form lookup
                if ( isset( $container[0]['cnt_form'] ) ) {
                    $lookup = json_decode( (string) $container[0]['cnt_form'], true );
                }
            } else {
                // Handle table lookup
                if ( isset( $container[0]['cnt_table'] ) ) {
                    $lookup = json_decode( (string) $container[0]['cnt_table'], true );
                }
            }
            if ( isset( $lookup['columns'] ) && is_array( $lookup['columns'] ) ) {
                foreach ( $lookup['columns'] as $column ) {
                    if ( $col === $column['columnName'] ) {
                        if ( !isset( $column['lookup'] ) ) {
                            return $this->WPDA_Rest_Response( '', [] );
                        }
                        $lookup_dbs = $column['lookup']['dbs'];
                        $lookup_tbl = $column['lookup']['tbl'];
                        if ( isset( $column['columnName'], $column['lookup']['defaultWhere'] ) ) {
                            $default_where = $column['lookup']['defaultWhere'];
                        }
                    }
                }
            }
            if ( $lookup_dbs === null || $lookup_dbs === "" || $lookup_tbl === null || $lookup_tbl === "" ) {
                return $this->bad_request();
            }
            $table_api = new WPDA_Table();
            return $table_api->lookup(
                $lookup_dbs,
                $lookup_tbl,
                $colk,
                $colv,
                $cold,
                $default_where
            );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_lookup_dbs( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $_dbs,
            $_tbl,
            $msg
        ) ) {
            $tree_api = new WPDA_Tree();
            return $tree_api->get_dbs();
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_lookup_tbl( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $dbs = $request->get_param( 'dbs' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $_dbs,
            $_tbl,
            $msg
        ) ) {
            $tree_api = new WPDA_Tree();
            return $tree_api->get_tbl_vws( $dbs );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_lookup_cls( $request ) {
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $dbs = $request->get_param( 'dbs' );
        $tbl = $request->get_param( 'tbl' );
        if ( $this->check_app_access(
            $app_id,
            $cnt_id,
            'select',
            $_dbs,
            $_tbl,
            $msg
        ) ) {
            $tree_api = new WPDA_Tree();
            return $tree_api->get_cls( $dbs, $tbl );
        } else {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            } else {
                return new \WP_Error('error', $msg, array(
                    'status' => 401,
                ));
            }
        }
    }

    public function app_meta( $request ) {
        $app_id = $request->get_param( 'app_id' );
        if ( !$this->main_app_access( $app_id, $msg ) ) {
            if ( 'rest_cookie_invalid_nonce' === $msg ) {
                return $this->invalid_nonce();
            }
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        return $this->get_app_meta( $app_id );
    }

    public function app_settings( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        $app_id = $request->get_param( 'app_id' );
        $cnt_id = $request->get_param( 'cnt_id' );
        $target = $request->get_param( 'target' );
        $settings = $request->get_param( 'settings' );
        $theme = $request->get_param( 'theme' );
        return $this->do_app_settings(
            $app_id,
            $cnt_id,
            $target,
            $settings,
            $theme
        );
    }

    public function app_init( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        return $this->get_app_init();
    }

    public function app_list( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        return $this->get_app_list();
    }

    public function app_table_meta( $request ) {
        $dbs = $request->get_param( 'dbs' );
        $tbl = $request->get_param( 'tbl' );
        $waa = $request->get_param( 'waa' );
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        $table_api = new WPDA_Table();
        return $this->WPDA_Rest_Response( '', $table_api->get_table_meta_data( $dbs, $tbl, $waa ) );
    }

    public function app_create( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        // App details
        $app_name = $request->get_param( 'app_name' );
        $app_title = $request->get_param( 'app_title' );
        $app_type = $request->get_param( 'app_type' );
        $app_settings = $request->get_param( 'app_settings' );
        // App container
        $app_dbs = $request->get_param( 'app_dbs' );
        $app_tbl = $request->get_param( 'app_tbl' );
        $app_cls = $request->get_param( 'app_cls' );
        $app_table = $request->get_param( 'app_table' );
        return $this->do_app_create(
            $app_name,
            $app_title,
            $app_type,
            $app_settings,
            $app_dbs,
            $app_tbl,
            $app_cls,
            $app_table
        );
    }

    public function app_remove( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        $app_id = $request->get_param( 'app_id' );
        return $this->do_app_remove( $app_id );
    }

    public function app_save( $request ) {
        if ( !$this->current_user_can_access() ) {
            return $this->unauthorized();
        }
        if ( !$this->current_user_token_valid( $request ) ) {
            return $this->invalid_nonce();
        }
        // App details
        $app_id = $request->get_param( 'app_id' );
        $app_name = $request->get_param( 'app_name' );
        $app_title = $request->get_param( 'app_title' );
        $app_type = $request->get_param( 'app_type' );
        $app_settings = $request->get_param( 'app_settings' );
        $app_add_to_menu = $request->get_param( 'app_add_to_menu' );
        // App container
        $app_dbs = $request->get_param( 'app_dbs' );
        $app_tbl = $request->get_param( 'app_tbl' );
        $app_cls = $request->get_param( 'app_cls' );
        return $this->do_app_save(
            $app_id,
            $app_name,
            $app_title,
            $app_type,
            $app_settings,
            $app_add_to_menu,
            $app_dbs,
            $app_tbl,
            $app_cls
        );
    }

    private function do_app_settings(
        $app_id,
        $cnt_id,
        $target,
        $settings,
        $theme
    ) {
        if ( 1 > $app_id || 1 > $cnt_id || 'table' !== $target && 'form' !== $target && 'theme' !== $target ) {
            return $this->bad_request();
        }
        if ( null === $settings || '' === $settings ) {
            // Perform reset
            switch ( $target ) {
                case 'table':
                    $error_msg = WPDA_App_Container_Model::update_table_settings( $cnt_id, null );
                    if ( '' !== $error_msg ) {
                        return new \WP_Error('error', $error_msg, array(
                            'status' => 403,
                        ));
                    }
                    break;
                case 'form':
                    $error_msg = WPDA_App_Container_Model::update_form_settings( $cnt_id, null );
                    if ( '' !== $error_msg ) {
                        return new \WP_Error('error', $error_msg, array(
                            'status' => 403,
                        ));
                    }
                    break;
                case 'theme':
                    $error_msg = WPDA_App_Model::update_theme( $app_id, null );
                    if ( '' !== $error_msg ) {
                        return new \WP_Error('error', $error_msg, array(
                            'status' => 403,
                        ));
                    }
                    break;
                default:
                    return $this->bad_request();
            }
            return $this->WPDA_Rest_Response( __( 'Reset was successful', 'wp-data-access' ) );
        }
        if ( 'table' === $target ) {
            // Update table settings
            $error_msg = WPDA_App_Container_Model::update_table_settings( $cnt_id, $settings );
            if ( '' !== $error_msg ) {
                return new \WP_Error('error', $error_msg, array(
                    'status' => 403,
                ));
            }
        } else {
            // Update form settings
            $error_msg = WPDA_App_Container_Model::update_form_settings( $cnt_id, $settings );
            if ( '' !== $error_msg ) {
                return new \WP_Error('error', $error_msg, array(
                    'status' => 403,
                ));
            }
        }
        $error_msg = WPDA_App_Model::update_theme( $app_id, $theme );
        if ( '' !== $error_msg ) {
            return new \WP_Error('error', $error_msg, array(
                'status' => 403,
            ));
        }
        return $this->WPDA_Rest_Response( __( 'Successfully saved settings', 'wp-data-access' ) );
    }

    private function do_app_remove( $app_id ) {
        WPDA_App_Model::delete( $app_id );
        WPDA_App_Container_Model::delete( $app_id );
        return $this->WPDA_Rest_Response( __( 'Successfully deleted app', 'wp-data-access' ) );
    }

    private function do_app_create(
        $app_name,
        $app_title,
        $app_type,
        $app_settings,
        $app_dbs,
        $app_tbl,
        $app_cls,
        $app_table
    ) {
        // Add app
        $insert = WPDA_App_Model::create(
            $app_name,
            $app_title,
            $app_type,
            $app_settings
        );
        if ( false !== $insert['app_id'] ) {
            $app_id = $insert['app_id'];
            // Add app container
            $container = WPDA_App_Container_Model::create(
                $app_id,
                $app_dbs,
                $app_tbl,
                json_encode( $app_cls ),
                $app_title,
                0,
                $app_table
            );
            if ( false !== $container['cnt_id'] ) {
                // App and container successfully saved
                return $this->WPDA_Rest_Response( __( 'Successfully saved changes', 'wp-data-access' ) );
            } else {
                // Insert failed
                // Remove previously created app
                WPDA_App_Model::delete( $app_id );
                return new \WP_Error('error', $container['msg'], array(
                    'status' => 403,
                ));
            }
        } else {
            // Insert failed
            return new \WP_Error('error', $insert['msg'], array(
                'status' => 403,
            ));
        }
    }

    private function get_app_init() {
        return $this->WPDA_Rest_Response( '', array(
            'roles' => $this->get_wp_roles(),
            'users' => $this->get_wp_users(),
        ) );
    }

    private function get_app_list() {
        $dataset = WPDA_App_Model::list();
        return $this->WPDA_Rest_Response( '', $dataset );
    }

    private function get_relation_columns( $container ) {
        return null;
    }

    private function reorder_details( $cnt_id_from, $cnt_id_to ) {
        return $this->bad_request();
    }

    private function get_app_container_meta( $app_id, $container, $rel_tab = false ) {
        $app = WPDA_App_Model::get_by_id( $app_id );
        if ( false === $app ) {
            return $this->bad_request();
        }
        if ( !isset( $container[0]['cnt_dbs'], $container[0]['cnt_tbl'], $container[0]['cnt_cls'] ) ) {
            return $this->bad_request();
        }
        $dbs = $container[0]['cnt_dbs'];
        $tbl = $container[0]['cnt_tbl'];
        $response = array(
            'app' => array(
                'app'       => $app,
                'container' => array_map( function ( $value ) {
                    $show = current_user_can( 'manage_options' );
                    if ( !$show ) {
                        // Hide database and table name in responses for non admin users.
                        unset($value['cnt_dbs']);
                        unset($value['cnt_tbl']);
                    }
                    return $value;
                }, $container ),
            ),
        );
        $cls = WPDA_List_Columns_Cache::get_list_columns( $dbs, $tbl );
        $access = array(
            'select' => array(),
            'insert' => array(),
            'update' => array(),
            'delete' => array(),
        );
        $settings = new stdClass();
        $settings_db = WPDA_Table_Settings_Model::query( $tbl, $dbs );
        if ( isset( $settings_db[0]['wpda_table_settings'] ) ) {
            $settings = json_decode( (string) $settings_db[0]['wpda_table_settings'] );
            // Remove old settings from response.
            unset($settings->form_labels);
            unset($settings->list_labels);
            unset($settings->custom_settings);
            unset($settings->search_settings);
        }
        $settings->env = $this->get_env();
        global $wpdb;
        $settings->wp = [
            'roles'       => $this->get_wp_roles(),
            'users'       => $this->get_wp_users(),
            'home'        => admin_url( 'admin.php' ),
            'tables'      => array_values( $wpdb->tables() ),
            'date_format' => get_option( 'date_format' ),
            'time_format' => get_option( 'time_format' ),
        ];
        $media = $this->get_media( $dbs, $tbl, $cls->get_table_columns() );
        $response['columns'] = $cls->get_table_columns();
        $response['table_labels'] = $cls->get_table_header_labels();
        $response['form_labels'] = $cls->get_table_column_headers();
        $response['primary_key'] = $cls->get_table_primary_key();
        $response['access'] = $access;
        $response['settings'] = $settings;
        $response['media'] = $media['media'];
        $response['wp_media'] = $media['wp_media'];
        $table_settings = json_decode( (string) $container[0]['cnt_table'], true );
        if ( isset( $table_settings['table']['defaultWhere'] ) ) {
            $default_where = $table_settings['table']['defaultWhere'];
        } else {
            $default_where = '';
        }
        $response['table_info'] = $this->get_table_info( $dbs, $tbl, $default_where );
        return $this->WPDA_Rest_Response( '', $response );
    }

    private function get_app_meta( $app_id ) {
        $container = WPDA_App_Container_Model::select( $app_id, 0 );
        if ( !isset( $container[0] ) ) {
            return $this->bad_request();
        }
        return $this->get_app_container_meta( $app_id, $container );
    }

    private function do_app_copy( $app_id ) {
        $copy = WPDA_App_Model::copy( $app_id );
        if ( false === $copy['app_id'] ) {
            return new \WP_Error('error', $copy['msg'], array(
                'status' => 403,
            ));
        } else {
            return $this->WPDA_Rest_Response( __( 'App successfully copied', 'wp-data-access' ) );
        }
    }

    private function do_app_save(
        $app_id,
        $app_name,
        $app_title,
        $app_type,
        $app_settings,
        $app_add_to_menu,
        $app_dbs,
        $app_tbl,
        $app_cls
    ) {
        $error_msg = WPDA_App_Model::update(
            $app_id,
            $app_name,
            $app_title,
            $app_type,
            $app_settings,
            $app_add_to_menu
        );
        if ( '' !== $error_msg ) {
            return new \WP_Error('error', $error_msg, array(
                'status' => 403,
            ));
        }
        $error_msg = WPDA_App_Container_Model::update_master(
            $app_id,
            $app_dbs,
            $app_tbl,
            json_encode( $app_cls ),
            0
        );
        if ( '' !== $error_msg ) {
            return new \WP_Error('error', $error_msg, array(
                'status' => 403,
            ));
        }
        return $this->WPDA_Rest_Response( __( 'Changes successfully saved', 'wp-data-access' ) );
    }

    private function main_app_access( $app_id, &$msg = '' ) {
        // Get app info
        $app = WPDA_App_Model::get_by_id( $app_id );
        if ( false === $app ) {
            // App not found
            $msg = __( 'Bad request', 'wp-data-access' );
            return false;
        }
        // Check access
        $app_settings_db = $app[0]['app_settings'];
        $app_settings = json_decode( (string) $app_settings_db, true );
        if ( !isset( $app_settings['rest_api']['authorization'], $app_settings['rest_api']['authorized_roles'], $app_settings['rest_api']['authorized_users'] ) || !is_array( $app_settings['rest_api']['authorized_roles'] ) || !is_array( $app_settings['rest_api']['authorized_users'] ) ) {
            // App contain no rest api settings
            $msg = __( 'Bad request', 'wp-data-access' );
            return false;
        }
        if ( !$this->current_user_can_access() && 'anonymous' !== $app_settings['rest_api']['authorization'] ) {
            // Check authorization
            // Check user role
            $user_roles = WPDA::get_current_user_roles();
            if ( !is_array( $user_roles ) || empty( array_intersect( $app_settings['rest_api']['authorized_roles'], $user_roles ) ) ) {
                // Check user login
                $user_login = WPDA::get_current_user_login();
                if ( !in_array( $user_login, $app_settings['rest_api']['authorized_users'] ) ) {
                    $msg = __( 'Unauthorized', 'wp-data-access' );
                    return false;
                }
            }
        }
        return true;
    }

    public function check_app_access(
        $app_id,
        $cnt_id,
        $action,
        &$dbs,
        &$tbl,
        &$msg = '',
        &$settings = array()
    ) {
        if ( !$this->main_app_access( $app_id, $msg ) ) {
            return false;
        }
        // Get container
        $container = WPDA_App_Container_Model::get_container( $cnt_id );
        if ( !is_array( $container ) || 0 === count( $container ) ) {
            // Container not found
            $msg = __( 'Bad request', 'wp-data-access' );
            return false;
        }
        if ( 'select' !== $action ) {
            $cnt_table = json_decode( (string) $container[0]['cnt_table'], true );
            if ( !isset( $cnt_table['table']['transactions'][$action] ) || false === $cnt_table['table']['transactions'][$action] ) {
                $cnt_relation = json_decode( (string) $container[0]['cnt_relation'], true );
                if ( !(isset( $cnt_relation['cnt_id_master'] ) && $this->check_master_container_access( $cnt_relation['cnt_id_master'], $action )) ) {
                    $msg = __( 'Unauthorized', 'wp-data-access' );
                    return false;
                }
            }
        }
        // Return database name, table name and columns
        $dbs = $container[0]['cnt_dbs'];
        $tbl = $container[0]['cnt_tbl'];
        $settings = array(
            'columns' => json_decode( (string) $container[0]['cnt_cls'], true ),
            'table'   => json_decode( (string) $container[0]['cnt_table'], true ),
            'form'    => json_decode( (string) $container[0]['cnt_form'], true ),
        );
        return true;
    }

    private function check_master_container_access( $cnt_id, $action ) {
        $container = WPDA_App_Container_Model::get_container( $cnt_id );
        if ( !is_array( $container ) || 0 === count( $container ) ) {
            // Container not found
            return false;
        }
        $cnt_table = json_decode( (string) $container[0]['cnt_table'], true );
        if ( !isset( $cnt_table['table']['transactions'][$action] ) || false === $cnt_table['table']['transactions'][$action] ) {
            $cnt_relation = json_decode( (string) $container[0]['cnt_relation'], true );
            if ( !(isset( $cnt_relation['cnt_id_master'] ) && $this->check_master_container_access( $cnt_relation['cnt_id_master'], $action )) ) {
                return false;
            }
        }
        return true;
    }

}
