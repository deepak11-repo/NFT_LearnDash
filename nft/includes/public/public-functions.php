<?php
class Public_Functions {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
        add_action('wp_ajax_fetch_nft_data', array($this, 'fetch_nft_data_callback'));
        add_action('wp_ajax_nopriv_fetch_nft_data', array($this, 'fetch_nft_data_callback'));
        add_shortcode('nft_verification', array($this, 'nft_verification_shortcode'));
    }

    public function enqueue_custom_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . '../../assets/js/verifyHandler.js', array('jquery'), '1.0', true);
        // wp_localize_script('custom-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'))); 
        wp_localize_script('custom-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('nft_verification_nonce')));        
        wp_enqueue_style('my-style', plugin_dir_url(__FILE__) . '../../assets/css/style.css');
        wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css');       
        wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array(), null, true); 
    }

    public function nft_verification_shortcode() {
        ob_start(); ?>    
        <form id="nftVerificationForm">
            <label for="nftCode">Enter your NFT Code:</label>
            <input type="text" id="nftCode" name="nftCode">
            <button type="submit">Verify</button>
        </form>    
        <div id="verificationResponse"></div>    
        <?php
        return ob_get_clean();
    }

    public function fetch_nft_data_callback() {
        $nonce = $_POST['nonce'];
        error_log( 'Nonce received: ' . $nonce ); // Log the nonce value
        if ( ! wp_verify_nonce( $nonce, 'nft_verification_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }    
        if ( isset( $_POST['nftCode'] ) ) {
            $nftCode = $_POST['nftCode'];
            $response = $this->fetch_data_from_node_app( $nftCode );
            if ( $response !== false ) {
                wp_send_json_success( $response );
            } else {
                wp_send_json_error( 'Error occurred while fetching data.' );
            }
        }
    
        wp_die();
    }
    
    
    public function fetch_data_from_node_app($nftCode) {
        $node_app_url = 'http://localhost:3000/certificate/fetchCertificate/' . urlencode($nftCode);
        $response = wp_remote_get($node_app_url);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("HTTP Error: $error_message");
            return false;
        } else {
            $body = wp_remote_retrieve_body($response);
            error_log("Response from Node.js application: $body");
            return $body;
        }
    }

}

new Public_Functions();