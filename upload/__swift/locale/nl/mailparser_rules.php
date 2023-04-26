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
    'isenabled'                 => 'Emailparserregel ingeschakeld',
    'desc_isenabled'            => 'Emailregels worden genegeerd als ze zijn uitgeschakeld.',
    'insertcriteria'            => 'Criteria toevoegen',
    'tabactions'                => 'Acties',
    'titlenoaction'             => 'Geen acties opgegeven',
    'msgnoaction'               => 'Er moet tenminste één actie worden opgegeven die de emailregel moet uitvoeren (als er een overeenkomst is).',
    'if'                        => 'Als',
    'titleinsertrule'           => 'Emailregel (%s) gemaakt',
    'msginsertrule'             => 'Emailregel (%s) succesvol gemaakt.',
    'titleupdaterule'           => 'Emailregel (%s) bijgewerkt',
    'msgupdaterule'             => 'Emailparserregel "%s" is succesvol bijgewerkt.',
    'titledelprules'            => 'Emailregels verwijderd (%d)',
    'msgdelprules'              => 'De volgende emailregels zijn verwijderd:',
    'titleenableprules'         => 'Emailregels ingeschakeld (%d)',
    'msgenableprules'           => 'De volgende emailregels zijn ingeschakeld:',
    'titledisableprules'        => 'Emailregels uitgeschakeld (%d)',
    'msgdisableprules'          => 'De volgende emailregels zijn uitgeschakeld:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Emailparser',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Bevat',
    'opnotcontains'             => 'Bevat niet',
    'opequal'                   => 'Gelijk aan',
    'opnotequal'                => 'Niet gelijk aan',
    'opgreater'                 => 'Groter dan',
    'opless'                    => 'Kleiner dan',
    'opregexp'                  => 'Reguliere expressie',
    'strue'                     => 'Waar',
    'sfalse'                    => 'Niet waar',

    // Criteria
    'psendername'               => 'Naam afzender',
    'desc_psendername'          => 'De afzendernaam wordt afgeleid uit de "From:"-header van het emailbericht.',

    'psenderemail'              => 'Emailadres afzender',
    'desc_psenderemail'         => 'Het e-mailadres van de afzender wordt afgeleid uit de "From:" kop van het e-mailbericht. Voorbeeld: <em>From: <span class="tabletitle">Afzendernaam</span> <afzenderemail@domein.com></em>',

    'pdestinationname'          => 'Naam bestemming (ontvanger)',
    'desc_pdestinationname'     => 'De bestemmingsnaam wordt afgeleid uit de "To:"-header van de email. De bestemmingsnaam is gewoonlijk de naam van de medewerker die op de ticket heeft geantwoord (tenzij anders geconfigureerd).',

    'pdestinationemail'         => 'Emailadres bestemming (ontvanger)',
    'desc_pdestinationemail'    => 'Het bestemmingsemailadres wordt afgeleid uit de "To:"-header van het emailbericht. Dit is gewoonlijk het adres van de emailwachtrij waar het antwoord naartoe wordt verzonden.',

    'preplytoname'              => 'Naam antwoorden aan',
    'desc_preplytoname'         => 'De naam voor de ontvanger om aan te antwoorden wordt afgeleid uit de "Replyl-To:"-header van het emailbericht.',

    'preplytoemail'             => 'Emailadres antwoorden aan',
    'desc_preplytoemail'        => 'Het emailadres om aan te antwoorden wordt afgeleid uit de "Reply-To:"-header van het emailbericht.',

    'psubject'                  => 'Onderwerp',
    'desc_psubject'             => 'Komt overeen met het onderwerp van de e-mail.',

    'precipients'               => 'Ontvangers',
    'desc_precipients'          => 'Komt overeen met de emailontvangers. Er kunnen meerdere ontvangers zijn aan wie een inkomende email is verzonden, waaronder in de "To:" en "CC:"-headers van het emailbericht.',

    'pbody'                     => 'Inhoud',
    'desc_pbody'                => 'Vergelijkt de resterende inhoud van de emailberichttekst nadat HTML (tenzij anders geconfigureerd) en breukregels zijn verwijderd.',

    'pbodysize'                 => 'Lengte inhoud',
    'desc_pbodysize'            => 'De totale grootte van de inhoud van de email in bytes.',

    'ptextbody'                 => 'Tekstinhoud',
    'desc_ptextbody'            => 'Een email kan twee typen inhoud hebben: <em>platte tekst</em>, <em>HTML</em>, of <em>beide</em>. Deze regel doorzoekt alleen de <em>tekst</em>versie van de inhoud. Merk op dat niet alle emails in <em>beide</em> indelingen worden verzonden.',

    'phtmlbody'                 => 'HTML-inhoud',
    'desc_phtmlbody'            => 'Een email kan twee typen inhoud hebben: <em>platte tekst</em>, <em>HTML</em>, of <em>beide</em>. Deze regel doorzoekt alleen de <em>HTML</em>-versie van de inhoud. Merk op dat niet alle emails in <em>beide</em> indelingen worden verzonden.',

    'ptextbodysize'             => 'Grootte tekstinhoud',
    'desc_ptextbodysize'        => 'Een email kan twee typen inhoud hebben: <em>platte tekst</em>, <em>HTML</em>, of <em>beide</em>. Deze regel vergelijkt de totale grootte van de <em>tekst</em>inhoud van de email in bytes.',

    'phtmlbodysize'             => 'Grootte HTML-inhoud',
    'desc_phtmlbodysize'        => 'Een email kan twee typen inhoud hebben: <em>platte tekst</em>, <em>HTML</em>, of <em>beide</em>. Deze regel vergelijkt de totale grootte van de <em>HTML</em>-inhoud van de email in bytes.',

    'pattachmentname'           => 'Bijlagenamen',
    'desc_pattachmentname'      => 'Vergelijkt namen van bijlagen die in de helpdesk zijn geaccepteerd (als gedefinieerd in het adminbeheerscherm).',

    'pattachmentsize'           => 'Bijlagegrootte',
    'desc_pattachmentsize'      => 'Vergelijkt de grootte van bijlagen die in de helpdesk zijn geaccepteerd (als gedefinieerd in het adminbeheerscherm).',

    'ptotalattachmentsize'      => 'Totale grootte bijlagen',
    'desc_ptotalattachmentsize' => 'Vergelijkt de <em>totale grootte</em> (in bytes) van bijlagen die in de helpdesk zijn geaccepteerd (als gedefinieerd in het adminbeheerscherm).',

    'pisreply'                  => 'Is een antwoord van een gebruiker',
    'desc_pisreply'             => 'Als de inkomende email een antwoord is op een bestaande ticket door een eindgebruiker, komt deze regel overeen.',

    'pisthirdparty'             => 'Is een antwoord van een derde',
    'desc_pisthirdparty'        => 'Als de inkomende email een antwoord is op een bestaande ticket van een adres dat aan de ticket is toegevoegd als derde, komt deze regel overeen.',

    'pfloodprotection'          => 'Floodbescherming getriggerd',
    'desc_pfloodprotection'     => 'Deze regel komt overeen als de floodbescherming van de helpdesk is getriggerd.',

    'pisstaffreply'             => 'Is een antwoord van een medewerker',
    'desc_pisstaffreply'        => 'Als de inkomende email een antwoord is op een bestaande ticket van een emailadres van een medewerker, komt deze regel overeen.',

    'pticketstatus'             => 'Ticketstatus (na parsen)',
    'desc_pticketstatus'        => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt de ticketstatus vergeleken door deze regel.',

    'pticketemailqueue'         => 'Emailwachtrij',
    'desc_pticketemailqueue'    => 'Als de inkomende email uit de opgegeven wachtrij is opgehaald, komt deze regel overeen.',

    'ptickettype'               => 'Tickettype (na parsen)',
    'desc_ptickettype'          => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt het tickettype vergeleken door deze regel.',

    'pticketpriority'           => 'Ticketprioriteit (na parsen)',
    'desc_pticketpriority'      => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt de prioriteit van de ticket vergeleken door deze regel.',

    'pticketusergroup'          => 'Ticketgebruikersgroep (na parsen)',
    'desc_pticketusergroup'     => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt de gebruikersgroep van de ticket vergeleken door deze regel.',

    'pticketdepartment'         => 'Ticketafdeling (na parsen)',
    'desc_pticketdepartment'    => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt de afdeling van de ticket vergeleken door deze regel.',

    'pticketowner'              => 'Ticketeigenaar (na parsen)',
    'desc_pticketowner'         => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt de eigenaar vergeleken door deze regel.',
    'prunassigned'              => '-- Niet toegewezen --',

    'pticketflagtype'           => 'Ticket flag (post-parse)',
    'desc_pticketflagtype'      => 'Als de inkomende email een antwoord is op een bestaande ticket, wordt de vlag van de ticket vergeleken door deze regel.',

    'pbayescategory'            => 'Bayesiaanse categorie',
    'desc_pbayescategory'       => 'De helpdesk haalt alle inkomende email door het Bayesiaanse filter en wijst een Bayesiaanse categorie met de hoogste score toe aan de ticket. Je kunt dit gebruiken om spam te filteren of het systeem te trainen automatisch tickets te routeren op basis van inhoud, door middel van aangepaste Bayesiaanse categorieën.',

    // Insert Rule
    'insertrule'                => 'Regel toevoegen',
    'iruletype'                 => 'Emailregeltype: ',
    'ipreparse'                 => 'Voordat de email is verwerkt (voor parsen)',
    'ipostparse'                => 'Nadat de email is verwerkt (na parsen)',

    'paignore'                  => 'Email negeren',
    'desc_paignore'             => 'De email wordt genegeerd door de helpdesk en wordt niet verwerkt tot een ticket of antwoord.',
    'panoautoresp'              => 'Geen automatisch antwoord verzenden',
    'desc_panoautoresp'         => 'Er wordt geen automatisch antwoord verzonden als ontvangstbevestiging van nieuwe email (als nieuwe ticket of antwoord).',
    'panoalerts'                => 'Notificatieregels niet verwerken',
    'desc_panoalerts'           => 'De helpdesknotificatieregels worden niet verwerkt, als dit is ingeschakeld.',
    'pnochange'                 => '-- Geen wijziging --',
    'pcstaff'                   => 'Ticket toewijzen',
    'desc_pcstaff'              => 'De ticket (hetzij een nieuwe of een antwoord op een bestaande) wordt aan deze medewerker toegewezen.',
    'pcstatus'                  => 'Ticketstatus wijzigen',
    'desc_pcstatus'             => 'De ticket wordt gewijzigd naar deze status.',
    'pcpriority'                => 'Ticketprioriteit wijzigen',
    'desc_pcpriority'           => 'De ticket wordt gewijzigd naar deze prioriteit.',
    'pcdepartment'              => 'Ticket verplaatsen naar afdeling',
    'desc_pcdepartment'         => 'De ticket wordt verplaatst naar deze afdeling.',
    'pcslaplan'                 => 'SLA-plan van ticket wijzigen',
    'desc_pcslaplan'            => 'De ticket wordt aan dit SLA-plan toegewezen.',
    'pcmovetotrash'             => 'Verplaatsen naar prullenbak',
    'desc_pcmovetotrash'        => 'De ticket wordt verplaatst naar de prullenbak',
    'pcflag'                    => 'Ticket vlaggen',
    'desc_pcflag'               => 'De ticket wordt met deze kleur gemarkeerd.',
    'paddnotes'                 => 'Notitie toevoegen aan de ticket',
    'desc_paddnotes'            => 'Er wordt een notitie toegevoegd aan de ticket.',
    'pcforward'                 => 'Email doorsturen',
    'desc_pcforward'            => 'De email wordt doorgestuurd naar dit adres.',
    'preply'                    => 'Email beantwoorden',
    'desc_preply'               => 'De email wordt automatisch beantwoord met dit bericht.',
    'panoticket'                => 'Niet verwerken als antwoord op ticket',
    'desc_panoticket'           => 'Zelfs als de email een antwoord is op een bestaande ticket, wordt de email niet toegevoegd aan de bestaande ticket, maar wordt er een nieuwe ticket gemaakt.',
    'pctickettype'              => 'Tickettype wijzigen',
    'desc_pctickettype'         => 'Het tickettype wordt gewijzigd naar het hier opgegeven type.',
    'pcaddtags'                 => 'Tags toevoegen aan ticket',
    'desc_pcaddtags'            => 'Deze tags worden aan de ticket toegevoegd.',
    'pcremovetags'              => 'Tags verwijderen van ticket',
    'desc_pcremovetags'         => 'Als de ticket een of meerdere van deze tags heeft, worden deze verwijderd.',
    'pcprivate'                 => 'Antwoord op ticket privé maken',
    'desc_pcprivate'            => 'Dit antwoord op een ticket wordt als privé gemarkeerd en is alleen zichtbaar voor andere medewerkergebruikers (dit is alleen van toepassing op antwoorden van medewerkers).',

    // Edit Rules
    'editrule'                  => 'Regel bewerken',
    'ptitle'                    => 'Titel emailregel',
    'desc_ptitle'               => 'Bijvoorbeeld <em>PayPal-ontvangsten</em>.',
    'pstop'                     => 'Stoppen met verwerken van andere regels',
    'desc_pstop'                => 'Als aan deze regel is voldaan, worden na deze regel geen andere regels verwerkt.',

    // Manage Rules
    'managerules'               => 'Regels',
    'ruletitle'                 => 'Regeltitel',
    'sortorder'                 => 'Uitvoeringsvolgorde',
    'desc_sortorder'            => 'Het is mogelijk dat dezelfde email voldoet aan meerdere emailregels. De helpdesk verwerkt de regels aan de hand van het uitvoeringsvolgordenummer, van laag naar hoog.',
    'ruletype'                  => 'Regeltype',
    'creationdate'              => 'Gemaakt',

    // Potentialy unused phrases in mailparser_rules.php
    'smatchtype'                => 'Match Type',
    'matchtype'                 => 'Criteria Options',
    'desc_matchtype'            => 'Select the grouping method for the criteria fields.',
    'smatchall'                 => 'Match <b>All</b> Criteria (AND)',
    'smatchany'                 => 'Match <b>Any</b> Criteria (OR)',
    'criteria'                  => 'Criteria',
    'newcriteria'               => 'New Criteria',
    'ruleinsertconfirm'         => 'Parser rule "%s" inserted successfully',
    'ruleupdateconfirm'         => 'Parser rule "%s" updated successfully',
    'invalidrule'               => 'ERROR: Invalid email parser rule',
    'ruledelconfirm'            => 'Parser rule deleted successfully',
    'rulelist'                  => 'Rule List',
    'notapplicable'             => '-- NA --',

);

return $__LANG;
