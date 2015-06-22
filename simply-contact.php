<?php
/*
Plugin Name: Simply Contact
Plugin URI:
Version: 3.0
Author: Eugene Trounev
Description: A super Simple contact form plugin.
*/

define( 'SIMPLECONTACT_PATH', plugin_dir_path(__FILE__) );
define( 'SIMPLECONTACT_URL', trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) ));
$simplyContact_plugin_name = "Simply Contact";
$simplyContact_plugin_shortname = "simplyContact";
$simplyContact_plugin_options = array(
    array( "name" => $simplyContact_plugin_name." Options",
    "type" => "title"),
    array( "name" => "E-Mail Settings",
        "description" => "Destination E-Mail address is set here",
        "type" => "section"),
    array( "type" => "open"),
    array( "name" => "E-Mail",
    "desc" => "The address where all the requests will be forwarded to.",
    "id" => $simplyContact_plugin_shortname."_email",
    "type" => "text",
    "std" => ""),
    array( "type" => "close")
);

function simplyContact_settings_page()
{
    global $simplyContact_plugin_name, $simplyContact_plugin_shortname, $simplyContact_plugin_options;
    $i=0;

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$simplyContact_plugin_name.' settings saved.</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$simplyContact_plugin_name.' settings reset.</strong></p></div>';

    ?>
        <div class="wrap">
        <h2><?php echo $simplyContact_plugin_name;?> Settings</h2>

        <form method="post">

            <?php foreach($simplyContact_plugin_options as $value){switch($value['type']){case "open":?>
                    <?php break;case "close":?>
                        </tbody>
                        </table>
                        <p class="submit"><input type="submit"  name="save<?php echo $i;?>" id="submit" class="button-primary" value="Save Changes"></p>
                    <?php break;case "title":?>
                        <p>To easily use the <?php echo $simplyContact_plugin_name;?>, you can use the menu below.</p>
                    <?php break;case "section":$i++;?>
                        <h3><?php echo $value['name'];?></h3>
                        <p><?php echo $value['description'];?></p>
                        <table class="form-table">
                            <tbody>
                    <?php break;case 'text':?>
                        <tr valign="top">
                            <th scope="row"><label for="<?php echo $value['id'];?>"><?php echo $value['name'];?></label></th>
                            <td>
                                <input name="<?php echo $value['id'];?>" id="<?php echo $value['id'];?>" class="regular-text" type="<?php echo $value['type'];?>" value="<?php if(get_settings($value['id'])!=""){echo stripslashes(get_settings($value['id']));}else {echo $value['std'];}?>" />
                                <span class="description"><?php echo $value['desc'];?></span>
                            </td>
                         </tr>
                    <?php break;case 'textarea':?>
                        <tr valign="top">
                            <th scope="row"><label for="<?php echo $value['id'];?>"><?php echo $value['name'];?></label></th>
                            <td>
                                <textarea name="<?php echo $value['id'];?>" type="<?php echo $value['type'];?>" cols="50" rows="6"><?php if(get_settings($value['id'])!=""){echo stripslashes(get_settings($value['id']));}else {echo $value['std'];}?></textarea>
                                <span class="description"><?php echo $value['desc'];?></span>
                            </td>
                         </tr>
                    <?php break;case 'select':?>
                        <tr valign="top">
                            <th scope="row"><label for="<?php echo $value['id'];?>"><?php echo $value['name'];?></label></th>
                            <td>
                                <select name="<?php echo $value['id'];?>" id="<?php echo $value['id'];?>">
                                <?php foreach($value['options'] as $option){?>
                                    <option <?php if(get_settings($value['id'])==$option){echo 'selected="selected"';}?>><?php echo $option;?></option><?php }?>
                                </select>
                                <span class="description"><?php echo $value['desc'];?></span>
                            </td>
                         </tr>
                    <?php break;case 'checkbox':?>
                        <tr valign="top">
                            <th scope="row"><label for="<?php echo $value['id'];?>"><?php echo $value['name'];?></label></th>
                            <td>
                                <?php if(get_option($value['id'])){$checked="checked=\"checked\"";}else{$checked="";}?>
                                <input type="checkbox" name="<?php echo $value['id'];?>" id="<?php echo $value['id'];?>" value="true" <?php echo $checked;?> />
                                <span class="description"><?php echo $value['desc'];?></span>
                            </td>
                         </tr>
                    <?php break;}}?>
            <input type="hidden" name="action" value="save" />
        </form>
        </div>
    <?php
}

function simplyContact_settings()
{
  global $simplyContact_plugin_name, $simplyContact_plugin_shortname, $simplyContact_plugin_options;
    if ( $_GET['page'] == "simply-contact/simply-contact.php" ) {
        if ( 'save' == $_REQUEST['action'] ) {
            foreach ($simplyContact_plugin_options as $value) {
                if( isset( $_REQUEST[ $value['id'] ] ) ) {
                    update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
                } else {
                    delete_option( $value['id'] );
                }
            }

            header("Location: admin.php?page=simply-contact/simply-contact.php&saved=true");
            die();
        }
    }
  add_options_page($simplyContact_plugin_name.' Options', $simplyContact_plugin_name, 8, __FILE__, 'simplyContact_settings_page');
}
add_action( 'admin_menu', 'simplyContact_settings');

function simplyContact_uninstall(){
    global $simplyContact_plugin_options;
    foreach ($options as $value) {
        delete_option( $value['id'] );
    }
}
register_deactivation_hook( __FILE__, 'simplyContact_uninstall' );

//--------------------------------------------------------------------------------------------
// Shortcodes and include processors
//--------------------------------------------------------------------------------------------

function simplyContact_shortcode_maker( $atts, $form, $style, $script ) {
    global $simplyContact_plugin_shortname;
    extract( shortcode_atts( array(), $atts ) );
    require_once (SIMPLECONTACT_PATH.'assets/lib/ft-nonce-lib.php');
    
    //Get form vars:
    $nonce = ft_nonce_create( 'enquiry_email' , 'simply_contact' );
    $formTitle = get_option($simplyContact_plugin_shortname."_form_title", 'default');
    $buttonText = get_option($simplyContact_plugin_shortname."_email_text", 'default');
    $style = "";//"<link rel='stylesheet' href='" . SIMPLECONTACT_URL . "/assets/css/" . $style . "' type='text/css' media='all' />";
    $script = "";//"<script type='text/javascript' src='".SIMPLECONTACT_URL."/assets/js/".$script."'></script>";
    $restURL = SIMPLECONTACT_URL."/assets/php/scFormProcessor.php";
    
    //Create form
    $form = preg_replace('/^\s+|\n|\r|\s+$/m', '',$form); //Clean the content. Remove all extra spaces and lines
    $form = sprintf($form,          //Source text
                    $formTitle,     //Form title
                    $buttonText,    //Submit button text
                    $nonce,          //Security string
                    $restURL
                   );           //Populate form with content
    return $style.$script.$form;
 }

function simplyContact_contact_shortcode( $atts, $content = null ) {
    return simplyContact_shortcode_maker(
      	$atts,
	$content,
      	"form-style.css",
      	""
      );
}
add_shortcode( 'scontact', 'simplyContact_contact_shortcode' );


function simplyContact_scripts_method() {
	wp_enqueue_script(
		'contact-js-script',
		SIMPLECONTACT_PATH.'assets/js/contact-form.js',
		array( 'jquery' )
	);
}

wp_enqueue_script('my_script', SIMPLECONTACT_URL . '/assets/js/contact-form.js', array('jquery'), '1.0.0', true);

?>