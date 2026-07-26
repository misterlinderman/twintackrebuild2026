<?php
/**
 * Configuration for the native customer grip intake wizard.
 *
 * Mirrors Gravity Forms form 9 field choices and step structure.
 *
 * @package TwinTack_Custom_Grips
 * @since   1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTCG_Intake_Config {

    /**
     * Wizard step labels (matches GF pagination pages).
     *
     * @return string[]
     */
    public static function get_steps() {
        return array(
            __( "Let's Get Started!", 'twintack-custom-grips' ),
            __( 'Design Your Grip', 'twintack-custom-grips' ),
            __( 'Quantity and Details', 'twintack-custom-grips' ),
            __( 'Final Step', 'twintack-custom-grips' ),
        );
    }

    /**
     * Design layout image choices.
     *
     * @return array<int, array{value: string, label: string, image: string}>
     */
    public static function get_design_layouts() {
        return array(
            array(
                'value' => 'Solid Color',
                'label' => __( 'Solid Color', 'twintack-custom-grips' ),
                'image' => self::asset_url( 'ptrn-solid.svg' ),
            ),
            array(
                'value' => '2-Color Fade',
                'label' => __( '2-Color Fade', 'twintack-custom-grips' ),
                'image' => self::asset_url( 'ptrn-2-color-fade.svg' ),
            ),
            array(
                'value' => '3-Color Fade',
                'label' => __( '3-Color Fade', 'twintack-custom-grips' ),
                'image' => self::asset_url( 'ptrn-3-color-fade.svg' ),
            ),
            array(
                'value' => 'Splatter',
                'label' => __( 'Splatter', 'twintack-custom-grips' ),
                'image' => self::asset_url( 'ptrn-splatter.svg' ),
            ),
        );
    }

    /**
     * Product color swatches (GF image_choice fields 41–43).
     *
     * @return array<int, array{value: string, label: string, image: string}>
     */
    public static function get_colors() {
        $colors = array(
            array( 'White', 'clr-white.svg', '2025/04' ),
            array( 'Grey', 'clr-grey.svg', '2025/04' ),
            array( 'Black', 'clr-black.svg', '2025/04' ),
            array( 'Red', 'clr-red.svg', '2025/04' ),
            array( 'Yellow', 'clr-yellow.svg', '2025/04' ),
            array( 'Royal', 'clr-royal.svg', '2025/04' ),
            array( 'Navy', 'clr-navy.svg', '2025/11' ),
            array( 'Orange', 'clr-orange.svg', '2025/04' ),
            array( 'Green', 'clr-green.svg', '2025/04' ),
            array( 'Purple', 'clr-purple.svg', '2025/04' ),
            array( 'Neon Green', 'clr-neon-green.svg', '2025/04' ),
            array( 'Neon Pink', 'clr-neon-pink.svg', '2025/04' ),
            array( 'Teal', 'clr-teal.svg', '2025/04' ),
            array( 'Violet', 'clr-violet.svg', '2025/04' ),
            array( 'Safety Yellow', 'clr-safety-yellow.svg', '2025/04' ),
        );

        $out = array();
        foreach ( $colors as $color ) {
            $out[] = array(
                'value' => $color[0],
                'label' => $color[0],
                'image' => self::asset_url( $color[1], $color[2] ),
            );
        }

        return $out;
    }

    /**
     * Quantity dropdown options (GF field 21).
     *
     * @return int[]
     */
    public static function get_quantity_options() {
        return array( 25, 50, 75, 100, 125, 150, 200, 250, 300, 500, 1000 );
    }

    /**
     * Unit price by quantity tier (GF product fields 20 / 22).
     *
     * @param int $quantity Order quantity.
     * @return string Formatted price.
     */
    public static function get_unit_price( $quantity ) {
        $amount = self::get_unit_price_amount( $quantity );
        if ( function_exists( 'wc_price' ) ) {
            return wc_price( $amount );
        }
        return '$' . number_format( $amount, 2 );
    }

    /**
     * Raw unit price float for JS.
     *
     * @param int $quantity Order quantity.
     * @return float
     */
    public static function get_unit_price_amount( $quantity ) {
        return ( (int) $quantity >= 75 ) ? 17.99 : 19.99;
    }

    /**
     * Upload asset URL helper.
     *
     * @param string $filename File name.
     * @param string $subdir   Upload subdirectory under wp-content/uploads/.
     * @return string
     */
    public static function asset_url( $filename, $subdir = '2025/04' ) {
        return content_url( '/uploads/' . trailingslashit( $subdir ) . $filename );
    }

    /**
     * Design fee notice HTML (GF field 40).
     *
     * @return string
     */
    public static function get_design_fee_notice_html() {
        ob_start();
        ?>
        <div class="ttcg-design-fee-notice">
            <h3><?php esc_html_e( 'Custom Design Fee: $50', 'twintack-custom-grips' ); ?></h3>
            <p><?php esc_html_e( 'Your custom grip design requires a one-time $50 design fee that will be added to your cart. This fee covers:', 'twintack-custom-grips' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Professional design setup of your custom grip pattern', 'twintack-custom-grips' ); ?></li>
                <li><?php esc_html_e( 'Digital proof creation for your approval', 'twintack-custom-grips' ); ?></li>
                <li><?php esc_html_e( 'Production template preparation', 'twintack-custom-grips' ); ?></li>
            </ul>
            <p><strong><?php esc_html_e( 'Important:', 'twintack-custom-grips' ); ?></strong>
                <?php esc_html_e( 'This fee is applied once per unique design. The fee is non-refundable once production begins. After approval, there are no additional design charges for reordering the same pattern.', 'twintack-custom-grips' ); ?>
            </p>
            <p class="ttcg-design-fee-notice__small"><?php esc_html_e( 'The $50 fee will be charged when you add this custom grip to your cart and complete checkout. Your custom grip production will begin after design approval.', 'twintack-custom-grips' ); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}
