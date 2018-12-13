<?php
/**
 * Plugin Name: CWN Daily Mail
 * Plugin URI: https://coworking-neuchatel.ch
 * Description: Send daily news email.
 * Version: 0.0.1+build20181213
 * Author: Manuel Schmalstieg
 * Author URI: https://ms-studio.net
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


// Good Info:
// http://wordpress.stackexchange.com/questions/179694/wp-schedule-event-every-day-at-specific-time

// wp_clear_scheduled_hook('cowork_task_hook');

if ( !wp_next_scheduled( 'cowork_task_hook' ) ) {

  wp_schedule_event( 
    strtotime('16:30:00'), 
    'daily', // daily hourly
    'cowork_task_hook' 
  );

  // note: wp_schedule_event should always take GMT!
  // donc si on veut l'envoi à 17:30, il faut mettre 16:30
}

add_action( 'cowork_task_hook', 'cowork_daily_task' );


function cowork_daily_task() {

  // Coworkers présents demain?

  $presences_du_jour = FrmProDisplaysController::get_shortcode(array('id' => 993));
  
  $reservations_du_jour = FrmProDisplaysController::get_shortcode(array('id' => 1008));
  
  // Editer la vue: 
  // https://coworking-neuchatel.ch/wp-admin/post.php?post=993&action=edit
  
  $cwn_has_news = 'false';
  $cwn_no_entries = '<div class="frm_no_entries">Aucune entrée trouvée</div>';
  
  if ( ( $presences_du_jour != $cwn_no_entries ) || ( $reservations_du_jour != $cwn_no_entries ) ) {
       
    $cwn_has_news = 'true';
  
  }
  
  // test if = 
    
  if ( $cwn_has_news == 'true' ) {
        
        
        $tomorrow = mktime( 0, 0, 0, date("m"), date("d")+1, date("Y") );
        $tomorrow = date_i18n( 'l j F', $tomorrow ); // lundi 7 décembre
        
        $to = 'newsletter@example.com';
        $headers[] = 'From: Coworking Neuchâtel <info@example.com>';
        $subject = '[Coworking] Daily News';
        
        // NOTE: on pourrait automatiquement obtenir les adresses email des coworkers actifs, via le plugin WooCommerce Memberships!
        
        $message = 'Cher coworkers,
';
      
      if ( $presences_du_jour != $cwn_no_entries ) {
        
        $message .= '
Demain, '.$tomorrow.', travailleront au coworking Neuchâtel:

';
        
        $message .= $presences_du_jour;
        
      }
      
      if ( $reservations_du_jour != $cwn_no_entries ) {
      
      // Message salle de réunion:
      
        if ( $presences_du_jour == $cwn_no_entries ) {
        
        // Pas de présences
        
          $message .= '
Demain, '.$tomorrow.', la salle de réunion du coworking sera occupée de la manière suivante:

';
        
        } else {
        
        // On a déjà eu le message des présences
        
        $message .= '
La salle de réunion sera occupée de la manière suivante:

';
        
        }
        
        $message .= $reservations_du_jour;
      }
      
        $message .= '
Au plaisir,
L’assistant automatisé du coworking
';
// PS: heure actuelle '. date('H:i:s') .'
        
        wp_mail( 
          $to,
          $subject,
          $message, 
          $headers 
        );
        
    } // end if news == true

} // end cowork_daily_task()