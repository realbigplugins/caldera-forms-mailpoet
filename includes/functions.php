<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initialize the processor
 */
add_action( 'caldera_forms_pre_load_processors', function(){

    new CF_MailPoet_Base( mp_caldera_register_addon(), mp_caldera_processor_extra_meta_fields(), 'mailpoet-caldera-integration' );

});

/**
 * MailPoet processor Config template
 */
function mp_caldera_processor_extra_meta_fields(){ 

    $fields = array();
    if( isset( $_REQUEST['edit'] ) ) {
        $form_id = sanitize_text_field( $_REQUEST['edit'] );

        $config = Caldera_Forms_Forms::get_form( $form_id );
        $fields = array();
        foreach( Caldera_Forms_Forms::get_fields( $config, true ) as $field_id => $field ){
            if( $field['type']=='text' || $field['type']=='email' )
                $fields[ $field['slug'] ] = $field[ 'label' ];
        }
    }
    

    $subscription_lists = \MailPoet\API\API::MP('v1')->getLists();
    $list_items = array();
    foreach( $subscription_lists as $list ){
        $list_items[ $list[ 'id' ] ] = $list[ 'name' ];
    }
    
    return array(
        array(
            'id' => 'email_field',
            'label' => __( 'Email Field', 'mp_cal_addon' ),
            'type' => 'dropdown',
            'required' => true,
            'options' => $fields,
        ),
        array(
            'id' => 'first_name_field',
            'label' => __( 'First Name Field', 'mp_cal_addon' ),
            'type' => 'dropdown',
            'required' => true,
            'options' => $fields,
        ),
        array(
            'id' => 'last_name_field',
            'label' => __( 'Last Name Field', 'mp_cal_addon' ),
            'type' => 'dropdown',
            'required' => true,
            'options' => $fields,
        ),
        array(
            'id' => 'list_id',
            'label' => __( 'List', 'mp_cal_addon' ),
            'type' => 'dropdown',
            'required' => true,
            'options' => $list_items,
            ),
        array(
            'id' => 'confirmation_field',
            'label' => __( 'Send Confirmation Email', 'mp_cal_addon' ),
            'type' => 'dropdown',
            'required' => true,
            'options' => array(
                'Yes' => __( 'Yes', 'mp_cal_addon' ),
                'No' => __( 'No', 'mp_cal_addon' ),
            )
        )
    );
}


/**
 * Registers the MailPoet Processor
 *
 * @param $processors
 * @return array
 */
function mp_caldera_register_addon(  ) {
    
    $processor = array(
        'name'				=>	__( 'MailPoet', 'mp_cal_addon' ),
        'description'		=>	__( 'Integrates a form with MailPoet', 'mp_cal_addon'),
        'icon'				=>	'',
        'author'			=>	'Real Big Plugins',
        'author_url'		=>	'https://calderaforms.com',
        'template'			=>	MP_CAL_INCLUDES_DIR . 'config.php',
    );

    return $processor;
}