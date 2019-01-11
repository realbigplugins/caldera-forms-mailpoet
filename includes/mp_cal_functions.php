<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MP_Cal_Functions {
    
    /**
	 * Initialization
	 */
    public function __construct() {
        
        /**
         * add filter to register addon with Caldera Forms
         */
        add_filter( 'caldera_forms_get_form_processors', array( $this, 'mp_acl_registration_addon' ), 50 );
    }

    /**
     * Registers the MailPoet Processor
     *
     * @param $processors
     * @return array
     */
    function mp_acl_registration_addon( $processors ) {
        
        $processors['mailpoet'] = array(
            'name'				=>	__( 'MailPoet', 'mp_cal_addon' ),
            'description'		=>	__( 'Integrates a form with MailPoet', 'mp_cal_addon' ),
            'icon'				=>	'',
            'author'			=>	'Real Big plugins',
            'author_url'		=>	'https://calderaforms.com',
            'post_processor'	=>	array( $this, 'cf_mailpoet_post_process' ),
            'template'			=>	MP_CAL_INCLUDES_DIR . 'config.php',
        );

        return $processors;
    }

    /**
     * MailPoet processor Config template
     */
    public function my_processor_extra_meta_processor_fields() {

        $form_id = sanitize_text_field( $_REQUEST['edit'] );

        $config = Caldera_Forms_Forms::get_form( $form_id );
        $fields = array();
        foreach( Caldera_Forms_Forms::get_fields( $config, true ) as $field_id => $field ) {
            if( $field['type']=='text' || $field['type']=='email' )
                $fields[ $field['slug'] ] = $field[ 'label' ];
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
     * Processes form and push to MailPoet
     *
     * @param array		$config			Config array of the processor
     * @param array		$form			array of the complete form config structure
     * @return array	array of the transaction result
     */
    function cf_mailpoet_post_process( $config, $form ) {

        if( isset( $config['processor_id'] ) && !empty( $config['processor_id'] ) ) {
            $processor_id = $config['processor_id'];
            
            if( is_array( $form['processors'] ) && count( $form['processors'] ) > 0 ) {
                $processor = $form['processors'][ $processor_id];
                if( !empty( $processor['type'] ) && $processor['type'] == 'mailpoet' ) {

                    $fields = $processor['config'];
                    if( is_array( $fields ) && count( $fields ) > 0 ) {
                        $email              = Caldera_Forms::get_field_data( $fields['email_field'], $form );
                        $first_name_field   = Caldera_Forms::get_field_data( $fields['first_name_field'], $form );
                        $last_name_field    = Caldera_Forms::get_field_data( $fields['last_name_field'], $form );
                        $lists              = array( $fields['list_id'] );
                        $confirmation       = trim( $fields['confirmation_field'] );
                        $confirmation_status = false;
                        if( $confirmation == 'Yes' ) {
                            $confirmation_status = true;
                        }
                        try {

                            $subscriber_data  = array(
                                'email' => $email,
                                'first_name' => $first_name_field,
                                'last_name' => $last_name_field
                            );
                            $options = array(
                                'send_confirmation_email' => $confirmation_status
                            );
                            $subscriber = \MailPoet\API\API::MP('v1')->addSubscriber( $subscriber_data, $lists, $options );
                         }
                         catch( Exception $exception ) {
                            echo $exception->getMessage();
                         }
                     }
                }
            }
        }

        Caldera_Forms::set_submission_meta('mailpoet', $result, $form, $config['processor_id'] );
    }
}

new MP_Cal_Functions();