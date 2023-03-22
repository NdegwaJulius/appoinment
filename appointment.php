<?php
/*
Plugin Name: Appointment Booking Plugin
Plugin URI: https://example.com/appointment-booking-plugin/
Description: A plugin that allows users to book appointments on the front-end, and admin to view the list of appointments that have been placed, sends a customized email to the user containing details of the appointment placed and details of the location for the appointment, and integrates with Zapier.
Version: 1.0
Author: Julius Ndegwa
Author URI: https://example.com/
License: GPL2
*/

// Register the appointment post type
function my_appointment_plugin_register_post_type() {
  $labels = array(
    'name' => __('Appointments'),
    'singular_name' => __('Appointment'),
    'add_new' => __('Add New'),
    'add_new_item' => __('Add New Appointment'),
    'edit_item' => __('Edit Appointment'),
    'new_item' => __('New Appointment'),
    'view_item' => __('View Appointment'),
    'search_items' => __('Search Appointments'),
    'not_found' => __('No appointments found'),
    'not_found_in_trash' => __('No appointments found in Trash'),
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'menu_icon' => 'dashicons-calendar-alt',
    'supports' => array('title'),
  );

  register_post_type('appointment', $args);
}
add_action('init', 'my_appointment_plugin_register_post_type');

// Add the appointment meta box
function my_appointment_plugin_add_meta_box() {
  add_meta_box('my_appointment_plugin_meta_box', __('Appointment Details'), 'my_appointment_plugin_meta_box_callback', 'appointment');
}
add_action('add_meta_boxes', 'my_appointment_plugin_add_meta_box');

// Appointment meta box callback
function my_appointment_plugin_meta_box_callback($post) {
  wp_nonce_field('my_appointment_plugin_save_meta_box', 'my_appointment_plugin_nonce');

  $appointment_date = get_post_meta($post->ID, '_appointment_date', true);
  $appointment_time = get_post_meta($post->ID, '_appointment_time', true);
  $appointment_location = get_post_meta($post->ID, '_appointment_location', true);
  $appointment_user_email = get_post_meta($post->ID, '_appointment_user_email', true);

  echo '<label for="appointment_date">'.__('Date').'</label><br>';
  echo '<input type="date" id="appointment_date" name="appointment_date" value="'.$appointment_date.'"><br>';

  echo '<label for="appointment_time">'.__('Time').'</label><br>';
  echo '<input type="time" id="appointment_time" name="appointment_time" value="'.$appointment_time.'"><br>';

  echo '<label for="appointment_location">'.__('Location').'</label><br>';
  echo '<input type="text" id="appointment_location" name="appointment_location" value="'.$appointment_location.'"><br>';

  echo '<label for="appointment_user_email">'.__('User Email').'</label><br>';
  echo '<input type="email" id="appointment_user_email" name="appointment_user_email" value="'.$appointment_user_email.'"><br>';
}

