<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_action( 'plugins_loaded', 'kt_variation_swatches_plugin_loaded' );

function kt_variation_swatches_plugin_loaded() {

	class kt_variation_swatches {

		public function __construct() {
			require KADENCE_WOO_EXTRAS_PATH . '/lib/swatches/kt-variations-swatches-output.php';
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'kt_variation_swatches_panel_tabs' ), 99 );
			add_action( 'woocommerce_product_data_panels', array( $this, 'kt_variation_swatches_panel_output' ), 99 );
			add_action( 'woocommerce_process_product_meta', array( $this, 'kt_variation_swatches_process_meta' ), 1, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'kt_variation_swatches_enqueue_scripts' ), 101 );
			add_action( 'admin_enqueue_scripts', array( $this, 'kt_variation_swatches_admin_enqueue_scripts' ) );
			add_action( 'admin_init', array( $this, 'kt_woo_extras_register_taxonomy_meta_boxes' ) );
			add_filter( 'body_class', array( $this, 'single_product_body_classes' ) );

			add_filter( 'product_attributes_type_selector', array( $this, 'add_attribute_select_types' ), 100 );
			add_action( 'woocommerce_product_option_terms', array( $this, 'manage_attribute_options'), 20, 3 );
		}
		public function manage_attribute_options( $attribute_taxonomy, $i, $attribute ) {
			if ( 'select' !== $attribute_taxonomy->attribute_type ) {
				?>
				<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo esc_attr( $i ); ?>][]">
					<?php
					$args      = array(
						'orderby'    => ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
						'hide_empty' => 0,
					);
					$all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );
					if ( $all_terms ) {
						foreach ( $all_terms as $term ) {
							$options = $attribute->get_options();
							$options = ! empty( $options ) ? $options : array();
							echo '<option value="' . esc_attr( $term->term_id ) . '"' . wc_selected( $term->term_id, $options ) . '>' . esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
						}
					}
					?>
				</select>
				<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></button>
				<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'woocommerce' ); ?></button>
				<button class="button fr plus add_new_attribute"><?php esc_html_e( 'Add new', 'woocommerce' ); ?></button>
				<?php
			}
		}
		public function add_attribute_select_types( $types ) {
			$types = array(
				'select' => __( 'Default', 'kadence-woo-extras' ),
				'dropdown' => __( 'Dropdown Select', 'kadence-woo-extras' ),
				'radio_box' => __( 'Radio Boxes', 'kadence-woo-extras' ),
				'color_image' => __( 'Image and Color swatches', 'kadence-woo-extras' ),
			);
			return $types;
		}
		/**
		 * Adds custom classes to indicate the button size for the single products.
		 *
		 * @param array $classes Classes for the body element.
		 * @return array Filtered body classes.
		 */
		public function single_product_body_classes( $classes ) {
			if ( is_product() ) {
				global $kt_woo_extras;
				if ( isset( $kt_woo_extras['variation_label'] ) && true == $kt_woo_extras['variation_label'] ) {
					$classes[] = 'kadence-sk-variation-above';
				}
			}
			return $classes;
		}
		public function kt_variation_swatches_panel_tabs() {
			?>
			<li class="kt-variation-swatches-tab show_if_variable"><a href="#kt_swatches"><span><?php echo __( 'Variation Swatches', 'kadence-woo-extras' ); ?></span></a></li>
			<?php
		}

		public function kt_variation_swatches_panel_output() {
			?>
			<div id="kt_swatches" class="panel kt-variation-swatches-content woocommerce_options_panel wc-metaboxes-wrapper">
				<div class="kt_swatches_container">
					<div class="kt_swatches_label">
						<?php echo __( 'Product variation attributes', 'kadence-woo-extras' ); ?> 
					</div>
					<?php $this->kt_variation_swatches_tab_output(); ?>
				</div>
			</div>
			<?php
		}

		public function kt_variation_swatches_tab_output() {
			global $woocommerce, $post, $kt_woo_extras;
			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $post->ID );
			} else {
				$product = new WC_Product( $post->ID );
			}
			if ( ! $product->is_type( 'variable' ) && ! $product->is_type( 'variable-subscription' ) ) {
				return;
			}
			$kt_variation_swatch_type           = get_post_meta( $post->ID, '_kt_variation_swatch_type', true );
			$kt_variation_swatch_type_options   = get_post_meta( $post->ID, '_kt_variation_swatch_type_options', true );

			if ( ! $kt_variation_swatch_type_options ) {
				$kt_variation_swatch_type_options = array();
			}

			if ( ! $kt_variation_swatch_type ) {
				$kt_variation_swatch_type = array();
			}

			$var_product = new WC_Product_Variable( $post->ID );

			$attributes = $var_product->get_variation_attributes();

			if ( $attributes && count( $attributes ) ) :
				// Start foreach with the product attributres
				$attribute_names = array_keys( $attributes );
				foreach ( $attribute_names as $attribute_name ) {
					$key = md5( sanitize_title( $attribute_name ) );
					if ( isset( $kt_variation_swatch_type[ $key ]['display_type'] ) ) {
						$value = $kt_variation_swatch_type[ $key ]['display_type'];
					} else {
						$value = 'default';
					}
					if ( isset( $kt_variation_swatch_type[ $key ]['display_label'] ) ) {
						$label = $kt_variation_swatch_type[ $key ]['display_label'];
					} else {
						$label = 'default';
					}
					if ( isset( $kt_variation_swatch_type[ $key ]['display_size'] ) ) {
						$size = $kt_variation_swatch_type[ $key ]['display_size'];
					} else {
						$size = 'default';
					}
					if ( isset( $kt_woo_extras['swatches_type'] ) ) {
						$kt_default_type = $kt_woo_extras['swatches_type'];
					} else {
						$kt_default_type = 'dropdown';
					}
					if ( isset( $kt_woo_extras['swatches_size'] ) ) {
						$kt_default_size = $kt_woo_extras['swatches_size'];
					} else {
						$kt_default_size = '50';
					}
					// echo Each attribute label and swatch type.
					echo '<div class="kt_swatches_attribute_panel" data-default-type="' . $kt_default_type . '" data-default-size="' . $kt_default_size . '">';
						echo '<a class="kt_attribute_label">' . wc_attribute_label( $attribute_name ) . '</a>';
						echo '<div class="kt_attribute_panel">';
							woocommerce_wp_select(
								array(
									'id'      => '_kt_variation_swatch_type[' . $key . '][display_type]',
									'class'   => 'select short kt_select_swatches_type',
									'label'   => __( 'Variation Style', 'kadence-woo-extras' ),
									'options' => array(
										'default'       => __( 'Default', 'kadence-woo-extras' ),
										'dropdown'      => __( 'Dropdown', 'kadence-woo-extras' ),
										'radio_box'     => __( 'Radio Box', 'kadence-woo-extras' ),
										'color_image'   => __( 'Color and image swatches', 'kadence-woo-extras' ),
										'taxonomy'      => __( 'Taxonomy defined', 'kadence-woo-extras' ),
									),
									'value' => $value,
								)
							);
							echo '<div class="kt_attribute_extra_settings ';
					if ( $value != 'default' ) {
						echo $value == 'color_image' ? 'panel_open' : ' ';
					} else {
						echo $kt_default_type == 'color_image' ? 'panel_open' : ' ';
					}
							echo '">';
								woocommerce_wp_select(
									array(
										'id'      => '_kt_variation_swatch_type[' . $key . '][display_label]',
										'class'   => 'select short kt_select_swatches_label',
										'label'   => __( 'Display Label', 'kadence-woo-extras' ),
										'options' => array(
											'default'       => __( 'Default', 'kadence-woo-extras' ),
											'false'         => __( 'No label', 'kadence-woo-extras' ),
											'above'         => __( 'Show above', 'kadence-woo-extras' ),
											'below'         => __( 'Show below', 'kadence-woo-extras' ),
											'tooltip'       => __( 'Show above on hover', 'kadence-woo-extras' ),
										),
										'value' => $label,
									)
								);
								woocommerce_wp_select(
									array(
										'id'      => '_kt_variation_swatch_type[' . $key . '][display_size]',
										'label'   => __( 'Swatch Size', 'kadence-woo-extras' ),
										'class'   => 'select short kt_select_swatches_size',
										'options' => array(
											'default'       => __( 'Default', 'kadence-woo-extras' ),
											'16'            => __( '16x16px', 'kadence-woo-extras' ),
											'30'            => __( '30x30px', 'kadence-woo-extras' ),
											'40'            => __( '40x40px', 'kadence-woo-extras' ),
											'50'            => __( '50x50px', 'kadence-woo-extras' ),
											'60'            => __( '60x60px', 'kadence-woo-extras' ),
											'75'            => __( '75x75px', 'kadence-woo-extras' ),
											'90'            => __( '90x90px', 'kadence-woo-extras' ),
											'120'           => __( '120x120px', 'kadence-woo-extras' ),
											'150'           => __( '150x150px', 'kadence-woo-extras' ),
										),
										'value' => $size,
									)
								);
							echo '</div>';
							// Get all attribute terms selected
							$attribute_terms = array();
					if ( taxonomy_exists( $attribute_name ) ) :
						$terms = get_terms( $attribute_name, array( 'hide_empty' => false ) );
						$selected_terms = isset( $attributes[ $attribute_name ] ) ? $attributes[ $attribute_name ] : array();
						foreach ( $terms as $term ) {
							if ( in_array( $term->slug, $selected_terms ) ) {
								$attribute_terms[] = array(
									'id' => md5( $term->slug ),
									'label' => $term->name,
								);
							}
						}
							else :
								foreach ( $attributes[ $attribute_name ] as $term ) {
									$attribute_terms[] = array(
										'id' => ( md5( sanitize_title( strtolower( $term ) ) ) ),
										'label' => esc_html( $term ),
									);
								}
							endif;
							echo '<div class="kt_swatches_attribute_table kt_swatches_clearfix ';
							if ( $value != 'default' ) {
								echo $value == 'color_image' ? 'panel_open' : ' ';
							} else {
								echo $kt_default_type == 'color_image' ? 'panel_open' : ' ';
							}
							echo '">';
								echo '<div class="kt_swatches_attribute_table_head kt_swatches_clearfix">';
									echo '<div class="kt_sa_preview">' . __( 'Preview', 'kadence-woo-extras' ) . '</div>';
									echo '<div class="kt_sa_name">' . __( 'Attribute', 'kadence-woo-extras' ) . '</div>';
									echo '<div class="kt_sa_type">' . __( 'Type', 'kadence-woo-extras' ) . '</div>';
								echo '</div>';
								echo '<div class="kt_swatches_attribute_table_body kt_swatches_clearfix">';
									// Loop through terms
							foreach ( $attribute_terms as $attribute_term ) :
								$attribute_term['id'] = ( $attribute_term['id'] );
								// Get image
								if ( isset( $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['image'] ) ) {
									$this_attribute_image = $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['image'];
								} else {
									$this_attribute_image = wc_placeholder_img_src();
								}
								// get ID
								if ( isset( $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['image_id'] ) ) {
									$this_attribute_image_id = $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['image_id'];
								} else {
									$this_attribute_image_id = null;
								}
								// Get thumbnail
								if ( isset( $this_attribute_image_id ) && ! empty( $this_attribute_image_id ) ) {
									$this_attribute_image_thumb = wp_get_attachment_image_src( $this_attribute_image_id, 'thumbnail' );
									$this_attribute_image_thumb = $this_attribute_image_thumb[0];
								} else {
									$this_attribute_image_thumb = wc_placeholder_img_src();
								}
								// Get color
								if ( isset( $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['color'] ) ) {
									$this_attribute_color = $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['color'];
								} else {
									$this_attribute_color = '#ffffff';
								}
								// Get type
								if ( isset( $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['type'] ) ) {
									$this_attribute_type = $kt_variation_swatch_type_options[ $key ][ $attribute_term['id'] ]['type'];
								} else {
									$this_attribute_type = 'color';
								}
								// Get type label
								if ( $this_attribute_type == 'image' ) {
									$this_attribute_type_label = __( 'Image', 'kadence-woo-extras' );
								} else {
									$this_attribute_type_label = __( 'Color', 'kadence-woo-extras' );
								}
								echo '<div class="kt_swatches_attribute_single">';
									echo '<div class="kt_swatches_attribute_table_subhead kt_swatches_clearfix"';
								if ( $size == 'default' ) {
									if ( isset( $kt_woo_extras['swatch_size'] ) ) {
										echo 'style="line-height:' . esc_attr( $kt_woo_extras['swatch_size'] ) . 'px"';
									} else {
										// do nothing
									}
								} else {
									echo 'style="line-height:' . esc_attr( $size ) . 'px"';
								}
									echo '">';
										echo '<div class="kt_sas_preview">';
											echo '<div class="kt_sas_preview_item"';
								if ( $this_attribute_type == 'image' ) {
									echo 'style="background-image:url(' . $this_attribute_image_thumb . ');';
								} else {
									echo 'style="background-color:' . $this_attribute_color . ';';
								}
								if ( $size == 'default' ) {
									if ( isset( $kt_woo_extras['swatches_size'] ) ) {
										echo 'width:' . esc_attr( $kt_woo_extras['swatches_size'] ) . 'px;';
										echo 'height:' . esc_attr( $kt_woo_extras['swatches_size'] ) . 'px;';
									} else {
										// do nothing
									}
								} else {
									echo 'width:' . esc_attr( $size ) . 'px;';
									echo 'height:' . esc_attr( $size ) . 'px;';
								}
											echo '"></div>';
												echo '</div>';
												echo '<div class="kt_sas_name">' . $attribute_term['label'] . '</div>';
												echo '<div class="kt_sas_type">' . $this_attribute_type_label . '</div>';
												echo '</div>';
												echo '<div class="kt_swatches_attribute_single_options kt_swatches_clearfix">';
												echo '<div class="kt_swatches_attribute_single_row kt_sas_option_type">';
											woocommerce_wp_select(
												array(
													'id'      => '_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][type]',
													'class'   => 'select short kt_select_swatches_type_single',
													'label'   => __( 'Attribute Color or Image', 'kadence-woo-extras' ),
													'options' => array(
														'color'         => __( 'Color', 'kadence-woo-extras' ),
														'image'         => __( 'Image', 'kadence-woo-extras' ),
													),
													'value' => $this_attribute_type,
												)
											);
										echo '</div>';
										echo '<div class="kt_swatches_attribute_single_row kt_sas_option_color" style="';
											echo $this_attribute_type == 'color' ? '' : 'display:none';
										echo '">';
											echo '<p class="form-field _kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][color]_field">';
												echo '<label for="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][color]">' . __( 'Color', 'kadence-woo-extras' ) . '</label>';
												echo '<input class="kt_swatch_color" id="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][color]" type="text" class="text" name="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][color]" value="' . $this_attribute_color . '" />';
											echo '</p>';
										echo '</div>';
										echo '<div class="kt_swatches_attribute_single_row kt_sas_option_image" style="';
											echo $this_attribute_type == 'image' ? '' : 'display:none';
										echo '">';
											echo '<p class="form-field _kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][image]_field">';
												echo '<label for="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][image]">' . __( 'Image', 'kadence-woo-extras' ) . '</label>';
												echo '<input class="kt_swatch_image" id="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][image]" type="text" class="text" name="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][image]" value="' . $this_attribute_image . '" />';
												echo '<input class="kt_swatch_image_id" id="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][image_id]" type="hidden" class="text" name="_kt_variation_swatch_type_options[' . $key . '][' . $attribute_term['id'] . '][image_id]" value="' . $this_attribute_image_id . '" />';
												echo '<a class="kt_swatches_upload_button button">' . __( 'Upload/Add image', 'kadence-woo-extras' ) . '</a>';
											echo '</p>';
										echo '</div>';
									echo '</div>';
								echo '</div>';
									endforeach;
								echo '</div>';
							echo '</div>';
							echo '</div>';
							echo '</div>';

				}

			endif;
		}

		public function kt_variation_swatches_process_meta( $post_id, $post ) {
			if ( isset( $_POST['_kt_variation_swatch_type'] ) ) {
				update_post_meta( $post_id, '_kt_variation_swatch_type', $_POST['_kt_variation_swatch_type'] );
			}
			if ( isset( $_POST['_kt_variation_swatch_type_options'] ) ) {
				update_post_meta( $post_id, '_kt_variation_swatch_type_options', $_POST['_kt_variation_swatch_type_options'] );
			}

		}
		public function kt_variation_swatches_enqueue_scripts() {
			if ( apply_filters( 'kadence_shop_kit_swatches_scripts_enqueue', is_product() ) ) {
				if ( wp_script_is( 'kt-wc-add-to-cart-variation', 'enqueued' ) ) {
					wp_dequeue_script( 'kt-wc-add-to-cart-variation' );
				}
				if ( wp_script_is( 'kt-add-to-cart-variation-radio', 'enqueued' ) ) {
					wp_dequeue_script( 'kt-add-to-cart-variation-radio' );
				}
				wp_deregister_script( 'kt-add-to-cart-variation-radio' );
				wp_enqueue_style( 'kadence_variation_swatches_css', KADENCE_WOO_EXTRAS_URL . 'lib/swatches/css/kt_variation_swatches.css', false, KADENCE_WOO_EXTRAS_VERSION );
				wp_register_script( 'kadence_variation_swatches', KADENCE_WOO_EXTRAS_URL . 'lib/swatches/js/kt_variation_swatches.js', array( 'jquery' ), KADENCE_WOO_EXTRAS_VERSION, true );
				wp_enqueue_script( 'kadence_variation_swatches' );
			}
		}
		public function kt_variation_swatches_admin_enqueue_scripts() {
			global $pagenow;
			if ( is_admin() && ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' || 'edit-tags.php' ) ) {
				wp_enqueue_media();
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'kadence_admin_swatches_css', KADENCE_WOO_EXTRAS_URL . 'lib/swatches/css/kt_admin_swatches.css', false, KADENCE_WOO_EXTRAS_VERSION );
				wp_register_script( 'kadence_admin_swatches', KADENCE_WOO_EXTRAS_URL . 'lib/swatches/js/kt_admin_swatches.js', array( 'jquery', 'wp-color-picker' ), KADENCE_WOO_EXTRAS_VERSION, true );
				wp_enqueue_script( 'kadence_admin_swatches' );
			}
		}
		public function kt_woo_extras_register_taxonomy_meta_boxes() {
			if ( ! class_exists( 'KT_WOO_EXTRAS_Taxonomy_Meta' ) ) {
				return;
			}

			$meta_sections = array();
			$prefix = 'kt_woo_extras_';
			if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
				$attributes = wc_get_attribute_taxonomies();
				$attribute_array = array();
				foreach ( $attributes as $tax ) {
					$attribute_array[] = 'pa_' . $tax->attribute_name;
				}
				// First meta section
				$meta_sections[] = array(
					'title'      => 'Swatch Taxonomy Image or Color Setup',
					'taxonomies' => $attribute_array,
					'id'         => 'kt_woo_extras_tax_swatch_type',

					'fields' => array(
						array(
							'name'    => __( 'Swatch Type', 'kadence-woo-extras' ),
							'id'      => $prefix . 'swatch_type',
							'type'    => 'select',
							'options' => array(
								'color' => __( 'Color', 'kadence-woo-extras' ),
								'image' => __( 'Image', 'kadence-woo-extras' ),
							),
						),
						array(
							'name' => __( 'Swatch Color', 'kadence-woo-extras' ),
							'id'   => $prefix . 'swatch_color',
							'type' => 'color',
						),
						array(
							'name' => __( 'Swatch Image', 'kadence-woo-extras' ),
							'id' => $prefix . 'swatch_image',
							'type' => 'image',
						),
					),
				);

				foreach ( $meta_sections as $meta_section ) {
					new KT_WOO_EXTRAS_Taxonomy_Meta( $meta_section );
				}
			}
		}

	}
	$GLOBALS['kt_variation_swatches'] = new kt_variation_swatches();
}

