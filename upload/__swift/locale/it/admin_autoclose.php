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
    'autoclose'                    => 'Chiusura automatica',
    'manage'                       => 'Gestione',
    'targetstatus'                 => 'Stato al quale cambiare',
    'tabgeneral'                   => 'Generale',

    'insertrule'                   => 'Inserisci Regola',
    'editrule'                     => 'Modifica Regola',

    'ruletitle'                    => 'Titolo regola',
    'desc_ruletitle'               => 'Inserire un titolo per questa regola di chiusura automatica.',
    'targetticketstatus'           => 'Cambia lo stato a',
    'desc_targetticketstatus'      => 'Una volta trascorso il tempo di inattività (vedi sotto), i ticket che corrispondono a questa regola verranno impostati su questo stato (dovrebbe essere lo stato di <strong>risolto</strong>).',
    'inactivitythreshold'          => 'Soglia di inattività',
    'desc_inactivitythreshold'     => 'Se un ticket non viene aggiornato entro questo numero di ore, sarà considerato inattivo. Questo è il primo passo verso la chiusura automatica di un ticket.',
    'closurethreshold'             => 'Soglia di chiusura',
    'desc_closurethreshold'        => 'Se un ticket è stato contrassegnato come inattivo e non viene aggiornato entro questo numero di ore, verrà impostato automaticamente allo stato specificato sopra.',
    'isenabled'                    => 'Regola abilitata',
    'desc_isenabled'               => 'Scegli se questa regola è abilitata o no.',
    'sortorder'                    => 'Ordine di esecuzione',
    'desc_sortorder'               => 'È possibile creare più regole di chiusura automatica. L\'ordine di esecuzione determina quali regole vengono eseguite per prima, dal più piccolo al più grande.',
    'sendpendingnotification'      => 'Invia email di notifica inattività',
    'desc_sendpendingnotification' => 'L\'helpdesk può notificare l\'utente che il loro ticket è stato contrassegnato come inattivo e che verrà chiuso se non riceve alcuna risposta.',
    'sendfinalnotification'        => 'Invia l\'ultima email di notifica di chiusura ticket',
    'desc_sendfinalnotification'   => 'L\'helpdesk può anche informare l\'utente che è stato chiuso il loro ticket.',
    'suppresssurveyemail'          => 'Sopprimi email di sondaggio cliente',
    'desc_suppresssurveyemail'     => 'Se hai abilitato gli inviti ai sondaggi di soddisfazione cliente quando un ticket è impostato sullo stato specificato sopra, potreste voler impedire che l\'helpdesk mandi inviti per i ticket chiusi automaticamente.',

    'insertcriteria'               => 'Inserisci criteri',


    'titleautocloseruledel'        => 'Regole di chiusura automatica eliminate (%d)',
    'msgautocloseruledel'          => 'Le seguenti regole di chiusura automatica sono state eliminate:',
    'titleautocloseruleenable'     => 'Regole di chiusura automatica attive (%d)',
    'msgautocloseruleenable'       => 'Le seguenti regole di chiusura automatica sono state attivate:',
    'titleautocloseruledisable'    => 'Regole di chiusura automatica non attive (%d)',
    'msgautocloseruledisable'      => 'Le seguenti regole di chiusura automatica sono state disattivate:',
    'titleautocloseruleinsert'     => 'Creata la regola di chiusura automatica',
    'msgautocloseruleinsert'       => 'La regola di chiusura automatica (%s) è stata creata con successo.',
    'titleautocloseruleupdate'     => 'Aggiornata la regola di chiusura automatica',
    'msgautocloseruleupdate'       => 'La regola di chiusura automatica (%s) è stata aggiornata con successo.',

    'titlenocriteriaadded'         => 'Nessun criterio specificato',
    'msgnocriteriaadded'           => 'È necessario specificare almeno un criterio per creare una regola di chiusura automatica (altrimenti l\'helpdesk non saprà quali ticket chiudere automaticamente).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- N/D --',
    'articketstatusid'             => 'Stato',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Priorità',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Dipartimento',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Proprietario',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'Coda email',
    'arusergroupid'                => 'Gruppo di utenti',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Segnala',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Categoria Bayesiana',
    'desc_arbayescategoryid'       => 'Ticket che sono stati abbinati a una categoria Bayesiana specifica.',
    'arcreator'                    => 'Autore',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Personale',
    'creatorclient'                => 'Utente',
    'arunassigned'                 => '-- Non assegnato --',
    'artemplategroupid'            => 'Gruppo di modelli',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Tipo',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