// Save the appointment meta box
function my_appointment_plugin_save_meta_box($post_id) {
  if (!isset($_POST['my_appointment_plugin_nonce'])) {
    return $post_id;
  }

  $nonce = $_POST['my_appointment_plugin_nonce'];
  if (!wp_verify_nonce($nonce, 'my_appointment_plugin_save_meta_box')) {
  return $post_id;
  }
  
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
  return $post_id;
  }
  
  if (!current_user_can('edit_post', $post_id)) {
  return $post_id;
  }
  
  $appointment_date = sanitize_text_field($_POST['appointment_date']);
  update_post_meta($post_id, '_appointment_date', $appointment_date);
  
  $appointment_time = sanitize_text_field($_POST['appointment_time']);
  update_post_meta($post_id, '_appointment_time', $appointment_time);
  
  $appointment_location = sanitize_text_field($_POST['appointment_location']);
  update_post_meta($post_id, '_appointment_location', $appointment_location);
  
  $appointment_user_email = sanitize_email($_POST['appointment_user_email']);
  update_post_meta($post_id, '_appointment_user_email', $appointment_user_email);
  }
  add_action('save_post', 'my_appointment_plugin_save_meta_box');
  
  // Add appointment booking form shortcode
  function my_appointment_plugin_shortcode($atts) {
  ob_start();
  include(plugin_dir_path(FILE) . 'templates/booking-form.php');
  return ob_get_clean();
  }
  add_shortcode('appointment_booking_form', 'my_appointment_plugin_shortcode');
  
  // Appointment booking form submission
  function my_appointment_plugin_submit_form() {
  if (isset($_POST['submit_appointment_booking'])) {
  $appointment_date = sanitize_text_field($_POST['appointment_date']);
  $appointment_time = sanitize_text_field($_POST['appointment_time']);
  $appointment_location = sanitize_text_field($_POST['appointment_location']);
  $appointment_user_email = sanitize_email($_POST['appointment_user_email']);
  $appointment_post = array(
    'post_title' => __('Appointment').' - '.$appointment_date.' '.$appointment_time,
    'post_type' => 'appointment',
    'post_status' => 'publish',
  );
//   $post_id = wp_insert_post($appointment_post);
  
//   update_post_meta($post_id, '_appointment_date', $appointment_date);
//   update_post_meta($post_id, '_appointment_time', $appointment_time);
//   update_post_meta($post_id, '_appointment_location', $appointment_location);
//   update_post_meta($post_id, '_appointment_user_email', $appointment_user_email);
$post_id = wp_insert_post($appointment_post);

update_post_meta($post_id, '_appointment_date', $appointment_date);
update_post_meta($post_id, '_appointment_time', $appointment_time);
update_post_meta($post_id, '_appointment_location', $appointment_location);
update_post_meta($post_id, '_appointment_user_email', $appointment_user_email);

wp_redirect(get_permalink(get_page_by_title('Appointment Booked')));
exit;

  
  // Send email to user
  $to = $appointment_user_email;
  $subject = __('Appointment Details');
  $message = __('Your appointment details:') . "\r\n\r\n";
  $message .= __('Date: ') . $appointment_date . "\r\n";
  $message .= __('Time: ') . $appointment_time . "\r\n";
  $message .= __('Location: ') . $appointment_location . "\r\n\r\n";
  $message .= __('Thank you for booking your appointment.');
  
  wp_mail($to, $subject, $message);
  
  // Redirect to success page
  wp_redirect(get_permalink(get_page_by_path('appointment-booking-success')));
  exit;
}
}
add_action('init', 'my_appointment_plugin_submit_form');

// Add the appointment booking success page
function my_appointment_plugin_add_success_page() {
$success_post = array(
'post_title' => __('Appointment Booking Success'),
'post_content' => __('Thank you for booking your appointment.'),
'post_type' => 'page',
'post_status' => 'publish',
);
wp_insert_post($success_post);
}
register_activation_hook(FILE, 'my_appointment_plugin_add_success_page');

function my_plugin_enqueue_scripts() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', array(), '4.3.1', 'all');
    
    // Enqueue Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), '4.3.1', true);
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');

// Enqueue Select2 CSS and JS
function my_appointment_booking_form_enqueue_scripts() {
  wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css' );
  wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js', array( 'jquery' ), '', true );
}
add_action( 'wp_enqueue_scripts', 'my_appointment_booking_form_enqueue_scripts' );

