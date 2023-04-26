<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'schedules'                  => 'Programaciones',

    'exportformat'               => 'Formato de informe',
    'desc_exportformat'          => 'El informe será generado (y se le enviará por correo electrónico) en este formato.',
    'recurrence'                 => 'Ejecutar informe cada',
    'desc_recurrence'            => 'Especifique con qué frecuencia se debe ejecutar el informe.',
    'firstexecutiondate'         => 'Fecha de primera ejecución',
    'nextexecutiondate'          => 'Fecha de próxima ejecución',
    'lastexecutiondate'          => 'Fecha de última ejecución',
    'desc_executiondate'         => 'Especifique la primera vez que este informe debe ser ejecutado. Las siguientes ejecuciones del informe se calcularán automáticamente.',
    'schedulecc'                 => 'Enviar a otras direcciones de correo electrónico',
    'desc_schedulecc'            => 'El informe se enviará a su dirección de correo electrónico. Opcionalmente indique otras direcciones de correo electrónico.',

    'recurrence_once'            => 'Una vez',
    'recurrence_hourly'          => 'Cada hora',
    'recurrence_daily'           => 'Cada día',
    'recurrence_weekly'          => 'Cada semana',
    'recurrence_bimonthly'       => 'Cada dos semanas',
    'recurrence_monthly'         => 'Mensual',
    'recurrence_quarterly'       => 'Trimestral',
    'recurrence_yearly'          => 'Anual',

    'titleinvalidformat'         => 'Formato de exportación no válido',
    'msginvalidformat'           => 'Por favor, seleccione un formato de exportación válido.',
    'titleinvalidrecurrence'     => 'Repetición no válida',
    'msginvalidrecurrence'       => 'Por favor, seleccione un tipo de repetición válido.',
    'titleinvalidexecutiondate'  => 'Fecha de primera ejecución no válida',
    'msginvalidexecutiondate'    => 'Por favor, especifique valor(es) válido(s) para las propiedades de fecha y hora de la primera fecha de ejecución.',
    'titlepastexecutiondate'     => 'La fecha para la primera ejecución ya ha pasado',
    'msgpastexecutiondate'       => 'Por favor, seleccione una fecha en el futuro para la primera ejecución.',
    'titleinvalidcc'             => 'Dirección de correo electrónico no válida',
    'msginvalidcc'               => 'Por favor compruebe la sintaxis de las direcciones de correo electrónico proporcionadas.',

    'notifyreportscheduleinsert' => 'Informe programado correctamente',
    'notifyreportscheduleupdate' => 'Programación de informe actualizada',

    'ccemailaddresses'           => 'Direcciones de correo electrónico copiadas en',

    'reportsendoncesub'          => 'Informe: %s',
    'reportsendhourlysub'        => 'Informe cada hora: %s',
    'reportsenddailysub'         => 'Informe diario: %s',
    'reportsendweeklysub'        => 'Informe semanal: %s',
    'reportsendbimonthlysub'     => 'Informe de cada dos semanas: %s',
    'reportsendmonthlysub'       => 'Informe mensual: %s',
    'reportsendquarterlysub'     => 'Informe trimestral: %s',
    'reportsendyearlysub'        => 'Informe anual: %s',

    'reportnoattachment'         => 'ningún archivo adjunto',
    'reportsendintro'            => 'Ha recibido este correo electrónico porque usted o alguien en su nombre (%s) ha programado un informe generado en el soporte técnico que se evíe a su bandeja de entrada automáticamente.',
    'reporterrorintro'           => 'El informe ha dado el siguiente error',
    'reporterrorccnote'          => 'Debido al error anterior, este mensaje no fue enviado a las otras direcciones de correo electrónico especificadas para recibir copias del informe.',

    'noexportresultfound'        => 'Este informe no ha producido ningún resultado, por lo que no hay nada que exportar.',
);

return $__LANG;
