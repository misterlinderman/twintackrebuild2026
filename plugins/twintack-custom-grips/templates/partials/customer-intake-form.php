<?php
/**
 * Customer native grip intake wizard (replaces Gravity Forms form 9).
 *
 * @package TwinTack_Custom_Grips
 * @since   1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$redirect_to  = home_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/' );
$login_url    = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), site_url( '/login/' ) );

$steps    = TTCG_Intake_Config::get_steps();
$layouts  = TTCG_Intake_Config::get_design_layouts();
$colors   = TTCG_Intake_Config::get_colors();
$quantities = TTCG_Intake_Config::get_quantity_options();
$default_qty = TTCG_Intake::MIN_QUANTITY;
$max_upload_label = size_format( wp_max_upload_size() );

$billing_phone = get_user_meta( $current_user->ID, 'billing_phone', true );
if ( empty( $billing_phone ) && class_exists( 'WC_Customer' ) ) {
    $wc_customer = new WC_Customer( $current_user->ID );
    $billing_phone = $wc_customer->get_billing_phone();
}
?>

<div class="ttcg-customer-intake ttcg-customer-intake--wizard" id="ttcg-customer-intake">

    <?php if ( ! is_user_logged_in() ) : ?>
        <div class="ttcg-customer-intake__login-notice">
            <h2><?php esc_html_e( 'Account Required', 'twintack-custom-grips' ); ?></h2>
            <p><?php esc_html_e( 'Log in or create a free account to design and order custom grips.', 'twintack-custom-grips' ); ?></p>
            <p>
                <a class="ttcg-btn ttcg-btn--primary" href="<?php echo esc_url( $login_url ); ?>">
                    <?php esc_html_e( 'Log In / Register', 'twintack-custom-grips' ); ?>
                </a>
            </p>
        </div>
    <?php else : ?>

        <div class="ttcg-intake-wizard__header">
            <h2 class="ttcg-intake-wizard__title"><?php esc_html_e( 'GRIP CONFIGURATOR', 'twintack-custom-grips' ); ?></h2>
            <p class="ttcg-intake-wizard__step-label" id="ttcg-intake-step-label">
                <?php
                printf(
                    /* translators: 1: current step number, 2: total steps, 3: step title */
                    esc_html__( 'Step %1$d of %2$d - %3$s', 'twintack-custom-grips' ),
                    1,
                    count( $steps ),
                    esc_html( $steps[0] )
                );
                ?>
            </p>
            <div class="ttcg-intake-wizard__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="25">
                <div class="ttcg-intake-wizard__progress-bar" id="ttcg-intake-progress-bar" style="width: 25%;"></div>
            </div>
        </div>

        <form id="ttcg-customer-intake-form" class="ttcg-intake-wizard__form" enctype="multipart/form-data" novalidate>

            <!-- Step 1: Pattern & Colors -->
            <div class="ttcg-intake-step" data-step="1">
                <h3 class="ttcg-intake-step__heading"><?php esc_html_e( 'Select Your Pattern and Colors', 'twintack-custom-grips' ); ?></h3>

                <fieldset class="ttcg-image-choice" data-field="design_layout">
                    <legend><?php esc_html_e( 'Which design layout would you like to use?', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></legend>
                    <div class="ttcg-image-choice__grid ttcg-image-choice__grid--layouts">
                        <?php foreach ( $layouts as $layout ) : ?>
                            <label class="ttcg-image-choice__option">
                                <input type="radio" name="design_layout" value="<?php echo esc_attr( $layout['value'] ); ?>" required>
                                <span class="ttcg-image-choice__card">
                                    <span class="ttcg-image-choice__image ttcg-image-choice__image--layout">
                                        <img src="<?php echo esc_url( $layout['image'] ); ?>" alt="<?php echo esc_attr( $layout['label'] ); ?>" loading="lazy">
                                    </span>
                                    <span class="ttcg-image-choice__label"><?php echo esc_html( $layout['label'] ); ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="ttcg-image-choice" data-color-group="primary">
                    <legend><?php esc_html_e( 'Product Color', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></legend>
                    <div class="ttcg-image-choice__grid ttcg-image-choice__grid--colors">
                        <?php foreach ( $colors as $color ) : ?>
                            <label class="ttcg-image-choice__option">
                                <input type="radio" name="primary_color" value="<?php echo esc_attr( $color['value'] ); ?>" required>
                                <span class="ttcg-image-choice__card">
                                    <span class="ttcg-image-choice__image ttcg-image-choice__image--color">
                                        <img src="<?php echo esc_url( $color['image'] ); ?>" alt="<?php echo esc_attr( $color['label'] ); ?>" loading="lazy">
                                    </span>
                                    <span class="ttcg-image-choice__label"><?php echo esc_html( $color['label'] ); ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="ttcg-image-choice ttcg-intake-step__conditional" data-color-group="secondary" hidden>
                    <legend><?php esc_html_e( 'Second Color', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></legend>
                    <div class="ttcg-image-choice__grid ttcg-image-choice__grid--colors">
                        <?php foreach ( $colors as $color ) : ?>
                            <label class="ttcg-image-choice__option">
                                <input type="radio" name="secondary_color" value="<?php echo esc_attr( $color['value'] ); ?>">
                                <span class="ttcg-image-choice__card">
                                    <span class="ttcg-image-choice__image ttcg-image-choice__image--color">
                                        <img src="<?php echo esc_url( $color['image'] ); ?>" alt="<?php echo esc_attr( $color['label'] ); ?>" loading="lazy">
                                    </span>
                                    <span class="ttcg-image-choice__label"><?php echo esc_html( $color['label'] ); ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="ttcg-image-choice ttcg-intake-step__conditional" data-color-group="tertiary" hidden>
                    <legend><?php esc_html_e( 'Third Color', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></legend>
                    <div class="ttcg-image-choice__grid ttcg-image-choice__grid--colors">
                        <?php foreach ( $colors as $color ) : ?>
                            <label class="ttcg-image-choice__option">
                                <input type="radio" name="tertiary_color" value="<?php echo esc_attr( $color['value'] ); ?>">
                                <span class="ttcg-image-choice__card">
                                    <span class="ttcg-image-choice__image ttcg-image-choice__image--color">
                                        <img src="<?php echo esc_url( $color['image'] ); ?>" alt="<?php echo esc_attr( $color['label'] ); ?>" loading="lazy">
                                    </span>
                                    <span class="ttcg-image-choice__label"><?php echo esc_html( $color['label'] ); ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>

            <!-- Step 2: Team, Logo, Instructions -->
            <div class="ttcg-intake-step" data-step="2" hidden>
                <h3 class="ttcg-intake-step__heading"><?php esc_html_e( 'Add Team Name, Logo, and Instructions', 'twintack-custom-grips' ); ?></h3>

                <div class="ttcg-form-row ttcg-form-row--full">
                    <label for="ttcg-intake-team_name">
                        <?php esc_html_e( 'Please provide your Team or School name (this will be used as the Design Name in our system):', 'twintack-custom-grips' ); ?>
                        <span class="ttcg-required">(Required)</span>
                    </label>
                    <input type="text" id="ttcg-intake-team_name" name="team_name" class="ttcg-input ttcg-input--large" required>
                </div>

                <div class="ttcg-form-row ttcg-form-row--full">
                    <label for="ttcg-intake-artwork_file">
                        <?php esc_html_e( 'Please upload a high resolution logo (JPEG, PNG, PDF, AI or EPS)', 'twintack-custom-grips' ); ?>
                    </label>
                    <input type="file" id="ttcg-intake-artwork_file" name="artwork_file" class="ttcg-input"
                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.ai,.eps,.svg">
                    <p class="ttcg-customer-intake__hint">
                        <?php
                        printf(
                            /* translators: %s: maximum upload size, e.g. 256 MB */
                            esc_html__( 'Max. file size: %s.', 'twintack-custom-grips' ),
                            esc_html( $max_upload_label )
                        );
                        ?>
                    </p>
                </div>

                <div class="ttcg-form-row ttcg-form-row--full">
                    <label for="ttcg-intake-feedback">
                        <?php esc_html_e( 'Please provide your Team Colors or any other design direction:', 'twintack-custom-grips' ); ?>
                    </label>
                    <textarea id="ttcg-intake-feedback" name="feedback" rows="6" class="ttcg-input ttcg-textarea ttcg-input--large"
                              placeholder="<?php esc_attr_e( 'Placement notes, font preferences, special requests…', 'twintack-custom-grips' ); ?>"></textarea>
                </div>
            </div>

            <!-- Step 3: Quantity & Pricing -->
            <div class="ttcg-intake-step" data-step="3" hidden>
                <h3 class="ttcg-intake-step__heading"><?php esc_html_e( 'Quantity & Specs', 'twintack-custom-grips' ); ?></h3>

                <?php echo TTCG_Intake_Config::get_design_fee_notice_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                <div class="ttcg-form-row ttcg-form-row--full">
                    <label for="ttcg-intake-quantity"><?php esc_html_e( 'Quantity', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></label>
                    <select id="ttcg-intake-quantity" name="quantity" class="ttcg-input ttcg-input--large" required>
                        <?php foreach ( $quantities as $qty ) : ?>
                            <option value="<?php echo esc_attr( $qty ); ?>" <?php selected( $qty, $default_qty ); ?>>
                                <?php echo esc_html( (string) $qty ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ttcg-intake-pricing" id="ttcg-intake-pricing">
                    <span class="ttcg-intake-pricing__label"><?php esc_html_e( 'Custom Grips', 'twintack-custom-grips' ); ?></span>
                    <span class="ttcg-intake-pricing__row">
                        <span class="ttcg-intake-pricing__prefix"><?php esc_html_e( 'Price:', 'twintack-custom-grips' ); ?></span>
                        <span class="ttcg-intake-pricing__price" id="ttcg-intake-unit-price">
                            <?php echo wp_kses_post( TTCG_Intake_Config::get_unit_price( $default_qty ) ); ?>
                        </span>
                    </span>
                </div>
            </div>

            <!-- Step 4: Customer Info -->
            <div class="ttcg-intake-step" data-step="4" hidden>
                <h3 class="ttcg-intake-step__heading"><?php esc_html_e( 'Customer Information', 'twintack-custom-grips' ); ?></h3>

                <div class="ttcg-customer-intake__grid">
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-first_name"><?php esc_html_e( 'First Name', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></label>
                        <input type="text" id="ttcg-intake-first_name" name="first_name" class="ttcg-input ttcg-input--large" required
                               value="<?php echo esc_attr( $current_user->first_name ); ?>">
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-last_name"><?php esc_html_e( 'Last Name', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></label>
                        <input type="text" id="ttcg-intake-last_name" name="last_name" class="ttcg-input ttcg-input--large" required
                               value="<?php echo esc_attr( $current_user->last_name ); ?>">
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-email"><?php esc_html_e( 'Email', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></label>
                        <input type="email" id="ttcg-intake-email" class="ttcg-input ttcg-input--large" readonly
                               value="<?php echo esc_attr( $current_user->user_email ); ?>">
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-phone"><?php esc_html_e( 'Phone', 'twintack-custom-grips' ); ?> <span class="ttcg-required">(Required)</span></label>
                        <input type="tel" id="ttcg-intake-phone" name="phone" class="ttcg-input ttcg-input--large" required
                               value="<?php echo esc_attr( $billing_phone ); ?>">
                    </div>
                </div>

                <p class="ttcg-customer-intake__hint ttcg-intake-checkout-note">
                    <?php esc_html_e( 'Billing and shipping details will be collected at checkout.', 'twintack-custom-grips' ); ?>
                </p>
            </div>

            <div class="ttcg-intake-wizard__footer">
                <button type="button" class="ttcg-btn ttcg-btn--nav" id="ttcg-intake-prev" hidden>
                    <?php esc_html_e( 'Previous', 'twintack-custom-grips' ); ?>
                </button>
                <button type="button" class="ttcg-btn ttcg-btn--nav ttcg-btn--primary" id="ttcg-intake-next">
                    <?php esc_html_e( 'Next', 'twintack-custom-grips' ); ?>
                </button>
                <button type="submit" class="ttcg-btn ttcg-btn--nav ttcg-btn--primary" id="ttcg-intake-submit" hidden>
                    <?php esc_html_e( 'Add to Cart', 'twintack-custom-grips' ); ?>
                </button>
            </div>

            <div class="ttcg-customer-intake__feedback" id="ttcg-intake-feedback" role="alert" aria-live="polite"></div>
        </form>

    <?php endif; ?>
</div>
