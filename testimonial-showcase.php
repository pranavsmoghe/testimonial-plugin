<?php
/*
Plugin Name: Testimonial Showcase
Description: Allows users to add testimonials using the WordPress console and display them using a shortcode.
Version: 1.0
Author: Pranav Moghe
*/

// Add a custom menu item in the WordPress console
add_action('admin_menu', 'testimonial_showcase_admin_menu');
function testimonial_showcase_admin_menu()
{
    add_menu_page(
        'Testimonial Showcase',
        'Testimonial Showcase',
        'manage_options',
        'testimonial-showcase',
        'testimonial_showcase_admin_page',
        'dashicons-testimonial'
    );
}

// Add a submenu item for editing testimonials
add_action('admin_menu', 'testimonial_showcase_edit_submenu');
function testimonial_showcase_edit_submenu()
{
    add_submenu_page(
        'testimonial-showcase',
        'Edit Testimonial',
        'Edit Testimonial',
        'manage_options',
        'testimonial-showcase-edit',
        'testimonial_showcase_edit_page'
    );
}

// Callback function to render the admin page
function testimonial_showcase_admin_page()
{
    if (isset($_POST['testimonial_submit'])) {
        $comment = sanitize_textarea_field($_POST['testimonial_comment']);
        $giver_name = sanitize_text_field($_POST['testimonial_giver_name']);

        // Get existing testimonials array or create a new one
        $testimonials = get_option('testimonial_showcase_testimonials', array());

        // Append the new testimonial to the array
        $testimonials[] = array(
            'comment' => $comment,
            'giver_name' => $giver_name,
        );

        // Update the option with the new testimonials array
        update_option('testimonial_showcase_testimonials', $testimonials);

        // Enqueue JavaScript for redirection
        add_action('admin_footer', 'testimonial_showcase_redirect');
    }

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['testimonial_index'])) {
        $testimonial_index = intval($_GET['testimonial_index']);

        // Get existing testimonials array
        $testimonials = get_option('testimonial_showcase_testimonials', array());

        // Remove the testimonial from the array
        if (isset($testimonials[$testimonial_index])) {
            unset($testimonials[$testimonial_index]);
            // Update the option with the updated testimonials array
            update_option('testimonial_showcase_testimonials', $testimonials);
        }
    }


    ?>
    <div class="wrap">
        <h1>Testimonial Showcase</h1>
        <p>Add your testimonial below:</p>
        <form method="post" action="">
            <textarea name="testimonial_comment" id="testimonial_comment" rows="4" cols="50"></textarea>
            <br>
            <label for="testimonial_giver_name">Testimonial Giver's Name:</label>
            <br>
            <input type="text" name="testimonial_giver_name" id="testimonial_giver_name" required>
            <br>
            <br>
            <input type="submit" name="testimonial_submit" class="button button-primary" value="Add Testimonial">
        </form>

        <?php
        // Display existing testimonials
        $testimonials = get_option('testimonial_showcase_testimonials', array());
        if (!empty($testimonials)) {
            echo '<h2>Existing Testimonials</h2>';
            foreach ($testimonials as $index => $testimonial) {
                echo '<div class="testimonial">';
                echo '<div class="testimonial-content">';
                echo '<p>' . esc_html($testimonial['comment']) . '</p>';
                echo '</div>';
                echo '<div class="testimonial-author">';
                echo '<p><strong>' . esc_html($testimonial['giver_name']) . '</strong></p>';
                echo '</div>';
                echo '<div class="testimonial-actions">';
                echo '<a href="?page=testimonial-showcase-edit&action=edit&testimonial_index=' . $index . '">Edit</a> | ';
                echo '<a href="?page=testimonial-showcase&action=delete&testimonial_index=' . $index . '">Delete</a>';
                echo '</div>';
                echo '</div>';
            }
        }
        ?>

    </div>
    <?php
}

// Callback function to render the edit page
function testimonial_showcase_edit_page()
{
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['testimonial_index'])) {
        $testimonial_index = intval($_GET['testimonial_index']);

        // Get existing testimonials array
        $testimonials = get_option('testimonial_showcase_testimonials', array());

        // Get the testimonial data to edit
        $testimonial_data = isset($testimonials[$testimonial_index]) ? $testimonials[$testimonial_index] : null;

        if (!$testimonial_data) {
            echo '<div class="wrap"><h1>Edit Testimonial</h1><p>Invalid testimonial data.</p></div>';
            return;
        }

        if (isset($_POST['testimonial_edit_submit'])) {
            $comment = sanitize_textarea_field($_POST['testimonial_comment']);
            $giver_name = sanitize_text_field($_POST['testimonial_giver_name']);

            // Update testimonial data
            $testimonials[$testimonial_index] = array(
                'comment' => $comment,
                'giver_name' => $giver_name,
            );

            // Update the option with the updated testimonials array
            update_option('testimonial_showcase_testimonials', $testimonials);

            // Enqueue JavaScript for redirection
            add_action('admin_footer', 'testimonial_showcase_redirect');
        }

        ?>
        <div class="wrap">
            <h1>Edit Testimonial</h1>
            <form method="post" action="">
                <label for="testimonial_comment">Testimonial:</label>
                <textarea name="testimonial_comment" id="testimonial_comment" rows="4" cols="50"><?php echo esc_textarea($testimonial_data['comment']); ?></textarea>
                <br>
                <label for="testimonial_giver_name">Person's Name:</label>
                <input type="text" name="testimonial_giver_name" id="testimonial_giver_name" value="<?php echo esc_attr($testimonial_data['giver_name']); ?>" required>
                <br>
                <input type="submit" name="testimonial_edit_submit" class="button button-primary" value="Update Testimonial">
            </form>
        </div>
        <?php
    } else {
        echo '<div class="wrap"><h1>Edit Testimonial</h1><p>Invalid action or testimonial index.</p></div>';
    }
}

// Function to display testimonials using shortcode
function testimonial_showcase_shortcode($atts)
{
    $testimonials = get_option('testimonial_showcase_testimonials', array());

    // Pre-coded HTML template for displaying the testimonial
    $html_template = '<div class="testimonial" style="font-family: Arial, sans-serif; background-color: #f9f9f9; text-align: center; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #fff; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); font-size: 16px; color: #333; line-height: 1.6; font-style: italic;">
        <div class="testimonial-content" style="font-size: 16px; line-height: 1.6;">
            <p style="margin: 0;">%s</p>
        </div>
        <div class="testimonial-author" style="margin-top: 10px;">
            <p style="font-size: 14px; margin: 0;"><strong>%s</strong></p>
        </div>
    </div>';

    $output = '';
    foreach ($testimonials as $testimonial) {
        $output .= sprintf($html_template, esc_html($testimonial['comment']), esc_html($testimonial['giver_name']));
    }

    return $output;
}
add_shortcode('testimonial_showcase', 'testimonial_showcase_shortcode');

// JavaScript for redirection
function testimonial_showcase_redirect()
{
    ?>
    <script>
    window.location.href = '<?php echo esc_url(admin_url('admin.php?page=testimonial-showcase')); ?>';
    </script>
    <?php
}
