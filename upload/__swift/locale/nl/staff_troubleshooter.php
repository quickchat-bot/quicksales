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
    'troubleshooter'             => 'Probleemoplosser',
    'categories'                 => 'Categorieën',
    'titletrcatmismatch'         => 'Er bestaat al een categorie met de naam %s',
    'msgtrcatmismatch'           => 'Er bestaat al een probleemoplossercategorie met de naam %s. Kies een andere naam.',
    'wininsertcategory'          => 'Categorie toevoegen',
    'wineditcategory'            => 'Categorie bewerken: %s',
    'insertcategory'             => 'Categorie toevoegen',
    'editcategory'               => 'Categorie bewerken',

    'titletrcategoryinsert'      => 'Probleemoplossercategorie (%s) gemaakt',
    'msgtrcategoryinsert'        => 'Probleemoplossercategorie (%s) is succesvol gemaakt.',
    'titletrcategoryupdate'      => 'Probleemoplossercategorie (%s) bijgewerkt',
    'msgtrcategoryupdate'        => 'Probleemoplossercategorie (%s) is succesvol bijgewerkt.',

    'trcategory'                 => 'Categorienaam',
    'desc_trcategory'            => 'Bijvoorbeeld <em>Mijn apparaat wil niet aan gaan</em>.',
    'next'                       => 'Volgende',

    'tabgeneral'                 => 'Algemeen',
    'taboptions'                 => 'Opties',
    'categorytype'               => 'Zichtbaarheid',
    'desc_categorytype'          => '<strong>Algemeen</strong> Beschikbaar voor iedereen, inclusief medewerkers en eindgebruikers.<br /><strong>Publiek</strong> Alleen beschikbaar voor eindgebruikers in het support center.<br /><strong>Privé</strong> Alleen beschikbaar voor je medewerkers in het medewerkerbeheerscherm.',
    'displayorder'               => 'Weergavevolgorde',
    'desc_displayorder'          => 'Probleemoplossercategorieën wordt weergegeven volgens hun weergavevolgordenummer, van laag naar hoog.',
    'tabpermissionsuser'         => 'Rechten: Gebruikers',
    'tabpermissionsstaff'        => 'Rechten: Medewerkers',
    'uservisibilitycustom'       => 'Beperken tot specifieke gebruikersgroepen',
    'desc_uservisibilitycustom'  => 'Als dit is ingeschakeld, kunnen alleen onderstaande gebruikersgroepen deze categorie zien.',
    'staffvisibilitycustom'      => 'Beperken tot specifieke medewerkerteams',
    'desc_staffvisibilitycustom' => 'Als dit is ingeschakeld, kunnen alleen onderstaande teams deze categorie zien.',
    'usergroups'                 => 'Gebruikersgroepen',
    'staffteams'                 => 'Medewerkerteams',

    'tablist'                    => 'Lijst',
    'tabtree'                    => 'Boom',
    'steptitle'                  => 'Staptitel',
    'categorytitle'              => 'Categorie',
    'author'                     => 'Auteur',
    'stepstatus'                 => 'Status',

    /**
     * ---------------------------------------------
     * INSERT TROUBLESHOOTER
     * ---------------------------------------------
     */
    'titletrstepinsert'          => 'Probleemoplosserstap (%s) gemaakt',
    'msgtrstepinsert'            => 'De probleemoplosserstap (%s) is succesvol gemaakt.',
    'titletrstepupdate'          => 'Probleemoplosserstap (%s) bijgewerkt',
    'msgtrstepupdate'            => 'De probleemoplosserstap (%s) is succesvol bijgewerkt.',
    'insertstep'                 => 'Stap toevoegen',
    'recursionsuffix'            => ' (mogelijke recursie)',
    'parentcategoryitem'         => '- Hoofdcategorie -',
    'desc_parentsteps'           => '',
    'tabgeneral'                 => 'Algemeen',
    'tabattachments'             => 'Bijlagen',
    'taboptions'                 => 'Opties',
    'publish'                    => 'Publiceren',
    'published'                  => 'Gepubliceerd',
    'draft'                      => 'Concept',
    'infobauthor'                => 'AUTEUR',
    'infobcreationdate'          => 'GEMAAKT OP',
    'infobeditedby'              => 'BEWERKT DOOR',
    'infobeditedon'              => 'BEWERKT OP',
    'ftarticletype'              => 'Type',
    'ftcategories'               => 'Categorieën',
    'ftdate'                     => 'Datum',
    'informationbox'             => 'Gegevens',
    'quickfilter'                => 'Snelfilter',
    'markasdraft'                => 'Markeren als concept',
    'saveasdraft'                => 'Opslaan als concept',
    'addfile'                    => 'Bestand toevoegen',
    'steptitle'                  => 'Staptitel',
    'desc_steptitle'             => '',
    'parentsteps'                => 'Bovenliggende stappen',
    'desc_parentsteps'           => '',
    'generalsettings'            => 'Algemene instellingen',
    'allowcomments'              => 'Reacties toestaan',
    'desc_allowcommentsstep'     => 'Of reacties zijn toegestaan bij deze stap.',
    'noticketrediractive'        => '-- Geen afdeling geselecteerd --',
    'ticketredirectionsettings'  => 'Ticketdoorverwijzingsinstellingen',
    'redirecttickets'            => 'Doorverwijzen naar ticket bij deze stap',
    'desc_redirecttickets'       => 'Schakel deze instelling in om de gebruiker uit te nodigen een ticket te maken nadat deze stap is bereikt (aan het eind).',
    'redirectdepartment'         => 'Ticketafdeling',
    'desc_redirectdepartment'    => 'Als geen afdeling is geselecteerd, wordt de gebruiker gevraagd er zelf een te selecteren.',
    'ticketsubject'              => 'Ticketonderwerp',
    'desc_ticketsubject'         => 'Als geen ticketonderwerp is opgegeven, wordt de gebruiker gevraagd een onderwerp in te voeren.',
    'tickettype'                 => 'Tickettype',
    'desc_tickettype'            => 'Specify a ticket type for the ticket a user who reaches this step will be invited to create.',
    'ticketpriority'             => 'Ticketprioriteit',
    'desc_ticketpriority'        => 'Als geen prioriteit is opgegeven, kan de gebruiker zelf een ticketprioriteit selecteren (indien van toepassing).',
    'nochange'                   => '-- Geen wijziging --',

    /**
     * ---------------------------------------------
     * VIEW ALL
     * ---------------------------------------------
     */
    'tabviewall'                 => 'Probleemoplosser',
    'troubleshooterdesc'         => 'Probleemoplossingswizards leiden je door een reeks stappen om algemene problemen te diagnosticeren en op te lossen. Begin door een probleemoplossercategorie te selecteren.',
    'trattachments'              => 'Bijlagen',
    'trnextsteps'                => 'Volgende stappen',
    'trnext'                     => 'Volgende',
    'trback'                     => 'Terug',
    'steps'                      => 'Stappen',
    'trcategoryviews'            => ' (%d weergaven)',

    // Potentialy unused phrases in staff_troubleshooter.php
    'desc_categorytitle'         => 'Enter the category title',

);

return $__LANG;
