<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MP_Cal_Functions {
    
    /**
	 * Initialization
	 */
    public function __construct() {
        
        // add filter to register addon with Caldera Forms
        add_filter( 'caldera_forms_get_form_processors', array( $this, 'mp_acl_registration_addon'), 50 );
    }

    /**
     * Registers the Mail Poet Processor
     *
     * @param $processors
     * @return array
     */
    function mp_acl_registration_addon( $processors ) {
        
        $processors['mailpoet'] = array(
            'name'				=>	__( 'MailPoet', 'mp_cal_addon' ),
            'description'		=>	__( 'Integrates a form with MailPoet', 'mp_cal_addon'),
            'icon'				=>	'',
            'author'			=>	'WooNinjas',
            'author_url'		=>	'https://calderaforms.com',
            'post_processor'	=>	array( $this, 'cf_mailpoet_post_process' ),
            'template'			=>	MP_CAL_INCLUDES_DIR . 'config.php',
        );
        return $processors;
    }


    /**
     * Processes form and push to MailPoet
     *
     * @param array		$config			Config array of the processor
     * @param array		$form			array of the complete form config structure
     *
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
                        $first_name_field     = Caldera_Forms::get_field_data( $fields['first_name_field'], $form );
                        $last_name_field      = Caldera_Forms::get_field_data( $fields['last_name_field'], $form );
                        $lists              = array( $fields['list_id'] );
                        $confirmation       = array( $fields['confirmation_field'] );
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