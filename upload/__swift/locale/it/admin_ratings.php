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
    'ratings'                     => 'Valutazioni',
    'tabgeneral'                  => 'Generale',
    'tabpermissionsstaff'         => 'Autorizzazioni (Personale)',
    'tabpermissionsuser'          => 'Autorizzazioni (Utenti)',
    'staffgroups'                 => 'Squadre del Personale',
    'usergroups'                  => 'Gruppi di Utenti',
    'ratingticket'                => 'Ticket',
    'ratingticketpost'            => 'Ticket pubblicati',
    'ratingchatsurvey'            => 'Sondaggio chat',
    'ratingchathistory'           => 'Cronologia chat',

    'staffvisibilitycustom'       => 'Limita la modifica delle valutazioni a certe squadre di personale',
    'desc_staffvisibilitycustom'  => 'Se attivo, solo le squadre qui sotto saranno in grado di modificare e impostare i valori per questa valutazione.',

    'buservisibilitycustom'       => 'Limita la valutazione a gruppi di utenti specifici',
    'desc_buservisibilitycustom'  => 'Se attivo, solo i gruppi di utenti qui sotto saranno in grado di modificare e impostare i valori per questa valutazione.',

    'bstaffvisibilitycustom'      => 'Limita le valutazioni a specifiche squadre del personale',
    'desc_bstaffvisibilitycustom' => 'Se attivo, solo le squadre qui sotto saranno in grado di visualizzare questa valutazione.',

    'isclientonly'                => 'Impedisci al personale di impostare i valori per questa valutazione',
    'desc_isclientonly'           => 'Se attivo, solo gli utenti saranno in grado di impostare i valori per questa valutazione.',

    'ratingscale'                 => 'Scala di valutazione',
    'desc_ratingscale'            => 'Il voto massimo che può essere dato. Ad esempio, se è selezionato <em>5</em>, il voto più basso possibile sarà 1 e il più alto 10.',

    'ratingtype'                  => 'Zona di helpdesk',
    'desc_ratingtype'             => 'L\'area dell\'helpdesk al quale si applica questa valutazione. <br /><br /><strong>Ticket:</strong> Utilizzato per valutare interi ticket.<br /><strong>Messaggio ticket:</strong> Utilizzato per valutare le risposte individuali dei ticket.<br /><strong>Sondaggio chat:</strong> Le valutazioni saranno aggiunte al sondaggio post chat.<br /><strong>Cronologia chat:</strong> Utilizzato per valutare le registrazioni di chat nel pannello di controllo del personale (destinato alla valutazione e il controllo interno).',

    'ratingtitle'                 => 'Etichetta di valutazione',
    'desc_ratingtitle'            => 'Per esempio, <em>Soddisfazione complessiva</em> o <em>Celerità di risoluzione</em>.',

    'displayorder'                => 'Ordine di visualizzazione',
    'desc_displayorder'           => 'Più valutazioni possono comparire nello stesso posto. Verranno visualizzati secondo il numero del loro ordine di visualizzazione, dal più piccolo al più grande.',

    'ratingdep'                   => 'Limita la valutazione ad un dipartimento',
    'desc_ratingdep'              => 'Seleziona un dipartimento per limitare la valutazione di ticket o chat appartenenti ad uno dipartimento specifico. Se non viene selezionato nessun dipartimento, la valutazione apparirà in tutti i reparti.',
    'ratingalldep'                => '-- Tutti i dipartimenti --',

    'iseditable'                  => 'Consenti la modifica dopo la prima presentazione',
    'desc_iseditable'             => 'Il valore impostato per questa valutazione può essere modificato, se attivato.',

    'ratingvisibility'            => 'Chi può inviare una valutazione?',
    'desc_ratingvisibility'       => '<strong>Pubblico</strong> Le valutazioni sono disponibili a tutti gli utenti. Seleziona questa opzione se desideri raccogliere feedback da utenti finali mentre le valutazioni rimangono visibili anche al personale.<br /><strong>Privato</strong> Le valutazioni sono disponibili solo agli utenti del personale, per scopi di valutazione interna.',

    'ratingvis'                   => 'Visibilità',
    'insertrating'                => 'Inserisci Valutazione',
    'desc_insertrating'           => '',
    'manageratings'               => 'Gestione',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Modifica valutazione: %s',
    'editrating'                  => 'Modifica valutazione',
    'desc_editrating'             => '',
    'titledelrating'              => 'Valutazioni eliminate: (%d)',
    'msgdelrating'                => 'Le seguenti valutazioni e tutti i rispettivi dati sono stati eliminati:',
    'titleratinginsert'           => 'Valutazione creata',
    'msgratinginsert'             => 'La valutazione (%s) è stata creata con successo.',
    'titleratingupdate'           => 'Valutazione aggiornata',
    'msgratingupdate'             => 'La valutazione (%s) è stata aggiornata con successo.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'Knowledgebase',
    'ratingtroubleshooter'        => 'Risolutore problematiche',
    'ratingnews'                  => 'Notizie',
);


return $__LANG;
