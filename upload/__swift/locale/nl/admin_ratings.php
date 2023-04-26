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
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'ratings'                     => 'Beoordelingen',
    'tabgeneral'                  => 'Algemeen',
    'tabpermissionsstaff'         => 'Rechten (Medewerker)',
    'tabpermissionsuser'          => 'Rechten (Gebruiker)',
    'staffgroups'                 => 'Medewerkerteams',
    'usergroups'                  => 'Gebruikersgroepen',
    'ratingticket'                => 'Ticket',
    'ratingticketpost'            => 'Ticketberichten',
    'ratingchatsurvey'            => 'Chatonderzoek',
    'ratingchathistory'           => 'Chatgeschiedenis',

    'staffvisibilitycustom'       => 'Aanpassen van beoordeling beperken tot bepaalde medewerkerteams',
    'desc_staffvisibilitycustom'  => 'Wanneer dit is ingeschakeld, kunnen alleen onderstaande teams deze beoordeling aanpassen en waarden instellen.',

    'buservisibilitycustom'       => 'Beoordeling beperken tot specifieke gebruikersgroepen',
    'desc_buservisibilitycustom'  => 'Wanneer dit is ingeschakeld, kunnen alleen onderstaande gebruikersgroepen deze beoordeling aanpassen en waarden instellen.',

    'bstaffvisibilitycustom'      => 'Beoordeling beperken tot specifieke medewerkerteams',
    'desc_bstaffvisibilitycustom' => 'Wanneer dit is ingeschakeld, kunnen alleen onderstaande teams deze beoordeling bekijken.',

    'isclientonly'                => 'Blokkeer instellen van waarden door medewerkers voor deze beoordeling',
    'desc_isclientonly'           => 'Als dit is ingeschakeld kunnen alleen eindgebruikers waarden instellen voor deze beoordeling.',

    'ratingscale'                 => 'Beoordelingsschaal',
    'desc_ratingscale'            => 'De maximale beoordeling die kan worden gegeven. Als bijvoorbeeld <em>5</em> is geselecteerd, is de laagst mogelijke beoordeling 1 en de hoogste 10.',

    'ratingtype'                  => 'Helpdeskdeel',
    'desc_ratingtype'             => 'Het deel van de helpdesk waarop deze beoordeling van toepassing is. <br /><br /><strong>Ticket:</strong> Wordt gebruikt om hele tickets te beoordelen.<br /><strong>Ticketbericht:</strong> Wordt gebruikt om afzonderlijke ticketreacties te beoordelen.<br /><strong>Chatonderzoek:</strong> Beoordelingen worden toegevoegd aan het post-chatonderzoek.<br /><strong>Chatgeschiedenis:</strong> Wordt gebruikt om chatrecords in het medewerkerbeheerscherm te beoordelen (bedoelt voor interne beoordeling en auditing).',

    'ratingtitle'                 => 'Beoordelingslabel',
    'desc_ratingtitle'            => 'Bijvoorbeeld <em>Algemene tevredenheid</em> of <em>Snelheid van afhandeling</em>.',

    'displayorder'                => 'Weergavevolgorde',
    'desc_displayorder'           => 'Meerdere beoordelingen kunnen op dezelfde plaats worden weergegeven. Ze worden weergegeven aan de hand van hun weergavevolgordenummer, van laag naar hoog.',

    'ratingdep'                   => 'Beoordeling beperken tot een afdeling',
    'desc_ratingdep'              => 'Selecteer een afdeling om deze beoordeling te beperken tot tickets of chats binnen een bepaalde afdeling. Als er geen afdeling is geselecteerd, wordt de beoordeling bij alle afdelingen weergegeven.',
    'ratingalldep'                => '-- Alle afdelingen --',

    'iseditable'                  => 'Wijzigingen toestaan na eerste indiening',
    'desc_iseditable'             => 'De waarde die voor deze beoordeling is ingesteld kan worden gewijzigd, indien dit is ingeschakeld.',

    'ratingvisibility'            => 'Wie kan een beoordeling geven?',
    'desc_ratingvisibility'       => '<strong>Publiek</strong> Beoordelingen zijn beschikbaar voor alle gebruikers. Selecteer deze optie als je feedback wilt verzamelen van je eindgebruikers en de beoordelingen ook zichtbaar wilt hebben voor medewerkers.<br /><strong>Privé</strong> Beoordelingen zijn alleen beschikbaar voor je medewerkers, voor interne beoordelingsdoeleinden.',

    'ratingvis'                   => 'Zichtbaarheid',
    'insertrating'                => 'Beoordeling toevoegen',
    'desc_insertrating'           => '',
    'manageratings'               => 'Beheren',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Beoordeling bewerken: %s',
    'editrating'                  => 'Beoordeling bewerken',
    'desc_editrating'             => '',
    'titledelrating'              => 'Beoordelingen verwijderd (%d)',
    'msgdelrating'                => 'De volgende beoordelingen en al hun gegevens zijn verwijderd:',
    'titleratinginsert'           => 'Beoordeling gemaakt',
    'msgratinginsert'             => 'De beoordeling (%s) is succesvol gemaakt.',
    'titleratingupdate'           => 'Beoordeling bijgewerkt',
    'msgratingupdate'             => 'De rating (%s) is succesvol bijgewerkt.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'Kennisbank',
    'ratingtroubleshooter'        => 'Probleemoplosser',
    'ratingnews'                  => 'Nieuws',
);


return $__LANG;