function my_appointment_booking_form_shortcode() {
  ob_start();
  ?>
  <!-- Button trigger modal -->
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#appointmentModal">
    <?php esc_attr_e('Book Appointment', 'my-appointment-plugin'); ?>
  </button>

  <!-- Modal -->
  <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="appointmentModalLabel"><?php esc_attr_e('Book Appointment', 'my-appointment-plugin'); ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'my-appointment-plugin'); ?>">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="post" action="">
            <div class="form-group">
              <label for="appointment_date"><?php esc_html_e('Date:', 'my-appointment-plugin'); ?></label>
              <input type="date" id="appointment_date" name="appointment_date" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="appointment_time"><?php esc_html_e('Time:', 'my-appointment-plugin'); ?></label>
              <input type="time" id="appointment_time" name="appointment_time" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="appointment_location"><?php esc_html_e('Location:', 'my-appointment-plugin'); ?></label>
              <input type="text" id="appointment_location" name="appointment_location" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="appointment_user_email"><?php esc_html_e('Email:', 'my-appointment-plugin'); ?></label>
              <input type="email" id="appointment_user_email" name="appointment_user_email" class="form-control" required>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_attr_e('Close', 'my-appointment-plugin'); ?></button>
              <input type="submit" name="submit_appointment_booking" value="<?php esc_attr_e('Book Appointment', 'my-appointment-plugin'); ?>" class="btn btn-primary">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('my_appointment_booking_form', 'my_appointment_booking_form_shortcode');


function my_appointment_plugin_appointments_page() {
  global $wpdb;

  $appointments_table_name = $wpdb->prefix . 'my_appointment_plugin_appointments';

  $appointments = $wpdb->get_results("SELECT * FROM $appointments_table_name");

  ?>
  <div class="wrap">
    <h1><?php esc_html_e('Appointments', 'my-appointment-plugin'); ?></h1>

    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th><?php esc_html_e('Date', 'my-appointment-plugin'); ?></th>
          <th><?php esc_html_e('Time', 'my-appointment-plugin'); ?></th>
          <th><?php esc_html_e('Location', 'my-appointment-plugin'); ?></th>
          <th><?php esc_html_e('User Email', 'my-appointment-plugin'); ?></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($appointments as $appointment) { ?>
          <tr>
            <td><?php echo esc_html($appointment->appointment_date); ?></td>
            <td><?php echo esc_html($appointment->appointment_time); ?></td>
            <td><?php echo esc_html($appointment->appointment_location); ?></td>
            <td><?php echo esc_html($appointment->appointment_user_email); ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <?php
}

function my_appointment_plugin_add_menu_page() {
  add_menu_page(
    'Appointments',
    'Appointments',
    'manage_options',
    'my-appointment-plugin-appointments',
    'my_appointment_plugin_appointments_page'
  );
}
add_action('admin_menu','my_appointment_plugin_add_menu_page');

// Function to send appointment confirmation email to user
function my_appointment_plugin_send_email($appointment_id) {
global $wpdb;

$appointments_table_name = $wpdb->prefix . 'my_appointment_plugin_appointments';

$appointment = $wpdb->get_row("SELECT * FROM $appointments_table_name WHERE id = $appointment_id");

if (!$appointment) {
return;
}

$to = $appointment->appointment_user_email;
$subject = 'Appointment Confirmation';
$message = 'Thank you for booking an appointment with us! Here are the details of your appointment: ' . "\r\n";
$message .= 'Date: ' . $appointment->appointment_date . "\r\n";
$message .= 'Time: ' . $appointment->appointment_time . "\r\n";
$message .= 'Location: ' . $appointment->appointment_location . "\r\n";

wp_mail($to, $subject, $message);
}

// Function to integrate with Zapier
function my_appointment_plugin_zapier_integration($args) {
global $wpdb;

$appointments_table_name = $wpdb->prefix . 'my_appointment_plugin_appointments';

$data = array(
'appointment_date' => $args['appointment_date'],
'appointment_time' => $args['appointment_time'],
'appointment_location' => $args['appointment_location'],
'appointment_user_email' => $args['appointment_user_email']
);

$wpdb->insert($appointments_table_name, $data);

$appointment_id = $wpdb->insert_id;

my_appointment_plugin_send_email($appointment_id);
}

add_action('rest_api_init', function () {
register_rest_route('my-appointment-plugin/v1', 'book-appointment', array(
'methods' => 'POST',
'callback' => 'my_appointment_plugin_zapier_integration',
));
});
// Shortcode to display appointment booking form on front-end
function my_appointment_plugin_booking_form_shortcode() {
  ob_start();
  
  include(plugin_dir_path(FILE) . 'templates/booking-form.php');
  
  return ob_get_clean();
  }
  
  add_shortcode('my_appointment_plugin_booking_form', 'my_appointment_plugin_booking_form_shortcode');
  
  // Function to create database table on plugin activation
  function my_appointment_plugin_create_table() {
  global $wpdb;
  
  $appointments_table_name = $wpdb->prefix . 'my_appointment_plugin_appointments';
  
  $charset_collate = $wpdb->get_charset_collate();
  
  $sql = "CREATE TABLE $appointments_table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  appointment_date date NOT NULL,
  appointment_time time NOT NULL,
  appointment_location varchar(255) NOT NULL,
  appointment_user_email varchar(255) NOT NULL,
  PRIMARY KEY (id)
  ) $charset_collate;";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
  }
  
  register_activation_hook(FILE, 'my_appointment_plugin_create_table');
  // Function to handle appointment form submission
