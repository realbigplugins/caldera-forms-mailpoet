<?php 

if ( class_exists( 'Caldera_Forms_Processor_UI' ) ) {
    echo Caldera_Forms_Processor_UI::config_fields( MP_Cal_Functions::my_processor_extra_meta_processor_fields() ); 
}
