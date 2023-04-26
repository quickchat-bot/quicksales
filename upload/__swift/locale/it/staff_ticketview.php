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
    'titlemasterviewdel'            => 'Impossibile Eliminare "%d" Visualizzazioni',
    'msgmasterviewdel'              => 'Kayako non è in grado di eliminare le seguenti visuali master dei ticket:',
    'manageviews'                   => 'Visualizzazioni',
    'insertview'                    => 'Inserisci Visualizzazione',
    'viewtitle'                     => 'Titolo visualizzazione ticket',
    'desc_viewtitle'                => 'Immetti un titolo per la visuale (numeri, lettere e solo spazi).',
    'viewscope'                     => 'Visualizza lo Scopo',
    'createdby'                     => 'Autore',
    'editview'                      => 'Modifica Visualizzazione',
    'titleticketviewinsert'         => 'Creata visuale ticket (%s)',
    'msgticketviewinsert'           => 'La visuale ticket (%s) è stata creata con successo.',
    'titleticketviewupdate'         => 'Aggiornata visuale ticket (%s)',
    'msgticketviewupdate'           => 'La visuale ticket (%s) è stata aggiornata con successo.',
    'titleviewfieldempty'           => 'Nessuna colonna di visualizzazione ticket è stata specificata',
    'msgviewfieldempty'             => 'È necessario specificare almeno una colonna per la visualizzazione ticket (altrimenti non ci sarebbe nulla da visualizzare!).',

    'viewlinkdepartment'            => 'Collega questa visuale con i dipartimenti',
    'desc_viewlinkdepartment'       => 'Questa visuale può essere caricata automaticamente durante la navigazione dei ticket in uno dei reparti qui selezionati.',

    'taboptions'                    => 'Opzioni',
    'tabcolumns'                    => 'Colonne',

    'viewscope_global'              => 'Tutto il Personale',
    'viewscope_private'             => 'Solo Io',
    'viewscope_team'                => 'La Mia Squadra',

    'viewunassigned'                => 'Non Assegnati',
    'viewassigned'                  => 'Assegnati a Me',
    'viewalltickets'                => 'Tutti i Ticket',

    'viewassignedfield'             => 'Visualizza i ticket che sono assegnati a',
    'desc_viewassignedfield'        => '<strong>Non assegnati</strong> Ticket che non sono assegnati a qualcuno.<br /><strong>Assegnati a me</strong> Ticket assegnati a te.<br /><strong>Tutti i ticket</strong> Tutti i ticket, sia assegnati a te sia a qualcun altro o che non sono assegnati.',
    'viewscope'                     => 'Visualizza lo Scopo',
    'desc_viewscope'                => '<strong>Tutto il personale</strong> Tutto il personale sarà in grado di utilizzare questa visuale.<br /><strong>Solo io</strong> La visuale sarà disponibile solo a te.<br /><strong>La mia squadra</strong> La visuale sarà disponibile per te e gli altri della tua squadra del personale.',

    'viewselectfields'              => 'Seleziona le colonne (trascina e rilascia per organizzare nel riquadro giallo)',
    'viewsortoptions'               => 'Opzioni di ordinamento',
    'viewsortby'                    => 'Ordina per',
    'desc_viewsortby'               => 'Come saranno ordinati inizialmente i ticket quando si usa questa visuale?',
    'viewsortorder'                 => 'Tipo di ordine',
    'desc_viewsortorder'            => 'I ticket possono essere ordinati in ordine ascendente (dal più piccolo o dal più grande, dal più recente al meno recente, ecc) o discendente.',
    'viewticketsperpage'            => 'Ticket da visualizzare in una pagina',
    'desc_viewticketsperpage'       => 'Specifica il numero massimo di ticket da visualizzare per pagina (prima che venga diviso in più pagine).',
    'generaloptions'                => 'Opzioni Generali',
    'viewautorefresh'               => 'Aggiorna automaticamente l\'elenco dei ticket',
    'desc_viewautorefresh'          => 'Quando è caricata questa visuale, l\'elenco dei ticket può essere aggiornato automaticamente. Utile per l\'utilizzo come tipo pannelli.',
    'autorefresh_disable'           => '-- Disattiva Auto Aggiornamento --',
    'autorefresh_30s'               => 'Aggiorna ogni 30 secondi',
    'autorefresh_1m'                => 'Aggiorna ogni minuto',
    'autorefresh_5m'                => 'Aggiorna ogni 5 minuti',
    'autorefresh_15m'               => 'Aggiorna ogni 15 minuti',
    'autorefresh_30m'               => 'Aggiorna ogni 30 minuti',
    'autorefresh_1h'                => 'Aggiornare ogni ora',
    'viewsetasowner'                => 'Automaticamente imposta come proprietario del ticket l\'utente del personale attivo',
    'desc_viewsetasowner'           => 'Se è in uso questa visuale, quando si risponde o si inoltra un ticket il proprietario verrà impostato automaticamente sull\'utente del personale attivo.',
    'viewdefaultstatusonreply'      => 'Stato predefinito del ticket quando si risponde a un ticket',
    'desc_viewdefaultstatusonreply' => 'Specifica in quale stato verrà impostato automaticamente un ticket quando un utente del personale che utilizza questa visuale risponde o inoltra un ticket.',
    'defaultstatus_unspecified'     => '-- Non specificato --',

    'afterreply_topticketlist'      => 'Elenco ticket di livello massimo (mostra tutti i ticket)',
    'afterreply_activeticketlist'   => 'Elenco ticket attivi (nel dipartimento attuale o nei risultati di ricerca)',
    'afterreply_ticket'             => 'Il ticket attivo',
    'afterreply_nextticket'         => 'Prossimo ticket disponibile',
    'viewafterreplyaction'          => 'Dopo che si risponde ad un ticket, portami a',
    'desc_viewafterreplyaction'     => 'Seleziona dove dovrebbe portarti (o chiunque stia usando questa visuale) Kayako dopo aver risposto o inoltrato un ticket.',

    'sort_asc'                      => 'Crescente',
    'sort_desc'                     => 'Decrescente',

    // Potentialy unused phrases in staff_ticketview.php
    'viewfilteroptions'             => 'Filter Options',
    'viewfilterdepartments'         => 'Filter Department(s)',
    'desc_viewfilterdepartments'    => 'Kayako by default will display tickets from all/selected department. However, you can use this option to filter the tickets based on the selected departments.',
    'viewfiltertype'                => 'Filter Ticket Type(s)',
    'desc_viewfiltertype'           => 'You can use this option to filter the tickets based on the selected ticket type(s).',
    'viewfilterstatus'              => 'Filter Ticket Status(es)',
    'desc_viewfilterstatus'         => 'You can use this option to filter the tickets based on the selected ticket status(es).',
);


return $__LANG;
