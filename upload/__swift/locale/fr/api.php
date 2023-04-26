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
    'invaliddatareceived'           => 'Données reçues non valides ou malformées ; impossible de continuer.',
    'errordataprocess'              => 'Le sous-système API a reçu des données malformées après l\'exécution de la fonction ; impossible de continuer.',
    'invalidemail'                  => 'Adresse e-mail non valide. Veuillez vérifier la syntaxe de l\'adresse de email.',

    // ======= CORE =======
    'staffusernameexists'           => 'Impossible d\'ajouter ou de mettre à jour un utilisateur du personnel. Un utilisateur du personnel ayant le même nom d\'utilisateur existe déjà.',
    'staffdoesnotexist'             => 'L\'utilisateur du personnel n\'existe pas',
    'staffgroupdoesnotexist'        => 'Le groupe de personnel n\'existe pas',
    'settingdoesnotexist'           => 'Le champ de paramètre spécifié n\'existe pas',
    'departmentmodmismatch'         => 'L\'application de service spécifiée pour le service parent %s (%s) ne correspond pas à l\'application spécifiée (%s)',
    'departmentmultipleparenterror' => SWIFTPRODUCT . ' autorise un seul niveau de sous services. Impossible d\'attribuer à un sous-service existant',
    'errorinvaliddepartment'        => 'Le service n\'existe pas',
    'errorinvalidparentdepartment'  => 'Le service parent n\'existe pas',
    // ======= TICKETS =======
);

return $__LANG;
