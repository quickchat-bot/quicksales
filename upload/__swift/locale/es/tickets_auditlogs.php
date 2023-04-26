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
    'duetimecleared'      => '- ELIMINADO -',

    // Activity Log
    'log_newreply'        => 'Respondió al ticket: nº %s',
    'log_forward'         => 'Reenvió el ticket: nº%s a: %s',
    'log_newticket'       => 'Creó el ticket: nº%s: %s',

    // Audit Log
    'al_newticket'        => 'Nuevo ticket creado por %s (%s): asunto: %s', // DONE
    'al_newticket_queue'  => 'Nuevo ticket creado por %s (%s): asunto: %s, cola de correo electrónico: %s', // DONE
    'al_newreply'         => 'Nueva respuesta creada por %s (%s)', // DONE
    'al_watch'            => 'Seguimiento de ticket activado: # %s por: %s', // DONE
    'al_owner'            => 'Propietario del ticket cambiado de : %s a: %s', // DONE
    'al_priority'         => 'Prioridad del ticket cambiada de : %s a: %s', // DONE
    'al_status'           => 'Estado del ticket cambiado de : %s a: %s', // DONE
    'al_statusautoclose'  => 'Estado del ticket cambiado de : %s a: %s por cierre automático', // DONE
    'al_type'             => 'Tipo de ticket cambiado de : %s a: %s', // DONE
    'al_department'       => 'Departamento del ticket cambiado de : %s a: %s', // DONE
    'al_due'              => 'Ajuste de vencimiento en: %s', // DONE
    'al_resolutiondue'    => 'Ajuste de fecha de vencimiento de resolución en: %s', // DONE
    'al_sla'              => 'Plan de SLA configurado en: %s', // DONE
    'al_slaclear'         => 'Plan de SLA asociado eliminado', // DONE
    'al_flag'             => 'Ticket marcado: %s', // DONE
    'al_flagclear'        => 'Marcado del ticket eliminado', // DONE
    'al_duestaffoverdue'  => 'Ticket marcado como vencido', // DONE
    'al_duestaffclear'    => 'Fecha de vencimiento del ticket eliminada', // DONE
    'al_resduestaffclear' => 'Fecha de vencimiento de resolución del ticket eliminada', // DONE
    'al_ban'              => 'Creador del ticket bloqueado: ID del ticket: nº%s, dirección de correo: %s', // DONE
    'al_merge'            => 'Ticket fusionado: ID del ticket: Nr.%s, asunto: %s, nombre: %s, dirección de correo electrónico: %s', // DONE
    'al_untrashticket'    => 'Ticket restaurado de la papelera: ID del ticket: nº%s, asunto: %s, nombre: %s, dirección de correo electrónico: %s', // DONE
    'al_deleteticket'     => 'Ticket eliminado: ID del ticket: nº%s, asunto: %s, nombre: %s, dirección de correo electrónico: %s', // DONE
    'al_trashticket'      => 'Ticket movido a papelera: ID del ticket: nº%s, asunto: %s, nombre: %s, dirección de correo electrónico: %s', // DONE
    'al_updateticketpost' => 'Entrada de ticket actualizada: creador: %s (%s)', // DONE
    'al_deleteticketpost' => 'Entrada de ticket eliminada: creador: %s (%s)', // DONE
    'al_ticketnote'       => 'Nota de ticket añadida a ticket', // DONE
    'al_usernote'         => 'Nota de usuario añadida a ticket', // DONE
    'al_deletenote'       => 'Nota de ticket eliminada', // DONE
    'al_updatenote'       => 'Nota de ticket actualizada', // DONE
    'al_delbilling'       => 'Entrada de facturación del ticket eliminada', // DONE
    'al_ticketbilling'    => 'Entrada de facturación del ticket añadida al ticket', // DONE
    'al_updticketbilling' => 'Entrada de facturación del ticket actualizada', // DONE
    'al_delfollowup'      => 'Seguimiento de ticket eliminado', // DONE
    'al_createfollowup'   => 'Seguimiento de tickets creado', // DONE
    'al_updateproperties' => 'Propiedades del ticket actualizadas: asunto: %s > %s, nombre: %s > %s, dirección de correo electrónico: %s > %s',

    'al_newforward'       => 'Nuevo mensaje de reenvío creado por %s(%s)',
    'al_escalated'        => 'Ticket escalado por regla: %s',
    'al_print'            => 'Ticket impreso por: %s',
    'al_cleardraft'       => 'Borrador de ticket eliminado por: %s',
    'al_savedraft'        => 'Borrador del ticket guardado por: %s',
    'al_forward'          => 'Ticket reenviado a: %s por: %s',
    'al_recipientdel'     => 'Destinatario del ticket: %s eliminado por: %s',
    'al_timetrackdel'     => 'Entrada de seguimiento de tiempo nº%s eliminada por: %s',
    'al_timetrack'        => 'Entrada de seguimiento de tiempo añadida para: %s por: %s (tiempo trabajado: %s tiempo facturable: %s)',
    'al_prule'            => 'Procesado por regla de correo electrónico: %s',
    'al_statusac'         => 'Estado del ticket cambiado de : %s a: %s por cierre automático',
    'al_xmlexport'        => 'Ticket exportado como XML por: %s',
    'al_pdfexport'        => 'Ticket exportado como PDF por: %s',
);

return $__LANG;
