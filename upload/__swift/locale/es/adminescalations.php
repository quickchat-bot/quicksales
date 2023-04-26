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
    // ======= BEGIN V4 LOCALES =======
    'escalations'                 => 'Escalaciones',
    'insertrule'                  => 'Insertar regla',
    'ruletitle'                   => 'Título de la regla',
    'desc_ruletitle'              => 'Introduzca un título para la regla de escalación, por ejemplo: <em>tickets vencidos</em>.',
    'escalationruledetails'       => 'Detalles de regla de escalación',
    'nochange'                    => '-- Ningún cambio --',
    'escalationplan'              => 'Plan de SLA',
    'desc_escalationplan'         => 'Esta regla de escalación se activará si un ticket vence según el plan de SLA especificado aquí.',
    'escalationaction'            => 'Acciones',
    'escalationstaff'             => 'Asignar ticket a un usuario del personal',
    'desc_escalationstaff'        => '',
    'escalationpriority'          => 'Cambiar la prioridad del ticket',
    'desc_escalationpriority'     => '',
    'escalationticketstatus'      => 'Cambiar estado del ticket',
    'desc_escalationticketstatus' => '',
    'escalationdepartment'        => 'Mover ticket a departamento',
    'desc_escalationdepartment'   => '',
    'escalationslaplan'           => 'Cambiar plan de SLA del ticket',
    'desc_escalationslaplan'      => '',
    'escalationflagtype'          => 'Marcar ticket',
    'desc_escalationflagtype'     => '',
    'noplanavailable'             => '--  No disponible --',
    'sendnotification'            => 'Enviar una notificación por correo electrónico al usuario',
    'desc_sendnotification'       => '',
    'titlenoplanavail'            => 'No se encontraron planes de SLA',
    'msgnoplanavail'              => 'Se precisa al menos un plan de SLA para poder crear una regla de escalación ya que la regla de escalación determina qué sucede con un ticket que infringe el plan de SLA.',
    'creationdate'                => 'Fecha de creación',
    'wineditescalationrule'       => 'Editar regla de escalación: %s',
    'invalidescalationrule'       => 'Se ha detectado un problema (regla de escalación no válida - Asegúrese de que la regla existe en la base de datos)',
    'editrule'                    => 'Editar regla',
    'desc_editrule'               => '',
    'titledelescalationrule'      => 'Reglas de escalación eliminadas (%d)',
    'msgdelescalationrule'        => 'Se eliminaron las siguientes reglas de escalación:',
    'titleinsertescalationrule'   => 'Regla de escalación creada',
    'msginsertescalationrule'     => 'La regla de escalación (%s) se ha creado correctamente.',
    'titleupdateescalationrule'   => 'Regla de escalación actualizada',
    'msgupdateescalationrule'     => 'La regla de escalación (%s) se ha actualizado correctamente.',
    'manageescalations'           => 'Administrar',
    'desc_manageescalations'      => '',
    'tabnotifications'            => 'Notificaciones',
    'insertnotification'          => 'Insertar notificación',
    'escalationtickettype'        => 'Cambiar el tipo de ticket',
    'desc_escalationtickettype'   => '',
    'escalationaddtags'           => 'Etiquetar ticket',
    'desc_escalationaddtags'      => '',
    'escalationremovetags'        => 'Eliminar etiquetas del ticket (si activado)',
    'desc_escalationremovetags'   => '',
    'eruletype'                   => 'Tipo de escalación',
    'desc_eruletype'              => 'Esta regla de escalación entrará en acción si un ticket vence según el plan de SLA arriba fijado.<br /><br /><strong>Respuesta vencida</strong> Esta regla de escalación entra en acción cuando se pasa la fecha límite de respuesta de un ticket.<br /><strong>Resolución vencida</strong> Entra en acción cuando pasa la fecha límite de resolución.<br /><strong>Una</strong> Entra en acción si la fecha límite de respuesta o de resolución pasan.',
    'eruletype_due'               => 'Respuesta vencida',
    'eruletype_resolutiondue'     => 'Resolución vencida',
    'eruletype_both'              => 'Respuesta o resolución vencida',
    // ======= END V4 LOCALES =======
);

return $__LANG;
