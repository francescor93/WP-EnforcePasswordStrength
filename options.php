<?php

// CREO LA PAGINA DI MENU
add_action('admin_menu', 'eps_main_menu');
function eps_main_menu() {
    add_options_page(
        'Enforce Password Strength', // TITOLO DELLA PAGINA NEL TAG TITLE E NELL'H1
        'Enforce Password Strength', // NOME MOSTRATO NEL MENU AMMINISTRATIVO GENERALE
        'manage_options', // PERMESSI NECESSARI PER VISUALIZZARE IL MENU
        'eps-menu', // SLUG DEL MENU
        'eps_show_page' // FUNZIONE CHE MOSTRA IL MENU
    );
}

// FUNZIONE CHE MOSTRA LA PAGINA DI MENU
function eps_show_page() {
    
    // SE L'UTENTE NON HA I PERMESSI PER VISUALIZZARLO ESCO
    if (!current_user_can('manage_options')) {
        return;
    }

    // MOSTRO I MESSAGGI
    settings_errors('eps_options');

    // MOSTRO IL CONTENITORE E IL FORM
    echo '
    <div class="wrap">
        <h1>' . esc_html(get_admin_page_title()) . '</h1>
        <form action="options.php" method="post">';

            // GENERO I CAMPI NONCE E ALTRI ELEMENTI NECESSARI AL FORM
            settings_fields('eps');
            
            // MOSTRO IL BLOCCO DI IMPOSTAZIONI PRECEDENTEMENTE GENERATO
            do_settings_sections('eps-menu');
    
            // MOSTRO IL PULSANTE SALVA
            submit_button(__('Save Settings', 'enforce-password-strength'));
            echo '
        </form>
    </div>';
}

// AL CARICAMENTO DELL'AMMINISTRAZIONE AGGIUNGO LA SEZIONE DI CONFIGURAZIONE EPS
add_action('admin_init', 'eps_settings_init');
function eps_settings_init() {

    // CREO IL NUOVO GRUPPO DI IMPOSTAZIONI "EPS OPTIONS"
    register_setting('eps', 'eps_options', array('sanitize_callback' => 'eps_validate'));

    // CREO UNA NUOVA SEZIONE PER L'IMPOSTAZIONE DEI REQUISITI NELLA PAGINA DI OPZIONI DI EPS
    add_settings_section(
        'eps_settings', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Minimum Requirements', 'enforce-password-strength'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'eps_show_settings', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'eps-menu' // PAGINA A CUI ASSEGNARE L'ELEMENTO
    );

    // NELLA SEZIONE CREATA CREO I NUOVI CAMPI PER L'INPUT DELL'UTENTE
    add_settings_field(
        'eps_uppercase', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Uppercase characters', 'enforce-password-strength'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'eps_show_contain', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'eps-menu', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'eps_settings', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'eps_uppercase', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'label_text' => __('Require at least one uppercase character in password', 'enforce-password-strength')
        )
    );
    add_settings_field(
        'eps_lowercase', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Lowercase characters', 'enforce-password-strength'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'eps_show_contain', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'eps-menu', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'eps_settings', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'eps_lowercase', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'label_text' => __('Require at least one lowercase character in password', 'enforce-password-strength')
        )
    );
    add_settings_field(
        'eps_number', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Numbers', 'enforce-password-strength'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'eps_show_contain', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'eps-menu', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'eps_settings', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'eps_number', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'label_text' => __('Require at least one number in password', 'enforce-password-strength')
        )
    );
    add_settings_field(
        'eps_symbol', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Symbols', 'enforce-password-strength'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'eps_show_contain', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'eps-menu', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'eps_settings', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'eps_symbol', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'label_text' => __('Require at least one symbol in password', 'enforce-password-strength')
        )
    );
    add_settings_field(
        'eps_length', // ID DA ASSEGNARE ALL'ELEMENTO
        __('Length', 'enforce-password-strength'), // TITOLO DA MOSTRARE SULL'ELEMENTO
        'eps_show_length', // FUNZIONE CHE MOSTRA L'ELEMENTO
        'eps-menu', // PAGINA A CUI ASSEGNARE L'ELEMENTO
        'eps_settings', // SEZIONE A CUI ASSEGNARE L'ELEMENTO
        array(
            'label_for' => 'eps_length', // VALORE DEL CAMPO "FOR" PER I TAG LABEL
            'label_text_before' => __('Minimum required length for the password', 'enforce-password-strength'),
            'label_text_after' => __('characters', 'enforce-password-strength')
        )
    );
}

// FUNZIONE CHE MOSTRA LA DESCRIZIONE DELLA SEZIONE DI CONFIGURAZIONE
function eps_show_settings($args) {
    echo '<p>' . __('Sets the minimum requirements you want to apply to the users passwords', 'enforce-password-strength') . '</p>';
}

// FUNZIONE CHE MOSTRA I CHECKBOX DELLA SEZIONE DI CONFIGURAZIONE
function eps_show_contain($args) {
    
    // OTTENGO I VALORI SALVATI ATTUALI
    $options = get_option('eps_options');
    
    // MOSTRO GLI ELEMENTI PER L'INSERIMENTO
    echo '
    <input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="eps_options[' . esc_attr($args['label_for']) . ']"' . ((((isset($options[esc_attr($args['label_for'])]))) AND ($options[esc_attr($args['label_for'])] == "1")) ? 'checked' : '') .  '>
    <label for="' . esc_attr($args['label_for']) . '">' . $args['label_text'] . '</label>';
}

// FUNZIONE CHE MOSTRA L'INPUT NUMBER DELLA SEZIONE DI CONFIGURAZIONE
function eps_show_length($args) {
    
    // OTTENGO I VALORI SALVATI ATTUALI
    $options = get_option('eps_options');
    
    // MOSTRO GLI ELEMENTI PER L'INSERIMENTO
    echo '
    <label for="' . esc_attr($args['label_for']) . '">' . $args['label_text_before'] . '</label>
    <input type="number" min="1" max="64" id="' . esc_attr($args['label_for']) . '" name="eps_options[' . esc_attr($args['label_for']) . ']"' . ((isset($options[esc_attr($args['label_for'])])) ? 'value="' . $options[esc_attr($args['label_for'])] . '"' : '') .  '>
    <label for="' . esc_attr($args['label_for']) . '">' . $args['label_text_after'] . '</label>';
}

// FUNZIONE CHE CONTROLLA L'INPUT DELL'UTENTE AL SALVATAGGIO
function eps_validate($input) {
    
    // CREO UN ARRAY VUOTO IN CUI SALVARE I VALORI CORRETTI
    $sanitizedinputs = array();
    
    // ELABORO I CHECKBOX
    $sanitizedinputs['eps_uppercase'] = (isset($input['eps_uppercase'])) ? '1' : '0';
    $sanitizedinputs['eps_lowercase'] = (isset($input['eps_lowercase'])) ? '1' : '0';
    $sanitizedinputs['eps_number'] = (isset($input['eps_number'])) ? '1' : '0';
    $sanitizedinputs['eps_symbol'] = (isset($input['eps_symbol'])) ? '1' : '0';
    
    // ELABORO L'INPUT PER LA LUNGHEZZA
    $sanitizedinputs['eps_length'] = (filter_var($input['eps_length'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range"=> 64)))) ? $input['eps_length'] : '8';
    
    // RESTITUISCO I VALORI DA SALVARE NEL DATABASE
    return $sanitizedinputs;
}