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
    'notifications'                  => 'Notificaties',
    'manage'                         => 'Beheren',
    'ruletitle'                      => 'Regeltitel',
    'desc_ruletitle'                 => 'Voer een titel in voor de notificatieregel.',
    'isenabled'                      => 'Notificatieregel ingeschakeld',
    'desc_isenabled'                 => 'Bepaal of deze regel in- of uitgeschakeld is.',
    'ruletype'                       => 'Type',
    'desc_ruletype'                  => 'De notificatieregel kan worden gekoppeld aan een van de volgende zaken.',
    'next'                           => 'Volgende',
    'creator'                        => 'Auteur',
    'wininsertnotification'          => 'Notificatie toevoegen',

    'unassigned'                     => '-- Niet toegewezen --',

    'tabgeneral'                     => 'Algemeen',
    'tabactions'                     => 'Acties',
    'insertcriteria'                 => 'Criteria toevoegen',

    'na_email'                       => 'Email',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Desktoptoepassing',
    'na_staff'                       => 'Eigen medewerkergebruikersaccount',
    'na_staffgroup'                  => 'Team',
    'na_department'                  => 'Afdeling',
    'na_user'                        => 'Gebruiker',
    'nactionemaildispatch'           => 'Email verzenden naar de ... van de ticketeigenaar',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Email verzenden naar andere emailadressen',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Email verzenden naar andere medewerker',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Notificatie verzenden naar de ... van de eigenaar van de actieve ticket',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Medewerker',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Selecteer een notificatieregeltype.',
    'titlenocriteria'                => 'Geen criteria opgegeven',
    'msgnocriteria'                  => 'Er is tenminste één criterium nodig om een notificatieregel te maken.',
    'titlenotificationinsert'        => 'Notificatieregel (%s) gemaakt',
    'msgnotificationinsert'          => 'De notificatieregel (%s) is succesvol gemaakt.',
    'titlenotificationupdate'        => 'Notificatieregel (%s) bijgewerkt',
    'msgnotificationupdate'          => 'De notificatieregel (%s) is succesvol bijgewerkt.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Email',
    'titleenablenotification'        => 'Notificatieregels ingeschakeld (%d)',
    'msgenablenotification'          => 'De volgende notificatieregels zijn ingeschakeld:<br />%s',
    'titledisablenotification'       => 'Notificatieregels uitgeschakeld (%d)',
    'msgdisablenotification'         => 'De volgende notificatieregels zijn uitgeschakeld:<br />%s',

    'emailprefix'                    => 'Emailonderwerpvoorvoegsel',
    'desc_emailprefix'               => 'Geef een emailonderwerpvoorvoegsel op om de door deze notificatieregel verzonden emails makkelijk te kunnen herkennen.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Chat',
    'nrule_message'                  => 'Offline bericht',
    'nrule_survey'                   => 'Chatonderzoek',
    'nrule_ticket'                   => 'Ticket',
    'nrule_user'                     => 'Gebruiker',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Tickettype',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Bayesiaanse categorie',
    'desc_nbayesian'                 => 'Tickets die overeenkomen met een bepaalde Bayesiaanse categorie.',
    'notapplicable'                  => '-- Niet beschikbaar --',
    'nticketstatus'                  => 'Ticketstatus',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Ticketprioriteit',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Gebruikersgroep',
    'desc_nusergroup'                => 'Zoekt naar tickets waarbij de ontvangers tot een bepaalde gebruikersgroep behoren.',
    'ndepartment'                    => 'Ticketafdeling',
    'desc_ndepartment'               => 'Tickets die tot een afdeling behoren.',
    'nowner'                         => 'Ticketeigenaar',
    'desc_nowner'                    => 'Tickets die aan een bepaalde medewerker zijn toegewezen.',
    'nunassigned'                    => '-- Niet toegewezen --',
    'nactivestaff'                   => '-- Actieve medewerker --',
    'nemailqueue'                    => 'Emailwachtrij',
    'desc_nemailqueue'               => 'Tickets die zijn gemaakt of beantwoord per email via een bepaalde emailwachtrij.',
    'nflagtype'                      => 'Ticketvlag',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Ticket gemaakt door',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Medewerker',
    'creatorclient'                  => 'Gebruiker',
    'nslaplan'                       => 'SLA-plan',
    'desc_nslaplan'                  => 'Tickets die momenteel aan een bepaald SLA-plan zijn toegewezen.',
    'ndaterange'                     => 'Ticketaanmaakdatum <periode>',
    'desc_ndaterange'                => 'Tickets die binnen deze tijdsperiode zijn gemaakt.',
    'nlastactivityrange'             => 'Ticket laatst bijgewerkt <periode>',
    'desc_nlastactivityrange'        => 'Tickets die zijn bijgewerkt (bijvoorbeeld op reageerd door iemand, of elke andere bijwerkingsgebeurtenis) binnen deze tijdsperiode.',
    'nlaststaffreplyrange'           => 'Laatsre antwoord van medewerker <periode>',
    'desc_nlaststaffreplyrange'      => 'Tickets die een antwoord hebben ontvangen van een medewerker binnen deze tijdsperiode.',
    'nlastuserreplyrange'            => 'Laatste antwoord van gebruiker <periode>',
    'desc_nlastuserreplyrange'       => 'Tickets die een antwoord hebben ontvangen van een gebruiker binnen deze tijdsperiode.',
    'nduerange'                      => 'Beantwoordingsdeadline <periode>',
    'desc_nduerange'                 => 'Tickets met een beantwoordingsdeadline binnen deze tijdsperiode.',
    'nisoverdue'                     => 'Over tijd: antwoord over tijd',
    'desc_nisoverdue'                => 'Tickets die over tijd zijn (omdat ze niet zijn beantwoord voor de beantwoordingsdeadline).',
    'nresolutionduerange'            => 'Afhandelingsdeadline <periode>',
    'desc_nresolutionduerange'       => 'Tickets met een afhandelingsdeadline binnen deze tijdsperiode.',
    'nisresolutionoverdue'           => 'Over tijd: afhandeling over tijd',
    'desc_nisresolutionoverdue'      => 'Tickets die over tijd zijn (omdat ze niet voor de afhandelingsdeadline zijn afgehandeld).',
    'ntemplategroup'                 => 'Sjabloongroep',
    'desc_ntemplategroup'            => 'Tickets die tot een bepaalde sjabloongroep behoren.',
    'ntimeworked'                    => 'Gewerkte tijd (in minuten)',
    'desc_ntimeworked'               => 'Tickets die overeenkomen met deze waarde voor <strong>gewerkte tijd</strong> (als onderdeel van ticketfacturerings- en tijdregistratievermeldingen).',
    'ntotalreplies'                  => 'Totaal aantal antwoorden',
    'desc_ntotalreplies'             => 'Tickets die dit aantal antwoorden hebben.',
    'npendingfollowups'              => 'Wachtend op follow-ups',
    'desc_npendingfollowups'         => 'Tickets met geplande follow-ups.',
    'nipaddress'                     => 'IP-adres',
    'desc_nipaddress'                => 'Als een ticketantwoord is gegeven vanuit het <strong>support center</strong>, heeft de helpdesk mogelijk een IP-adres gelogd voor de gebruiker.',
    'nisemailed'                     => 'Gemaakt via email',
    'desc_nisemailed'                => 'Tickets die gemaakt zijn via email.',
    'nisedited'                      => 'Is bewerkt',
    'desc_nisedited'                 => 'Tickets die zijn bewerkt.',
    'nhasnotes'                      => 'Heeft notities',
    'desc_nhasnotes'                 => 'Tickets met ticketnotities.',
    'nhasattachments'                => 'Heeft bijlagen',
    'desc_nhasattachments'           => 'Tickets met bestandsbijlagen.',
    'nisescalated'                   => 'Is geëscaleerd',
    'desc_nisescalated'              => 'Tickets die geëscaleerd zijn door minstens één escalatieregel.',
    'nhasdraft'                      => 'Heeft een concept',
    'desc_nhasdraft'                 => 'Tickets waarbij een conceptantwoord is opgeslagen.',
    'nhasbilling'                    => 'Heeft facturerings- en tijdregistratievermeldingen',
    'desc_nhasbilling'               => 'Tickets met facturerings- en tijdregistratievermeldingen.',
    'nisphonecall'                   => 'Is een telefoonticket',
    'desc_nisphonecall'              => 'Tickets die zijn gemarkeerd als <em>telefoongesprek</em>type.',
    'nislabeled'                     => 'Is getagd',
    'desc_nislabeled'                => 'Tickets die zijn getagd.',

    'nticketevent'                   => 'Gebeurtenis',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Nieuwe ticket gemaakt',
    'tevent_newticketnotes'          => 'Nieuwe ticketnotitie toegevoegd',
    'tevent_newclientreply'          => 'Nieuw antwoord van eindgebruiker',
    'tevent_newclientsurvey'         => 'Nieuwe indiening van onderzoek',
    'tevent_newstaffreply'           => 'Nieuw antwoord van medewerker',
    'tevent_ticketassigned'          => 'Ticket toegewezen',
    'tevent_newuser'                 => 'Nieuwe gebruikersregistratie',

    'nuserevent'                     => 'Gebeurtenis',
    'desc_nuserevent'                => 'Deze regel wordt getriggerd door deze gebeurtenis.',
);

return $__LANG;
