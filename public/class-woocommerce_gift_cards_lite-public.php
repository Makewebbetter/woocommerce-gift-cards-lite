<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_gift_cards_lite
 * @subpackage Woocommerce_gift_cards_lite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woocommerce_gift_cards_lite
 * @subpackage Woocommerce_gift_cards_lite/public
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Woocommerce_gift_cards_lite_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		require_once MWB_WGC_DIRPATH . 'includes/class-woocommerce_gift_cards_common_function.php';
		$this->mwb_common_fun = new Woocommerce_gift_cards_common_function();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_gift_cards_lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_gift_cards_lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce_gift_cards_lite-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'thickbox' );

	}

	/**
	 * Function is used to compatible with price based on country.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_price_based_country_giftcard()
	 * @param      array $array The array of product type.
	 * @return  $array
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_price_based_country_giftcard( $array ) {
		$array[] = 'wgm_gift_card';
		return $array;
	}
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$mail_settings = get_option( 'mwb_wgm_mail_settings', array() );
		$giftcard_message_length = $this->mwb_common_fun->mwb_wgm_get_template_data( $mail_settings, 'mwb_wgm_mail_setting_giftcard_message_length' );
		if ( $giftcard_message_length == '' ) {
			$giftcard_message_length = 300;
		}

		$mwb_wgm = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'pricing_type' => array(),
			'product_id' => 0,
			/* translators: %s: seconds */
			'price_field' => sprintf( __( 'Price: %sField is empty', 'woocommerce_gift_cards_lite' ), '</b>' ),
			/* translators: %s: seconds */
			'to_empty' => sprintf( __( 'Recipient Email: %sField is empty.', 'woocommerce_gift_cards_lite' ), '</b>' ),
			/* translators: %s: seconds */
			'to_empty_name' => sprintf( __( 'Recipient Name: %sField is empty.', 'woocommerce_gift_cards_lite' ), '</b>' ),
			/* translators: %s: seconds */
			'to_invalid' => sprintf( __( 'Recipient Email: %sInvalid email format.', 'woocommerce_gift_cards_lite' ), '</b>' ),
			/* translators: %s: seconds */
			'from_empty' => sprintf( __( 'From: %sField is empty.', 'woocommerce_gift_cards_lite' ), '</b>' ),
			/* translators: %s: seconds */
			'msg_empty' => sprintf( __( 'Message: %sField is empty.', 'woocommerce_gift_cards_lite' ), '</b>' ),
			/* translators: %s: seconds */
			'msg_length_err' => sprintf( __( 'Message: %1$sMessage length cannot exceed %2$s characters.', 'woocommerce_gift_cards_lite' ), '</b>', $giftcard_message_length ),
			'msg_length' => $giftcard_message_length,
			/* translators: %s: seconds */
			'price_range' => sprintf( __( 'Price Range: %sPlease enter price within Range.', 'woocommerce_gift_cards_lite' ), '</b>' ),
			'is_pro_active' => mwb_uwgc_pro_active(),
		);
		if ( is_product() ) {
			global $post;
			$product_id = $post->ID;
			$product_types = wp_get_object_terms( $product_id, 'product_type' );
			if ( isset( $product_types[0] ) ) {
				$product_type = $product_types[0]->slug;
				if ( $product_type == 'wgm_gift_card' ) {
					// for price based on country.
					if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {
						$mwb_wgm_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
						if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
							if ( isset( $mwb_wgm_pricing['type'] ) ) {
								$product_pricing_type = $mwb_wgm_pricing['type'];
								if ( $product_pricing_type == 'mwb_wgm_range_price' ) {
									$from_price = $mwb_wgm_pricing['from'];
									$to_price = $mwb_wgm_pricing['to'];

									$from_price = wcpbc_the_zone()->get_exchange_rate_price( $from_price );
									$to_price = wcpbc_the_zone()->get_exchange_rate_price( $to_price );
									$mwb_wgm_pricing['from'] = $from_price;
									$mwb_wgm_pricing['to'] = $to_price;
								}
							}
						}
					} else {
						$mwb_wgm_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
					}

					$mwb_wgm['pricing_type'] = $mwb_wgm_pricing;
					$mwb_wgm['product_id'] = $product_id;
					wp_enqueue_script( 'thickbox' );
					$mwb_wgm['mwb_wgm_nonce'] = wp_create_nonce( 'mwb-wgc-verify-nonce' );
					wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce_gift_cards_lite-public.js', array( 'jquery' ), '2.1.0' );
					wp_localize_script( $this->plugin_name, 'mwb_wgm', $mwb_wgm );
					wp_enqueue_script( $this->plugin_name );
				}
			}
		}
	}
	/**
	 * Adds some of the fields for Gift Card Product
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_before_add_to_cart_button()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_before_add_to_cart_button() {
		global $product;
		if ( isset( $product ) && ! empty( $product ) ) {
			$mwb_wgc_enable = mwb_wgm_giftcard_enable();
			if ( $mwb_wgc_enable ) {
				$product_id = $product->get_id();
				if ( isset( $product_id ) && ! empty( $product_id ) ) {
					$product_types = wp_get_object_terms( $product_id, 'product_type' );
					$product_type = $product_types[0]->slug;
					if ( $product_type == 'wgm_gift_card' ) {
						$product_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
						if ( isset( $product_pricing ) && ! empty( $product_pricing ) ) {
							?>
							<div class="mwb_wgm_added_wrapper">
								<?php
								wp_nonce_field( 'mwb_wgm_single_nonce', 'mwb_wgm_single_nonce_field' );
								if ( isset( $product_pricing['type'] ) ) {
									$product_pricing_type = $product_pricing['type'];
									if ( $product_pricing_type == 'mwb_wgm_range_price' ) {
										$default_price = $product_pricing['default_price'];
										$from_price = $product_pricing['from'];
										$to_price = $product_pricing['to'];
										$text_box_price = ( $default_price >= $from_price && $default_price <= $to_price ) ? $default_price : $from_price;
										// hooks for discount features.
										do_action( 'mwb_wgm_range_price_discount', $product, $product_pricing, $text_box_price );

										if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {
											if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
												$default_price = wcpbc_the_zone()->get_exchange_rate_price( $default_price );
												$to_price = wcpbc_the_zone()->get_exchange_rate_price( $to_price );
												$from_price = wcpbc_the_zone()->get_exchange_rate_price( $from_price );
											}
											?>
											<p class="mwb_wgm_section selected_price_type">
												<?php $mwb_new_price = ( $default_price >= $from_price && $default_price <= $to_price ) ? $default_price : $from_price; ?>
												<label><?php esc_html_e( 'Enter Price Within Above Range:', 'woocommerce_gift_cards_lite' ); ?></label>	
												<input type="number" class="input-text mwb_wgm_price" id="mwb_wgm_price" name="mwb_wgm_price" value="<?php echo esc_attr( $mwb_new_price ); ?>" max="<?php echo esc_attr( $to_price ); ?>" min="<?php echo esc_attr( $from_price ); ?>">
											</p>
											<?php
										} else {
											?>
											<p class="mwb_wgm_section selected_price_type">
												<?php $mwb_new_price = ( $default_price >= $from_price && $default_price <= $to_price ) ? $default_price : $from_price; ?>
												<label><?php esc_html_e( 'Enter Price Within Above Range:', 'woocommerce_gift_cards_lite' ); ?></label>	
												<input type="number" class="input-text mwb_wgm_price" id="mwb_wgm_price" name="mwb_wgm_price" value="<?php echo esc_attr( $mwb_new_price ); ?>" max="<?php echo esc_attr( $to_price ); ?>" min="<?php echo esc_attr( $from_price ); ?>">
											</p>
											<?php
										}
									}
									if ( $product_pricing_type == 'mwb_wgm_default_price' ) {
										$default_price = $product_pricing['default_price'];
										?>
										<input type="hidden" class="mwb_wgm_price" id="mwb_wgm_price" name="mwb_wgm_price" value="<?php echo esc_attr( $default_price ); ?>">
										<?php
										// hooks for discount features.
										do_action( 'mwb_wgm_default_price_discount', $product, $product_pricing );
									}
									if ( $product_pricing_type == 'mwb_wgm_selected_price' ) {
										$default_price = $product_pricing['default_price'];
										$selected_price = $product_pricing['price'];
										if ( ! empty( $selected_price ) ) {
											?>
											<p class="mwb_wgm_section selected_price_type">
												<label class="mwb_wgc_label"><?php esc_html_e( 'Choose Gift Card Selected Price:', 'woocommerce_gift_cards_lite' ); ?></label><br/>
												<?php
												$selected_prices = explode( '|', $selected_price );
												if ( isset( $selected_prices ) && ! empty( $selected_prices ) ) {
													?>
													<select name="mwb_wgm_price" class="mwb_wgm_price" id="mwb_wgm_price" >
														<?php
														foreach ( $selected_prices as $price ) {
															if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {

																if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
																	$default_price = wcpbc_the_zone()->get_exchange_rate_price( $default_price );
																	$prices = wcpbc_the_zone()->get_exchange_rate_price( $price );
																	if ( $prices == $default_price ) {
																		?>
																		<option  value="<?php echo esc_attr( $price ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?>" selected><?php echo wp_kses_post( wc_price( $prices ) ); ?></option>
																		<?php
																	} else {
																		?>
																		<option  value="<?php echo esc_attr( $price ); ?>"><?php echo wp_kses_post( wc_price( $prices ) ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?></option>
																		<?php
																	}
																} else {
																	if ( $price == $default_price ) {
																		?>
																		<option  value="<?php echo esc_attr( $price ); ?>" selected><?php echo wp_kses_post( wc_price( $price ) ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?></option>
																		<?php
																	} else {
																		?>
																		<option  value="<?php echo esc_attr( $price ); ?>"><?php echo wp_kses_post( wc_price( $price ) ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?></option>
																		<?php
																	}
																}
															} else {
																if ( $price == $default_price ) {
																	?>
																	<option  value="<?php echo esc_attr( $price ); ?>" selected><?php echo wp_kses_post( wc_price( $price ) ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?></option>
																	<?php
																} else {
																	?>
																	<option  value="<?php echo esc_attr( $price ); ?>"><?php echo wp_kses_post( wc_price( $price ) ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped. ?></option>
																	<?php
																}
															}
														}
														?>
													</select>
													<?php
												}
												?>
											</p>	
											<?php
										}
									}
									if ( $product_pricing_type == 'mwb_wgm_user_price' ) {
										$default_price = $product_pricing['default_price'];
										// hooks for discount features.
										do_action( 'mwb_wgm_user_price_discount', $product, $product_pricing );
										// price based on country.
										if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {
											$default_price = $product_pricing['default_price'];
											if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
												$default_price = wcpbc_the_zone()->get_exchange_rate_price( $default_price );
											}
											?>
											<p class="mwb_wgm_section selected_price_type">
												<label class="mwb_wgc_label"><?php esc_html_e( 'Enter Gift Card Price', 'woocommerce_gift_cards_lite' ); ?>:</label>	

												<input type="number" class="mwb_wgm_price" id="mwb_wgm_price" name="mwb_wgm_price" min="1" value=<?php echo esc_attr( $default_price ); ?>>
											</p>	
											<?php
										} else {
											?>
											<p class="mwb_wgm_section selected_price_type">
												<label class="mwb_wgc_label"><?php esc_html_e( 'Enter Gift Card Price', 'woocommerce_gift_cards_lite' ); ?>:</label>
												<input type="number" class="mwb_wgm_price" id="mwb_wgm_price" name="mwb_wgm_price" min="1" value=<?php echo esc_attr( $default_price ); ?>>
											</p>
											<?php
										}
									}
								}
								do_action( 'mwb_wgm_select_date', $product_id );
								?>
								<p class="mwb_wgm_section mwb_from">
									<label class="mwb_wgc_label"><?php esc_html_e( 'From', 'woocommerce_gift_cards_lite' ); ?>:</label>	
									<input type="text"  name="mwb_wgm_from_name" id="mwb_wgm_from_name" class="mwb_wgm_from_name" placeholder="<?php esc_attr_e( 'Enter the sender name', 'woocommerce_gift_cards_lite' ); ?>" required="required">
								</p>
								<p class="mwb_wgm_section mwb_message">
									<label class="mwb_wgc_label"><?php esc_html_e( 'Gift Message:', 'woocommerce_gift_cards_lite' ); ?></label>	
									<textarea name="mwb_wgm_message" id="mwb_wgm_message" class="mwb_wgm_message"></textarea>
									<?php
									$mail_settings = get_option( 'mwb_wgm_mail_settings', array() );
									$giftcard_message_length = $this->mwb_common_fun->mwb_wgm_get_template_data( $mail_settings, 'mwb_wgm_mail_setting_giftcard_message_length' );
									if ( $giftcard_message_length == '' ) {
										$giftcard_message_length = 300;
									}
									esc_html_e( 'Characters:', 'woocommerce_gift_cards_lite' );
									?>
									(<span id="mwb_box_char">0</span>/<?php echo esc_html( $giftcard_message_length ); ?>)
								</p>
								<?php
								$delivery_settings = get_option( 'mwb_wgm_delivery_settings', true );
								$mwb_wgm_delivery_setting_method = $this->mwb_common_fun->mwb_wgm_get_template_data( $delivery_settings, 'mwb_wgm_send_giftcard' );
								if ( ! mwb_uwgc_pro_active() ) {
									if ( $mwb_wgm_delivery_setting_method == 'customer_choose' || $mwb_wgm_delivery_setting_method == 'shipping' ) {
										$mwb_wgm_delivery_setting_method = 'Mail to recipient';
									}
								}
								?>
								<div class="mwb_wgm_section mwb_delivery_method">
									<label class = "mwb_wgc_label"><?php esc_html_e( 'Delivery Method', 'woocommerce_gift_cards_lite' ); ?>:</label>
									<?php
									if ( ( isset( $mwb_wgm_delivery_setting_method ) && $mwb_wgm_delivery_setting_method == 'Mail to recipient' ) || ( $mwb_wgm_delivery_setting_method == '' ) ) {
										?>
										<div class="mwb_wgm_delivery_method">
											<input type="radio" name="mwb_wgm_send_giftcard" value="Mail to recipient" class="mwb_wgm_send_giftcard" checked="checked" id="mwb_wgm_to_email_send" >
											<span class="mwb_wgm_method"><?php esc_html_e( 'Mail To Recipient', 'woocommerce_gift_cards_lite' ); ?></span>
											<div class="mwb_wgm_delivery_via_email">
												<input type="text"  name="mwb_wgm_to_email" id="mwb_wgm_to_email" class="mwb_wgm_to_email"placeholder="<?php esc_attr_e( 'Enter the Recipient Email ', 'woocommerce_gift_cards_lite' ); ?>">
												<input type="text"  name="mwb_wgm_to_name_optional" id="mwb_wgm_to_name_optional" class="mwb_wgm_to_email"placeholder="<?php esc_attr_e( 'Enter the Recipient Name', 'woocommerce_gift_cards_lite' ); ?>">
												<span class= "mwb_wgm_msg_info"><?php esc_html_e( 'We will send it to recipient email address.', 'woocommerce_gift_cards_lite' ); ?></span>
											</div>
										</div>
										<?php
									}
									if ( isset( $mwb_wgm_delivery_setting_method ) && $mwb_wgm_delivery_setting_method == 'Downloadable' ) {
										?>
										<div class="mwb_wgm_delivery_method">
											<input type="radio" name="mwb_wgm_send_giftcard" value="Downloadable" class="mwb_wgm_send_giftcard" checked="checked" id="mwb_wgm_send_giftcard_download">
											<span class="mwb_wgm_method"><?php esc_html_e( 'You Print & Give To Recipient', 'woocommerce_gift_cards_lite' ); ?></span>
											<div class="mwb_wgm_delivery_via_buyer">
												<input type="text"  name="mwb_wgm_to_email_name" id="mwb_wgm_to_download" class="mwb_wgm_to_email" placeholder="<?php esc_attr_e( 'Enter the Recipient Name', 'woocommerce_gift_cards_lite' ); ?>">
												<span class= "mwb_wgm_msg_info"><?php esc_html_e( 'After Checkout, you can print your giftcard', 'woocommerce_gift_cards_lite' ); ?></span>
											</div>
										</div>
										<?php
									}
									do_action( 'mwb_wgm_add_delivery_method', $product_id );
									?>

								</div>
								<?php
								$mwb_wgm_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
								$templateid = $mwb_wgm_pricing['template'];
								$assigned_temp = '';
								$choosed_temp = '';
								$default_selected = isset( $mwb_wgm_pricing['by_default_tem'] ) ? $mwb_wgm_pricing['by_default_tem'] : false;

								$mwb_wgm_hide_giftcard_thumbnail = '';
								$mwb_wgm_hide_giftcard_thumbnail = apply_filters( 'mwb_wgm_hide_giftcard_product_thumbnail', $mwb_wgm_hide_giftcard_thumbnail );

								if ( is_array( $templateid ) && ! empty( $templateid ) ) {
									foreach ( $templateid as $key => $temp_id ) {
										$featured_img = wp_get_attachment_image_src( get_post_thumbnail_id( $temp_id ), 'single-post-thumbnail' );
										if ( empty( $featured_img[0] ) ) {
											$featured_img[0] = MWB_WGC_URL . 'assets/images/mom.png';
										}
										$selected_class = '';
										if ( isset( $default_selected ) && $default_selected != null && $default_selected == $temp_id ) {
											$selected_class = 'mwb_wgm_pre_selected_temp';
											$choosed_temp = $temp_id;
										} elseif ( empty( $default_selected ) && is_array( $templateid ) ) {
											$selected_class = 'mwb_wgm_pre_selected_temp';
										} elseif ( is_array( $default_selected ) && ! empty( $default_selected ) ) {
											$selected_class = 'mwb_wgm_pre_selected_temp';
											$choosed_temp = $temp_id;
										}
										$assigned_temp .= '<img class = "mwb_wgm_featured_img ' . $selected_class . '" id="' . $temp_id . '" style="width: 70px; height: 70px; display: inline-block;margin-right:5px;" src="' . $featured_img[0] . '">';
									}
								} elseif ( ! is_array( $templateid ) && ! empty( $templateid ) ) {
									$featured_img = wp_get_attachment_image_src( get_post_thumbnail_id( $templateid ), 'single-post-thumbnail' );
									if ( empty( $featured_img[0] ) ) {
										$featured_img[0] = MWB_WGC_URL . 'assets/images/mom.png';
									}
									$assigned_temp .= '<img class = "mwb_wgm_featured_img mwb_wgm_pre_selected_temp" id="' . $templateid . '" style="width: 70px; height: 70px; display: inline-block;" src="' . $featured_img[0] . '">';
									$choosed_temp = $templateid;
								}
								if ( isset( $mwb_wgm_hide_giftcard_thumbnail ) && $mwb_wgm_hide_giftcard_thumbnail != 'on' ) {
									?>
									<div class="mwb_wgm_selected_template" style="display: inline-block; text-decoration: none; padding-right:20px;"><?php echo wp_kses_post( $assigned_temp ); ?></div>
									<?php

								} else {
									?>
									
									<div class="mwb_wgm_selected_template" style="display: none; text-decoration: none; padding-right:20px;"><?php echo wp_kses_post( $assigned_temp ); ?></div>
									
									<?php
								}
								?>
								<input name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" type="hidden" class="mwb_wgm_hidden_pro_id">
								<?php if ( is_array( $templateid ) && ! empty( $templateid ) ) { ?>
									<input name="mwb_wgm_selected_temp" id="mwb_wgm_selected_temp" value="<?php echo esc_attr( $choosed_temp ); ?>" type="hidden">
									<?php
								}
								$other_settings = get_option( 'mwb_wgm_other_settings', array() );
								$mwb_wgm_preview_disable = $this->mwb_common_fun->mwb_wgm_get_template_data( $other_settings, 'mwb_wgm_additional_preview_disable' );
								if ( empty( $mwb_wgm_preview_disable ) ) {
									?>
									<br/>
									<span class="mwg_wgm_preview_email" ><a id="mwg_wgm_preview_email" href="javascript:void(0);"><?php esc_html_e( 'PREVIEW', 'woocommerce_gift_cards_lite' ); ?></a></span>
									<?php
								}
								?>
							</div>
							<?php
						}
					}
				}
			}
		}
	}

	/**
	 * Adds the meta data into the Cart Item.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_add_cart_item_data()
	 * @param   array $the_cart_data  The array of Cart Data.
	 * @param   array $product_id  product Id.
	 * @param   array $variation_id  variation_id.
	 * @return  $the_cart_data
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_add_cart_item_data( $the_cart_data, $product_id, $variation_id ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			$product_types = wp_get_object_terms( $product_id, 'product_type' );
			if ( isset( $product_types[0] ) ) {
				$product_type = $product_types[0]->slug;
				if ( $product_type == 'wgm_gift_card' ) {
					check_admin_referer( 'mwb_wgm_single_nonce', 'mwb_wgm_single_nonce_field' );
					// for price based on country.
					if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {
						if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
							$product_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
							$product_pricing_type = $product_pricing['type'];
							if ( isset( $_POST['mwb_wgm_price'] ) && ! empty( $_POST['mwb_wgm_price'] ) ) {
								if ( $product_pricing_type == 'mwb_wgm_range_price' || $product_pricing_type == 'mwb_wgm_user_price' ) {
									$exchange_rate = wcpbc_the_zone()->get_exchange_rate();
									$mwb_price = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_price'] ) );
									$_POST['mwb_wgm_price'] = floatval( $mwb_price / $exchange_rate );
								}
							}
						}
					}
					if ( isset( $_POST['mwb_wgm_send_giftcard'] ) && ! empty( $_POST['mwb_wgm_send_giftcard'] ) ) {
						$product_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
						if ( isset( $product_pricing ) && ! empty( $product_pricing ) ) {

							if ( isset( $_POST['mwb_wgm_to_email'] ) && ! empty( $_POST['mwb_wgm_to_email'] ) ) {

								$item_meta['mwb_wgm_to_email'] = sanitize_email( wp_unslash( $_POST['mwb_wgm_to_email'] ) );
							}
							if ( isset( $_POST['mwb_wgm_to_email_name'] ) && ! empty( $_POST['mwb_wgm_to_email_name'] ) ) {

								$item_meta['mwb_wgm_to_email'] = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_to_email_name'] ) );
							}
							if ( isset( $_POST['mwb_wgm_from_name'] ) && ! empty( $_POST['mwb_wgm_from_name'] ) ) {

								$item_meta['mwb_wgm_from_name'] = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_from_name'] ) );
							}
							if ( isset( $_POST['mwb_wgm_message'] ) && ! empty( $_POST['mwb_wgm_message'] ) ) {
								$item_meta['mwb_wgm_message'] = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_message'] ) );
							}
							if ( isset( $_POST['mwb_wgm_send_giftcard'] ) && ! empty( $_POST['mwb_wgm_send_giftcard'] ) ) {
								$item_meta['delivery_method'] = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_send_giftcard'] ) );
							}
							if ( isset( $_POST['mwb_wgm_price'] ) ) {

								$mwb_wgm_price = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_price'] ) );

								if ( isset( $product_pricing['type'] ) && $product_pricing['type'] == 'mwb_wgm_default_price' ) {
									if ( isset( $product_pricing['default_price'] ) && $product_pricing['default_price'] == $mwb_wgm_price ) {
										$item_meta['mwb_wgm_price'] = $mwb_wgm_price;
									} else {
										$item_meta['mwb_wgm_price'] = $product_pricing['default_price'];
									}
								} else if ( isset( $product_pricing['type'] ) && $product_pricing['type'] == 'mwb_wgm_selected_price' ) {

									$price = $product_pricing['price'];
									$price = explode( '|', $price );
									if ( isset( $price ) && is_array( $price ) ) {
										if ( in_array( $mwb_wgm_price, $price ) ) {
											$item_meta['mwb_wgm_price'] = $mwb_wgm_price;
										} else {
											$item_meta['mwb_wgm_price'] = $product_pricing['default_price'];
										}
									}
								} else if ( isset( $product_pricing['type'] ) && $product_pricing['type'] == 'mwb_wgm_range_price' ) {

									if ( $mwb_wgm_price > $product_pricing['to'] || $mwb_wgm_price < $product_pricing['from'] ) {
										$item_meta['mwb_wgm_price'] = $product_pricing['default_price'];
									} else {
										$item_meta['mwb_wgm_price'] = $mwb_wgm_price;
									}
								} else {
									$item_meta['mwb_wgm_price'] = $mwb_wgm_price;
								}
							}
							if ( isset( $_POST['mwb_wgm_selected_temp'] ) ) {
								$item_meta['mwb_wgm_selected_temp'] = sanitize_text_field( wp_unslash( $_POST['mwb_wgm_selected_temp'] ) );
							}
							$item_meta = apply_filters( 'mwb_wgm_add_cart_item_data', $item_meta, $the_cart_data, $product_id, $variation_id );
							$the_cart_data ['product_meta'] = array( 'meta_data' => $item_meta );
						}
					}
				}
			}
		}
		return $the_cart_data;
	}

	/**
	 * List out the Meta Data into the Cart Items.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_get_item_data()
	 * @param   array $item_meta  New Item Meta.
	 * @param   array $existing_item_meta  existing_item_meta.
	 * @return  $item_meta
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_get_item_data( $item_meta, $existing_item_meta ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			if ( isset( $existing_item_meta ['product_meta']['meta_data'] ) ) {
				foreach ( $existing_item_meta['product_meta'] ['meta_data'] as $key => $val ) {
					if ( $key == 'mwb_wgm_to_email' ) {
						$item_meta [] = array(
							'name' => esc_html__( 'To', 'woocommerce_gift_cards_lite' ),
							'value' => stripslashes( $val ),
						);
					}
					if ( $key == 'mwb_wgm_from_name' ) {
						$item_meta [] = array(
							'name' => esc_html__( 'From', 'woocommerce_gift_cards_lite' ),
							'value' => stripslashes( $val ),
						);
					}
					if ( $key == 'mwb_wgm_message' ) {
						$item_meta [] = array(
							'name' => esc_html__( 'Gift Message', 'woocommerce_gift_cards_lite' ),
							'value' => stripslashes( $val ),
						);
					}
					$item_meta = apply_filters( 'mwb_wgm_get_item_meta', $item_meta, $key, $val );
				}
			}
		}
		return $item_meta;
	}

	/**
	 * Set the Gift Card Price into Cart.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_before_calculate_totals()
	 * @param   array $cart  Cart Data.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_before_calculate_totals( $cart ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			if ( isset( $cart ) && ! empty( $cart ) ) {
				foreach ( $cart->cart_contents as $key => $value ) {
					$product_id = $value['product_id'];
					$pro_quant = $value['quantity'];
					if ( isset( $value['product_meta']['meta_data'] ) ) {
						if ( isset( $value['product_meta']['meta_data']['mwb_wgm_price'] ) ) {
							$gift_price = $value['product_meta']['meta_data']['mwb_wgm_price'];

							if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {
								if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
									$gift_price = wcpbc_the_zone()->get_exchange_rate_price( $gift_price );
								}
							}
							$gift_price = apply_filters( 'mwb_wgm_before_calculate_totals', $gift_price, $value );
							$value['data']->set_price( $gift_price );
						}
					}
				}
			}
		}
	}

	/**
	 * Displays the Different Price type for Gift Cards into single product page
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_get_price_html()
	 * @param string $price_html price.
	 * @param object $product product.
	 * @return $price_html.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_get_price_html( $price_html, $product ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			$product_id = $product->get_id();
			if ( isset( $product_id ) ) {
				$product_types = wp_get_object_terms( $product_id, 'product_type' );
				if ( isset( $product_types[0] ) ) {
					$product_type = $product_types[0]->slug;
					if ( $product_type == 'wgm_gift_card' ) {
						$product_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
						if ( isset( $product_pricing ) && ! empty( $product_pricing ) ) {
							if ( isset( $product_pricing['type'] ) ) {
								$product_pricing_type = $product_pricing['type'];
								if ( $product_pricing_type == 'mwb_wgm_default_price' ) {
									$new_price = '';
									$default_price = $product_pricing['default_price'];
									$price_html = $price_html;
								}
								if ( $product_pricing_type == 'mwb_wgm_range_price' ) {
									$price_html = '';
									$from_price = $product_pricing['from'];
									$to_price = $product_pricing['to'];
									// price based on country.
									if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {
										if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
											$from_price = wcpbc_the_zone()->get_exchange_rate_price( $from_price );
											$to_price = wcpbc_the_zone()->get_exchange_rate_price( $to_price );
										}
										$price_html .= '<ins><span class="woocommerce-Price-amount amount">' . wc_price( $from_price ) . ' - ' . wc_price( $to_price ) . '</span></ins>';
									} else {
										$price_html .= '<ins><span class="woocommerce-Price-amount amount">' . wc_price( $from_price ) . ' - ' . wc_price( $to_price ) . '</span></ins>';
									}
								}
								if ( $product_pricing_type == 'mwb_wgm_selected_price' ) {
									$selected_price = $product_pricing['price'];
									if ( ! empty( $selected_price ) ) {
										$selected_prices = explode( '|', $selected_price );
										if ( isset( $selected_prices ) && ! empty( $selected_prices ) ) {
											$price_html = '';
											$price_html .= '<ins><span class="woocommerce-Price-amount amount">';
											$last_range = end( $selected_prices );
											// price based on country.
											if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {

												if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
													$last_range = wcpbc_the_zone()->get_exchange_rate_price( $last_range );
													$selected_prices[0] = wcpbc_the_zone()->get_exchange_rate_price( $selected_prices[0] );
												}
												$price_html .= wc_price( $selected_prices[0] ) . '-' . wc_price( $last_range ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
											} else {
												$price_html .= wc_price( $selected_prices[0] ) . '-' . wc_price( $last_range ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
											}
											$price_html .= '</span></ins>';
										}
									}
								}
							}
						}
						$price_html = apply_filters( 'mwb_wgm_pricing_html', $price_html, $product, $product_pricing );
					}
				}
			}
		}
		return $price_html;
	}

	/**
	 * Handles Coupon Generation process on the order Status Changed process.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_order_status_changed()
	 * @param int    $order_id order Id.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {

		$mwb_wgm_mail_template_data = array();
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			if ( $old_status != $new_status ) {
				if ( $new_status == 'completed' || $new_status == 'processing' ) {

					$mailalreadysend = get_post_meta( $order_id, 'mwb_wgm_order_giftcard', true );
					if ( $mailalreadysend == 'send' ) {
						return;
					} else {
						$datecheck = true;
						$general_setting = get_option( 'mwb_wgm_general_settings', array() );
						$giftcard_selected_date = $this->mwb_common_fun->mwb_wgm_get_template_data( $general_setting, 'mwb_uwgc_general_setting_enable_selected_date' );
						if ( $giftcard_selected_date == 'on' ) {
							update_post_meta( $order_id, 'mwb_wgm_order_giftcard', 'notsend' );
						}
					}

					$gift_msg = '';
					$to = '';
					$from = '';
					$gift_order = false;
					$selected_template = '';
					$original_price = 0;
					$delivery_method = '';
					$order = wc_get_order( $order_id );
					foreach ( $order->get_items() as $item_id => $item ) {
						$mailsend = false;
						$to_name = '';
						$woo_ver = WC()->version;
						$gift_img_name = '';
						if ( $woo_ver < '3.0.0' ) {
							$item_quantity = $order->get_item_meta( $item_id, '_qty', true );
							$product = $order->get_product_from_item( $item );
							$pro_id = $product->id;
							if ( isset( $item['item_meta']['To'] ) && ! empty( $item['item_meta']['To'] ) ) {
								$mailsend = true;
								$to = $item['item_meta']['To'][0];

							}
							if ( isset( $item['item_meta']['From'] ) && ! empty( $item['item_meta']['From'] ) ) {
								$mailsend = true;
								$from = $item['item_meta']['From'][0];
							}
							if ( isset( $item['item_meta']['Message'] ) && ! empty( $item['item_meta']['Message'] ) ) {
								$mailsend = true;
								$gift_msg = $item['item_meta']['Message'][0];
							}
							if ( isset( $item['item_meta']['Delivery Method'] ) && ! empty( $item['item_meta']['Delivery Method'] ) ) {
								$mailsend = true;
								$delivery_method = $item['item_meta']['Delivery Method'][0];
							}
							if ( isset( $item['item_meta']['Original Price'] ) && ! empty( $item['item_meta']['Original Price'] ) ) {
								$mailsend = true;
								$original_price = $item['item_meta']['Original Price'][0];
							}
							if ( isset( $item['item_meta']['Selected Template'] ) && ! empty( $item['item_meta']['Selected Template'] ) ) {
								$mailsend = true;
								$selected_template = $item['item_meta']['Selected Template'][0];
							}
						} else {
							$item_quantity = wc_get_order_item_meta( $item_id, '_qty', true );
							$product = $item->get_product();
							$pro_id = $product->get_id();
							$item_meta_data = $item->get_meta_data();
							$gift_date_check = false;
							$gift_date = '';
							$original_price = 0;
							foreach ( $item_meta_data as $key => $value ) {
								if ( isset( $value->key ) && $value->key == 'To' && ! empty( $value->value ) ) {
									$mailsend = true;
									$to = $value->value;
								}
								if ( isset( $value->key ) && $value->key == 'From' && ! empty( $value->value ) ) {
									$mailsend = true;
									$from = $value->value;
								}
								if ( isset( $value->key ) && $value->key == 'Message' && ! empty( $value->value ) ) {
									$mailsend = true;
									$gift_msg = $value->value;
								}
								if ( isset( $value->key ) && $value->key == 'Delivery Method' && ! empty( $value->value ) ) {
									$mailsend = true;
									$delivery_method = $value->value;
								}
								if ( isset( $value->key ) && $value->key == 'Original Price' && ! empty( $value->value ) ) {
									$mailsend = true;
									$original_price = $value->value;
								}
								if ( isset( $value->key ) && $value->key == 'Selected Template' && ! empty( $value->value ) ) {
									$mailsend = true;
									$selected_template = $value->value;
								}
							}
						}
						$mwb_wgm_mail_template_data = array(
							'to' => $to,
							'from' => $from,
							'gift_msg' => $gift_msg,
							'delivery_method' => $delivery_method,
							'original_price' => $original_price,
							'selected_template' => $selected_template,
							'mail_send' => $mailsend,
							'product_id' => $pro_id,
							'item_id'   => $item_id,
							'item_quantity' => $item_quantity,
							'datecheck' => $datecheck,
						);

						$mwb_wgm_mail_template_data = apply_filters( 'mwb_wgm_mail_templates_data_set', $mwb_wgm_mail_template_data, $order->get_items(), $order_id );

						if ( isset( $mwb_wgm_mail_template_data['datecheck'] ) && ! $mwb_wgm_mail_template_data['datecheck'] ) {
							continue;
						}

						if ( isset( $mwb_wgm_mail_template_data['mail_send'] ) && $mwb_wgm_mail_template_data['mail_send'] ) {

							$gift_order = true;
							$inc_tax_status = get_option( 'woocommerce_prices_include_tax', false );
							if ( $inc_tax_status == 'yes' ) {
								$inc_tax_status = true;
							} else {
								$inc_tax_status = false;
							}
							$couponamont = $original_price;

							$mwb_wgm_lite = true;
							$mwb_wgm_lite = apply_filters( 'mwb_wgm_check_coupon_creation_mails', $mwb_wgm_mail_template_data, $order_id, $item, $mwb_wgm_lite );

							if ( $mwb_wgm_lite ) {
								$general_setting = get_option( 'mwb_wgm_general_settings', array() );
								$giftcard_coupon_length = $this->mwb_common_fun->mwb_wgm_get_template_data( $general_setting, 'mwb_wgm_general_setting_giftcard_coupon_length' );
								if ( $giftcard_coupon_length == '' ) {
									$giftcard_coupon_length = 5;
								}
								for ( $i = 1; $i <= $item_quantity; $i++ ) {
									$gift_couponnumber = mwb_wgm_coupon_generator( $giftcard_coupon_length );
									if ( $this->mwb_common_fun->mwb_wgm_create_gift_coupon( $gift_couponnumber, $couponamont, $order_id, $item['product_id'], $to ) ) {
										$todaydate = date_i18n( 'Y-m-d' );
										$expiry_date = $this->mwb_common_fun->mwb_wgm_get_template_data( $general_setting, 'mwb_wgm_general_setting_giftcard_expiry' );
										$expirydate_format = $this->mwb_common_fun->mwb_wgm_check_expiry_date( $expiry_date );
										$mwb_wgm_common_arr['order_id'] = $order_id;
										$mwb_wgm_common_arr['product_id'] = $pro_id;
										$mwb_wgm_common_arr['to'] = $to;
										$mwb_wgm_common_arr['from'] = $from;
										$mwb_wgm_common_arr['gift_couponnumber'] = $gift_couponnumber;
										$mwb_wgm_common_arr['gift_msg'] = $gift_msg;
										$mwb_wgm_common_arr['expirydate_format'] = $expirydate_format;
										$mwb_wgm_common_arr['couponamont'] = $couponamont;
										$mwb_wgm_common_arr['selected_template'] = isset( $selected_template ) ? $selected_template : '';
										$mwb_wgm_common_arr['delivery_method'] = $delivery_method;
										$mwb_wgm_common_arr['item_id'] = $item_id;
										$mwb_wgm_common_arr = apply_filters( 'mwb_wgm_common_arr_data', $mwb_wgm_common_arr, $item, $order );
										$mwb_wgm_coupon_code = $this->mwb_common_fun->mwb_wgm_common_functionality( $mwb_wgm_common_arr, $order );
									}
								}
							}
						}
					}
					if ( $gift_order && isset( $mwb_wgm_mail_template_data['datecheck'] ) && $mwb_wgm_mail_template_data['datecheck'] ) {
						update_post_meta( $order_id, 'mwb_wgm_order_giftcard', 'send' );
					}
					do_action( 'mwb_wgm_thankyou_coupon_order_status_change', $order_id, $new_status );
				}
			}
		}
	}

	/**
	 * Hide coupon feilds from cart page if only giftcard products are there
	 *
	 * @name mwb_wgm_hidding_coupon_field_on_cart
	 * @param bool $enabled boolean return.
	 * return $enabled.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_wgm_hidding_coupon_field_on_cart( $enabled ) {
		$bool = false;
		$bool2 = false;
		$is_checkout = false;
		if ( ! empty( WC()->cart ) ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = wc_get_product( $cart_item['product_id'] );
				if ( $_product->is_type( 'wgm_gift_card' ) ) {
					$bool = true;
				} else {
					$bool2 = true;
				}
			}
		}
		if ( $bool && is_cart() && ! $bool2 ) {
			$enabled = false;
		} elseif ( ! $bool && $bool2 && is_cart() ) {
			$enabled = true;
		} elseif ( $bool && $bool2 ) {
			$enabled = true;
		} elseif ( $bool && is_checkout() && ! $bool2 ) {
			$enabled = false;
		} elseif ( ! $bool && $bool2 && is_checkout() ) {
			$enabled = true;
		}
		return $enabled;
	}

	/**
	 * Hide order meta fields from thankyou page and email template.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_hide_order_metafields()
	 * @param array $formatted_meta formatted_meta.
	 * @return $temp_metas.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_hide_order_metafields( $formatted_meta ) {
		$temp_metas = array();
		if ( isset( $formatted_meta ) && ! empty( $formatted_meta ) && is_array( $formatted_meta ) ) {
			foreach ( $formatted_meta as $key => $meta ) {
				if ( isset( $meta->key ) && ! in_array( $meta->key, array( 'Delivery Method', 'Original Price' ) ) ) {

					$temp_metas[ $key ] = $meta;
				}
			}
			$temp_metas = apply_filters( 'mwb_wgm_hide_order_metafields', $temp_metas, $formatted_meta );
			return $temp_metas;
		} else {
			return $formatted_meta;
		}
	}

	/**
	 * Adds the Order-item-meta inside the each gift card orders.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_checkout_create_order_line_item()
	 * @param object $item item object.
	 * @param string $cart_key cart key.
	 * @param array  $values cart values.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_checkout_create_order_line_item( $item, $cart_key, $values ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			if ( isset( $values ['product_meta'] ) ) {
				foreach ( $values ['product_meta'] ['meta_data'] as $key => $val ) {
					$order_val = stripslashes( $val );
					if ( $val ) {
						if ( $key == 'mwb_wgm_to_email' ) {
							$item->add_meta_data( 'To', $order_val );
						}
						if ( $key == 'mwb_wgm_from_name' ) {
							$item->add_meta_data( 'From', $order_val );
						}
						if ( $key == 'mwb_wgm_message' ) {
							$item->add_meta_data( 'Message', $order_val );
						}
						if ( $key == 'mwb_wgm_price' ) {
							$item->add_meta_data( 'Original Price', $order_val );
						}
						if ( $key == 'delivery_method' ) {
							$item->add_meta_data( 'Delivery Method', $order_val );
						}
						if ( $key == 'mwb_wgm_selected_temp' ) {
							$item->add_meta_data( 'Selected Template', $order_val );
						}
						do_action( 'mwb_wgm_checkout_create_order_line_item', $item, $key, $order_val );
					}
				}
			}
		}
	}

	/**
	 * Removes Add to cart button and Adds View Card Button.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_loop_add_to_cart_link()
	 * @param string $link add link.
	 * @param object $product product.
	 * @return $link.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_loop_add_to_cart_link( $link, $product ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			$product_id = $product->get_id();
			if ( isset( $product_id ) ) {
				$product_types = wp_get_object_terms( $product_id, 'product_type' );
				if ( isset( $product_types[0] ) ) {
					$product_type = $product_types[0]->slug;
					if ( $product_type == 'wgm_gift_card' ) {
						$product_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
						if ( isset( $product_pricing ) && ! empty( $product_pricing ) ) {
							$link = sprintf(
								'<a rel="nofollow" href="%s" class="%s">%s</a>',
								esc_url( get_the_permalink() ),
								esc_attr( isset( $class ) ? $class : 'button' ),
								esc_html( apply_filters( 'mwb_wgm_view_card_text', __( 'VIEW CARD', 'woocommerce_gift_cards_lite' ) ) )
							);
						}
						$link = apply_filters( 'mwb_wgm_loop_add_to_cart_link', $link, $product );
					}
				}
			}
		}
		return $link;
	}

	/**
	 * Enable the Taxes for Gift Card if the required setting is enabled
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_product_is_taxable()
	 * @param bool   $taxable taxable.
	 * @param object $product product.
	 * @return $taxable.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_product_is_taxable( $taxable, $product ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			$genaral_settings = get_option( 'mwb_wgm_general_settings', array() );
			$giftcard_tax_cal_enable = $this->mwb_common_fun->mwb_wgm_get_template_data( $genaral_settings, 'mwb_wgm_general_setting_tax_cal_enable' );
			if ( ! isset( $giftcard_tax_cal_enable ) || empty( $giftcard_tax_cal_enable ) ) {
				$giftcard_tax_cal_enable = 'off';
			}
			if ( $giftcard_tax_cal_enable == 'off' ) {
				$product_id = $product->get_id();
				$product_types = wp_get_object_terms( $product_id, 'product_type' );
				if ( isset( $product_types[0] ) ) {
					$product_type = $product_types[0]->slug;
					if ( $product_type == 'wgm_gift_card' ) {
						$taxable = false;
					}
				}
			}
		}
		return $taxable;
	}

	/**
	 * Set the error notice div on single product page
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_before_main_content()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_before_main_content() {
		global $post;
		if ( isset( $post->ID ) ) {
			$product_id = $post->ID;
			$product_types = wp_get_object_terms( $product_id, 'product_type' );
			if ( isset( $product_types[0] ) ) {
				$product_type = $product_types[0]->slug;
				if ( $product_type == 'wgm_gift_card' ) {
					?>
					<div class="woocommerce-error" id="mwb_wgm_error_notice" style="display:none;"></div>
					<?php
				}
			}
		}
	}

	/**
	 * Show/Hide Gift Card product from shop page depending upon the required setting.
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_product_query()
	 * @param bool   $query query.
	 * @param object $query_object query object.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_product_query( $query, $query_object ) {
		$mwb_wgc_enable = mwb_wgm_giftcard_enable();
		if ( $mwb_wgc_enable ) {
			$genaral_settings = get_option( 'mwb_wgm_general_settings', array() );
			$giftcard_shop_page = $this->mwb_common_fun->mwb_wgm_get_template_data( $genaral_settings, 'mwb_wgm_general_setting_shop_page_enable' );
			if ( ! isset( $giftcard_shop_page ) || empty( $giftcard_shop_page ) ) {
				$giftcard_shop_page = 'off';
			}
			if ( $giftcard_shop_page != 'on' ) {
				if ( is_shop() ) {
					$args = array(
						'post_type' => 'product',
						'posts_per_page' => -1,
						'meta_key' => 'mwb_wgm_pricing',
					);
					$gift_products = array();
					$loop = new WP_Query( $args );
					if ( $loop->have_posts() ) :
						while ( $loop->have_posts() ) :
							$loop->the_post();
							global $product;
							$product_id = $loop->post->ID;
							$product_types = wp_get_object_terms( $product_id, 'product_type' );
							if ( isset( $product_types[0] ) ) {
								$product_type = $product_types[0]->slug;
								if ( $product_type == 'wgm_gift_card' ) {
									$gift_products[] = $product_id;
								}
							}
						endwhile;
					endif;
					$query->set( 'post__not_in', $gift_products );
				}
			}
		}
	}

	/**
	 * Adjust the Gift Card Amount, when it has been applied to any product for getting discount
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_woocommerce_new_order_item()
	 * @param  int    $item_id item_id.
	 * @param object $item item.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_woocommerce_new_order_item( $item_id, $item ) {
		if ( get_class( $item ) == 'WC_Order_Item_Coupon' ) {

			$coupon_code = $item->get_code();
			$the_coupon = new WC_Coupon( $coupon_code );
			$coupon_id = $the_coupon->get_id();
			if ( isset( $coupon_id ) ) {
				$rate = 1;
				// price based on country.
				if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {

					if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {

						$rate = wcpbc_the_zone()->get_exchange_rate();
					}
				}
				$giftcardcoupon = get_post_meta( $coupon_id, 'mwb_wgm_giftcard_coupon', true );
				if ( ! empty( $giftcardcoupon ) ) {
					$mwb_wgm_discount = $item->get_discount();
					$mwb_wgm_discount_tax = $item->get_discount_tax();
					$amount = get_post_meta( $coupon_id, 'coupon_amount', true );

					$total_discount = $this->mwb_common_fun->mwb_wgm_calculate_coupon_discount( $mwb_wgm_discount, $mwb_wgm_discount_tax );
					$total_discount = $total_discount / $rate;

					if ( $amount < $total_discount ) {
						$remaining_amount = 0;
					} else {
						$remaining_amount = $amount - $total_discount;
						$remaining_amount = round( $remaining_amount, 2 );
					}
					update_post_meta( $coupon_id, 'coupon_amount', $remaining_amount );
					do_action( 'mwb_wgm_send_mail_remaining_amount', $coupon_id, $remaining_amount );
				} else {
					do_action( 'mwb_wgm_offline_giftcard_coupon', $coupon_id, $item );
				}
			}
		}
	}

	/**
	 * Disable the Shipping fee if there is only Gift Card Product
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_wc_shipping_enabled()
	 * @param bool $enable enable.
	 * @return $enable.
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_wc_shipping_enabled( $enable ) {
		if ( is_checkout() || is_cart() ) {
			global $woocommerce;
			$gift_bool = false;
			$other_bool = false;
			$gift_bool_ship = false;
			if ( isset( WC()->cart ) && ! empty( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
					$product_types = wp_get_object_terms( $product_id, 'product_type' );
					if ( isset( $product_types[0] ) ) {
						$product_type = $product_types[0]->slug;
						if ( $product_type == 'wgm_gift_card' ) {
							if ( $cart_item['product_meta']['meta_data']['delivery_method'] == 'Mail to recipient' || $cart_item['product_meta']['meta_data']['delivery_method'] == 'Downloadable' ) {
								$gift_bool = true;
							} elseif ( $cart_item['product_meta']['meta_data']['delivery_method'] == 'Shipping' ) {
								$gift_bool_ship = true;
							}
						} else if ( ! $cart_item['data']->is_virtual() ) {
							$other_bool = true;
						}
					}
				}
				if ( $gift_bool && ! $gift_bool_ship && ! $other_bool ) {
					$enable = false;
				} else {
					$enable = true;
				}
			}
		}
		return $enable;
	}

	/**
	 * Create the Thickbox Query
	 *
	 * @since 1.0.0
	 * @name mwb_wgm_preview_thickbox_rqst
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_preview_thickbox_rqst() {
		check_ajax_referer( 'mwb-wgc-verify-nonce', 'mwb_nonce' );
		$_POST['mwb_wgc_preview_email'] = 'mwb_wgm_single_page_popup';
		$_POST['tempId'] = isset( $_POST['tempId'] ) ? stripcslashes( sanitize_text_field( wp_unslash( $_POST['tempId'] ) ) ) : '';
		$_POST['message'] = isset( $_POST['message'] ) ? stripcslashes( sanitize_text_field( wp_unslash( $_POST['message'] ) ) ) : '';
		$_POST = apply_filters( 'mwb_wgm_upload_giftcard_image', $_POST );
		$_POST['width'] = '630';
		$_POST['height'] = '530';
		$_POST['TB_iframe'] = true;
		$query = http_build_query( wp_unslash( $_POST ) );
		$ajax_url = home_url( "?$query" );
		echo esc_attr( $ajax_url );
		wp_die();
	}
	/**
	 * Show the Preview Email Template for SIngle Product Page inside the thickbox
	 *
	 * @since 1.0.0
	 * @name mwb_wgc_preview_email
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_wgm_preview_email_on_single_page() {

		if ( isset( $_GET['mwb_wgc_preview_email'] ) && $_GET['mwb_wgc_preview_email'] == 'mwb_wgm_single_page_popup' ) {
			$product_id = isset( $_GET['product_id'] ) ? sanitize_text_field( wp_unslash( $_GET['product_id'] ) ) : '';
			$product_pricing = get_post_meta( $product_id, 'mwb_wgm_pricing', true );
			$product_pricing_type = $product_pricing['type'];
			$general_setting = get_option( 'mwb_wgm_general_settings', array() );
			$giftcard_coupon_length_display = $this->mwb_common_fun->mwb_wgm_get_template_data( $general_setting, 'mwb_wgm_general_setting_giftcard_coupon_length' );
			if ( $giftcard_coupon_length_display == '' ) {
				$giftcard_coupon_length_display = 5;
			}
			$password = '';
			for ( $i = 0;$i < $giftcard_coupon_length_display;$i++ ) {
				$password .= 'x';
			}
			$giftcard_prefix = $general_setting['mwb_wgm_general_setting_giftcard_prefix'];
			$coupon = $giftcard_prefix . $password;
			$expiry_date = $general_setting['mwb_wgm_general_setting_giftcard_expiry'];
			$expirydate_format = $this->mwb_common_fun->mwb_wgm_check_expiry_date( $expiry_date );
			$mwb_temp_id = isset( $_GET['tempId'] ) ? sanitize_text_field( wp_unslash( $_GET['tempId'] ) ) : '';
			$templateid = $product_pricing['template'];
			if ( is_array( $templateid ) && array_key_exists( 0, $templateid ) ) {
				$temp = $templateid[0];
			} else {
				$temp = $templateid;
			}
			$args['to'] = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
			$args['from'] = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
			$args['message'] = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
			$args['coupon'] = apply_filters( 'mwb_wgm_qrcode_coupon', $coupon );
			$args['expirydate'] = $expirydate_format;

			$args = apply_filters( 'mwb_wgm_add_preview_template_fields', $args );

			if ( class_exists( 'WCPBC_Pricing_Zone' ) ) {  // Added for price based on country.
				if ( wcpbc_the_zone() != null && wcpbc_the_zone() ) {
					if ( isset( $product_pricing_type ) && $product_pricing_type == 'mwb_wgm_range_price' ) {
						$amt = isset( $_GET['price'] ) ? sanitize_text_field( wp_unslash( $_GET['price'] ) ) : '';
					} elseif ( isset( $product_pricing_type ) && $product_pricing_type == 'mwb_wgm_user_price' ) {
						$amt = isset( $_GET['price'] ) ? sanitize_text_field( wp_unslash( $_GET['price'] ) ) : '';
					} else {
						$amt = isset( $_GET['price'] ) ? sanitize_text_field( wp_unslash( $_GET['price'] ) ) : '';
						$amt = wcpbc_the_zone()->get_exchange_rate_price( $amt );
					}
					$args['amount'] = wc_price( $amt );
				} else {
					$amt = isset( $_GET['price'] ) ? sanitize_text_field( wp_unslash( $_GET['price'] ) ) : '';
					$args['amount'] = wc_price( $amt );
				}
			} else {
				$amt = isset( $_GET['price'] ) ? sanitize_text_field( wp_unslash( $_GET['price'] ) ) : '';
				$args['amount'] = wc_price( $amt );
			}
			$args['templateid'] = isset( $mwb_temp_id ) && ! empty( $mwb_temp_id ) ? $mwb_temp_id : $temp;
			$args['product_id'] = $product_id;
			$message = $this->mwb_common_fun->mwb_wgm_create_gift_template( $args );
			echo $message; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
			die;
		}
	}
}
