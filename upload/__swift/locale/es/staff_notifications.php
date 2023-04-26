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
    'notifications'                  => 'Notificaciones',
    'manage'                         => 'Administrar',
    'ruletitle'                      => 'Título de la regla',
    'desc_ruletitle'                 => 'Escriba un título para la regla de notificación.',
    'isenabled'                      => 'Regla de notificación activada',
    'desc_isenabled'                 => 'Defina si esta regla está activada o desactivada.',
    'ruletype'                       => 'Tipo',
    'desc_ruletype'                  => 'Se puede asociar la regla de notificación con uno de los siguientes elementos.',
    'next'                           => 'Siguiente',
    'creator'                        => 'Creador',
    'wininsertnotification'          => 'Insertar notificación',

    'unassigned'                     => '-- Sin asignar --',

    'tabgeneral'                     => 'General',
    'tabactions'                     => 'Acciones',
    'insertcriteria'                 => 'Introducir criterios',

    'na_email'                       => 'Correo electrónico',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Aplicación de escritorio',
    'na_staff'                       => 'Cuenta propia de usuario del personal',
    'na_staffgroup'                  => 'Equipo',
    'na_department'                  => 'Departamento',
    'na_user'                        => 'Usuario',
    'nactionemaildispatch'           => 'Enviar correo electrónico al ... del propietario del ticket',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Enviar correo electrónico a otras direcciones de correo electrónico',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Enviar correo electrónico a otros miembros del personal',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Enviar notificación al... activo del ticket',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Personal',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Seleccione un tipo de regla de notificación.',
    'titlenocriteria'                => 'No se han especificado criterios',
    'msgnocriteria'                  => 'Debe especificar al menos un criterio para crear una regla de notificación.',
    'titlenotificationinsert'        => 'Regla de notificación (%s) creada',
    'msgnotificationinsert'          => 'La regla de notificación (%s) se ha creado correctamente.',
    'titlenotificationupdate'        => 'Regla de notificación (%s) actualizada',
    'msgnotificationupdate'          => 'La regla de notificación (%s) se ha actualizado correctamente.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Correo electrónico',
    'titleenablenotification'        => 'Reglas de notificación activadas (%d)',
    'msgenablenotification'          => 'Se han activado las siguientes reglas de notificación:<br />%s',
    'titledisablenotification'       => 'Reglas de notificación desactivadas (%d)',
    'msgdisablenotification'         => 'Se han desactivado las siguientes reglas de notificación:<br />%s',

    'emailprefix'                    => 'Prefijo del asunto del correo electrónico',
    'desc_emailprefix'               => 'Especificar un prefijo del asunto del correo electrónico para identificar fácilmente los correos electrónicos enviados por esta regla de notificación.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Chat',
    'nrule_message'                  => 'Mensaje cuando desconectado',
    'nrule_survey'                   => 'Encuesta sobre chat',
    'nrule_ticket'                   => 'Ticket',
    'nrule_user'                     => 'Usuario',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Tipo de ticket',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Categoría bayesiana',
    'desc_nbayesian'                 => 'Tickets que se corresponden con un categoría bayesiana específica.',
    'notapplicable'                  => '--  No disponible --',
    'nticketstatus'                  => 'Estado del ticket',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Prioridad del ticket',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Grupo de usuarios',
    'desc_nusergroup'                => 'Buscar tickets cuyos destinatarios pertenezcan a un determinado grupo de usuarios.',
    'ndepartment'                    => 'Departamento de tickets',
    'desc_ndepartment'               => 'Tickets que pertenecen a un departamento.',
    'nowner'                         => 'Propietario del ticket',
    'desc_nowner'                    => 'Tickets asignados a un determinado usuario del personal.',
    'nunassigned'                    => '-- Sin asignar --',
    'nactivestaff'                   => '-- Personal activo --',
    'nemailqueue'                    => 'Cola de correo electrónico',
    'desc_nemailqueue'               => 'Tickets creados o respondidos por correo electrónico a través de una determinada cola de correo electrónico.',
    'nflagtype'                      => 'Marca del ticket',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Ticket creado por',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Personal',
    'creatorclient'                  => 'Usuario',
    'nslaplan'                       => 'Plan de SLA',
    'desc_nslaplan'                  => 'Tickets que actualmente están asignados a un determinado plan de SLA.',
    'ndaterange'                     => 'Fecha de creación del ticket <range>',
    'desc_ndaterange'                => 'Tickets creados dentro en este periodo de tiempo.',
    'nlastactivityrange'             => 'Última actualización del ticket <range>',
    'desc_nlastactivityrange'        => 'Tickets actualizados (es decir, respondidos por cualquier persona, o cualquier otro mecanismo de actualización) en este periodo.',
    'nlaststaffreplyrange'           => 'Última respuesta del personal <range>',
    'desc_nlaststaffreplyrange'      => 'Tickets que recibieron una respuesta de un usuario del personal en este periodo.',
    'nlastuserreplyrange'            => 'Última respuesta de usuario <range>',
    'desc_nlastuserreplyrange'       => 'Tickets que recibieron una respuesta de un usuario en este periodo.',
    'nduerange'                      => 'Fecha límite de respuesta <range>',
    'desc_nduerange'                 => 'Tickets cuya fecha límite de respuesta esta dentro de este periodo.',
    'nisoverdue'                     => 'Vencido: Respuesta vencida',
    'desc_nisoverdue'                => 'Tickets vencidos (por no ser respondidos antes de la fecha límite).',
    'nresolutionduerange'            => 'Fecha límite de resolución <range>',
    'desc_nresolutionduerange'       => 'Tickets cuyo plazo de resolución está dentro de este periodo.',
    'nisresolutionoverdue'           => 'Vencido: Resolución vencida',
    'desc_nisresolutionoverdue'      => 'Tickets vencidos (por no ser resueltos antes de la fecha límite).',
    'ntemplategroup'                 => 'Grupo de plantillas',
    'desc_ntemplategroup'            => 'Tickets que pertenecen a un determinado grupo de plantillas.',
    'ntimeworked'                    => 'Tiempo trabajado (en minutos)',
    'desc_ntimeworked'               => 'Tickets cuyo valor para <strong>tiempo trabajado</strong> (como parte de entradas de seguimiento de tiempo y de facturación de tickets) coincide con este.',
    'ntotalreplies'                  => 'Total de respuestas',
    'desc_ntotalreplies'             => 'Tickets con tantas respuestas.',
    'npendingfollowups'              => 'Seguimientos pendiente',
    'desc_npendingfollowups'         => 'Tickets con seguimientos de tickets programados.',
    'nipaddress'                     => 'Dirección IP',
    'desc_nipaddress'                => 'Si se ha responido a un ticket desde el <strong>centro de soporte</strong>, puede que el helpdesk haya registrado una dirección IP para el usuario.',
    'nisemailed'                     => 'Creado por correo electrónico',
    'desc_nisemailed'                => 'Tickets que fueron creados por correo electrónico.',
    'nisedited'                      => 'Ha sido editado',
    'desc_nisedited'                 => 'Tickets que han sido editados.',
    'nhasnotes'                      => 'Tiene notas',
    'desc_nhasnotes'                 => 'Tickets que tienen notas de ticket.',
    'nhasattachments'                => 'Tiene archivos adjuntos',
    'desc_nhasattachments'           => 'Tickets que tienen archivos adjuntos.',
    'nisescalated'                   => 'Ha sido escalado',
    'desc_nisescalated'              => 'Tickets que han sido escalados por al menos una regla de escalación.',
    'nhasdraft'                      => 'Tiene un borrador',
    'desc_nhasdraft'                 => 'Tickets con borradores de respuestas guardados.',
    'nhasbilling'                    => 'Tiene entradas de seguimiento de tiempo y facturación',
    'desc_nhasbilling'               => 'Tickets que tienen entradas de seguimiento de tiempo y facturación.',
    'nisphonecall'                   => 'Es un ticket telefónico',
    'desc_nisphonecall'              => 'Tickets que han sido marcados como tipo de ticket <em>llamada telefónica</em>.',
    'nislabeled'                     => 'Con etiqueta',
    'desc_nislabeled'                => 'Tickets con etiquetas.',

    'nticketevent'                   => 'Evento',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Ticket nuevo creado',
    'tevent_newticketnotes'          => 'Nueva nota de ticket añadida',
    'tevent_newclientreply'          => 'Nueva respuesta de usuario final',
    'tevent_newclientsurvey'         => 'Nuevo recibo de encuesta',
    'tevent_newstaffreply'           => 'Nueva respuesta del personal',
    'tevent_ticketassigned'          => 'Ticket asignado',
    'tevent_newuser'                 => 'Nuevo registro de usuario',

    'nuserevent'                     => 'Evento',
    'desc_nuserevent'                => 'Esta regla se aplicará ante este evento.',
);

return $__LANG;
