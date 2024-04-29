<?php
class Public_Functions {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
        add_action('wp_ajax_fetch_nft_data', array($this, 'fetch_nft_data_callback'));
        add_action('wp_ajax_nopriv_fetch_nft_data', array($this, 'fetch_nft_data_callback'));
        add_shortcode('nft_verification', array($this, 'nft_verification_shortcode'));
        add_action('learndash_course_completed', array($this, 'custom_function_on_course_completion')); 
    }

    public function enqueue_custom_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . '../../assets/js/verifyHandler.js', array('jquery'), '1.0', true);
        wp_localize_script('custom-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('nft_verification_nonce')));        
        wp_enqueue_style('my-style', plugin_dir_url(__FILE__) . '../../assets/css/style.css');
        wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css');       
        wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array(), null, true); 
    }

    
    public function custom_function_on_course_completion($data) {
        error_log('custom_function_on_course_completion called');
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_completion_data';
        $post_title = $data['course']->post_title;
        $display_name = $data['user']->data->display_name;
        $user_email = $data['user']->data->user_email;
        $course_enrollment_timestamp = strtotime($data['user']->data->user_registered);
        $course_enrollment = date('Y-m-d', $course_enrollment_timestamp);
        $course_completion_date = date('Y-m-d'); 
        error_log("Course completion data - Post Title: $post_title, Display Name: $display_name, User Email: $user_email, Course Enrollment: $course_enrollment, Completion Date: $course_completion_date");
        $wpdb->insert(
            $table_name,
            array(
                'post_title' => $post_title,
                'display_name' => $display_name,
                'user_email' => $user_email,
                'course_enrollment' => $course_enrollment,
                'course_completion_date' => $course_completion_date,
            )
        );
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