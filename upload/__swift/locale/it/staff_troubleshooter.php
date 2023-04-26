<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'troubleshooter'             => 'Risolutore problematiche',
    'categories'                 => 'Categorie',
    'titletrcatmismatch'         => 'Esiste già una categoria chiamata %s',
    'msgtrcatmismatch'           => 'Esiste già una categoria del risolutore con il nome %s. Scegli un\'altra categoria.',
    'wininsertcategory'          => 'Inserisci Categoria',
    'wineditcategory'            => 'Modifica Categoria: %s',
    'insertcategory'             => 'Inserisci Categoria',
    'editcategory'               => 'Modifica Categoria',

    'titletrcategoryinsert'      => 'Creata categoria del risolutore (%s)',
    'msgtrcategoryinsert'        => 'Categoria risolutore (%s) creata con successo.',
    'titletrcategoryupdate'      => 'Aggiornata categoria risolutore (%s)',
    'msgtrcategoryupdate'        => 'Categoria risolutore (%s) aggiornata con successo.',

    'trcategory'                 => 'Nome categoria',
    'desc_trcategory'            => 'Per esempio, <em>Il mio dispositivo non si accende</em>.',
    'next'                       => 'Avanti',

    'tabgeneral'                 => 'Generale',
    'taboptions'                 => 'Opzioni',
    'categorytype'               => 'Visibilità',
    'desc_categorytype'          => '<strong>Globale</strong> Disponibile per tutti incluso il personale e gli utenti finali.<br /><strong>Pubblico</strong> Disponibile solo agli utenti finali nel centro di supporto.<br /><strong>Privato</strong> Disponibile solo al personale nel pannello di controllo del personale.',
    'displayorder'               => 'Ordine di visualizzazione',
    'desc_displayorder'          => 'Le categorie del risolutore sono elencate in base al numero di ordine visualizzato, dal più piccolo al più grande.',
    'tabpermissionsuser'         => 'Autorizzazioni: Utenti',
    'tabpermissionsstaff'        => 'Autorizzazioni: Personale',
    'uservisibilitycustom'       => 'Limita a specifici gruppi di utenti',
    'desc_uservisibilitycustom'  => 'Se attivo, solo i gruppi di utenti qui sotto saranno in grado di vedere questa categoria.',
    'staffvisibilitycustom'      => 'Limita a specifiche squadre del personale',
    'desc_staffvisibilitycustom' => 'Se attivo, solo le squadre qui sotto saranno in grado di vedere questa categoria.',
    'usergroups'                 => 'Gruppi di Utenti',
    'staffteams'                 => 'Squadre del Personale',

    'tablist'                    => 'Elenco',
    'tabtree'                    => 'Albero',
    'steptitle'                  => 'Titolo del Passaggio',
    'categorytitle'              => 'Categoria',
    'author'                     => 'Autore',
    'stepstatus'                 => 'Stato',

    /**
     * ---------------------------------------------
     * INSERT TROUBLESHOOTER
     * ---------------------------------------------
     */
    'titletrstepinsert'          => 'Creato passaggio del risolutore (%s)',
    'msgtrstepinsert'            => 'Il passaggio del risolutore (%s) è stato creato con successo.',
    'titletrstepupdate'          => 'Aggiornato passaggio del risolutore (%s)',
    'msgtrstepupdate'            => 'Il passaggio del risolutore (%s) è stato aggiornato con successo.',
    'insertstep'                 => 'Inserisci Passaggio',
    'recursionsuffix'            => ' (Possibile Ricorsione)',
    'parentcategoryitem'         => '- Categoria Root -',
    'desc_parentsteps'           => '',
    'tabgeneral'                 => 'Generale',
    'tabattachments'             => 'Allegati',
    'taboptions'                 => 'Opzioni',
    'publish'                    => 'Pubblica',
    'published'                  => 'Pubblicato',
    'draft'                      => 'Bozza',
    'infobauthor'                => 'AUTORE',
    'infobcreationdate'          => 'CREATO IL',
    'infobeditedby'              => 'MODIFICATO DA',
    'infobeditedon'              => 'MODIFICATO IL',
    'ftarticletype'              => 'Tipo',
    'ftcategories'               => 'Categorie',
    'ftdate'                     => 'Data',
    'informationbox'             => 'Informazioni',
    'quickfilter'                => 'Filtro Rapido',
    'markasdraft'                => 'Contrassegna come Bozza',
    'saveasdraft'                => 'Salva come Bozza',
    'addfile'                    => 'Aggiungi File',
    'steptitle'                  => 'Titolo del Passaggio',
    'desc_steptitle'             => '',
    'parentsteps'                => 'Passaggi genitore',
    'desc_parentsteps'           => '',
    'generalsettings'            => 'Impostazioni Generali',
    'allowcomments'              => 'Consenti commenti',
    'desc_allowcommentsstep'     => 'Se saranno consentiti commenti in questo passaggio.',
    'noticketrediractive'        => '-- Nessun Dipartimento Selezionato --',
    'ticketredirectionsettings'  => 'Impostazioni di Reindirizzamento dei Ticket',
    'redirecttickets'            => 'Reindirizza al ticket in questo passaggio',
    'desc_redirecttickets'       => 'Abilita questa impostazione per invitare l\'utente a creare un ticket appena raggiunge questo passaggio (cioè l\'ultima fermata).',
    'redirectdepartment'         => 'Dipartimento del ticket',
    'desc_redirectdepartment'    => 'Se nessun dipartimento è selezionato, all\'utente verrà chiesto di selezionare il proprio.',
    'ticketsubject'              => 'Oggetto del ticket',
    'desc_ticketsubject'         => 'Se non viene specificato nessun oggetto nel ticket, l\'utente sarà in grado di inserirne uno.',
    'tickettype'                 => 'Tipo di ticket',
    'desc_tickettype'            => 'Specifica il tipo di ticket che verrà utilizzato quando l\'utente che raggiunge questo passaggio viene invitato a creare un ticket.',
    'ticketpriority'             => 'Priorità del ticket',
    'desc_ticketpriority'        => 'Se nessuna priorità viene specificata, l\'utente sarà in grado di selezionare la priorità del ticket (se applicabile).',
    'nochange'                   => '-- Nessun Cambiamento --',

    /**
     * ---------------------------------------------
     * VIEW ALL
     * ---------------------------------------------
     */
    'tabviewall'                 => 'Risolutore problematiche',
    'troubleshooterdesc'         => 'Le procedure guidate del risolutore ti guideranno attraverso dei passi per diagnosticare e risolvere problemi comuni. Inizia scegliendo una categoria del risolutore.',
    'trattachments'              => 'Allegati',
    'trnextsteps'                => 'Prossimi Passaggi',
    'trnext'                     => 'Avanti',
    'trback'                     => 'Indietro',
    'steps'                      => 'Passaggi',
    'trcategoryviews'            => ' (%d Visualizzazioni)',

    // Potentialy unused phrases in staff_troubleshooter.php
    'desc_categorytitle'         => 'Enter the category title',

);

return $__LANG;
