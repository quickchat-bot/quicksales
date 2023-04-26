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
    'troubleshooter'                 => 'Risolutore problematiche',

    // Manage Categories
    'categories'                     => 'Categorie',
    'managecategories'               => 'Gestisci le Categorie',
    'desc_troubleshootercat'         => '',
    'tscattitle'                     => 'Titolo Categoria',
    'desc_tscattitle'                => '',
    'tscatviews'                     => 'Visualizzazioni',
    'desc_tscatviews'                => 'Quante volte è stata visualizzata la catagoria. Viene incrementata automaticamente ogni volta che un utente clicca su una di esse.',
    'tscatdisporder'                 => 'Ordine di Visualizzazione',
    'desc_tscatdisporder'            => 'Le categorie sono ordinate secondo il numero di ordine visualizzato, dal più piccolo al più grande.',
    'tscatlist'                      => 'Elenco delle Categorie',
    'tscatdesc'                      => 'Descrizione',
    'desc_tscatdesc'                 => 'La descrizione della categoria viene visualizzata sotto il titolo. Il limite della lunghezza è di 255 caratteri.',
    'steps'                          => 'Passi',

    // Insert Category
    'insertcategory'                 => 'Inserisci Categoria',
    'tscatdetails'                   => 'Dettagli Categoria',
    'templategroups'                 => 'Gruppi di Modelli',
    'desc_templategroups'            => 'Seleziona i gruppi di modelli che consentono di visualizzare questa categoria del risolutore nel centro di supporto.',
    'inserttscat'                    => 'Inserisci Categoria',
    'updatetscat'                    => 'Aggiorna la Categoria',
    'selectonetgroup'                => 'ERRORE: È necessario selezionare almeno un Gruppo di Modelli',
    'troubleshootercatinsertconfirm' => 'Creato categoria del risolutore (%s)',
    'tcatdeleteconfirm'              => 'Categoria del risolutore eliminata con successo',
    'tcatsdeleteconfirm'             => 'Categorie del risolutore eliminate con successo',
    'tcatdelconfirm'                 => 'Sei sicuro di voler eliminare questa categoria?\\nEliminando una categoria eliminerà pure tutti i passaggi della risoluzione delle problematiche contenute.',
    'invalidtroubleshootercategory'  => 'Categoria del risolutore non valida',

    // Edit Category
    'editcategory'                   => 'Modifica Categoria',
    'troubleshootercatupdateconfirm' => 'È stata aggiornata la categoria del risolutore (%s)',

    // Manage Steps
    'troubleshootersteps'            => 'Passaggi del Risolutore',
    'managesteps'                    => 'Gestisci i Passaggi',
    'addstep'                        => 'Aggiungi Passaggio',
    'addcategory'                    => 'Aggiungi Categoria',
    'troubleshooters'                => 'Risolutori problematiche',
    'filter'                         => 'Filtra',
    'stepdelconfirmmsg'              => 'Sei sicuro di voler eliminare questo passaggio? Eliminando un passaggio si elimineranno pure tutti i passaggi figli.',
    'tsdelconfirm'                   => 'Passaggi del risolutore eliminati',
    'stepdeleteconfirm'              => 'Passaggi del risolutore %s eliminati',
    'filtertgroupid'                 => 'Gruppi di Modelli',
    'desc_filtertgroupid'            => 'Filtra per gruppo di modelli. Verranno mostrate solo le categorie del risolutore appartenenti al gruppo di modelli selezionato.',
    'listview'                       => 'Visualizzazione Elenco',
    'tssteplist'                     => 'Elenco Passaggi del Risolutore',
    'treeview'                       => 'Vista ad Albero',

    // Insert Step
    'insertstep'                     => 'Inserisci Passaggio',
    'stepdetails'                    => 'Dettagli dei Passaggi del Risolutore',
    'tssubject'                      => 'Oggetto',
    'tsdisporder'                    => 'Ordine di Visualizzazione',
    'desc_tsdisporder'               => 'I passaggi del risolutore sono ordinati secondo il numero di ordine visualizzato, dal più piccolo al più grande.',
    'updatestep'                     => 'Aggiorna Passaggio',
    'tslinks'                        => 'Passaggi Genitore',
    'selectonelink'                  => 'ERRORE: Si prega di selezionare almeno un passaggio genitore',
    'tsaddconfirm'                   => 'È stato creato il passaggio del risolutore (%s)',
    'editstep'                       => 'Modifica Passaggio',

    // Edit Step
    'invalidtroubleshooter'          => 'Risolutore non valido',
    'tsupdateconfirm'                => 'È stato aggiornato il passaggio del risolutore (%s)',
    'updatestep'                     => 'Aggiorna Passaggio',
    'editstep'                       => 'Modifica Passaggio',

    // Comments
    'comments'                       => 'Commenti',
    'legend'                         => 'Legenda: ',

    // Reports
    'views'                          => 'Visualizzazioni',
    'steptitle'                      => 'Titolo del Passaggio',

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
