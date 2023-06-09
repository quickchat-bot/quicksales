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
    'insertholiday'              => 'Insertar día festivo',
    'impex'                      => 'Importar/Exportar',
    'manageholidays'             => 'Días festivos',
    'desc_manageholidays'        => '',
    'desc_insertholiday'         => '',
    'holidaytitle'               => 'Nombre del festivo',
    'desc_holidaytitle'          => 'Por ejemplo, <em>Día de la Constitución</em>.',
    'holidaydate'                => 'Fecha del festivo',
    'desc_holidaydate'           => 'La fecha en que tendrá lugar este festivo.',
    'tabslaplans'                => 'Planes de SLA',
    'flagicon'                   => 'Icono de marcador',
    'desc_flagicon'              => 'Suba o enlace un icono para este marcador. Puede utilizar {$themepath} para indicar el directorio de marcadores, por ejemplo:<em>{$themepath}es.gif</em>',
    'slaplans'                   => 'Planes de SLA disponibles',
    'slaiscustom'                => 'Enlazar con planes específicos de SLA',
    'desc_slaiscustom'           => 'Este festivo puede vincularse con planes específicos de SLA, si se activa.',
    'customall'                  => 'Todos',
    'custom'                     => 'Personalizado',
    'wineditslaholiday'          => 'Editar festivo: %s',
    'editholiday'                => 'Editar festivo',
    'desc_editholiday'           => '',
    'invalidslaholiday'          => 'Se ha detectado un problema (festivo no válido - Asegúrese de que el festivo del SLA existe en la base de datos)',
    'titleinsertslaholiday'      => 'Día festivo creado',
    'msginsertslaholiday'        => 'El día festivo %s se ha creado correctamente.',
    'titleupdateslaholiday'      => 'Día festivo actualizado',
    'msgupdateslaholiday'        => 'El día festivo %s se ha actualizado correctamente.',
    'titledelslaholidays'        => 'Día festivo eliminado (%d)',
    'msgdelslaholidays'          => 'Se eliminaron los siguientes días festivos:',
    'slaimportexport'            => 'Importar/Exportar',
    'desc_slaimportexport'       => '',
    'tabexport'                  => 'Exportar',
    'tabimport'                  => 'Importar',
    'import'                     => 'Importar',
    'export'                     => 'Exportar',
    'exporttitle'                => 'Título',
    'desc_exporttitle'           => 'Introduzca un título para este paquete de vacaciones.',
    'exportauthor'               => 'Autor',
    'desc_exportauthor'          => 'El autor de este paquete de vacaciones.',
    'exportfilename'             => 'Nombre del archivo',
    'desc_exportfilename'        => 'Especifique el nombre del archivo al que exportar este paquete de vacaciones.',
    'slaholidaypack'             => 'Paquete de vacaciones',
    'slaholidayfile'             => 'Importar un archivo de paquete de vacaciones',
    'desc_slaholidayfile'        => 'Seleccione un archivo de paquete de vacaciones de su computadora para cargarlo en el helpdesk.',
    'titleslaholidayimpex'       => 'Paquete de vacaciones importado',
    'msgslaholidayimpex'         => 'Se importaron %d festivos al helpdesk correctamente.',
    'titleslaholidayimpexfailed' => 'No se pudo importar paquete de vacaciones',
    'msgslaholidayimpexfailed'   => 'Hubo un problema al importar el archivo del paquete de vacaciones. Puede que contenga datos no válidos.',
    'insertschedule'             => 'Insertar horario',
    'sladayopen24'               => 'Abierto (24 horas)',
    'sladayclosed'               => 'Cerrado',
    'sladayopencustom'           => 'Abierto (personalizado)',
    'titleinvalidhrange'         => 'Problema con el rango de horas',
    'msginvalidhrange'           => 'Hubo un problema con los siguientes rangos de horas:',
    'manageschedules'            => 'Administrar horarios',
    'desc_manageschedules'       => '',
    'titleinsertslasched'        => 'Horario de SLA creado',
    'msginsertslasched'          => 'El horario de SLA (%s) se ha creado correctamente.',
    'titleupdateslasched'        => 'Horario de SLA actualizado',
    'msgupdateslasched'          => 'El horario de SLA (%s) se ha actualizado correctamente.',
    'creationdate'               => 'Fecha de creación',
    'invalidslaschedule'         => 'Se ha detectado un problema (horario de SLA no válido - Asegúrese de que el horario de SLA existe en la base de datos)',
    'editschedule'               => 'Editar horario',
    'desc_editschedule'          => '',
    'titleslaschedulenodel'      => 'No se pudo eliminar el horario de SLA',
    'msgslaschedulenodel'        => 'Los siguientes horarios de SLA no pudieron ser eliminados al ser utilizados actualmente por algunos planes de SLA.',
    'titledelslaschedules'       => 'Horarios de SLA eliminados (%d)',
    'msgdelslaschedules'         => 'Se eliminaron los siguientes horarios de SLA:',
    'slasettings'                => 'Configuración de SLA',
    'tabsettings'                => 'Configuración',
    'sla'                        => 'SLA',
    'insertplan'                 => 'Insertar plan',
    'desc_insertplan'            => '',
    'tabholidays'                => 'Días festivos',
    'plantitle'                  => 'Título del plan de SLA',
    'desc_plantitle'             => 'Por ejemplo, <em>Plan estándar de SLA para el soporte de tickets</em>.',
    'resolutionduehrs'           => 'Fecha del vencimiento de la resolución',
    'desc_resolutionduehrs'      => 'El número de horas en las que los tickets asignados a este plan de SLA deben resolverse (cambiar a un estado del tipo resuelto). Escriba el número de horas y minutos separados por un punto decimal (es decir, 1.30 se convierte en 1 hora y 30 minutos)',
    'resolutionduehrs2'          => 'Fecha límite de resolución',
    'overduehrs'                 => 'Fecha límite de respuesta',
    'desc_overduehrs'            => 'El número de horas en las que el ticket debe ser respondido$ (después de una respuesta de un usuario final). Escriba el número de horas y minutos separados por un punto decimal (es decir, 1.30 se convierte en 1 hora y 30 minutos)',
    'planschedule'               => 'Horario de SLA',
    'desc_planschedule'          => 'Especificar el horario de trabajo que utilizará este SLA para calcular cuando vencen los tickets.',
    'smatchtype'                 => 'Tipo de coincidencia de criterios',
    'matchtype'                  => 'Tipo de coincidencia de criterios',
    'desc_matchtype'             => 'Cómo la búsqueda trata los siguientes criterios.',
    'smatchall'                  => 'Coincidir con todos los criterios (AND)',
    'smatchany'                  => 'Coincidir con cualquier criterio (OR)',
    'isenabled'                  => 'Plan de SLA activado',
    'desc_isenabled'             => 'Si este plan SLA está activado.',
    'sortorder'                  => 'Orden de planes de SLA',
    'desc_sortorder'             => 'Con un ticket concreto pueden coincidir varios planes de SLA según los criterios que se especifiquen debajo. El orden determina qué reglas se ejecutan primero, de la más pequeña a la más grande (orden 1 se ejecutará antes de la orden 5).',
    'insertcriteria'             => 'Introducir criterios',
    'manageplans'                => 'Planes',
    'schedules'                  => 'Horarios',
    'desc_manageplans'           => '',
    'scheduletitle'              => 'Título de horario',
    'desc_scheduletitle'         => 'Por ejemplo, <em>Horarios de oficina de soporte</em> o <em>Horarios del helpdesk de nivel 2</em>.',
    'titlenocriteriaadded'       => 'No se pudo crear el plan de SLA',
    'msgnocriteriaadded'         => 'Debe especificar al menos un criterio para crear un plan de SLA (de lo contrario el helpdesk no sabe qué tickets asignar al plan SLA).',
    'noscheduleavailable'        => '-- Sin horario disponible --',
    'titlenoslasched'            => 'No hay horarios de trabajo disponibles',
    'msgnoslasched'              => 'No hay horarios de trabajo disponibles. Debe crear uno para poder crear un plan de SLA (de lo contrario el helpdesk no sabe cuándo iniciar o pausar el reloj).',
    'nocustomholidays'           => 'No se encontraron días festivos.',
    'editplan'                   => 'Editar Plan',
    'desc_editplan'              => '',
    'invalidslaplan'             => 'Ha surgido un problema (plan de SLA no válido - Asegúrese de que el plan de SLA existe en la base de datos)',
    'titleslaplandel'            => 'Planes de SLA eliminados (%d)',
    'msgslaplandel'              => 'Se eliminaron los siguientes planes de SLA:',
    'titleslaplanenable'         => 'Planes de SLA activados (%d)',
    'msgslaplanenable'           => 'Se activaron los siguientes planes de SLA:',
    'titleslaplandisable'        => 'Planes de SLA a desactivados (%d)',
    'msgslaplandisable'          => 'Se activaron los siguientes planes de SLA:',
    'linkedholidays'             => 'Vacaciones vinculadas',
    'titleslaplaninsert'         => 'Plan de SLA creado',
    'msgslaplaninsert'           => 'Plan de SLA (%s) se ha creado correctamente.',
    'titleslaplanupdate'         => 'Plan de SLA actualizado',
    'msgslaplanupdate'           => 'Plan de SLA (%s) se ha actualizado correctamente.',
    'if'                         => 'Si',
    'scheduledesc'               => 'Información del horario',

    // SLA Rules
    'srticketstatus'             => 'Estado del ticket',
    'desc_srticketstatus'        => '',
    'srticketpriority'           => 'Prioridad del ticket',
    'desc_srticketpriority'      => '',
    'srdepartmentid'             => 'Departamento de tickets',
    'desc_srdepartmentid'        => 'Tickets que pertenecen a un departamento.',
    'srownerstaffid'             => 'Propietario del ticket',
    'desc_srownerstaffid'        => 'Tickets asignados a un determinado usuario del personal.',
    'sremailqueueid'             => 'Cola de correo electrónico',
    'desc_sremailqueueid'        => 'Tickets creados o respondidos por correo electrónico a través de una determinada cola de correo electrónico.',
    'srusergroupid'              => 'Grupo de usuarios',
    'desc_srusergroupid'         => 'Buscar tickets cuyos destinatarios pertenezcan a un determinado grupo de usuarios.',
    'notapplicable'              => '-- NA --',
    'srfullname'                 => 'Nombre del destinatario',
    'desc_srfullname'            => 'Buscar los nombres de los destinatarios que participan en un ticket.',
    'sremail'                    => 'Dirección de correo electrónico',
    'desc_sremail'               => 'Buscar ticket por direcciones de correo electrónico del destinatario.',
    'srlastreplier'              => 'Última respuesta de',
    'desc_srlastreplier'         => '',
    'srsubject'                  => 'Asunto del ticket',
    'desc_srsubject'             => 'Buscar asunto del ticket.',
    'srcharset'                  => 'Juego de caracteres',
    'desc_srcharset'             => 'Tickets de determinados juegos de caracteres.',
    'srflagtype'                 => 'Marca del ticket',
    'desc_srflagtype'            => '',
    'srbayescategory'            => 'Categoría bayesiana',
    'desc_srbayescategory'       => 'Tickets que se corresponden con un categoría bayesiana específica.',
    'srcreator'                  => 'Ticket creado por',
    'desc_srcreator'             => '',
    'creatorstaff'               => 'Personal',
    'creatorclient'              => 'Usuario',
    'srunassigned'               => '-- Sin asignar --',
    'srtemplategroup'            => 'Grupo de plantillas',
    'desc_srtemplategroup'       => 'Ticket que pertenecen a un determinado grupo de plantillas.',
    'srisresolved'               => 'Está resuelto',
    'desc_srisresolved'          => 'Busca tickets que hayan sido resueltos (en un estado <strong>resuelto</strong>, en contraposición a un estado <strong>abierto</strong>).',
    'srtickettype'               => 'Tipo de ticket',
    'desc_srtickettype'          => '',
    'srwasreopened'              => 'Ticket ha sido reabierto',
    'desc_srwasreopened'         => 'Busca tickets que se resolvieran luego fueran reabiertos.',
    'srtotalreplies'             => 'Total de respuestas',
    'desc_srtotalreplies'        => 'Tickets con tantas respuestas.',


    'slajanuary'                 => 'Enero (01)',
    'slafebruary'                => 'Febrero (02)',
    'slamarch'                   => 'Marzo (03)',
    'slaapril'                   => 'Abril (04)',
    'slamay'                     => 'Mayo (05)',
    'slajune'                    => 'Junio (06)',
    'slajuly'                    => 'Julio (07)',
    'slaaugust'                  => 'Agosto (08)',
    'slaseptember'               => 'Septiembre (09)',
    'slaoctober'                 => 'Octubre (10)',
    'slanovember'                => 'Noviembre (11)',
    'sladecember'                => 'Diciembre (12)',

    // Potentialy unused phrases in adminsla.php
    'desc_insertschedule'        => '',
);

return $__LANG;
