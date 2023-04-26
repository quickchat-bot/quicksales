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
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

$__LANG = array(
    // Scheduled tasks
    'scheduledtasks'       => 'Operazioni pianificate',
    'desc_scheduledtasks'  => 'Queste attività verranno eseguite agli intervalli illustrati qui sotto per compiere le varie operazioni di helpdesk. Se sei un cliente Download e il vostro helpdesk viene utilizzato di rado, è consigliato impostare un cron sul lato server per attivare il sistema di pianificazione delle attività. <a href="https://go.opencart.com.vn/?pageid=NBTHelpDeskTasks" target="_blank" rel="noopener noreferrer">Informati sulle attività pianificate</a>.',
    'tasklist'             => 'Elenco Attività',
    'nextrun'              => 'Prossima Esecuzione',
    'lastrun'              => 'Ultima Esecuzione',
    'runsevery'            => 'Si Esegue Ogni',
    'xminute'              => '%s minuti',
    'xhour'                => '%s ore',
    'xday'                 => '%s giorni',
    'runnow'               => 'Esegui',
    'scheduledexecute'     => 'L\'attività (%s) verrà eseguita alla prossima attivazione del sistema di pianificazione delle attività',
    'taskdisabled'         => 'Attività %s disabilitata',
    'taskenabled'          => 'Attività %s abilitata ',
    'tasklog'              => 'Registro Attività',
    'securl'               => 'URL: ',
    'tasklogs'             => 'Registri di Attività',
    'executiontime'        => 'Tempo di Esecuzione',
    'cronlogdeleteconfirm' => 'Voce del registro delle attività (%s) eliminata',
    'execute'              => 'Esegui',
    'titledisablecron'     => 'Operazioni pianificate disabilitate (%s)',
    'msgdisablecron'       => 'Le seguenti operazioni pianificate sono state disattivate e non verranno eseguite:',
    'titleenablecron'      => 'Operazioni pianificate abilitate (%d)',
    'msgenablecron'        => 'Le seguenti attività pianificate sono state abilitate:',
    'titleexecutecron'     => '%d operazioni pianificate sono state eseguite',
    'msgexecutecron'       => 'Le seguenti operazioni pianificate sono state eseguite:',

    // ======= TASK ENTRIES =======
    'cronhourlycleanup'    => 'Pulitura oraria del sistema',
    'crondailycleanup'     => 'Pulitura giornaliera del sistema',
    'cronsearchengine'     => 'Ri-indicizzazione del motore di ricerca',
    'tickets'              => 'Operazioni ticket (escalation di SLA, pulitura, ecc.)',
    'parser'               => 'Recupera email (POP3/IMAP)',
    'parsercleanup'        => 'Pulitura analizzatore email',
    'livechat'             => 'Pulitura chat dal vivo',
    'newssync'             => 'Sincronizza i feed RSS delle notizie',
    'ticketautoclose'      => 'Chiusura automatica ticket',
    'defaultcoreminute'    => 'Sincronizzazione al minuto delle operazioni interne',
    'defaultcorehourly'    => 'Sincronizzazione all\'ora delle operazioni interne',
    'defaultcoredaily'     => 'Sincronizzazione giornaliera delle operazioni interne',
    'defaultcoreweekly'    => 'Sincronizzazione settimanale delle operazioni interne',
    'defaultcoremonthly'   => 'Sincronizzazione mensile delle operazioni interne',
    'reportemailing'       => 'Esegui i report pianificati',
    'ticketfollowup'       => 'Esegui le attività di follow-up dei ticket',
);


return $__LANG;
