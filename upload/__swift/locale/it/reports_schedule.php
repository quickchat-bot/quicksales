<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'schedules'                  => 'Orari',

    'exportformat'               => 'Formato del report',
    'desc_exportformat'          => 'Il report sarà generato (e ti sarà inviato via email) in questo formato.',
    'recurrence'                 => 'Esegui il report ogni',
    'desc_recurrence'            => 'Specifica quante volte il report verrà eseguito.',
    'firstexecutiondate'         => 'Data prima esecuzione',
    'nextexecutiondate'          => 'Data prossima esecuzione',
    'lastexecutiondate'          => 'Data ultima esecuzione',
    'desc_executiondate'         => 'Specifica la prima volta che questo report verrà eseguito. Le successive esecuzioni di report verranno calcolati automaticamente.',
    'schedulecc'                 => 'Invia ad altri indirizzi email',
    'desc_schedulecc'            => 'Il report sarà inviato al tuo indirizzo email. Facoltativamente, puoi specificare altri indirizzi email.',

    'recurrence_once'            => 'Una volta',
    'recurrence_hourly'          => 'Oraria',
    'recurrence_daily'           => 'Giornaliera',
    'recurrence_weekly'          => 'Settimanale',
    'recurrence_bimonthly'       => 'Ogni due settimane',
    'recurrence_monthly'         => 'Mensile',
    'recurrence_quarterly'       => 'Trimestrale',
    'recurrence_yearly'          => 'Annuo',

    'titleinvalidformat'         => 'Formato di esportazione non valido',
    'msginvalidformat'           => 'Si prega di selezionare un formato di esportazione valido.',
    'titleinvalidrecurrence'     => 'Ricorrenza non valida',
    'msginvalidrecurrence'       => 'Si prega di selezionare un tipo di ricorrenza valido.',
    'titleinvalidexecutiondate'  => 'Data prima esecuzione non valida',
    'msginvalidexecutiondate'    => 'Si prega di specificare valori validi nelle proprietà di data e ora per la data di prima esecuzione.',
    'titlepastexecutiondate'     => 'La data di prima esecuzione è nel passato',
    'msgpastexecutiondate'       => 'Si prega di selezionare una data futura per la prima esecuzione.',
    'titleinvalidcc'             => 'Indirizzo email non valido',
    'msginvalidcc'               => 'Si prega di controllare la sintassi degli indirizzi email forniti.',

    'notifyreportscheduleinsert' => 'Rapporto pianificato con successo',
    'notifyreportscheduleupdate' => 'Aggiornata la pianificazione dei report',

    'ccemailaddresses'           => 'Indirizzi email copiati in',

    'reportsendoncesub'          => 'Rapporto: %s',
    'reportsendhourlysub'        => 'Rapporto orario: %s',
    'reportsenddailysub'         => 'Rapporto giornaliero: %s',
    'reportsendweeklysub'        => 'Rapporto settimanale: %s',
    'reportsendbimonthlysub'     => 'Rapporto bi-settimanale: %s',
    'reportsendmonthlysub'       => 'Rapporto mensile: %s',
    'reportsendquarterlysub'     => 'Rapporto trimestrale: %s',
    'reportsendyearlysub'        => 'Rapporto annuale: %s',

    'reportnoattachment'         => 'nessun allegato',
    'reportsendintro'            => 'Hai ricevuto questa email perché hai [oppure (%s) per conto tuo] pianificato che venisse eseguito ed inviato alla tua casella un report dell\'helpdesk in automatico.',
    'reporterrorintro'           => 'Il report ha restituito il seguente errore',
    'reporterrorccnote'          => 'A causa dell\'errore di cui sopra questa email non è stata inviata agli altri indirizzi email specificati per ricevere copie del report.',

    'noexportresultfound'        => 'Questo report non ha prodotto alcun risultato, quindi non c\'era nulla da esportare.',
);

return $__LANG;
