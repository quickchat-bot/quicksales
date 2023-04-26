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
    // ======= BEGIN v4 LOCALES =======
    'tickets'                               => 'Tickets',
    'tabgeneral'                            => 'Algemeen',
    'tabpermissions'                        => 'Rechten',
    'staffvisibilitycustom'                 => 'Beperk wijzigen van tickets naar deze status',
    'desc_staffvisibilitycustom'            => 'Als dit is ingeschakeld, kunnen alleen onderstaande teams tickets op deze status zetten.',
    'staffgroups'                           => 'Medewerkerteams',
    'triggersurvey'                         => 'Email voor tevredenheidsonderzoek verzenden',
    'desc_triggersurvey'                    => 'Wanneer een ticket naar deze status wordt gewijzigd, wordt een email verzonden naar de gebruiker om om een beoordeling en feedback te vragen.',
    'statustitle'                           => 'Statusnaam',
    'desc_statustitle'                      => 'Voer een naam in voor deze status.',
    'statuscolor'                           => 'Statuskleur',
    'desc_statuscolor'                      => 'Wij raden groen aan voor open statussen en donkere kleuren voor afgehandelde statussen.',
    'statusbgcolor'                         => 'Achtergrondkleur status',
    'desc_statusbgcolor'                    => 'In het medewerkerbeheerscherm wordt de hele ticketeigenschappenbalk gevuld met deze kleur (afhankelijk van de ticketstatus). Je kunt \'t niet missen!',
    'displayorder'                          => 'Weergavevolgorde',
    'desc_displayorder'                     => 'Ticketstatussen worden weergegeven volgens hun weergavevolgorde, van laag naar hoog.',
    'displayiconstatus'                     => 'Pictogram',
    'desc_displayiconstatus'                => 'Upload of link een pictogram voor deze status. {$themepath} kan worden gebruikt om naar de afbeeldingendirectory van de helpdesk te verwijderen, bijvoorbeeld <em>{$themepath}pictogram_statusopen.gif</em>.',
    'statusdep'                             => 'Status koppelen aan afdeling',
    'desc_statusdep'                        => 'Koppel deze status aan een bepaalde afdeling. Als u dit niet doet, is de status beschikbaar voor alle ticketafdelingen.',
    'statusalldep'                          => '-- Niet gekoppeld --',
    'markasresolved'                        => 'Is dit een <em>afgehandelde</em> ticketstatus?',
    'desc_markasresolved'                   => 'Als tickets die op deze status zijn gezet afgehandeld of gesloten zijn (bijvoorbeeld hersteld, afgehandeld, afgerond), schakel dan deze optie in. Hierdoor worden deze verborgen op lijsten met open tickets.',
    'displayinmainlist'                     => 'Weergaven in hoofdticketlijst',
    'desc_displayinmainlist'                => 'Tickets die op deze status worden gezet worden in de hoofdticketlijst weergegeven.',
    'displaycount'                          => 'Ticketaantal weergeven in ticketnavigatieboom',
    'desc_displaycount'                     => 'Wanneer dit is ingeschakeld wordt het aantal tickets dat op deze status is gezet weergegeven in de ticketnavigatieboom in het medewerkerbeheerscherm. Wij raden aan dat u deze instelling uitschakelt voor alle afgehandelde statussen.',
    'statustype2'                           => 'Statustype',
    'desc_statustype2'                      => '<strong>Publiek</strong> Statussen zijn overal zichtbaar en voor je eindgebruikers.<br /><strong>Privé</strong> Statussen zijn alleen zichtbaar voor je medewerkers.',
    'resetduetime'                          => 'Ticketbeantwoordingsdeadline wissen bij deze status',
    'desc_resetduetime'                     => 'Wanneer tickets naar deze status worden gewijzigd wordt de deadline van de ticket automatisch gewist.',
    'dispatchnotification'                  => 'Notificatie verzenden naar gebruiker',
    'desc_dispatchnotification'             => 'Als dit is ingeschakeld verzendt het systeem een notificatie aan de gebruiker/auteur wanneer deze status actief wordt voor een ticket.',
    'insertstatus'                          => 'Status toevoegen',
    'desc_insertstatus'                     => '',
    'invalidticketstatus'                   => 'Ticketstatus kon niet worden geladen.',
    'managestatus'                          => 'Statussen',
    'desc_managestatus'                     => '',
    'wineditticketstatus'                   => 'Status bewerken: %s',
    'editstatus'                            => 'Status bewerken',
    'desc_editstatus'                       => '',
    'taboptions'                            => 'Opties',
    'titledelticketstatus'                  => 'Ticketstatussen verwijderd (%d)',
    'msgdelticketstatus'                    => 'De volgende ticketstatussen zijn verwijderd:',
    'titleticketstatusinsert'               => 'Ticketstatus %s gemaakt',
    'msgticketstatusinsert'                 => 'De ticketstatus %s is succesvol gemaakt.',
    'titleticketstatusupdate'               => 'Ticketstatus %s bijgewerkt',
    'msgticketstatusupdate'                 => 'De ticketstatus %s is succesvol bijgewerkt.',
    'titlemasterstatusdelete'               => 'Ticketstatussen konden niet worden verwijderd (%d)',
    'msgmasterstatusdelete'                 => 'De volgende hoofdticketstatussen konden niet worden verwijderd:',
    'tablanguages'                          => 'Talen: Vertaling',
    'title_statusinuse'                     => 'Status in gebruik',
    'message_statusinuse_editassociation'   => 'Er zijn tickets op deze status gezet die tot de bestaande gekoppelde afdeling behoren. Je moet de status van de tickets wijzigen, voordat je de gekoppelde afdeling kunt bijwerken.',
    'message_statusinuse_createassociation' => 'Er zijn tickets op deze status gezet die tot de bestaande gekoppelde afdeling behoren. Je moet de status van de tickets wijzigen, voordat je de gekoppelde afdeling kunt bijwerken.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
