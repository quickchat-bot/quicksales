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
    'mpsettings'             => 'Impostazioni',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Impostazioni',
    'mailparser'             => 'Analizzatore Email',


    'titleinsertban'         => 'Bloccato indirizzo email',
    'msginsertban'           => 'L\'indirizzo email %s è stato bloccato e l\'analizzatore email non elaborerà più email da questo indirizzo.',
    'titleupdateban'         => 'Aggiornato il blocco per l\'indirizzo email',
    'msgupdateban'           => 'È stato aggiornato con successo il blocco per l\'indirizzo email %s.',
    'wineditban'             => 'Modifica il Blocco Analizzatore',
    'titledelbans'           => 'Blocco indirizzi email rimossi (%d)',
    'msgdelbans'             => 'Sono stati eliminati i seguenti blocchi di indirizzo email:',

    // Email Bans
    'emailbans'              => 'Blocchi Email',
    'emailbanlist'           => 'Elenco Blocchi Email',
    'bannedemail'            => 'Indirizzo email',
    'bannedby'               => 'Bloccato da',
    'insertban'              => 'Inserisci Blocco',
    'insertnewban'           => 'Inserisci Nuovo Blocco',
    'desc_bannedemail'       => 'Inserisci l\'indirizzo email da bloccare. Eventuali messaggi in arrivo da questo indirizzo verranno ignorati dall\'helpdesk. Per bloccare un intero dominio, immettere <em>@ilnomedominio.it</em>.',
    'parserbaninsertconfirm' => 'Blocco email "%s" inserito con successo',
    'parserbansdelconfirm'   => 'Blocco email eliminato/i con successo',
    'invalidemailban'        => 'L\'ID blocco indirizzo email specificato non è valido',
    'editban'                => 'Modifica Blocco',
    'bandelconfirmmsg'       => 'Sei sicuro di voler eliminare questa voce di blocco email?  Questa azione è irreversibile!',
    'parserbanupdconfirm'    => 'Blocco di indirizzo email %s aggiornato',
    'desc_mailbans'          => 'È possibile impedire agli utenti problematici di inviare ticket con l\'aggiunta di un blocco email per il loro indirizzo.  Ogni volta che QuickSupport riceve un email, verificherà l\'indirizzo del mittente sull\'elenco dei blocchi, e se l\'indirizzo è bloccato il messaggio verrà ignorato senza preavviso.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Inserisci Regola di Catch-all',
    'titlenqcatchall'        => 'Configura le code email prima di creare regole di catch-all',
    'msgnqcatchall'          => 'Le regole di catch-all determinano cosa accade alla posta che va all\'helpdesk. Se non ci sono code email configurate, le email non possono arrivare all\'helpdesk.',
    'wineditcatchall'        => 'Modifica Regola di Catch-all',
    'titledelcatchall'       => 'Regole di catch-all eliminate (%d)',
    'msgdelcatchall'         => 'Sono state eliminate le seguenti regole di catch-all:<br>',
    'titleupdatecatchall'    => 'Regola di catch-all (%s) aggiornata',
    'msgupdatecatchall'      => 'La regola di catch-all (%s) è stata aggiornata con successo.',
    'titleinsertcatchall'    => 'Regola di catch-all creata',
    'msginsertcatchall'      => 'La regola di catch-all (%s) è stata creata con successo.',
    'catchallrules'          => 'Regole di Catch-all',
    'desc_catchallrules'     => 'Le regole di catch-all usano espressioni regolari per confrontare gli indirizzi dei messaggi email in arrivo. I messaggi possono quindi essere indirizzati a qualsiasi coda email desiderata. Per ulteriori informazioni sulle espressioni regolari, vedi <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">preg_match()</a>.',
    'insertrule'             => 'Inserisci Regola',
    'insertnewrule'          => 'Inserisci Nuova Regola',
    'editrule'               => 'Modifica Regola',
    'rregexp'                => 'L\'espressione corrisponde',
    'desc_rregexp'           => 'Immetti un\'espressione regolare per confrontarla all\'indirizzo email del mittente. Se corrisponde, questa regola di catch-all verrà attivata. Per esempio:<br /><br /><strong>/^(.*)(\\@tuodominio\\.it)$/i</strong> Corrisponde a qualsiasi indirizzo email che ha un dominio di tuodominio.it (*@tuodominio.it)<br /><strong>/^(support\\@)(.*)$/i</strong> Corrisponde a support@*',
    'sortorder'              => 'Ordine di esecuzione',
    'desc_sortorder'         => 'Le regole di catch-all saranno eseguite secondo il numero di ordine di esecuzione, dal più piccolo al più grande (1 verrà eseguito prima di 5).',
    'emailqueue'             => 'Indirizza verso la coda email',
    'desc_emailqueue'        => 'La posta in arrivo catturata da questa regola di catch-all verrà indirizzata a questa coda di posta elettronica.',
    'ruletitle'              => 'Titolo regola',
    'desc_ruletitle'         => 'Ad esempio, <em>Regola per prendere tutto @acme.com</em>.',
    'noemailqueueadd'        => '-- Nessuna coda email configurata --',
    'editcatchallrule'       => 'Modifica Regola di Catch-all',
    'emailqueue'             => 'Indirizza verso la coda email',

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
