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
    'ratings'                     => 'Calificaciones',
    'tabgeneral'                  => 'General',
    'tabpermissionsstaff'         => 'Permisos (personal)',
    'tabpermissionsuser'          => 'Permisos (usuario)',
    'staffgroups'                 => 'Equipos de personal',
    'usergroups'                  => 'Grupos de usuarios',
    'ratingticket'                => 'Ticket',
    'ratingticketpost'            => 'Entradas de tickets',
    'ratingchatsurvey'            => 'Encuesta sobre chat',
    'ratingchathistory'           => 'Historial del chat',

    'staffvisibilitycustom'       => 'Restringir modificación de valoraciones a ciertos equipos de personal',
    'desc_staffvisibilitycustom'  => 'Si se activa, solo los equipos de abajo podrán modificar y determinar los valores de esta calificación.',

    'buservisibilitycustom'       => 'Restringir la calificación a grupos de usuarios específicos',
    'desc_buservisibilitycustom'  => 'Si se activa, solo los grupos de abajo podrán modificar y determinar los valores de esta calificación.',

    'bstaffvisibilitycustom'      => 'Restringir la calificación a equipos de personal específicos',
    'desc_bstaffvisibilitycustom' => 'Si se activa, solo los equipos de abajo podrán ver esta calificación.',

    'isclientonly'                => 'Impedir que el personal determine los valores de esta calificación',
    'desc_isclientonly'           => 'Solo los usuarios finales podrán establecer los valores de esta calificación si esto está activado.',

    'ratingscale'                 => 'Escala de calificación',
    'desc_ratingscale'            => 'La calificación máxima que se puede otorgar. Por ejemplo, si se selecciona <em>5</em>, la calificación más baja posible será 1 y la más alta 10.',

    'ratingtype'                  => 'Área del helpdesk',
    'desc_ratingtype'             => 'El área del helpdesk que evalúa esta calificación. <br /><br /><strong>Ticket:</strong> Se usa para calificar tickets enteros.<br /><strong>Entradas de tickets:</strong> Se usa para calificar respuestas individuales de tickets.<br /><strong>Encuesta de chat:</strong> La calificación se añade a la encuesta después del chat.<br /><strong>Historial del chat:</strong> Se usa para calificar los historiales de chats en el panel de control del personal (ideada para la evaluación y auditoría interna).',

    'ratingtitle'                 => 'Etiqueta de calificación',
    'desc_ratingtitle'            => 'Por ejemplo, <em>satisfacción general</em> o <em>velocidad de resolución</em>.',

    'displayorder'                => 'Orden de visualización',
    'desc_displayorder'           => 'En el mismo lugar pueden aparecer varias calificaciones. Se visualizarán según el número del orden de visualización, del más bajo al más alto.',

    'ratingdep'                   => 'Restringir calificación a un departamento',
    'desc_ratingdep'              => 'Seleccione un departamento para restringir esta calificación a tickets o chats de un departamento específico. Si no selecciona ningún departamento, la calificación aparecerá en todos sus departamentos.',
    'ratingalldep'                => '-- Todos los departamentos --',

    'iseditable'                  => 'Permitir modificación después de primer envío',
    'desc_iseditable'             => 'El valor establecido para esta calificación puede ser modificado, si se activa.',

    'ratingvisibility'            => '¿Quién puede enviar una calificación?',
    'desc_ratingvisibility'       => '<strong>Público</strong> Calificaciones disponibles para todos los usuarios. Seleccione esta opción si quiere obtener retroalimentación de sus usuarios finales y quiere que también sea visible para su personal.<br /><strong>Privado</strong> Calificaciones solo disponibles para usuarios para calificación interna.',

    'ratingvis'                   => 'Visibilidad',
    'insertrating'                => 'Introducir calificación',
    'desc_insertrating'           => '',
    'manageratings'               => 'Administrar',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Editar calificación: %s',
    'editrating'                  => 'Editar calificación',
    'desc_editrating'             => '',
    'titledelrating'              => 'Calificaciones eliminadas (%d)',
    'msgdelrating'                => 'Las siguientes calificaciones y todos sus datos han sido eliminados:',
    'titleratinginsert'           => 'Calificación creada',
    'msgratinginsert'             => 'La calificación (%s) se ha creado correctamente.',
    'titleratingupdate'           => 'Calificación actualizada',
    'msgratingupdate'             => 'La calificación (%s) se ha actualizado correctamente.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'Base de conocimientos',
    'ratingtroubleshooter'        => 'Solucionador de problemas',
    'ratingnews'                  => 'Noticias',
);


return $__LANG;
