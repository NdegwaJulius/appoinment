
#Appointment Booking Plugin
This is a WordPress plugin that allows users to book appointments on the front-end, and admin to view the list of appointments that have been placed. The plugin sends a customized email to the user containing details of the appointment placed and details of the location for the appointment, and integrates with Zapier.

#Installation
To use this plugin, follow these steps:

Download the plugin and install it on your WordPress site.
Activate the plugin.
#Usage
The plugin registers a new post type called "Appointments".

#Adding an Appointment
To add a new appointment, follow these steps:

Go to "Appointments" in the WordPress dashboard and click "Add New".
Fill in the appointment details, including the date, time, location, and user email.
Click "Publish" to save the appointment.
#Viewing Appointments
To view the list of appointments, go to "Appointments" in the WordPress dashboard.

#Editing Appointments
To edit an appointment, click "Edit" next to the appointment you want to edit.

#Deleting Appointments
To delete an appointment, click "Trash" next to the appointment you want to delete.

#Displaying the Appointment Booking Form
To display the appointment booking form on a page or post, use the shortcode [appointment_booking_form].

#Customizing the Email Template
The plugin sends a customized email to the user after an appointment is booked. To customize the email, edit the template file located at plugins/appointment-booking-plugin/templates/email-template.php.

#Integrating with Zapier
To integrate with Zapier, follow these steps:

Create a Zap and select the "Webhooks by Zapier" app as the trigger.
Select "POST" as the action and enter the URL https://your-site.com/wp-json/my-appointment-plugin/v1/appointments.
Zapier will send a webhook to this URL every time a new appointment is booked.
#Functions
Here is a brief explanation of what each function does:

my_appointment_plugin_register_post_type() registers a custom post type called 'appointment'.
my_appointment_plugin_add_meta_box() adds a meta box to the appointment post type that allows users to enter details about the appointment.
my_appointment_plugin_meta_box_callback() is the callback function that renders the appointment meta box.
my_appointment_plugin_save_meta_box() saves the appointment meta box data when the appointment is saved.
my_appointment_plugin_shortcode() creates a shortcode that displays the appointment booking form.
my_appointment_plugin_submit_form() handles the form submission and creates a new appointment post with the submitted data.
This plugin provides a simple way for users to book appointments and for admin to manage the appointments. 
Additionally, it sends customized emails to the users with the appointment details and integrates with Zapier, making it easy to connect to other services.
