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
    'autoclose'                    => 'Cierre automático',
    'manage'                       => 'Administrar',
    'targetstatus'                 => 'Estado a cambiar a',
    'tabgeneral'                   => 'General',

    'insertrule'                   => 'Insertar regla',
    'editrule'                     => 'Editar regla',

    'ruletitle'                    => 'Título de la regla',
    'desc_ruletitle'               => 'Inserte un título para esta regla de cierre automático.',
    'targetticketstatus'           => 'Cambiar estado a',
    'desc_targetticketstatus'      => 'Una vez haya pasado el tiempo de inactividad (vea abajo), los tickets que coincidan con esta regla adquirirán este estado (debería ser el estado <strong>resuelto</strong>).',
    'inactivitythreshold'          => 'Umbral de inactividad',
    'desc_inactivitythreshold'     => 'Si el ticket no es actualizado dentro de este número de horas, se considerará al ticket inactivo. Este es el primer paso hacia el cierre automático de un ticket.',
    'closurethreshold'             => 'Umbral de cierre',
    'desc_closurethreshold'        => 'Si un ticket ha sido marcado como inactivo y continúa sin ser actualizado dentro de este número de horas, pasará automáticamente al estado especificado arriba.',
    'isenabled'                    => 'Regla activada',
    'desc_isenabled'               => 'Determinar si la regla está activada o no.',
    'sortorder'                    => 'Orden de ejecución',
    'desc_sortorder'               => 'Es posible crear múltiples reglas de cierre automático. La orden de ejecución determina qué reglas se ejecutan primero, del número más pequeño al más grande.',
    'sendpendingnotification'      => 'Enviar correo electrónico de notificación de inactividad',
    'desc_sendpendingnotification' => 'El helpdesk puede notificar al usuario de que su ticket ha sido marcado como inactivo y que será cerrado si no se recibe respuesta.',
    'sendfinalnotification'        => 'Enviar correo electrónico de notificación final sobre el cierre del ticket',
    'desc_sendfinalnotification'   => 'El helpdesk también puede notificar al usuario de que su ticket ha sido cerrado.',
    'suppresssurveyemail'          => 'Suprimir el correo electrónico de encuesta al cliente',
    'desc_suppresssurveyemail'     => 'Si ha activado las invitaciones a encuestas de satisfacción al cliente cuando un ticket cambia al estado arriba especificado puede desear evitar que el helpdesk envíe una invitación a una encuesta para tickets cerrados automáticamente.',

    'insertcriteria'               => 'Introducir criterios',


    'titleautocloseruledel'        => 'Reglas de cierre automático eliminadas (%d)',
    'msgautocloseruledel'          => 'Se eliminaron las siguientes reglas de cierre automático:',
    'titleautocloseruleenable'     => 'Reglas de cierre automático activadas (%d)',
    'msgautocloseruleenable'       => 'Se activaron las siguientes reglas de cierre automático:',
    'titleautocloseruledisable'    => 'Reglas de cierre automático desactivadas (%d)',
    'msgautocloseruledisable'      => 'Se desactivaron las siguientes reglas de cierre automático:',
    'titleautocloseruleinsert'     => 'Regla de cierre automático creada',
    'msgautocloseruleinsert'       => 'La regla de cierre automático (%s) se ha creado correctamente.',
    'titleautocloseruleupdate'     => 'Regla de cierre automático actualizada',
    'msgautocloseruleupdate'       => 'La regla de cierre automático (%s) se ha actualizado correctamente.',

    'titlenocriteriaadded'         => 'No se han especificado criterios',
    'msgnocriteriaadded'           => 'Debe especificar al menos un criterio para crear una regla de cierre automático (de lo contrario el helpdesk no sabrá qué ticket debe cerrar automáticamente).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- NA --',
    'articketstatusid'             => 'Estado',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Prioridad',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Departamento',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Propietario',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'Cola de correo electrónico',
    'arusergroupid'                => 'Grupo de usuarios',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Marcar',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Categoría bayesiana',
    'desc_arbayescategoryid'       => 'Tickets que se corresponden con un categoría bayesiana específica.',
    'arcreator'                    => 'Creador',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Personal',
    'creatorclient'                => 'Usuario',
    'arunassigned'                 => '-- Sin asignar --',
    'artemplategroupid'            => 'Grupo de plantillas',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Tipo',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
