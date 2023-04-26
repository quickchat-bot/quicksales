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
    'mpsettings'             => 'Paramètres',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Paramètres',
    'mailparser'             => 'Analyseur d\'email',


    'titleinsertban'         => 'Adresse email bannie',
    'msginsertban'           => 'L\'adresse email %s a été bannie et l\'analyseur de courriel ne traitera plus les emails provenant de cette adresse.',
    'titleupdateban'         => 'Bannissement d\'adresse email mis à jour',
    'msgupdateban'           => 'Le bannissement de l\'adresse email pour %s a été correctement mis à jour.',
    'wineditban'             => 'Modifier le paramétrage de bannissement de l\'analyseur',
    'titledelbans'           => 'Le bannissement de l\'email (%d) à été levé',
    'msgdelbans'             => 'Les bannissements affectant les emails suivants ont été supprimés :',

    // Email Bans
    'emailbans'              => 'Bannissement d\'adresses email',
    'emailbanlist'           => 'Liste d\'email bannis',
    'bannedemail'            => 'Adresse email',
    'bannedby'               => 'Bannie par',
    'insertban'              => 'Ajouter une bannissement',
    'insertnewban'           => 'Ajouter un nouveau ban',
    'desc_bannedemail'       => 'Entrez l\'adresse email à interdire. Tous les messages électroniques provenant de cette adresse seront ignorées par le helpdesk. Pour bannir un nom de domaine entier, entrez <em>@nomdedomaine.com</em>.',
    'parserbaninsertconfirm' => 'Le bannisement de l\'adresse email « %s » a été ajouté avec succès',
    'parserbansdelconfirm'   => 'Bannissement(s) d\'adresse(s) email supprimé(s) avec succès',
    'invalidemailban'        => 'ID de bannissement d\'email spécifiée non valide',
    'editban'                => 'Modifier le bannissement',
    'bandelconfirmmsg'       => 'Êtes-vous sûre de que vouloir supprimer ce bannissement d\'email ?  Cette action est irréversible !',
    'parserbanupdconfirm'    => 'Bannissement d\'email de %s mis à jour',
    'desc_mailbans'          => 'Vous pouvez empêcher les utilisateurs à problème de soumettre des tickets en ajoutant un bannissement pour leur email.  Chaque fois que Kayako reçoit un email, il compare l\'adresse de l\'auteur à la liste de bannissement et si l\'adresse est bannie, le message sera ignoré sans préavis.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Ajouter la règle fourre-tout',
    'titlenqcatchall'        => 'Configurez les files d\'attente de messagerie avant de créer des règles fourre-tout',
    'msgnqcatchall'          => 'Les règles fourre-tout déterminent le traitement des emails qui arrivent dans le helpdesk. Si les files d\'attente ne sont pas configurées, le emails ne peuvent pas arriver au helpdesk.',
    'wineditcatchall'        => 'Modifier la règle fourre-tout',
    'titledelcatchall'       => 'Règle fourre-tout supprimée (%d)',
    'msgdelcatchall'         => 'Les règles fourre-tout suivantes ont été supprimées : <br>',
    'titleupdatecatchall'    => 'Règle fourre-tout (%s) mise à jour',
    'msgupdatecatchall'      => 'La règle fourre-tout (%s) a été mise à jour avec succès.',
    'titleinsertcatchall'    => 'Règle fourre-tout créée',
    'msginsertcatchall'      => 'La règle fourre-tout (%s) a été créée avec succès.',
    'catchallrules'          => 'Règles fourre-tout',
    'desc_catchallrules'     => 'Les règles fourre-tout règles utilisent des expressions régulières pour identifier les emails entrants. Les emails peuvent être routés vers la file d\'attente de votre choix. Pour plus d\'informations sur les expressions régulières, consultez  <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">preg_match()</a>.',
    'insertrule'             => 'Ajouter une règle',
    'insertnewrule'          => 'Ajouter une nouvelle règle',
    'editrule'               => 'Modifier la règle',
    'rregexp'                => 'Correspondance de motif',
    'desc_rregexp'           => 'Entrez une expression régulère à faire correspondre à l\'adresse e-mail de l\'expéditeur. Si elle correspond, cette règle fourre-tout se déclenche. Par exemple :<br /><br /><strong>/^(.*)(\\@votredomaine\\.com)$/i</strong> correspond à n\'importe quelle adresse email ayant pour domaine, votredomaine.com (*@votredomaine.com)<br /><strong>/^(support\\@)(.*)$/i</strong> Correspond à support@*',
    'sortorder'              => 'Ordre d\'exécution',
    'desc_sortorder'         => 'Les règles fourre-tout seront exécutées selon leur numéro d\'ordre d\'exécution, du premier au dernier (1 s\'exécutera avant 5).',
    'emailqueue'             => 'Router vers la file d\'attente',
    'desc_emailqueue'        => 'Le courrier entrant capturé par cette règle de fourre-tout sera routé vers cette file d\'attente.',
    'ruletitle'              => 'Titre de la règle',
    'desc_ruletitle'         => 'Par exemple, <em>Règle pour attrapper tous les @acme.com</em>.',
    'noemailqueueadd'        => '-- Aucune file d\'attente configurée --',
    'editcatchallrule'       => 'Modifier la règle fourre-tout',
    'emailqueue'             => 'Router vers la file d\'attente',

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
