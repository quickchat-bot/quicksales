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
    'autoclose'                    => 'Automatisch sluiten',
    'manage'                       => 'Beheren',
    'targetstatus'                 => 'Status te wijzigen naar',
    'tabgeneral'                   => 'Algemeen',

    'insertrule'                   => 'Regel toevoegen',
    'editrule'                     => 'Regel bewerken',

    'ruletitle'                    => 'Regeltitel',
    'desc_ruletitle'               => 'Voer een titel in voor deze regel voor automatisch sluiten.',
    'targetticketstatus'           => 'Status wijzigen naar',
    'desc_targetticketstatus'      => 'Wanneer de inactiviteitsperiode is verstreken (zie onder), worden tickets die aan deze regel voldoen op deze status gezet (dit moet een <strong>afgehandelde</strong> status zijn).',
    'inactivitythreshold'          => 'Inactiviteitslimiet',
    'desc_inactivitythreshold'     => 'Als een ticket niet binnen dit aantal uren wordt bijgewerkt, wordt de ticket als inactief beschouwd. Dit is de eerste stap naar het automatisch sluiten van een ticket.',
    'closurethreshold'             => 'Sluitingslimiet',
    'desc_closurethreshold'        => 'Als een ticket als inactief is gemarkeerd en binnen dit aantal uren nog steeds niet is bijgewerkt, wordt de ticket automatisch op de hierboven aangegeven status gezet.',
    'isenabled'                    => 'Regel is ingeschakeld',
    'desc_isenabled'               => 'Bepaal of de regel actief is of niet.',
    'sortorder'                    => 'Volgorde van uitvoering',
    'desc_sortorder'               => 'Het is mogelijk meerdere regels voor automatisch sluiten te maken. De uitvoeringsvolgorde bepaalt welke regels eerst worden uitgevoerd, van laag naar hoog.',
    'sendpendingnotification'      => 'Notificatie-mail sturen over inactiviteit',
    'desc_sendpendingnotification' => 'De helpdesk kan de gebruiker informeren dat de ticket als inactief is gemarkeerd en dat deze gesloten wordt indien er geen antwoord wordt ontvangen.',
    'sendfinalnotification'        => 'Laatste notificatie-mail sturen over ticketsluiting',
    'desc_sendfinalnotification'   => 'De helpdesk kan de gebruiker ook informeren dat de ticket is gesloten.',
    'suppresssurveyemail'          => 'Tevredenheidsonderzoek e-mail blokkeren',
    'desc_suppresssurveyemail'     => 'Als je uitnodigingen voor klanttevredenheidsonderzoek hebt ingeschakeld wanneer een ticket op de hierboven aangegeven status wordt gezet, wil je misschien voorkomen dat de helpdesk een onderzoeksuitnodiging stuurt voor automatisch gesloten tickets.',

    'insertcriteria'               => 'Criteria toevoegen',


    'titleautocloseruledel'        => 'Regels voor automatisch sluiten verwijderd (%d)',
    'msgautocloseruledel'          => 'De volgende regels voor automatisch sluiten zijn verwijderd:',
    'titleautocloseruleenable'     => 'Regels voor automatisch sluiten geactiveerd (%d)',
    'msgautocloseruleenable'       => 'De volgende regels voor automatisch sluiten zijn geactiveerd:',
    'titleautocloseruledisable'    => 'Regels voor automatisch sluiten gedeactiveerd (%d)',
    'msgautocloseruledisable'      => 'De volgende regels voor automatisch sluiten zijn gedeactiveerd:',
    'titleautocloseruleinsert'     => 'Regels voor automatisch sluiten gemaakt',
    'msgautocloseruleinsert'       => 'De regel voor automatisch sluiten (%s) is succesvol gemaakt.',
    'titleautocloseruleupdate'     => 'Regel voor automatisch sluiten bijgewerkt',
    'msgautocloseruleupdate'       => 'De regel voor automatisch sluiten (%s) is succesvol bijgewerkt.',

    'titlenocriteriaadded'         => 'Geen criteria opgegeven',
    'msgnocriteriaadded'           => 'Je moet tenminste één criterium opgeven om een regel voor automatisch sluiten te maken (anders weet de helpdesk niet welke tickets automatisch moeten worden gesloten).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- NVT --',
    'articketstatusid'             => 'Status',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Prioriteit',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Afdeling',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Eigenaar',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'E-mail wachtrij',
    'arusergroupid'                => 'Gebruikersgroep',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Vlag',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Bayesiaanse categorie',
    'desc_arbayescategoryid'       => 'Tickets die overeenkomen met een bepaalde Bayesiaanse categorie.',
    'arcreator'                    => 'Maker',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Medewerker',
    'creatorclient'                => 'Gebruiker',
    'arunassigned'                 => '-- Niet toegewezen --',
    'artemplategroupid'            => 'Sjabloongroep',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Type',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
