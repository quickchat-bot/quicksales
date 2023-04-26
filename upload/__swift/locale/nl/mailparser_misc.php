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
    // ======= BEGIN v4 LOCALES =======
    'mpsettings'             => 'Instellingen',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Instellingen',
    'mailparser'             => 'Emailparser',


    'titleinsertban'         => 'Emailadres geband',
    'msginsertban'           => 'Het emailadres %s is geband en de emailparser verwerkt email van dit adres niet langer.',
    'titleupdateban'         => 'Emailadresban bijgewerkt',
    'msgupdateban'           => 'De emailadresban voor %s is succesvol bijgewerkt.',
    'wineditban'             => 'Parserban bewerken',
    'titledelbans'           => 'Emailadresbans opgeheven (%d)',
    'msgdelbans'             => 'De volgende emailadresbans zijn verwijderd:',

    // Email Bans
    'emailbans'              => 'Emailbans',
    'emailbanlist'           => 'Emailbanlijst',
    'bannedemail'            => 'Emailadres',
    'bannedby'               => 'Geband door',
    'insertban'              => 'Ban toevoegen',
    'insertnewban'           => 'Nieuwe ban toevoegen',
    'desc_bannedemail'       => 'Voer het te bannen emailadres in. Alle inkomende emailberichten van dit adres worden door de helpdesk genegeerd. Om een gehele domeinnaam te bannen voer je <em>@domeinnaam.com</em> in.',
    'parserbaninsertconfirm' => 'Emailban "%s" succesvol toegevoegd',
    'parserbansdelconfirm'   => 'Emailban(s) succesvol verwijderd',
    'invalidemailban'        => 'Ongeldige emailadresban-ID opgegeven',
    'editban'                => 'Ban bewerken',
    'bandelconfirmmsg'       => 'Weet je zeker dat je deze emailbanvermelding wilt verwijderen? Deze actie is onomkeerbaar!',
    'parserbanupdconfirm'    => 'Emailadresban voor %s bijgwerkt',
    'desc_mailbans'          => 'Je kunt voorkomen dat probleemgebruikers tickets indienen door een emailban toe te voegen voor hun adres. Wanneer QuickSupport een emailbericht ontvangt, wordt het afzenderadres vergeleken met de banlijst en wordt het bericht zonder kennisgeving genegeerd, als het adres is geband.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Catch-allregel toevoegen',
    'titlenqcatchall'        => 'Configureer emailwachtrijen voordat je catch-allregels maakt',
    'msgnqcatchall'          => 'Catch-allregels bepalen wat er gebeurt met inkomende email bij de helpdesk. Zonder geconfigureerde emailwachtrijen kan de helpdesk geen email ontvangen.',
    'wineditcatchall'        => 'Catch-allregel bewerken',
    'titledelcatchall'       => 'Catch-allregels verwijderd (%d)',
    'msgdelcatchall'         => 'De volgende catch-allregels zijn verwijderd:<br>',
    'titleupdatecatchall'    => 'Catch-allregel (%s) bijgewerkt',
    'msgupdatecatchall'      => 'De catch-allregel (%s) is succesvol bijgewerkt.',
    'titleinsertcatchall'    => 'Catch-allregel gemaakt',
    'msginsertcatchall'      => 'De catch-allregel (%s) is succesvol gemaakt.',
    'catchallrules'          => 'Catch-allregels',
    'desc_catchallrules'     => 'Catch-all regels maken gebruik van regliere expressies om de adressen van inkomende emailberichten te vergelijken. De berichten kunnen dan worden doorgestuurd naar de gewenste emailwachtrij. Zie voor meer informatie over reguliere expressies <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">preg_match()</a>.',
    'insertrule'             => 'Regel toevoegen',
    'insertnewrule'          => 'Nieuwe revel toevoegen',
    'editrule'               => 'Regel bewerken',
    'rregexp'                => 'Patroonvergelijking',
    'desc_rregexp'           => 'Voer een reguliere expressie in om te vergelijken met het emailadres van de afzender. Als deze overeenkomt, wordt deze catch-allregel uitgevoerd. Bijvoorbeeld:<br /><br /><strong>/^(.*)(\\@jouwdomein\\.com)$/i</strong> komt overeen met elk emailadres met het domein jouwdomein.com (*@jouwdomein.com)<br /><strong>/^(support\\@)(.*)$/i</strong> komt overeen met support@*',
    'sortorder'              => 'Uitvoeringsvolgorde',
    'desc_sortorder'         => 'Catch-allregels worden uitgevoerd volgens hun uitvoeringsvolgordenummer, van laag naar hoog (1 wordt uitgevoerd voor 5).',
    'emailqueue'             => 'Route naar emailwachtrij',
    'desc_emailqueue'        => 'Inkomende email die door deze catch-allregel wordt opgevangen wordt naar deze emailwachtrij gestuurd.',
    'ruletitle'              => 'Regeltitel',
    'desc_ruletitle'         => 'Bijvoorbeeld <em>Regel om alles van @acme.com af te vangen</em>.',
    'noemailqueueadd'        => '-- Geen emailwachtrijen geconfigureerd --',
    'editcatchallrule'       => 'Catch-allregel bewerken',
    'emailqueue'             => 'Route naar emailwachtrij',

    // Potentialy unused phrases in mailparser_misc.php
    'invalidemailaddress'    => 'Invalid email address specified',

    'errnoqueue'             => 'ERROR: You do not have any email queues in the database. You need to have at least one email queue to utilize catch-all rules.',
    'catchruleinsertconfirm' => 'Catch-all rule "%s" inserted successfully',
    'catchruleupdconfirm'    => 'Catch-all rule "%s" updated successfully',
    'catchallruledelconfirm' => 'Catch-all rule(s) deleted successfully',
    'invalidcatchalrule'     => 'Invalid catch all rule specified',
    'cruledelconfirmmsg'     => 'Are you sure you wish to delete this catch-all rule?  This action is irreversible!',
);

return $__LANG;
