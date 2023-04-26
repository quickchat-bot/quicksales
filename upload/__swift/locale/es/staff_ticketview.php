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
    'titlemasterviewdel'            => 'No se puede(n) eliminar "%d" vista(s)',
    'msgmasterviewdel'              => 'QuickSupport no pudo eliminar la(s) siguiente(s) vista(s)  principal(es):',
    'manageviews'                   => 'Vistas',
    'insertview'                    => 'Insertar vista',
    'viewtitle'                     => 'Título de vista de ticket',
    'desc_viewtitle'                => 'Escriba un título para la vista (solo con números, letras y espacios).',
    'viewscope'                     => 'Alcance de vista',
    'createdby'                     => 'Creador',
    'editview'                      => 'Editar vista',
    'titleticketviewinsert'         => 'Vista de ticket (%s) creada',
    'msgticketviewinsert'           => 'La vista de tickets (%s) se ha creado correctamente.',
    'titleticketviewupdate'         => 'Vista de ticket (%s) actualizada',
    'msgticketviewupdate'           => 'La vista de tickets (%s) se ha actualizado correctamente.',
    'titleviewfieldempty'           => 'No se especificaron columnas en la vista de ticket',
    'msgviewfieldempty'             => 'Debe especificar al menos una columna para una vista de ticket (de lo contrario no habría nada que mostrar).',

    'viewlinkdepartment'            => 'Vincular esta vista con departamentos',
    'desc_viewlinkdepartment'       => 'Se puede cargar automáticamente esta vista cuando se visualizan los tickets en uno de los departamentos seleccionados aquí.',

    'taboptions'                    => 'Opciones',
    'tabcolumns'                    => 'Columnas',

    'viewscope_global'              => 'Todo el personal',
    'viewscope_private'             => 'Solo yo',
    'viewscope_team'                => 'Mi equipo',

    'viewunassigned'                => 'Sin asignar',
    'viewassigned'                  => 'Asignados a mí',
    'viewalltickets'                => 'Todos los tickets',

    'viewassignedfield'             => 'Mostrar tickets asignados a',
    'desc_viewassignedfield'        => '<strong>Sin asignar</strong> Tickets que no asignados a nadie.<br /><strong>Asignados a mí</strong> Tickets asignados a usted.<br /><strong>Todos los tickets</strong> Todos los tickets, asignados a usted, a otra persona o a nadie.',
    'viewscope'                     => 'Alcance de vista',
    'desc_viewscope'                => '<strong>Todo el personal</strong> Todo el personal podrá usar esta vista.<br /><strong>Solo yo</strong> La vista estará disponible solo para usted.<br /><strong>Mi equipo</strong> La vista esatrá disponible para usted y otros miembros de su equipo.',

    'viewselectfields'              => 'Seleccionar columnas (arrastrar, soltar y organizar en el campo amarillo)',
    'viewsortoptions'               => 'Opciones de organización',
    'viewsortby'                    => 'Organizar por',
    'desc_viewsortby'               => '¿Cómo se organizarán inicialmente los tickets cuando esta vista esté en uso?',
    'viewsortorder'                 => 'Orden de organización',
    'desc_viewsortorder'            => 'Los tickets pueden ordenarse de forma ascendente (de menor a mayor, de más reciente a menos reciente, etc.) o descendente.',
    'viewticketsperpage'            => 'Entradas que mostrar por página',
    'adminviewticket'         => 'The number of tickets per page is too high.',
    'desc_adminviewticket'    => 'Staff ticket views are currently limited to showing %s tickets per page. Contact your administrator to increase this limit.',
    'desc_viewticketsperpage' => 'Specify the number of tickets to display before breaking the view into multiple pages. Note: This value cannot exceed the default maximum your administrator has set for staff views.',    'generaloptions'                => 'Opciones generales',
    'viewautorefresh'               => 'Actualizar automáticamente la lista de tickets',
    'desc_viewautorefresh'          => 'Cuando se cargue esta vista, se podrá actualizar automáticamente la lista de tickets. Útil para usuarios con paneles.',
    'autorefresh_disable'           => '-- Desactivar actualización automática --',
    'autorefresh_30s'               => 'Actualizar cada 30 segundos',
    'autorefresh_1m'                => 'Actualizar cada minuto',
    'autorefresh_5m'                => 'Actualizar cada 5 minutos',
    'autorefresh_15m'               => 'Actualizar cada 15 minutos',
    'autorefresh_30m'               => 'Actualizar cada 30 minutos',
    'autorefresh_1h'                => 'Actualizar cada hora',
    'viewsetasowner'                => 'Establecer automáticamente como propietario del ticket al usuario personal activo',
    'desc_viewsetasowner'           => 'Al responder o reenviar un ticket, se define automáticamente al usuario del personal activo como propietario del ticket si se usa esta visita.',
    'viewdefaultstatusonreply'      => 'Estado de ticket por defecto al responder a un ticket',
    'desc_viewdefaultstatusonreply' => 'Determine qué estado obtendrá un ticket automáticamente cuando un usuario del personal que usa esta vista responda o reenvíe un ticket.',
    'defaultstatus_unspecified'     => '-- No especificado --',

    'afterreply_topticketlist'      => 'Lista de tickets de alto nivel (ver todos los tickets)',
    'afterreply_activeticketlist'   => 'Lista de tickets activos (dentro del departamento actual o los resultados de búsqueda)',
    'afterreply_ticket'             => 'El ticket activo',
    'afterreply_nextticket'         => 'Siguiente ticket disponible',
    'viewafterreplyaction'          => 'Cuando se haya respondido a un ticket, llevarme a',
    'desc_viewafterreplyaction'     => 'Seleccione a dónde le llevará QuickSupport (o quien esté utilizando esta vista) después de responder o reenviar un ticket.',

    'sort_asc'                      => 'Ascendente',
    'sort_desc'                     => 'Descendente',

    // Potentialy unused phrases in staff_ticketview.php
    'viewfilteroptions'             => 'Filter Options',
    'viewfilterdepartments'         => 'Filter Department(s)',
    'desc_viewfilterdepartments'    => 'QuickSupport by default will display tickets from all/selected department. However, you can use this option to filter the tickets based on the selected departments.',
    'viewfiltertype'                => 'Filter Ticket Type(s)',
    'desc_viewfiltertype'           => 'You can use this option to filter the tickets based on the selected ticket type(s).',
    'viewfilterstatus'              => 'Filter Ticket Status(es)',
    'desc_viewfilterstatus'         => 'You can use this option to filter the tickets based on the selected ticket status(es).',
);


return $__LANG;
