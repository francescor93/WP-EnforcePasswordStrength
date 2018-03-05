<?php
/*
* Plugin Name: Enforce Password Strength
* Description: Check the password during registration, login and reset, enforcing them to contain at least an uppercase, a lowercase, a number, a symbol and to have a minimum length of eight characters.
* Author: Francesco Rega
* Author URI: https://github.com/francescor93
* Version: 1.0.0
*/

// INCLUDO LA TRADUZIONE
add_action('plugins_loaded', 'loadEpsTranslation');
function loadEpsTranslation() {
	load_plugin_textdomain('enforce-password-strength', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// CREO IL LINK ALLE IMPOSTAZIONI
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'epsSettingsLink');
function epsSettingsLink($links) {
    $settings_link = '<a href="options-general.php?page=eps-menu">' . __('Settings') . '</a>';
    array_push($links, $settings_link);
  	return $links;
}

// INCLUDO IL FILE CHE GESTISCE LE IMPOSTAZIONI
require __DIR__ . '/options.php';

 
// AGGIUNGO I FILTRI DURANTE L'AGGIORNAMENTO DEL PROFILO, LA REGISTRAZIONE E IL RESET DELLA PASSWORD
add_action('user_profile_update_errors', 'validateProfileUpdate', 10, 3);
add_filter('registration_errors', 'validateRegistration', 10, 3);
add_action('validate_password_reset', 'validatePasswordReset', 10, 2);
add_filter('wp_authenticate_user', 'validateLoginPassword',10,2);

// IMPOSTO LE OPZIONI DI DEFAULT SE NON SONO DEFINITE
register_activation_hook(__FILE__, 'initEps');
function initEps() {
    if (!get_option('eps_options')) {
        $default = array(
            'eps_uppercase' => '1',
            'eps_lowercase' => '1',
            'eps_number' => '1',
            'eps_symbol' => '1',
            'eps_length' => '8'
        );
        add_option('eps_options', $default);
    }
}

// ALL'AGGIORNAMENTO DEL PROFILO RESTITUISCO LA SICUREZZA DELLA PASSWORD
function validateProfileUpdate(WP_Error &$errors, $update, &$user) {
    return validateComplexPassword($errors);
}
 
// ALLA REGISTRAZIONE RESTITUISCO LA SICUREZZA DELLA PASSWORD
function validateRegistration(WP_Error &$errors, $sanitized_user_login, $user_email) {
    return validateComplexPassword($errors);
}

// AL RESET DELLA PASSWORD RESTITUISCO LA SICUREZZA DELLA PASSWORD
function validatePasswordReset(WP_Error $errors, $userData) {
    return validateComplexPassword($errors);
}

// AL LOGIN CORRETTO VERIFICO ANCHE SE LA PASSWORD RISPETTA I REQUISITI MINIMI
function validateLoginPassword ($user, $password) {
    
    // SE NON SUPERA IL CONTROLLO DEI REQUISITI MINIMI DI SICUREZZA RESTITUISCO UN ERRORE
    if (!isStrongPassword($password)) {
        $isWeakPassword = new WP_Error('weakpass', __('<strong>ERROR</strong>: This password does not meet the minimum safety requirements. If you have already reset it to the new requirements make sure you have written it correctly; otherwise ask now for a reset.', 'enforce-password-strength'));
        return $isWeakPassword;
    }
    
    // OPPURE RESTITUISCO IL DATO RICEVUTO IN INGRESSO
    return $user;
}

// FUNZIONE CHE CONTROLLA LA SICUREZZA GENERALE DI UNA PASSWORD SALVATA
function validateComplexPassword($errors) {
    
    // RECUPERO LA PASSWORD DAL FORM, SE DEFINITA, ALTRIMENTI LA CONSIDERO NULLA
	$password = ((isset($_POST['pass1'])) AND (trim($_POST['pass1']))) ? $_POST['pass1'] : null;
    
	// SE NON HO UNA PASSWORD O CI SONO ERRORI PRECEDENTI TERMINO
	if ((empty($password)) OR ($errors->get_error_data('pass'))) {
		return $errors;
    }
 
	// SE DALL'INVIO AL VALIDATORE HO UN RISULTATO NEGATIVO
	if (!isStrongPassword($password)) {
        
        // RECUPERO LE OPZIONI DEL PLUGIN
        $options = get_option('eps_options');
        
        // IMPOSTO IL MESSAGGIO INIZIALE
        $errormessage = __('ERROR: New password must ', 'enforce-password-strength');

        
        // SE HO REQUISITI DI CONTENUTO
        if (($options['eps_uppercase'] == 1) OR ($options['eps_lowercase'] == 1) OR ($options['eps_number'] == 1) OR ($options['eps_symbol'] == 1)) {
            
            // INDICO CHE DEVE AVERE CONTENUTO
            $errormessage .= __('contain at least ', 'enforce-password-strength');
            
            // AGGIUNGO OGNI VALORE ATTIVATO
            if ($options['eps_uppercase'] == 1) {
                $errormessage .= __('an uppercase, ', 'enforce-password-strength');
            }
            if ($options['eps_lowercase'] == 1) {
                $errormessage .= __('a lowercase, ', 'enforce-password-strength');
            }
            if ($options['eps_number'] == 1) {
                $errormessage .= __('a number, ', 'enforce-password-strength');
            }
            if ($options['eps_symbol'] == 1) {
                $errormessage .= __('a symbol, ', 'enforce-password-strength');
            }
            
            // CONTINUO IL MESSAGGIO
            $errormessage .= __('and must ', 'enforce-password-strength');
        }
        
        // IN OGNI CASO AGGIUNGO IL VALORE DELLA LUNGHEZZA
        $errormessage .= sprintf(__('have a minimum length of %d characters', 'enforce-password-strength'), $options['eps_length']);
        
        $errors->add('pass', $errormessage);
    }

    // RESTITUISCO LA RISPOSTA CON EVENTUALI ERRORI
	return $errors;
}
 
// FUNZIONE CHE CONTROLLA SE UNA PASSWORD RISPETTA I REQUISITI MINIMI DI SICUREZZA
function isStrongPassword($password) {
    
    // RECUPERO LE OPZIONI DEL PLUGIN
    $options = get_option('eps_options');
    
    // VERIFICO SE LA PASSWORD CONTIENE LETTERE MAIUSCOLE, SE HO LA RELATIVA OPZIONE ATTIVA
    if ($options['eps_uppercase'] == 1) {
        if (preg_match ('/[A-Z]/', $password) !== 1) {
            return false;
        }
    }
    
    // VERIFICO SE LA PASSWORD CONTIENE LETTERE MINUSCOLE, SE HO LA RELATIVA OPZIONE ATTIVA
    if ($options['eps_lowercase'] == 1) {
        if (preg_match ('/[a-z]/', $password) !== 1) {
            return false;
        }
    }
    
    // VERIFICO SE LA PASSWORD CONTIENE NUMERI, SE HO LA RELATIVA OPZIONE ATTIVA
    if ($options['eps_number'] == 1) {
        if (preg_match ('/[0-9]/', $password) !== 1) {
            return false;
        }
    }
    
    // VERIFICO SE LA PASSWORD CONTIENE SIMBOLI, SE HO LA RELATIVA OPZIONE ATTIVA
    if ($options['eps_symbol'] == 1) {
        if (preg_match ('/[^A-Za-z0-9\s]/', $password) !== 1) {
            return false;
        }
    }

    // VERIFICO SE LA PASSWORD HA UNA LUNGHEZZA SUPERIORE O UGUALE A QUELLA IMPOSTATA NELLE OPZIONI
    if (strlen($password) <= $options['eps_length']) {
        return false;
    }
    
	// SE TUTTI I REQUISITI SONO STATI SUPERATI RESTITUISCO UNA RISPOSTA AFFERMATIVA
    return true;
}