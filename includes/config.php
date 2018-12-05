<?php
/**
 * MailChimp processor Config template
 *
 * @package   Caldera_Forms_MailChimp
 */

global $wpdb;
$form_id = $_REQUEST['edit'];
$config = Caldera_Forms_Forms::get_form( $form_id );
$array = array();

foreach( Caldera_Forms_Forms::get_fields( $config, true ) as $field_id => $field ) {

    if( $field['type']=='text' || $field['type'] == 'email' ) {
        $array[$field['slug']] = $field['label'];
    }
}

$subscription_lists = \MailPoet\API\API::MP('v1')->getLists();

$sql = "SELECT * FROM " . $wpdb->prefix . "cf_forms WHERE type='primary' and form_id = '".$form_id."'";
$records = $wpdb->get_results( $sql );

$email_field = '';
$first_name_field = '';
$last_name_field = '';
$list_id = '';
$confirmation_field = '';
if( count( $records ) > 0 ) {
    $record = $records[ 0 ];
    $data = maybe_unserialize( $record->config );
    $processors = $data[ 'processors' ]; //\MailPoet
    if( is_array( $processors ) && count( $processors ) > 0 ) {
        foreach( $processors as $process ) {
            if( $process['type'] == 'mailpoet' ) {
                $configs = $process['config'];
                if( is_array( $configs ) && count( $configs ) ) {
                    $email_field = $configs['email_field'];
                    $first_name_field = $configs['first_name_field'];
                    $last_name_field = $configs['last_name_field'];
					$list_id = $configs['list_id'];
					$confirmation_field = $configs['confirmation_field'];
                }
            }
        }
    }
}

?>
<div class="caldera-config-group">
    <label for="{{_id}}-email-field">
        <?php esc_html_e( 'Email Field', 'mp_cal_addon' ); ?>
    </label>
    <div class="caldera-config-field">
        <select  id="{{_id}}-email-field" name="{{_name}}[email_field]" aria-describedby="{{_id}}-email-field-desc" class="block-input">
            <?php
                foreach( $array as $field_id => $field ) {
                    ?>
                        <option value="<?php echo $field_id;?>" <?php echo $email_field == $field_id ? 'selected' : ''; ?> ><?php echo $field;?></option>
                    <?php
                }   
            ?>
       </select>
    </div>
</div>
<div class="caldera-config-group">
    <label for="{{_id}}-first-name-field">
        <?php esc_html_e( 'First Name Field', 'mp_cal_addon' ); ?>
    </label>
    <div class="caldera-config-field">
        <select  id="{{_id}}-first-name-field" name="{{_name}}[first_name_field]"  aria-describedby="{{_id}}-first-name-field-desc" class="block-input">
            <?php
                foreach( $array as $field_id => $field ) {
                    ?>
                        <option value="<?php echo $field_id;?>" <?php echo $first_name_field == $field_id ? 'selected' : ''; ?>><?php echo $field;?></option>
                    <?php
                }   
            ?>
        </select>
    </div>
</div>
<div class="caldera-config-group">
    <label for="{{_id}}-last-name-field">
        <?php esc_html_e( 'Last Name Field', 'mp_cal_addon' ); ?>
    </label>
    <div class="caldera-config-field">
        <select  id="{{_id}}-last-name-field" name="{{_name}}[last_name_field]"  aria-describedby="{{_id}}-last-name-field-desc" class="block-input">
            <?php
                foreach( $array as $field_id => $field ) {
                    ?>
                        <option value="<?php echo $field_id;?>" <?php echo $last_name_field == $field_id ? 'selected' : ''; ?>><?php echo $field;?></option>
                    <?php
                }   
            ?>
        </select>
    </div>
</div>
<div id="list_{{_id}}-wrap">
    <div class="caldera-config-group">
        <label for="list_{{_id}}" >
            <?php esc_html_e('List', 'mp_cal_addon'); ?>
        </label>
        <div class="caldera-config-field">
            <select id="{{_id}}-list-id"  name="{{_name}}[list_id]">
                <?php
                    foreach( $subscription_lists as $list ){
                        ?>
                            <option value="<?php echo $list['id'];?>" <?php echo $list_id == $list['id'] ? 'selected' : ''; ?>><?php echo $list['name'];?></option>
                        <?php
                    }   
                ?>
            </select>
        </div>
    </div>
</div>	
<div class="caldera-config-group">
    <label for="{{_id}}-confirmation-field">
        <?php esc_html_e( 'Send Confirmation Email', 'mp_cal_addon' ); ?>
    </label>
    <div class="caldera-config-field">
        <select  id="{{_id}}-confirmation-field" name="{{_name}}[confirmation_field]"  aria-describedby="{{_id}}-confirmation-field-desc" class="block-input">
			<option value="Yes" selected><?php esc_html_e( 'Yes', 'mb_cal_addon' ); ?></option>
			<option value="No" <?php echo $confirmation_field == 'No' ? 'selected' : ''; ?>><?php esc_html_e( 'No', 'mp_cal_addon' ); ?></option>
        </select>
    </div>
</div>