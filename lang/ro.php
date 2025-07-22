<?php

return [
    'details_title' => '%s – Detalii',
    'list_title' => '%s – Listă',
    'edit_title' => 'Editează %s',
    'create_title' => 'Creează %s',
    
    'edit_button' => 'Editează',
    'view_button' => 'Vizualizează',
    'delete_button' => 'Șterge',
    'create_button' => 'Creează',
    'update_button' => 'Salvează',
    'cancel_button' => 'Anulează',
    'back_to_all' => 'Vezi toate %s',
    'add_new_button' => 'Adaugă %s nou(ă)',

// --- Authentication & Login/Register Page ---
    'login_button' => 'Autentificare',
    'logout_button' => 'Ieșire',
    'login_title' => 'Autentificare',
    'email_label' => 'Email',
    'password_label' => 'Parolă',
    'login_invalid_credentials' => 'Datele de autentificare sunt incorecte.',
    'auth_required' => 'Trebuie să fii autentificat pentru a accesa această pagină.',
    'remember_me' => 'Ține-mă minte',
    'forgot_password' => 'Ai uitat parola?',
    'continue_button' => 'Continuă',
    'or_separator' => 'SAU',
    'continue_with_google' => 'Continuă cu Google',
    'continue_with_microsoft' => 'Continuă cu Microsoft',
    'no_account_yet' => 'Nu ai încă un cont?',
    'register_now' => 'Înregistrează o companie nouă',

    // Registration
    'register_title' => 'Înregistrează o companie nouă',
    'cui_label' => 'CUI (Cod Unic de Înregistrare)',
    'agree_and_continue_button' => 'Creează contul companiei',
    'registration_success_new_company' => 'Compania și contul de administrator au fost create! Verifică-ți email-ul pentru a primi parola de acces.',
    'registration_error_cui_exists' => 'O companie cu acest CUI este deja înregistrată în sistem.',
    'registration_error_email_exists' => 'Un utilizator cu acest email este deja înregistrat.',
    'error_invalid_data' => 'Te rugăm să completezi toate câmpurile cu date valide.',
    'password_email_subject' => 'Datele tale de acces CRM',
    'password_email_body' => "Salut,\n\nContul tău de administrator pentru platforma CRM a fost creat.\n\nParola ta este: %s\n\nO poți schimba din setările contului după prima autentificare.\n\nO zi bună!",

    // Shared Auth Footer
    'help' => 'Ajutor',
    'terms_and_conditions' => 'Termeni și condiții',
    'privacy_and_cookies' => 'Confidențialitate și cookie-uri',
    'private_browser_notice' => 'Folosește un browser privat dacă acesta nu este dispozitivul tău.',
    'read_more' => 'Citește mai multe',


    //other confirmations and stuff
    'delete_confirm' => 'Ești sigur că vrei să ștergi acest element?',
    'record_id' => 'ID înregistrare',
    'actions' => 'Acțiuni',

    'success_create' => '%s a fost creat(ă) cu succes.',
    'success_update' => '%s a fost actualizat(ă) cu succes.',
    'success_delete' => '%s a fost șters(ă).',
    'error_generic' => 'A apărut o eroare.',

    // EntityController translations
    'entity_not_found' => 'Tipul de entitate „%s” nu a fost găsit.',
    'entity_not_found_generic' => 'Entitatea nu a fost găsită.',
    'missing_record_id' => 'ID-ul înregistrării lipsește.',
    'record_not_found_or_unauthorized' => 'Înregistrarea nu a fost găsită sau nu aparține companiei tale.',

    'add_related' => 'Adaugă %s',
    'related_entities' => 'Entități asociate',
    'no_related_found' => 'Nu există înregistrări asociate.',


    // Field Management
    'field_created_success'   => 'Câmpul a fost adăugat cu succes.',
    'field_create_error'      => 'A apărut o eroare la adăugarea câmpului.',
    'field_updated_success'   => 'Câmpul a fost actualizat cu succes.',
    'field_update_error'      => 'A apărut o eroare la actualizarea câmpului.',
    'field_deleted_success'   => 'Câmpul a fost șters cu succes.',
    'field_delete_error'      => 'A apărut o eroare la ștergerea câmpului.',
    'field_id_missing'        => 'ID-ul câmpului lipsește.',
    'field_not_found'         => 'Câmpul specificat nu a fost găsit.',
    'entity_type_missing'     => 'Tipul de entitate nu a fost specificat.',

    // Field views
    'field_list_title'        => 'Câmpuri pentru %s',
    'field_add_title'         => 'Adaugă câmp pentru %s',
    'field_edit_title'        => 'Editează câmp pentru %s',
    'field_name'              => 'Nume',
    'field_slug'              => 'Slug',
    'field_type'              => 'Tip',
    'field_required'          => 'Obligatoriu',
    'field_primary_label'     => 'Etichetă primară',
    'field_none_defined'      => 'Nu există câmpuri definite pentru această entitate.',
    'field_add_button'        => 'Adaugă câmp nou',
    'field_type_text'         => 'Text',
    'field_type_number'       => 'Număr',
    'field_type_date'         => 'Dată',
    'field_type_relation'     => 'Relație',
    'edit_button'             => 'Editează',
    'delete_button'           => 'Șterge',
    'save_button'             => 'Salvează',
    'cancel_button'           => 'Anulează',
    'confirm_delete_field'    => 'Sigur dorești să ștergi acest câmp?',

    // Entity management
    'entity_list_title'        => 'Entități disponibile',
    'entity_add_title'         => 'Adaugă entitate nouă',
    'entity_edit_title'        => 'Editează entitate',
    'entity_name'              => 'Nume entitate',
    'entity_slug'              => 'Slug',
    'entity_description'       => 'Descriere',
    'entity_created_success'   => 'Entitatea a fost creată cu succes.',
    'entity_create_error'      => 'A apărut o eroare la crearea entității.',
    'entity_updated_success'   => 'Entitatea a fost actualizată cu succes.',
    'entity_update_error'      => 'A apărut o eroare la actualizarea entității.',
    'entity_deleted_success'   => 'Entitatea a fost ștearsă.',
    'entity_delete_error'      => 'A apărut o eroare la ștergerea entității.',
    'entity_id_missing'        => 'ID-ul entității lipsește.',

    // Entity UI (continued)
    'entity_add_button'         => 'Adaugă entitate',
    'entity_none_defined'       => 'Nu există entități definite încă.',
    'confirm_delete_entity'     => 'Sigur dorești să ștergi această entitate?',

    //relationships
    'entity_relationships_title'  => 'Relații între entități',
    'add_relationship_button'     => 'Adaugă relație',
    'relationship_type'           => 'Tip relație',
    'parent_entity'               => 'Entitate părinte',
    'child_entity'                => 'Entitate copil',

    'related_parents' => 'Entități părinte',
    'toggle_section' => 'Afișează / ascunde',

    'manage_entities_button' => 'Administrează entitățile',
    'edit_fields_button' => 'Câmpuri',


];
