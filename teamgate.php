<?php
/*
    Plugin Name: Teamgate CRM Lead Management
    Author: Liudas Šumskas
    Plugin URL: https://www.teamgate.com
    Description: A simple Teamgate CRM plugin to generate new sales opportunities within WordPress.
    Verison: 1.0
    Author URL: https://www.teamgate.com
    Domain Path /teamgate-crm
    Text Domain: teamgate-crm
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

register_activation_hook(__FILE__, 'teamgate_leads_activation_check');

function teamgate_activation_check() {
    if (!in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        wp_die(__('<b>Warning</b> : Install/Activate Contact Form 7 to activate "Teamgate CRM Lead Management" plugin', 'teamgate'));
    }
}

/* Capture post data on form submit in Contact Form 7 */
add_action('wpcf7_mail_sent', 'teamgate_wpcf7_mail_sent');

function teamgate_wpcf7_mail_sent($contact_form) {
    $title = $contact_form->title;
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        $posted_data = $submission->get_posted_data();
    }

    if (!empty($posted_data['teamgate-entry-handler']) && in_array(
                    $posted_data['teamgate-entry-handler'], 
                    array('leads', 'people', 'companies', 'deals')
                )) {
        try {
            require dirname(__FILE__) . '/php-sdk/vendor/autoload.php';
            $api = new \Teamgate\API([
                'apiKey' => esc_attr(get_option('teamgate-app-key')),
                'authToken' => esc_attr(get_option('teamgate-auth-token'))
            ]);
            $method = $posted_data['teamgate-entry-handler'];
            $api->$method->create($posted_data);
        } catch (Exception $e) {
            return;
        }
    }
}

/* Shortcode handler */
add_action('init', 'contact_form_7_teamgate_submit', 11);

function contact_form_7_teamgate_submit() {
    if (function_exists('wpcf7_add_shortcode')) {
        wpcf7_add_shortcode('teamgatesubmit', 'wpcf7_teamgate_submit_shortcode_handler', false);
    } else {
        return;
    }
}

/**
 * Regenerate shortcode into Teamgate submit button
 */
function wpcf7_teamgate_submit_shortcode_handler($tag) {
    $tag = new WPCF7_Shortcode($tag);
    $class = wpcf7_form_controls_class($tag->type);
    $atts = array();

    $entry = $tag->get_option('entry');
    $type = (empty($entry)) ? 'lead' : $entry[0];

    $value = isset($tag->values[0]) ? $tag->values[0] : '';

    if (empty($value)) {
        $value = __('Submit', 'contact-form-7');
    }

    $atts['type'] = 'submit';
    $atts['value'] = $value;

    $atts = wpcf7_format_atts($atts);

    $html = '<input type="hidden" name="teamgate-entry-handler" value="' . $type . '" />';
    $html .= sprintf('<input %1$s />', $atts);

    return $html;
}

/* * **********************************~: Admin Section of Teamgate submit button :~*********************************** */

/* Tag generator */

add_action('admin_init', 'wpcf7_add_tag_generator_teamgate_submit', 55);

function wpcf7_add_tag_generator_teamgate_submit() {
    if (class_exists('WPCF7_TagGenerator')) {
        $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->add('teamgate-submit', __('Teamgate Submit button', 'teamgate'), 'wpcf7_tg_pane_teamgate_submit', array('nameless' => 1));
    }
}

/** Parameters field for generating tag at backend * */
function wpcf7_tg_pane_teamgate_submit($contact_form) {
    $description = __("Generate a form-tag for a Teamgate submit button which call to Teamgate API after submitting the form. Fields must have matching Teamgate API entry type parameters.", 'contact-form-7');
    $desc_link = wpcf7_link('', __('Teamgate submit', 'teamgate'));
    ?>
    <div class="control-box">
        <fieldset>
            <legend><?php echo sprintf(esc_html($description), $desc_link); ?></legend>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td colspan="2"><?php echo esc_html(__('Button Label', 'teamgate')); ?>
                            <font style="font-size:10px"> (optional)</font><br />
                            <input type="text" name="values" class="oneline" /></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo esc_html(__('Teamgate Entry Type', 'teamgate')); ?><br />
                            <select name="types" onchange="document.getElementById('entry').value = this.value;">
                                <option value="leads">Lead</option>
                                <option value="people">Contact</option>
                                <option value="companies">Company</option>
                                <option value="deals">Deal</option>
                            </select>
                            <input type="hidden" name="entry" id="entry" value="lead" class="oneline option">
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <div class="insert-box">
        <input type="text" name="teamgatesubmit" class="tag code" readonly="readonly" onfocus="this.select()" />

        <div class="submitbox">
            <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(__('Insert Tag', 'teamgate')); ?>" />
        </div>
    </div>
    <?php
}

add_action( 'admin_menu', 'teamgate_settings_admin_menu' );

function teamgate_settings_admin_menu() {
    if (current_user_can('administrator')) {
    add_submenu_page( 'wpcf7',
        __( 'Teamgate CRM', 'teamgate' ),
        __( 'Teamgate CRM', 'teamgate' ),
        'wpcf7_edit_contact_forms', 'wpcf7-teamgate',
        'teamgate_settings_page' );
    }
}

function teamgate_settings_page() {
?>
<div class="wrap">
<h2>Teamgate CRM Settings</h2>
<form  method="post" action="">
    <?php wp_nonce_field('teamgate-settings-apply'); ?>
	<p><?php echo esc_html(__( 'API Key', 'teamgate' )); ?>:
	<input type="text" name="teamgate-app-key" value="<?php echo esc_attr(get_option('teamgate-app-key')); ?>" size="85"/></p>
    <p><?php echo esc_html(__( 'Auth Token', 'teamgate' )); ?>:
	<input type="text" name="teamgate-auth-token" value="<?php echo esc_attr(get_option('teamgate-auth-token')); ?>" size="85"/></p>
	<p><a href="https://www.teamgate.com/" target="_blank">More about Teamgate</a></p>
	<p><input type="submit" value="<?php echo esc_attr(__( 'Apply', 'teamgate' ));?>" class="button button-primary button-large"></p>
</form>
</div>
<?php
}

//Save the data
add_action( 'admin_init', 'teamgate_settings_admin_data');

function teamgate_settings_admin_data() {
    if (isset($_POST['teamgate-app-key']) && isset($_POST['teamgate-auth-token']) && check_admin_referer('teamgate-settings-apply')) {
        if (!empty($_POST['teamgate-app-key'])) {
            update_option('teamgate-app-key', sanitize_text_field($_POST['teamgate-app-key'])); 
        } else {
            update_option('teamgate-app-key', ''); 
        }
        if (!empty($_POST['teamgate-auth-token'])) {
            update_option('teamgate-auth-token', sanitize_text_field($_POST['teamgate-auth-token'])); 
        } else {
            update_option('teamgate-auth-token', ''); 
        }
        wp_safe_redirect(menu_page_url('wpcf7-teamgate', false)); 
	}

}