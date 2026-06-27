<?php
/**
 * TwinTack Manual Order Payment Fix
 *
 * Handles payment issues with manually created orders
 * Ensures orders can accept payment when sent to customers
 *
 * @package TwinTack2025
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TwinTack_Manual_Order_Payment_Fix {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        // Hook into order actions
        add_action('woocommerce_order_status_changed', [$this, 'ensure_order_can_accept_payment'], 10, 3);
        add_action('woocommerce_admin_order_actions_end', [$this, 'add_payment_fix_button']);
        add_action('wp_ajax_twintack_fix_order_payment', [$this, 'ajax_fix_order_payment']);
        
        // Add admin notice for problematic orders
        add_action('admin_notices', [$this, 'show_payment_fix_notices']);
        
        // Filter the pay for order URL to add debugging
        add_filter('woocommerce_get_checkout_payment_url', [$this, 'debug_payment_url'], 10, 2);
        
        // Add debug info to order edit screen
        add_action('woocommerce_admin_order_data_after_order_details', [$this, 'add_payment_debug_info']);
    }
    
    /**
     * Ensure order can accept payment when status changes
     */
    public function ensure_order_can_accept_payment($order_id, $old_status, $new_status) {
        // Only process for orders that should accept payment
        $payment_statuses = ['pending', 'failed'];
        
        if (!in_array($new_status, $payment_statuses)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Check if order needs fixing
        if (!$order->needs_payment() && $order->get_total() > 0) {
            $this->fix_order_payment_settings($order);
        }
    }
    
    /**
     * Fix order payment settings
     */
    public function fix_order_payment_settings($order) {
        $fixes_applied = [];
        
        // Ensure payment method is set
        if (!$order->get_payment_method()) {
            $order->set_payment_method('stripe');
            $order->set_payment_method_title('Stripe');
            $fixes_applied[] = 'payment_method';
        }
        
        // Ensure billing email is set (required for payment)
        if (!$order->get_billing_email() && $order->get_customer_id()) {
            $customer = new WC_Customer($order->get_customer_id());
            if ($customer->get_email()) {
                $order->set_billing_email($customer->get_email());
                $fixes_applied[] = 'billing_email';
            }
        }
        
        // Save if fixes were applied
        if (!empty($fixes_applied)) {
            $order->save();
            $order->add_order_note(
                sprintf(
                    'TwinTack: Payment settings fixed automatically (%s)',
                    implode(', ', $fixes_applied)
                )
            );
            
            error_log("TwinTack: Fixed payment settings for order #{$order->get_id()}: " . implode(', ', $fixes_applied));
        }
        
        return $fixes_applied;
    }
    
    /**
     * Add payment fix button to order actions
     */
    public function add_payment_fix_button($order) {
        if (!$order->needs_payment() && $order->get_total() > 0) {
            echo '<div class="twintack-payment-fix" style="margin-top: 10px;">';
            echo '<button type="button" class="button button-secondary" onclick="twintackFixOrderPayment(' . $order->get_id() . ')">';
            echo '🔧 Fix Payment Settings';
            echo '</button>';
            echo '</div>';
            
            // Add inline JavaScript
            ?>
            <script>
            function twintackFixOrderPayment(orderId) {
                if (!confirm('Fix payment settings for this order?')) return;
                
                jQuery.post(ajaxurl, {
                    action: 'twintack_fix_order_payment',
                    order_id: orderId,
                    _wpnonce: '<?php echo wp_create_nonce('twintack_fix_payment'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✅ Order payment settings fixed!\n\nFixes applied: ' + response.data.fixes.join(', '));
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.data.message);
                    }
                });
            }
            </script>
            <?php
        }
    }
    
    /**
     * AJAX handler for fixing order payment
     */
    public function ajax_fix_order_payment() {
        check_ajax_referer('twintack_fix_payment');
        
        if (!current_user_can('edit_shop_orders')) {
            wp_die('Insufficient permissions');
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(['message' => 'Order not found']);
        }
        
        $fixes = $this->fix_order_payment_settings($order);
        
        // Force refresh order
        clean_post_cache($order_id);
        $order = wc_get_order($order_id);
        
        $can_pay_now = $order->needs_payment();
        
        if ($can_pay_now) {
            wp_send_json_success([
                'message' => 'Order payment settings fixed successfully',
                'fixes' => $fixes,
                'payment_url' => $order->get_checkout_payment_url()
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Order still cannot accept payment. Manual investigation needed.'
            ]);
        }
    }
    
    /**
     * Show admin notices for problematic orders
     */
    public function show_payment_fix_notices() {
        $screen = get_current_screen();
        
        if ($screen->id !== 'shop_order') {
            return;
        }
        
        global $post;
        if (!$post) {
            return;
        }
        
        $order = wc_get_order($post->ID);
        if (!$order) {
            return;
        }
        
        // Check if order should accept payment but can't
        if (!$order->needs_payment() && $order->get_total() > 0 && in_array($order->get_status(), ['pending', 'failed'])) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>⚠️ Payment Issue Detected:</strong> 
                    This order has a total of $<?php echo $order->get_total(); ?> but cannot accept payment. 
                    <button type="button" class="button button-small" onclick="twintackFixOrderPayment(<?php echo $order->get_id(); ?>)">
                        Fix Now
                    </button>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Add payment debug info to order edit screen
     */
    public function add_payment_debug_info($order) {
        ?>
        <div class="twintack-payment-debug" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">
            <h4>🔍 Payment Debug Info</h4>
            <p>
                <strong>needs_payment():</strong> <?php echo $order->needs_payment() ? '✅ TRUE' : '❌ FALSE'; ?><br>
                <strong>Status:</strong> <?php echo $order->get_status(); ?><br>
                <strong>Total:</strong> $<?php echo $order->get_total(); ?><br>
                <strong>Payment Method:</strong> <?php echo $order->get_payment_method() ?: 'NOT SET'; ?><br>
                <strong>Billing Email:</strong> <?php echo $order->get_billing_email() ?: 'NOT SET'; ?>
            </p>
            
            <?php if ($order->needs_payment()): ?>
                <p>
                    <strong>Payment URL:</strong><br>
                    <code><?php echo $order->get_checkout_payment_url(); ?></code>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Debug payment URL issues
     */
    public function debug_payment_url($url, $order) {
        // Log payment URL generation for debugging
        if (!$order->needs_payment()) {
            error_log("TwinTack: Payment URL requested for order #{$order->get_id()} that doesn't need payment");
        }
        
        return $url;
    }
    
    /**
     * Static method to fix a specific order
     */
    public static function fix_order($order_id) {
        $instance = new self();
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return false;
        }
        
        return $instance->fix_order_payment_settings($order);
    }
}

// Initialize the class
new TwinTack_Manual_Order_Payment_Fix();

/**
 * Helper function for quick fixes
 */
if (!function_exists('twintack_fix_order_payment')) {
    function twintack_fix_order_payment($order_id) {
        return TwinTack_Manual_Order_Payment_Fix::fix_order($order_id);
    }
}
?> 