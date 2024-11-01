<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>


<?php



    ?>

    <form method="post" action="options.php">
        <?php settings_fields( 'ohmem-settings-group' ); ?>
        <?php do_settings_sections( 'ohmem-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Choose the page for guests to be redirect to</th>
                <td>
                    <?php
                    $args = array(
                        'selected' => get_option('ohmem-oops-page'),
                        'name' => 'ohmem-oops-page'
                    );
                        wp_dropdown_pages( $args );
                    ?>
                </td>
                <td rowspan="3" class="vtop">
                    <h2> Shortcode list: </h2>
                    <ol>
                        <li><span class="w100">[members]</span>  : Display content for login users only </li>
                        <li><span class="w100">[guests]</span>  : Display content for guest users only </li>
                        <li><span class="w100">[login-form]</span>  : Display login form for guest </li>
                        <li><span class="w100">[member-name]</span>  : Display login user nickname </li>
                        <li><span class="w100">[register]</span>  : Display registration form  </li>
                    </ol>

                </td>
                <td>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Choose the page for registers (after registration) to be redirect to (optional)</th>
                <td>
                    <?php
                    $args = array(
                        'selected' => get_option('ohmem-register-redirect'),
                        'name' => 'ohmem-register-redirect',
                        'show_option_none' => '---None---'
                    );
                    //
                        wp_dropdown_pages( $args );
                    ?>
                </td>
                <td></td>
            </tr>
			<tr valign="top">
                <th scope="row">Text for members instead of login form</th>
                <td>
                    <textarea cols="30" rows="5" name="ohmem-message" id="ohmem-message"><?php  echo get_option('ohmem-message'); ?></textarea>

                </td>
                <td >


                </td>

            </tr>

        </table>

        <?php submit_button(); ?>

    </form>

