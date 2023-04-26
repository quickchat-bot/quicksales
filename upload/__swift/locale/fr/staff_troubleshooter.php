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
    'troubleshooter'             => 'Dépannage',
    'categories'                 => 'Catégories',
    'titletrcatmismatch'         => 'Il existe déjà une catégorie appelée %s',
    'msgtrcatmismatch'           => 'Une catégorie de dépannage ayant le nom %s existe déjà. Veuillez en choisir un autre.',
    'wininsertcategory'          => 'Ajouter la catégorie',
    'wineditcategory'            => 'Modifier la catégorie : %s',
    'insertcategory'             => 'Ajouter la catégorie',
    'editcategory'               => 'Modifier la catégorie',

    'titletrcategoryinsert'      => 'Catégorie de dépannage (%s) créée',
    'msgtrcategoryinsert'        => 'Catégorie de dépannage (%s) créée avec succès.',
    'titletrcategoryupdate'      => 'Catégorie de dépannage (%s) mise à jour',
    'msgtrcategoryupdate'        => 'Catégorie de dépannage (%s) mise à jour avec succès.',

    'trcategory'                 => 'Nom de la catégorie',
    'desc_trcategory'            => 'Par exemple, <em>Mon appareil ne s\'allume pas</em>.',
    'next'                       => 'Suivante',

    'tabgeneral'                 => 'Généralités',
    'taboptions'                 => 'Options',
    'categorytype'               => 'Visibilité',
    'desc_categorytype'          => '<strong>Global</strong> Accessible à tous, y compris le personnel et les utilisateurs finaux. <br /> <strong>Public</strong> Disponible uniquement aux utilisateurs finaux dans le centre d\'assistance. <br /> <strong>Privé</strong> disponible uniquement pour votre personnel dans le tableau de bord du personnel.',
    'displayorder'               => 'Ordre d\'affichage',
    'desc_displayorder'          => 'Les catégories de dépannage sont répertoriées selon leur numéro d\'ordre d\'affichage, du premier au dernier.',
    'tabpermissionsuser'         => 'Autorisations : utilisateurs',
    'tabpermissionsstaff'        => 'Autorisations : personnel',
    'uservisibilitycustom'       => 'Limiter à des groupes d\'utilisateurs spécifiques',
    'desc_uservisibilitycustom'  => 'Si activé, seuls les groupe d\'utilisateurs ci-dessous seront en mesure de voir cette catégorie.',
    'staffvisibilitycustom'      => 'Limiter à des équipes du personnel spécifiques',
    'desc_staffvisibilitycustom' => 'Si activé, seules les équipes ci-dessous seront en mesure de voir cette catégorie.',
    'usergroups'                 => 'Groupes d\'utilisateurs',
    'staffteams'                 => 'Équipes d\'employés',

    'tablist'                    => 'Liste',
    'tabtree'                    => 'Arbre',
    'steptitle'                  => 'Titre de l\'étape',
    'categorytitle'              => 'Catégorie',
    'author'                     => 'Auteur',
    'stepstatus'                 => 'État',

    /**
     * ---------------------------------------------
     * INSERT TROUBLESHOOTER
     * ---------------------------------------------
     */
    'titletrstepinsert'          => 'Étape de dépannage (%s) créée',
    'msgtrstepinsert'            => 'L\'étape de dépannage (%s) a été créée avec succès.',
    'titletrstepupdate'          => 'Étape de dépannage (%s) mise à jour',
    'msgtrstepupdate'            => 'L\'étape de dépannage (%s) a été mise à jour avec succès.',
    'insertstep'                 => 'Ajouter une étape',
    'recursionsuffix'            => ' (Récursivité possible)',
    'parentcategoryitem'         => '- Catégorie racine -',
    'desc_parentsteps'           => '',
    'tabgeneral'                 => 'Généralités',
    'tabattachments'             => 'Pièces jointes',
    'taboptions'                 => 'Options',
    'publish'                    => 'Publier',
    'published'                  => 'Publié',
    'draft'                      => 'Brouillon',
    'infobauthor'                => 'AUTEUR',
    'infobcreationdate'          => 'CRÉÉE LE',
    'infobeditedby'              => 'MODIFIÉ PAR',
    'infobeditedon'              => 'MODIFIÉ LE',
    'ftarticletype'              => 'Type',
    'ftcategories'               => 'Catégories',
    'ftdate'                     => 'Date',
    'informationbox'             => 'Information',
    'quickfilter'                => 'Filtre rapide',
    'markasdraft'                => 'Marquer comme projet',
    'saveasdraft'                => 'Enregistrer comme projet',
    'addfile'                    => 'Ajouter fichier',
    'steptitle'                  => 'Titre de l\'étape',
    'desc_steptitle'             => '',
    'parentsteps'                => 'Étapes parents',
    'desc_parentsteps'           => '',
    'generalsettings'            => 'Paramètres généraux',
    'allowcomments'              => 'Autoriser les commentaires',
    'desc_allowcommentsstep'     => 'Défini si les commentaires seront autorisés pour cette étape.',
    'noticketrediractive'        => '-- Aucun service sélectionné --',
    'ticketredirectionsettings'  => 'Paramètres de transfert vers un ticket',
    'redirecttickets'            => 'Tranférer vers un ticket à cette étape',
    'desc_redirecttickets'       => 'Activez ce paramètre pour inviter l\'utilisateur à créer un ticket dès qu\'il atteint cette étape (c.-à-d., le bout de la ligne).',
    'redirectdepartment'         => 'Service de ticket',
    'desc_redirectdepartment'    => 'Si aucun service n\'est sélectionnée, l\'utilisateur devra choisir le sien.',
    'ticketsubject'              => 'Objet du ticket',
    'desc_ticketsubject'         => 'Si aucun objet de ticket n\'est spécifié, l\'utilisateur pourra en entrer un de son choix.',
    'tickettype'                 => 'Type de ticket',
    'desc_tickettype'            => 'Spécifiez le type du ticket pour le ticket que l\'utilisateur ayant atteint cette étape sera amené à créer.',
    'ticketpriority'             => 'Priorité du ticket',
    'desc_ticketpriority'        => 'Si aucune priorité n\'est spécifiée, l\'utilisateur sera en mesure de choisir la priorité du ticket (le cas échéant).',
    'nochange'                   => '-- Aucun changement --',

    /**
     * ---------------------------------------------
     * VIEW ALL
     * ---------------------------------------------
     */
    'tabviewall'                 => 'Dépannage',
    'troubleshooterdesc'         => 'Les assistants de dépannage vous guident à travers une série de mesures pour diagnostiquer et résoudre les problèmes communs. Commencez par choisir une catégorie de dépannage.',
    'trattachments'              => 'Pièces jointes',
    'trnextsteps'                => 'Prochaines étapes',
    'trnext'                     => 'Suivante',
    'trback'                     => 'Retour',
    'steps'                      => 'Étapes',
    'trcategoryviews'            => ' (Affichages %d)',

    // Potentialy unused phrases in staff_troubleshooter.php
    'desc_categorytitle'         => 'Enter the category title',

);

return $__LANG;
