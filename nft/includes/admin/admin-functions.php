<?php
class My_Admin_Functions {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
        add_action('admin_menu', array($this, 'add_manage_nft_menu'));
        add_action('wp_ajax_issue_certificate_ajax', array($this,'issue_certificate_ajax'));
    }

    public function enqueue_custom_scripts() {
        wp_enqueue_script('manage-nft-script', plugin_dir_url(__FILE__) . '../../assets/js/generateNFT.js', array('jquery'), '1.0', true);    
        wp_localize_script('manage-nft-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_style('admin-style', plugin_dir_url(__FILE__) . '../../assets/css/admin-style.css');
        wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css');       
        wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array(), null, true); 
    }
    
    public function add_manage_nft_menu() {
        add_menu_page(
            'Manage NFT',           // Page title
            'Manage NFT',           // Menu title
            'manage_options',       // Capability
            'manage-nft',           // Menu slug
            array($this, 'manage_nft_page'),    // Callback function specified as a method of the class
            'dashicons-analytics',  // Icon
            20                      // Position
        );
    }

    public function manage_nft_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'course_completion_data';
        $results = $wpdb->get_results("SELECT * FROM $table_name");       
        echo '<div class="wrap">';
        echo '<h2>Manage NFT</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Post Title</th><th>Display Name</th><th>User Email</th><th>Course Enrollment</th><th>Completion Date</th><th>Generate NFT</th></tr></thead>';
        echo '<tbody>';    
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . $row->id . '</td>';
            echo '<td>' . $row->post_title . '</td>';
            echo '<td>' . $row->display_name . '</td>';
            echo '<td>' . $row->user_email . '</td>';
            echo '<td>' . $row->course_enrollment . '</td>';
            echo '<td>' . $row->course_completion_date . '</td>';
            echo '<td><button class="verify-button" data-id="' . $row->id . '">Generate NFT</button></td>';
            echo '</tr>';
        }    
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    
    public function issue_certificate_ajax() {
    // Verify nonce if needed
    // Check user permissions if needed
    // Get the data passed from JavaScript
    $data = $_POST['data'];
    $student_mail = isset($_POST['studentMail']) ? $_POST['studentMail'] : '';
    error_log('Data received in issue_certificate_ajax: ' . print_r($data, true));
    $this->issue_certificate($data, $student_mail);
    wp_die();
    }

    public function issue_certificate($data, $student_mail) {
        $response = wp_remote_post( 'http://localhost:3000/certificate/issueCertificate', array(
            'body'    => wp_json_encode( $data ), 
            'headers' => array( 
                'Content-Type' => 'application/json', 
            ),
        ) );
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            error_log( "HTTP Error: $error_message" );
        } 
        else {
            $response_code = wp_remote_retrieve_response_code( $response ); 
            $response_body = wp_remote_retrieve_body( $response ); 
            $decoded_response = json_decode( $response_body, true );
            if ( isset($decoded_response['tokenId']) ) {
                $tokenId = $decoded_response['tokenId'];
                error_log( "Token ID: $tokenId" );            

                $subject = 'Your NFT Code';
                $message = '<p>Dear Student,</p>';
                $message .= '<p>We pleased to provide you with the unique NFT code generated for your certificate</p>';
                $message .= '<p>Your NFT token code: <strong>' . $tokenId . '</strong></p>';
                $message .= '<p>Thank you for your participation!</p>';
                $headers = array('Content-Type: text/html; charset=UTF-8');

                $sent = wp_mail($student_mail, $subject, $message, $headers); 

                if ($sent) {
                    echo 'Email sent successfully';
                    error_log('Verification email sent to ' . $student_mail);
                } else {
                    echo 'Failed to send email';
                    error_log('Failed to send verification email to ' . $student_mail);
                }
            }  else {
                error_log( "Token ID not found in response" );            
            }
            error_log( "HTTP Response Code: $response_code" );
            error_log( "Response from server: $response_body" );
        }
    }
    
}

new My_Admin_Functions();