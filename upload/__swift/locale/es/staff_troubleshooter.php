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
    'troubleshooter'             => 'Solucionador de problemas',
    'categories'                 => 'Categorías',
    'titletrcatmismatch'         => 'Una categoría llamada %s ya existe',
    'msgtrcatmismatch'           => 'Una categoría del solucionador de problemas con el nombre de %s ya existe. Por favor, elija otro.',
    'wininsertcategory'          => 'Insertar categoría',
    'wineditcategory'            => 'Editar categoría: %s',
    'insertcategory'             => 'Insertar categoría',
    'editcategory'               => 'Editar categoría',

    'titletrcategoryinsert'      => 'Categoría del solucionador de problemas (%s) creada',
    'msgtrcategoryinsert'        => 'La categoría del solucionador de problemas (%s) se creó con éxito.',
    'titletrcategoryupdate'      => 'Categoría del solucionador de problemas (%s) actualizada',
    'msgtrcategoryupdate'        => 'La categoría del solucionador de problemas (%s) se actualizó con éxito.',

    'trcategory'                 => 'Nombre de categoría',
    'desc_trcategory'            => 'Por ejemplo, <em>Mi dispositivo no se enciende</em>.',
    'next'                       => 'Siguiente',

    'tabgeneral'                 => 'General',
    'taboptions'                 => 'Opciones',
    'categorytype'               => 'Visibilidad',
    'desc_categorytype'          => '<strong>Global</strong> Disponible para todos incluidos los usuarios del personal y finales.<br /><strong>Público</strong> Disponible solo para los usuarios finales en el centro de soporte.<br /><strong>Privado</strong> Disponible solo para su personal en el panel de control del personal.',
    'displayorder'               => 'Orden de visualización',
    'desc_displayorder'          => 'Las categorías del solucionador de problemas se enumeran según su número de orden de visualización, de menor a mayor.',
    'tabpermissionsuser'         => 'Permisos: Usuarios',
    'tabpermissionsstaff'        => 'Permisos: Personal',
    'uservisibilitycustom'       => 'Restringir a grupos de usuarios específicos',
    'desc_uservisibilitycustom'  => 'Si se activa solo los grupos de usuarios de abajo podrán ver esta categoría.',
    'staffvisibilitycustom'      => 'Restringir a determinados equipos de personal',
    'desc_staffvisibilitycustom' => 'Si se activa, sólo los equipos de abajo podran ver esta categoría.',
    'usergroups'                 => 'Grupos de usuarios',
    'staffteams'                 => 'Equipos de personal',

    'tablist'                    => 'Lista',
    'tabtree'                    => 'Estructura',
    'steptitle'                  => 'Título de paso',
    'categorytitle'              => 'Categoría',
    'author'                     => 'Autor',
    'stepstatus'                 => 'Estado',

    /**
     * ---------------------------------------------
     * INSERT TROUBLESHOOTER
     * ---------------------------------------------
     */
    'titletrstepinsert'          => 'Paso del solucionador de problemas (%s) creado',
    'msgtrstepinsert'            => 'El paso del solucionador de problemas (%s) se ha creado correctamente.',
    'titletrstepupdate'          => 'Paso del solucionador de problemas (%s) actualizado',
    'msgtrstepupdate'            => 'El paso del solucionador de problemas (%s) se ha actualizado correctamente.',
    'insertstep'                 => 'Insertar paso',
    'recursionsuffix'            => ' (Posible repetición)',
    'parentcategoryitem'         => '- Raíz de categoría -',
    'desc_parentsteps'           => '',
    'tabgeneral'                 => 'General',
    'tabattachments'             => 'Archivos adjuntos',
    'taboptions'                 => 'Opciones',
    'publish'                    => 'Publicar',
    'published'                  => 'Publicado',
    'draft'                      => 'Borrador',
    'infobauthor'                => 'AUTOR',
    'infobcreationdate'          => 'CREADO EL',
    'infobeditedby'              => 'EDITADO POR',
    'infobeditedon'              => 'EDITADO EL',
    'ftarticletype'              => 'Tipo',
    'ftcategories'               => 'Categorías',
    'ftdate'                     => 'Fecha',
    'informationbox'             => 'Información',
    'quickfilter'                => 'Filtro rápido',
    'markasdraft'                => 'Marcar como borrador',
    'saveasdraft'                => 'Guardar como borrador',
    'addfile'                    => 'Añadir archivo',
    'steptitle'                  => 'Título de paso',
    'desc_steptitle'             => '',
    'parentsteps'                => 'Pasos principales',
    'desc_parentsteps'           => '',
    'generalsettings'            => 'Configuración general',
    'allowcomments'              => 'Permitir comentarios',
    'desc_allowcommentsstep'     => 'Si se permiten comentarios en este paso.',
    'noticketrediractive'        => '-- Ningún departamento seleccionado --',
    'ticketredirectionsettings'  => 'Configuración de redirección del ticket',
    'redirecttickets'            => 'Redirigir a ticket en este paso',
    'desc_redirecttickets'       => 'Active esta configuración para invitar al usuario a crear un ticket cuando haya llegado a este paso (es decir, como paso final).',
    'redirectdepartment'         => 'Departamento de tickets',
    'desc_redirectdepartment'    => 'Si no se ha seleccionado ningún departamento, se pedirá al usuario que seleccione el suyo.',
    'ticketsubject'              => 'Asunto del ticket',
    'desc_ticketsubject'         => 'Si no se especifica ningún asunto del ticket, el usuario podrá introducir el suyo.',
    'tickettype'                 => 'Tipo de ticket',
    'desc_tickettype'            => 'Specify a ticket type for the ticket a user who reaches this step will be invited to create.',
    'ticketpriority'             => 'Prioridad del ticket',
    'desc_ticketpriority'        => 'Si no se especifica ninguna prioridad, el usuario podrá seleccionar su propia prioridad del ticket (si corresponde).',
    'nochange'                   => '-- Ningún cambio --',

    /**
     * ---------------------------------------------
     * VIEW ALL
     * ---------------------------------------------
     */
    'tabviewall'                 => 'Solucionador de problemas',
    'troubleshooterdesc'         => 'El asistente del solucionador de problemas le guía a través de una serie de pasos para poder diagnosticar y resolver problemas comunes. Comience eligiendo una categoría del solucionador de problemas.',
    'trattachments'              => 'Archivos adjuntos',
    'trnextsteps'                => 'Próximos pasos',
    'trnext'                     => 'Siguiente',
    'trback'                     => 'Volver',
    'steps'                      => 'Pasos',
    'trcategoryviews'            => ' (%d visitas)',

    // Potentialy unused phrases in staff_troubleshooter.php
    'desc_categorytitle'         => 'Enter the category title',

);

return $__LANG;