function my_appointment_plugin_handle_form_submission() {
  if (isset($_POST['my_appointment_plugin_submit'])) {
  global $wpdb;
  $appointments_table_name = $wpdb->prefix . 'my_appointment_plugin_appointments';

$date = sanitize_text_field($_POST['my_appointment_plugin_date']);
$time = sanitize_text_field($_POST['my_appointment_plugin_time']);
$location = sanitize_text_field($_POST['my_appointment_plugin_location']);
$user_email = sanitize_text_field($_POST['my_appointment_plugin_user_email']);

$wpdb->insert(
  $appointments_table_name,
  array(
    'appointment_date' => $date,
    'appointment_time' => $time,
    'appointment_location' => $location,
    'appointment_user_email' => $user_email,
  )
);

// Send email to user
$to = $user_email;
$subject = 'Appointment Confirmation';
$message = 'Your appointment has been confirmed. Details are as follows: <br><br>' .
           'Date: ' . $date . '<br>' .
           'Time: ' . $time . '<br>' .
           'Location: ' . $location . '<br><br>' .
           'Thank you for using our appointment booking system.';
$headers = array('Content-Type: text/html; charset=UTF-8');
wp_mail($to, $subject, $message, $headers);

wp_redirect(home_url());
exit();
}
}

add_action('init', 'my_appointment_plugin_handle_form_submission');
// Register the plugin with Zapier
add_action('rest_api_init', 'my_appointment_plugin_register_rest_routes');

function my_appointment_plugin_register_rest_routes() {
register_rest_route(
'my-appointment-plugin/v1',
'/appointments',
array(
'methods' => 'POST',
'callback' => 'my_appointment_plugin_rest_appointments_handler',
'permission_callback' => '__return_true',
)
);
}

// Handle the appointment submission via REST API
function my_appointment_plugin_rest_appointments_handler(WP_REST_Request $request) {
$parameters = $request->get_params();

global $wpdb;

$appointments_table_name = $wpdb->prefix . 'my_appointment_plugin_appointments';

$date = sanitize_text_field($parameters['date']);
$time = sanitize_text_field($parameters['time']);
$location = sanitize_text_field($parameters['location']);
$user_email = sanitize_text_field($parameters['user_email']);

$wpdb->insert(
$appointments_table_name,
array(
'appointment_date' => $date,
'appointment_time' => $time,
'appointment_location' => $location,
'appointment_user_email' => $user_email,
)
);

// Send email to user
$to = $user_email;
$subject = 'Appointment Confirmation';
$message = 'Your appointment has been confirmed. Details are as follows: <br><br>' .
'Date: ' . $date . '<br>' .
'Time: ' . $time . '<br>' .
'Location: ' . $location . '<br><br>' .
'Thank you for using our appointment booking system.';
$headers = array('Content-Type: text/html; charset=UTF-8');
wp_mail($to, $subject, $message, $headers);

return new WP_REST_Response(array('message' => 'Appointment booked successfully'), 200);
}
