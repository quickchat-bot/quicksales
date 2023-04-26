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
    'tickets'                   => 'Tickets',
    'insertpriority'            => 'Insertar prioridad',
    'tabgeneral'                => 'General',
    'tabpermissions'            => 'Permisos',
    'tablanguages'              => 'Idiomas',
    'prioritytitle'             => 'Título de prioridad',
    'desc_prioritytitle'        => 'Por ejemplo, <em>alta</em> o <em>nivel 1</em>.',
    'prioritytype'              => 'Tipo de prioridad',
    'desc_prioritytype'         => '<strong>Privada</strong> Las prioridades solo son visibles para usuarios del personal pero no para usuarios finales.<br /><strong>Pública</strong> Las prioridades son visibles para ambos.',
    'forecolor'                 => 'Color al frente',
    'desc_forecolor'            => 'Puede especificar un color para en el primer plano de (el texto) la prioridad.',
    'bgcolor'                   => 'Color de fondo',
    'desc_bgcolor'              => 'Puede especificar un color de fondo (sombreado) para la prioridad (asegúrese de que el color al frente de arriba sea visibles sobre el color de fondo).',
    'displayorder'              => 'Orden de visualización',
    'desc_displayorder'         => 'Las prioridades se enumeran según su orden de visualización, la más pequeña la más grande.',
    'displayiconpriority'       => 'Icono',
    'desc_displayiconpriority'  => 'Suba o enlace un icono para esta prioridad. Puede utilizar {$themepath} para señalar el directorio de imágenes del soporte técnico, por ejemplo: <em>{$themepath}icon_prioritymedium.gif</em>.',
    'uservisibilitycustom'      => 'Restringir prioridad a grupos de usuarios específicos',
    'desc_uservisibilitycustom' => 'Si seactiva, solo los grupos de usuarios de abajo podrán ver (y si procede, seleccionar) esta prioridad.',
    'usergroups'                => 'Grupos de usuarios',
    'invalidticketpriority'     => 'Se ha encontrado un problema (prioridad de tickets no válida - Asegúrese de que la prioridad existe en la base de datos)',
    'editpriority'              => 'Editar prioridad',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Prioridades',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Editar prioridad de tickets: %s',
    'titledelticketpriority'    => 'Prioridades de tickets eliminadas (%d)',
    'msgdelticketpriority'      => 'Se eliminaron las siguientes prioridades de tickets:',
    'titleticketpriorityinsert' => 'Prioridad de tickets %s creada',
    'msgticketpriorityinsert'   => 'La prioridad (%s) se ha creado correctamente.',
    'titleticketpriorityupdate' => 'Prioridad de tickets %s actualizada',
    'msgticketpriorityupdate'   => 'La prioridad (%s) se ha actualizado correctamente.',
    'titlemasterprioritydelete' => 'Las prioridades de tickets no pudieron ser eliminadas',
    'msgmasterprioritydelete'   => 'No se pudieron eliminar las siguientes prioridades de tickets principales:',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
