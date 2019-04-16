<?php
/**
 * Plugin Name: Intrepid Tours
 * Description: Shows basic list of available tours.  Shortcode: [lastminutetours];  To manually update data got to: https://(yourwebsites.cm)/wp-content/plugins/intrepid-tours/get_feed.php

 This will update the data.  It takes a while, half an hour to an hour.
 * Version:     1.1
 * Author:      Lisa Armstrong
 * Author URI:  https://workingwebsites.ca/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
 //https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/

 /*
 The following are hard coded intrpedTours_vue.js
  Field id to populate trip info: tripinfo
 */

 //===== LOAD SCRIPTS =====//
 //Get Vue
 function intrpedTours_enqueue_vuejs() {
   $dev = 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js';
   $live = 'https://cdn.jsdelivr.net/npm/vue';

   //Register the script for future use.
   wp_register_script('intourVue', $dev);
 }
 add_action('wp_enqueue_scripts', 'intrpedTours_enqueue_vuejs');

 //Get Axios
 function intrpedTours_enqueue_axiosjs() {
   //Register the script for future use.
   wp_register_script('intourAxios', 'https://unpkg.com/axios/dist/axios.min.js');
 }
 add_action('wp_enqueue_scripts', 'intrpedTours_enqueue_axiosjs');

 //Get Vuejs script
 function intrpedTours_enqueue_intrpedjs() {
   //Register the script for future use.   Make sure it goes in the footer, after the DOM is loaded.
   wp_register_script('intourIntrped', plugin_dir_url( __FILE__ ). 'intrpedTours_vue.js', 'intrpedTours_enqueue_vuejs', false, true);
 }
 add_action('wp_enqueue_scripts', 'intrpedTours_enqueue_intrpedjs', true);



//===== ADD SHORTCODE =====//
function intrpedTours_scTable($atts){
  //Load the script when the shortcode is called;
  wp_enqueue_script('intourVue');
  wp_enqueue_script('intourAxios');
  wp_enqueue_script('intourMoment');
  wp_enqueue_script('intourIntrped');

  //Pass the path to vue
  wp_localize_script('intourIntrped', 'intourJSVars', array(
    'pluginsUrl' => plugin_dir_url( __FILE__ ),
  ));

  //Build html.  Checks if file exists in theme.
  if(file_exists(get_stylesheet_directory().'/intrepid-tours/templates/lastminute_table.html')){
    $strTemplateTable = file_get_contents (get_stylesheet_directory().'/intrepid-tours/templates/lastminute_table.html');
  }else{
    $strTemplateTable = file_get_contents (plugin_dir_path(__FILE__).'templates/lastminute_table.html');
  }

  if(file_exists(get_stylesheet_directory().'/intrepid-tours/templates/lastminute_table.html')){
    $strTemplateMoreinfo = file_get_contents (get_stylesheet_directory().'/intrepid-tours/templates/lastminute_moreinfo.html');
  }else{
    $strTemplateMoreinfo = file_get_contents (plugin_dir_path(__FILE__).'templates/lastminute_moreinfo.html');
  }


  $strDisplay = '<div id="intourApp">
                  <lastminute-table v-on:select-trip="getSelected" v-show="showList"></lastminute-table>
                  <lastminute-moreinfo
                    v-bind:seltrip="selTrip"
                    v-on:view-list="displayList"
                    v-on:call-to-action="calltoAction"
                    v-show="showDetail"
                  ></lastminute-moreinfo>
                </div>';
  //Return HTML
	return $strTemplateTable
        .$strTemplateMoreinfo
        .$strDisplay;
}
add_shortcode( 'lastminutetours', 'intrpedTours_scTable' );
 ?>
