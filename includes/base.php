<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class CF_MailPoet_Base
 */
class CF_MailPoet_Base extends Caldera_Forms_Processor_Processor implements Caldera_Forms_Processor_Interface_Process {

	public function pre_processor( array $config, array $form, $proccesid ){

        /**
         * Setup $this->data_object
         */
		$this->set_data_object_initial( $config, $form );
        
        /**
         * At this point errors would be beacuse of missing requirments
         */
		$errors = $this->data_object->get_errors();
		if ( ! empty( $errors ) ) {
			return $errors;
		}

		/**
         * Get all processor field values as an array
         */
		$values = $this->data_object->get_values();
        if( isset( $config['processor_id'] ) && !empty( $config['processor_id'] ) ) {
            $processor_id = $config['processor_id'];
            if( is_array( $form['processors'] ) && count( $form['processors'] ) > 0 ) {
                $processor = $form['processors'][ $processor_id];
                if( !empty( $processor['type'] ) && $processor['type'] == 'mailpoet-caldera-integration' ) {

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
                            $this->data_object->add_error( __( $exception->getMessage(), 'mp_cal_addon' ) );
                        }
                    } else {
                        $this->data_object->add_error( __( 'Invalid Form', 'mp_cal_addon' ) );
                    }
                }
            }
        }
        $errors = $this->data_object->get_errors();
        if ( ! empty( $errors ) ) {
			return $errors;
		}
        
        /**
         * Before ending this method, store the processor data in the transient
         */
		$this->setup_transata( $proccesid );
	}



	/**
	 *  Process callback
	 *
	 * @param array $config Processor config
	 * @param array $form Form config
	 * @param string $proccesid Process ID
	 *
	 * @return array
	 */
	public function processor( array $config, array $form, $proccesid ){

	}
	
}
