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
    'troubleshooter'                 => 'Dépannage',

    // Manage Categories
    'categories'                     => 'Catégories',
    'managecategories'               => 'Gérer les catégories',
    'desc_troubleshootercat'         => '',
    'tscattitle'                     => 'Titre de la catégorie',
    'desc_tscattitle'                => '',
    'tscatviews'                     => 'Affichages',
    'desc_tscatviews'                => 'Le nombre de fois que la catégorie a été affichée. Ce nombre augmente automatiquement chaque fois qu\'un utilisateur clique sur une catégorie.',
    'tscatdisporder'                 => 'Ordre d\'affichage',
    'desc_tscatdisporder'            => 'Les catégories sont triées selon leur numéro d\'ordre d\'affichage, du premier au dernier.',
    'tscatlist'                      => 'Liste des catégories',
    'tscatdesc'                      => 'Description',
    'desc_tscatdesc'                 => 'Une description de la catégorie s\'affiche en dessous du titre. La description est limitée à 255 caractères.',
    'steps'                          => 'Étapes',

    // Insert Category
    'insertcategory'                 => 'Ajouter la catégorie',
    'tscatdetails'                   => 'Informations de la catégorie',
    'templategroups'                 => 'Groupes de gabarits',
    'desc_templategroups'            => 'Sélectionnez les groupes de gabarits qui permettent d\'afficher cette catégorie de dépannage dans le centre d\'assistance.',
    'inserttscat'                    => 'Ajouter la catégorie',
    'updatetscat'                    => 'Mise à jour de la categorie',
    'selectonetgroup'                => 'ERREUR : Vous devez sélectionner au moins un groupe de gabarits',
    'troubleshootercatinsertconfirm' => 'Catégorie de dépannage créée (%s)',
    'tcatdeleteconfirm'              => 'Catégorie de dépannage supprimée avec succès',
    'tcatsdeleteconfirm'             => 'Catégorie de dépannage supprimée avec succès',
    'tcatdelconfirm'                 => 'Êtes-vous sûr de vouloir supprimer cette catégorie? \\nEn supprimant une catégorie vous supprimez également toutes les étapes de dépannage qu\'elle contient.',
    'invalidtroubleshootercategory'  => 'Catégorie de dépannage non valide',

    // Edit Category
    'editcategory'                   => 'Modifier la catégorie',
    'troubleshootercatupdateconfirm' => 'Catégorie de dépannage mise à jour : (%s)',

    // Manage Steps
    'troubleshootersteps'            => 'Étapes de dépannage',
    'managesteps'                    => 'Gérer les étapes',
    'addstep'                        => 'Ajouter une étape',
    'addcategory'                    => 'Ajouter la catégorie',
    'troubleshooters'                => 'Dépannages',
    'filter'                         => 'Filtre',
    'stepdelconfirmmsg'              => 'Êtes-vous sûr de que vouloir supprimer cette étape ? En supprimant une étape vous supprimez toutes les étapes enfant qu\'elle inclue.',
    'tsdelconfirm'                   => 'Étapes de dépannage supprimées',
    'stepdeleteconfirm'              => 'Étapes de dépannage %s supprimées',
    'filtertgroupid'                 => 'Groupes de gabarits',
    'desc_filtertgroupid'            => 'Filtrer par groupe de gabarits. Seules les catégories de dépannage sous le groupe de gabarit sélectionné seront affichées.',
    'listview'                       => 'Affichage de la liste',
    'tssteplist'                     => 'Liste des étapes de dépannage',
    'treeview'                       => 'Vue de l\'arborescence',

    // Insert Step
    'insertstep'                     => 'Ajouter une étape',
    'stepdetails'                    => 'Informations de l\'étape de dépannage',
    'tssubject'                      => 'Objet',
    'tsdisporder'                    => 'Ordre d\'affichage',
    'desc_tsdisporder'               => 'Les étapes de dépannage sont triées selon leur numéro d\'ordre d\'affichage, du premier au dernier.',
    'updatestep'                     => 'Mise à jour de l\'étape',
    'tslinks'                        => 'Étapes parents',
    'selectonelink'                  => 'ERREUR : Veuillez sélectionner au moins une étape parent',
    'tsaddconfirm'                   => 'Étape de dépannage créée (%s)',
    'editstep'                       => 'Modifier l\'étape',

    // Edit Step
    'invalidtroubleshooter'          => 'Dépannage non valide',
    'tsupdateconfirm'                => 'Étape de dépannage mise à jour (%s)',
    'updatestep'                     => 'Mise à jour de l\'étape',
    'editstep'                       => 'Modifier l\'étape',

    // Comments
    'comments'                       => 'Commentaires',
    'legend'                         => 'Légende : ',

    // Reports
    'views'                          => 'Affichages',
    'steptitle'                      => 'Titre de l\'étape',

    // Potentialy unused phrases in troubleshooter.php
    'desc_tslinks'                   => 'Select the Parent Steps for this Step. The Troubleshooter works in a tree-based navigational manner and revolves around parent-child relationships. You can select multiple Parent Steps by pressing the CTRL Key and clicking on the Step title.',
    'importexport'                   => 'Import/Export',
    'export'                         => 'Export',
    'exportxml'                      => 'Export XML',
    'exportcat'                      => 'Troubleshooter Categories',
    'desc_exportcat'                 => 'Select the Troubleshooter Categories to Export. Only the Steps under the Selected Categories will be exported.',
    'importtroubleshooter'           => 'Import Troubleshooter',
    'troubleshooterfile'             => 'Import File',
    'desc_troubleshooterfile'        => 'Please select the XML file to import',
    'importxml'                      => 'Import XML',
    'importconfirm'                  => 'Imported data from XML file',
    'popularcategories'              => 'Pouplar Categories',
    'popularsteps'                   => 'Popular Steps',
);

return $__LANG;
