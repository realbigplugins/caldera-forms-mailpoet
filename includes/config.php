<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'Caldera_Forms_Processor_UI' ) ) {
    echo Caldera_Forms_Processor_UI::config_fields( mp_caldera_processor_extra_meta_fields() ); 
}