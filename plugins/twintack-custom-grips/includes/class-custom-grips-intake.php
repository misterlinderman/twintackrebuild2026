<?php
/**
 * TTCG_Intake — Native customer grip order intake (replaces Gravity Forms).
 *
 * Collects design details, adds the deposit SKU to cart with grip_design_data
 * metadata (same shape as legacy GF handlers in Grip Manager).
 *
 * @package TwinTack_Custom_Grips
 * @since   1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTCG_Intake {

    /** @var TTCG_Intake|null */
    private static $instance = null;

    const NONCE_ACTION = 'ttcg_intake_nonce';
    const DEPOSIT_SKU  = 'grip-design-deposit';
    const MIN_QUANTITY = 25;
    const MAX_QUANTITY = 1000;

    /**
     * @return TTCG_Intake
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'ttcg_grip_intake', array( $this, 'render_shortcode' ) );
        add_action( 'wp_ajax_ttcg_submit_grip_intake', array( $this, 'handle_submit' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
        add_filter( 'the_content', array( $this, 'replace_legacy_gravityform_shortcodes' ), 9 );
    }

    /**
     * Whether the current front-end request is a grip intake context.
     *
     * @return bool
     */
    public static function is_grip_intake_context() {
        if ( is_admin() ) {
            return false;
        }

        if ( is_page_template( array( 'templates/template-gripform.php', 'templates/template-gripform-clean.php' ) ) ) {
            return true;
        }

        global $post;
        if ( $post instanceof WP_Post ) {
            if ( has_shortcode( $post->post_content, 'ttcg_grip_intake' ) ) {
                return true;
            }
            if ( preg_match( '/\[gravityform[^\]]*id=["\']?(8|9)["\']?/i', $post->post_content ) ) {
                return true;
            }
        }

        return (bool) apply_filters( 'ttcg_is_grip_intake_context', false );
    }

    /**
     * Whether the current request should load intake assets.
     *
     * @return bool
     */
    public function page_has_intake_form() {
        return self::is_grip_intake_context();
    }

    /**
     * Swap legacy GF forms 8/9 shortcodes for native intake (Phase 2 cutover).
     *
     * @param string $content Post content.
     * @return string
     */
    public function replace_legacy_gravityform_shortcodes( $content ) {
        if ( is_admin() || ! is_string( $content ) || strpos( $content, '[gravityform' ) === false ) {
            return $content;
        }

        return preg_replace(
            '/\[gravityform[^\]]*id=["\']?(8|9)["\']?[^\]]*\]/i',
            '[ttcg_grip_intake]',
            $content
        );
    }

    /**
     * Enqueue CSS/JS on grip intake pages.
     */
    public function maybe_enqueue_assets() {
        if ( ! $this->page_has_intake_form() ) {
            return;
        }

        wp_enqueue_style(
            'ttcg-customer-intake',
            TTCG_PLUGIN_URL . 'assets/css/customer-intake.css',
            array(),
            TTCG_VERSION
        );

        wp_enqueue_script(
            'ttcg-customer-intake',
            TTCG_PLUGIN_URL . 'assets/js/customer-intake.js',
            array( 'jquery' ),
            TTCG_VERSION,
            true
        );

        wp_localize_script( 'ttcg-customer-intake', 'ttcgIntake', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( self::NONCE_ACTION ),
            'min_qty'  => self::MIN_QUANTITY,
            'max_qty'  => self::MAX_QUANTITY,
            'strings'  => array(
                'submitting' => __( 'Adding to cart…', 'twintack-custom-grips' ),
                'submit'     => __( 'Continue to Cart & Pay Deposit', 'twintack-custom-grips' ),
                'error'      => __( 'Something went wrong. Please try again.', 'twintack-custom-grips' ),
                'login'      => __( 'Please log in to order custom grips.', 'twintack-custom-grips' ),
            ),
        ) );
    }

    /**
     * Shortcode callback.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_shortcode( $atts = array() ) {
        ob_start();
        self::render_form( $atts );
        return ob_get_clean();
    }

    /**
     * Output the customer intake form markup.
     *
     * @param array $atts Optional attributes (reserved for future use).
     */
    public static function render_form( $atts = array() ) {
        include TTCG_PLUGIN_DIR . 'templates/partials/customer-intake-form.php';
    }

    /**
     * AJAX: validate intake, upload artwork, add deposit to cart.
     */
    public function handle_submit() {
        if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'twintack-custom-grips' ) ), 403 );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in to order custom grips.', 'twintack-custom-grips' ) ), 403 );
        }

        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            wp_send_json_error( array( 'message' => __( 'WooCommerce cart is unavailable.', 'twintack-custom-grips' ) ) );
        }

        $grip_data = $this->build_grip_data_from_request();
        if ( is_wp_error( $grip_data ) ) {
            wp_send_json_error( array( 'message' => $grip_data->get_error_message() ) );
        }

        if ( ! empty( $_FILES['artwork_file'] ) && ! empty( $_FILES['artwork_file']['tmp_name'] ) ) {
            $upload = $this->upload_artwork_file( $_FILES['artwork_file'] );
            if ( is_wp_error( $upload ) ) {
                wp_send_json_error( array( 'message' => $upload->get_error_message() ) );
            }
            $grip_data['artwork_url']      = $upload['url'];
            $grip_data['artwork_filename'] = $upload['filename'];
        }

        $grip_data = apply_filters( 'ttcg_grip_intake_data', $grip_data );

        $product_id = $this->get_deposit_product_id();
        if ( ! $product_id ) {
            wp_send_json_error( array( 'message' => __( 'Custom grip deposit product is not configured.', 'twintack-custom-grips' ) ) );
        }

        $cart_key = WC()->cart->add_to_cart(
            $product_id,
            1,
            0,
            array(),
            array( 'grip_design_data' => $grip_data )
        );

        if ( ! $cart_key ) {
            wp_send_json_error( array( 'message' => __( 'Could not add the deposit to your cart.', 'twintack-custom-grips' ) ) );
        }

        WC()->cart->calculate_totals();
        WC()->cart->set_session();

        do_action( 'ttcg_grip_intake_submitted', $grip_data, $cart_key );

        wp_send_json_success( array(
            'message'  => __( 'Your custom grip design has been added. Complete checkout to pay the design deposit.', 'twintack-custom-grips' ),
            'cart_url' => wc_get_cart_url(),
        ) );
    }

    /**
     * Build cart metadata from POST (matches Grip Manager GF handler shape).
     *
     * @return array|WP_Error
     */
    private function build_grip_data_from_request() {
        $user = wp_get_current_user();

        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
        $team_name  = isset( $_POST['team_name'] ) ? sanitize_text_field( wp_unslash( $_POST['team_name'] ) ) : '';
        $quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;
        $feedback   = isset( $_POST['feedback'] ) ? sanitize_textarea_field( wp_unslash( $_POST['feedback'] ) ) : '';

        $design_layout   = isset( $_POST['design_layout'] ) ? sanitize_text_field( wp_unslash( $_POST['design_layout'] ) ) : '';
        $primary_color   = isset( $_POST['primary_color'] ) ? sanitize_text_field( wp_unslash( $_POST['primary_color'] ) ) : '';
        $secondary_color = isset( $_POST['secondary_color'] ) ? sanitize_text_field( wp_unslash( $_POST['secondary_color'] ) ) : '';
        $tertiary_color  = isset( $_POST['tertiary_color'] ) ? sanitize_text_field( wp_unslash( $_POST['tertiary_color'] ) ) : '';

        $customer_name = trim( $first_name . ' ' . $last_name );
        if ( empty( $customer_name ) ) {
            $customer_name = trim( $user->first_name . ' ' . $user->last_name );
        }
        if ( empty( $customer_name ) ) {
            $customer_name = $user->display_name;
        }

        if ( empty( $team_name ) ) {
            return new WP_Error( 'missing_team', __( 'Team or school name is required.', 'twintack-custom-grips' ) );
        }

        if ( $quantity < self::MIN_QUANTITY || $quantity > self::MAX_QUANTITY ) {
            return new WP_Error(
                'invalid_quantity',
                sprintf(
                    /* translators: 1: minimum quantity, 2: maximum quantity */
                    __( 'Quantity must be between %1$d and %2$d.', 'twintack-custom-grips' ),
                    self::MIN_QUANTITY,
                    self::MAX_QUANTITY
                )
            );
        }

        if ( empty( $design_layout ) ) {
            return new WP_Error( 'missing_layout', __( 'Please select a pattern / layout.', 'twintack-custom-grips' ) );
        }

        if ( empty( $primary_color ) ) {
            return new WP_Error( 'missing_color', __( 'Primary color is required.', 'twintack-custom-grips' ) );
        }

        if ( self::layout_needs_secondary( $design_layout ) && empty( $secondary_color ) ) {
            return new WP_Error( 'missing_secondary', __( 'Secondary color is required for this pattern.', 'twintack-custom-grips' ) );
        }

        if ( self::layout_needs_tertiary( $design_layout ) && empty( $tertiary_color ) ) {
            return new WP_Error( 'missing_tertiary', __( 'Tertiary color is required for this pattern.', 'twintack-custom-grips' ) );
        }

        $design_type = self::build_design_type_string( $design_layout, $primary_color, $secondary_color, $tertiary_color );

        return array(
            'customer_name'   => $customer_name,
            'customer_email'  => $user->user_email,
            'team_name'       => $team_name,
            'design_type'     => $design_type,
            'design_layout'   => $design_layout,
            'primary_color'   => $primary_color,
            'secondary_color' => $secondary_color,
            'tertiary_color'  => $tertiary_color,
            'quantity'        => $quantity,
            'artwork_url'     => '',
            'artwork_filename'=> '',
            'feedback'        => $feedback,
            'form_entry_id'   => '',
            'form_type'       => 'new',
            'timestamp'       => current_time( 'timestamp' ),
        );
    }

    /**
     * @param string $layout Pattern label.
     * @return bool
     */
    public static function layout_needs_secondary( $layout ) {
        return in_array( $layout, array( '2-Color Fade', '3-Color Fade', 'Splatter' ), true );
    }

    /**
     * @param string $layout Pattern label.
     * @return bool
     */
    public static function layout_needs_tertiary( $layout ) {
        return '3-Color Fade' === $layout;
    }

    /**
     * Build display design type string (legacy form 9 parity).
     *
     * @param string $layout   Pattern / layout slug.
     * @param string $primary  Primary color.
     * @param string $secondary Secondary color.
     * @param string $tertiary Tertiary color.
     * @return string
     */
    public static function build_design_type_string( $layout, $primary, $secondary = '', $tertiary = '' ) {
        if ( 'Solid Color' === $layout ) {
            return $layout . ' (' . $primary . ')';
        }

        $colors = $primary;
        if ( ! empty( $secondary ) ) {
            $colors .= ' + ' . $secondary;
        }
        if ( ! empty( $tertiary ) ) {
            $colors .= ' + ' . $tertiary;
        }

        return $layout . ' (' . $colors . ')';
    }

    /**
     * Upload artwork via WordPress upload handler.
     *
     * @param array $file $_FILES entry.
     * @return array|WP_Error Keys: url, filename.
     */
    private function upload_artwork_file( $file ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $allowed = apply_filters( 'ttcg_intake_allowed_mimes', array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
            'pdf'          => 'application/pdf',
            'ai'           => 'application/postscript',
            'eps'          => 'application/postscript',
            'svg'          => 'image/svg+xml',
        ) );

        $check = wp_check_filetype( $file['name'], $allowed );
        if ( empty( $check['type'] ) ) {
            return new WP_Error( 'invalid_file', __( 'Artwork file type is not allowed.', 'twintack-custom-grips' ) );
        }

        $upload = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => $allowed ) );
        if ( isset( $upload['error'] ) ) {
            return new WP_Error( 'upload_error', $upload['error'] );
        }

        return array(
            'url'      => $upload['url'],
            'filename' => basename( $file['name'] ),
        );
    }

    /**
     * Deposit product ID (same SKU as Grip Manager).
     *
     * @return int
     */
    public function get_deposit_product_id() {
        $product_id = wc_get_product_id_by_sku( self::DEPOSIT_SKU );
        if ( $product_id ) {
            return (int) $product_id;
        }

        if ( ! class_exists( 'WC_Product_Simple' ) ) {
            return 0;
        }

        $product = new WC_Product_Simple();
        $product->set_name( 'Custom Grip Design Deposit' );
        $product->set_regular_price( '50.00' );
        $product->set_sku( self::DEPOSIT_SKU );
        $product->set_virtual( true );
        $product->set_sold_individually( true );
        $product->set_status( 'publish' );

        return (int) $product->save();
    }
}
