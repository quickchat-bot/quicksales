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
    'schedules'                  => 'Planningen',

    'exportformat'               => 'Rapportindeling',
    'desc_exportformat'          => 'Het rapport wordt in deze indeling gegenereerd (en naar je geëmaild).',
    'recurrence'                 => 'Rapport uitvoeren elke',
    'desc_recurrence'            => 'Bepaal hoe vaak het rapport moet worden uitgevoerd.',
    'firstexecutiondate'         => 'Datum eerste uitvoering',
    'nextexecutiondate'          => 'Datum volgende uitvoering',
    'lastexecutiondate'          => 'Datum laatste uitvoering',
    'desc_executiondate'         => 'Geef op wanneer het rapport voor het eerst moet worden uitgevoerd. Volgende tijdstippen voor het uitvoeren van rapporten worden automatisch berekend.',
    'schedulecc'                 => 'Verzenden naar andere emailadressen',
    'desc_schedulecc'            => 'Het rapport wordt naar je emailadres verzonden. Geef optioneel andere emailadressen op.',

    'recurrence_once'            => 'Eenmaal',
    'recurrence_hourly'          => 'Elk uur',
    'recurrence_daily'           => 'Dagelijks',
    'recurrence_weekly'          => 'Wekelijks',
    'recurrence_bimonthly'       => 'Tweewekelijks',
    'recurrence_monthly'         => 'Maandelijks',
    'recurrence_quarterly'       => 'Driemaandelijks',
    'recurrence_yearly'          => 'Jaarlijks',

    'titleinvalidformat'         => 'Ongeldige exportindeling',
    'msginvalidformat'           => 'Selecteer een geldige exportindeling.',
    'titleinvalidrecurrence'     => 'Ongeldige herhaling',
    'msginvalidrecurrence'       => 'Selecteer een geldig herhalingstype.',
    'titleinvalidexecutiondate'  => 'Ongeldige datum eerste uitvoering',
    'msginvalidexecutiondate'    => 'Gebruik geldige waarde(n) voor de datum en tijd van de datum voor de eerste uitvoering.',
    'titlepastexecutiondate'     => 'Datum eerste uitvoering is in het verleden',
    'msgpastexecutiondate'       => 'Selecteer een datum in de toekomst voor de eerste uitvoering.',
    'titleinvalidcc'             => 'Ongeldig emailadres',
    'msginvalidcc'               => 'Controleer de syntax van het opgegeven emailadres.',

    'notifyreportscheduleinsert' => 'Rapport succesvol gepland',
    'notifyreportscheduleupdate' => 'Rapportplanning bijgewerkt',

    'ccemailaddresses'           => 'Emailadressen gekopieerd in',

    'reportsendoncesub'          => 'Rapport: %s',
    'reportsendhourlysub'        => 'Uurrapport: %s',
    'reportsenddailysub'         => 'Dagelijks rapport: %s',
    'reportsendweeklysub'        => 'Wekelijks rapport: %s',
    'reportsendbimonthlysub'     => 'Tweewekelijks rapport: %s',
    'reportsendmonthlysub'       => 'Maandelijks rapport: %s',
    'reportsendquarterlysub'     => 'Driemaandelijks rapport: %s',
    'reportsendyearlysub'        => 'jaarlijks rapport: %s',

    'reportnoattachment'         => 'geen bijlage',
    'reportsendintro'            => 'Je hebt deze email ontvangen omdat jij of iemand in jouw naam (%s) een helpdeskrapport hebt gepland om automatisch te worden uitgevoerd en naar je mailbox te worden afgeleverd.',
    'reporterrorintro'           => 'Het rapport gaf de volgende fout',
    'reporterrorccnote'          => 'Vanwege bovenstaande fout is deze email niet verzonden naar de andere emailadressen die zijn opgegeven om een kopie te ontvangen van het rapport.',

    'noexportresultfound'        => 'Dit rapport heeft geen resultaten geproduceert, dus er was niets om te exporteren.',
);

return $__LANG;
