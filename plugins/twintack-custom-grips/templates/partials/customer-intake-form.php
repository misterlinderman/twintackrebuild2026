<?php
/**
 * Customer native grip intake form (replaces Gravity Forms on public order pages).
 *
 * @package TwinTack_Custom_Grips
 * @since   1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$redirect_to  = home_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/' );
$login_url    = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), site_url( '/login/' ) );
?>

<div class="ttcg-customer-intake" id="ttcg-customer-intake">

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

        <form id="ttcg-customer-intake-form" class="ttcg-customer-intake__form" enctype="multipart/form-data" novalidate>

            <div class="ttcg-customer-intake__section">
                <h3 class="ttcg-customer-intake__section-title"><?php esc_html_e( 'Your Information', 'twintack-custom-grips' ); ?></h3>
                <div class="ttcg-customer-intake__grid">
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-first_name"><?php esc_html_e( 'First Name', 'twintack-custom-grips' ); ?> <span class="ttcg-required">*</span></label>
                        <input type="text" id="ttcg-intake-first_name" name="first_name" class="ttcg-input" required
                               value="<?php echo esc_attr( $current_user->first_name ); ?>">
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-last_name"><?php esc_html_e( 'Last Name', 'twintack-custom-grips' ); ?> <span class="ttcg-required">*</span></label>
                        <input type="text" id="ttcg-intake-last_name" name="last_name" class="ttcg-input" required
                               value="<?php echo esc_attr( $current_user->last_name ); ?>">
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-email"><?php esc_html_e( 'Email', 'twintack-custom-grips' ); ?></label>
                        <input type="email" id="ttcg-intake-email" class="ttcg-input" readonly
                               value="<?php echo esc_attr( $current_user->user_email ); ?>">
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-team_name"><?php esc_html_e( 'Team / School Name', 'twintack-custom-grips' ); ?> <span class="ttcg-required">*</span></label>
                        <input type="text" id="ttcg-intake-team_name" name="team_name" class="ttcg-input" required>
                    </div>
                    <div class="ttcg-form-row">
                        <label for="ttcg-intake-quantity"><?php esc_html_e( 'Estimated Quantity', 'twintack-custom-grips' ); ?> <span class="ttcg-required">*</span></label>
                        <input type="number" id="ttcg-intake-quantity" name="quantity" class="ttcg-input"
                               min="<?php echo esc_attr( TTCG_Intake::MIN_QUANTITY ); ?>"
                               max="<?php echo esc_attr( TTCG_Intake::MAX_QUANTITY ); ?>"
                               value="<?php echo esc_attr( TTCG_Intake::MIN_QUANTITY ); ?>" required>
                    </div>
                </div>
            </div>

            <div class="ttcg-customer-intake__section">
                <h3 class="ttcg-customer-intake__section-title"><?php esc_html_e( 'Design Options', 'twintack-custom-grips' ); ?></h3>
                <div class="ttcg-customer-intake__grid">
                    <div class="ttcg-form-row ttcg-form-row--full">
                        <label for="ttcg-intake-design_layout"><?php esc_html_e( 'Pattern / Layout', 'twintack-custom-grips' ); ?> <span class="ttcg-required">*</span></label>
                        <select id="ttcg-intake-design_layout" name="design_layout" class="ttcg-input" required>
                            <option value=""><?php esc_html_e( '— Select —', 'twintack-custom-grips' ); ?></option>
                            <option value="Solid Color"><?php esc_html_e( 'Solid Color', 'twintack-custom-grips' ); ?></option>
                            <option value="2-Color Fade"><?php esc_html_e( '2-Color Fade', 'twintack-custom-grips' ); ?></option>
                            <option value="3-Color Fade"><?php esc_html_e( '3-Color Fade', 'twintack-custom-grips' ); ?></option>
                            <option value="Splatter"><?php esc_html_e( 'Splatter', 'twintack-custom-grips' ); ?></option>
                        </select>
                    </div>
                    <div class="ttcg-form-row" data-color-field="primary">
                        <label for="ttcg-intake-primary_color"><?php esc_html_e( 'Primary Color', 'twintack-custom-grips' ); ?> <span class="ttcg-required">*</span></label>
                        <input type="text" id="ttcg-intake-primary_color" name="primary_color" class="ttcg-input" required
                               placeholder="<?php esc_attr_e( 'e.g., Navy, Red, Gold', 'twintack-custom-grips' ); ?>">
                    </div>
                    <div class="ttcg-form-row" data-color-field="secondary">
                        <label for="ttcg-intake-secondary_color"><?php esc_html_e( 'Secondary Color', 'twintack-custom-grips' ); ?></label>
                        <input type="text" id="ttcg-intake-secondary_color" name="secondary_color" class="ttcg-input">
                    </div>
                    <div class="ttcg-form-row" data-color-field="tertiary">
                        <label for="ttcg-intake-tertiary_color"><?php esc_html_e( 'Tertiary Color', 'twintack-custom-grips' ); ?></label>
                        <input type="text" id="ttcg-intake-tertiary_color" name="tertiary_color" class="ttcg-input">
                    </div>
                </div>
            </div>

            <div class="ttcg-customer-intake__section">
                <h3 class="ttcg-customer-intake__section-title"><?php esc_html_e( 'Logo & Instructions', 'twintack-custom-grips' ); ?></h3>
                <div class="ttcg-form-row ttcg-form-row--full">
                    <label for="ttcg-intake-artwork_file"><?php esc_html_e( 'Logo / Artwork Upload', 'twintack-custom-grips' ); ?></label>
                    <p class="ttcg-customer-intake__hint"><?php esc_html_e( 'Upload your team logo or reference artwork (JPG, PNG, PDF, AI, EPS, SVG).', 'twintack-custom-grips' ); ?></p>
                    <input type="file" id="ttcg-intake-artwork_file" name="artwork_file" class="ttcg-input"
                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.ai,.eps,.svg">
                </div>
                <div class="ttcg-form-row ttcg-form-row--full">
                    <label for="ttcg-intake-feedback"><?php esc_html_e( 'Design Instructions', 'twintack-custom-grips' ); ?></label>
                    <textarea id="ttcg-intake-feedback" name="feedback" rows="5" class="ttcg-input ttcg-textarea"
                              placeholder="<?php esc_attr_e( 'Placement notes, font preferences, special requests…', 'twintack-custom-grips' ); ?>"></textarea>
                </div>
            </div>

            <div class="ttcg-customer-intake__actions">
                <button type="submit" class="ttcg-btn ttcg-btn--primary ttcg-btn--large" id="ttcg-intake-submit">
                    <?php esc_html_e( 'Continue to Cart & Pay Deposit', 'twintack-custom-grips' ); ?>
                </button>
                <p class="ttcg-customer-intake__deposit-note">
                    <?php esc_html_e( 'A one-time design deposit is required before our team begins your mockup.', 'twintack-custom-grips' ); ?>
                </p>
            </div>

            <div class="ttcg-customer-intake__feedback" id="ttcg-intake-feedback" role="alert" aria-live="polite"></div>
        </form>

    <?php endif; ?>
</div>
