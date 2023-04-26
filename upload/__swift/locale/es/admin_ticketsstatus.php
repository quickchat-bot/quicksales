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
    // ======= BEGIN v4 LOCALES =======
    'tickets'                               => 'Tickets',
    'tabgeneral'                            => 'General',
    'tabpermissions'                        => 'Permisos',
    'staffvisibilitycustom'                 => 'Restirngir cambio de tickets a este estado',
    'desc_staffvisibilitycustom'            => 'Si se activa, solo los equipos mencionados abajo podrán cambiar tickets a este estado.',
    'staffgroups'                           => 'Equipos de personal',
    'triggersurvey'                         => 'Enviar correo electrónico con encuesta de satisfacción',
    'desc_triggersurvey'                    => 'Cuando un ticket cambia a este estado, se envía un correo electrónico al usuario pidiendo su calificación y retroalimentación.',
    'statustitle'                           => 'Nombre del estado',
    'desc_statustitle'                      => 'Introduzca un nombre para este estado.',
    'statuscolor'                           => 'Color del estado',
    'desc_statuscolor'                      => 'Recomendamos verde para estados abiertos y colores oscuros para estados resueltos.',
    'statusbgcolor'                         => 'Color de fondo del estado',
    'desc_statusbgcolor'                    => 'En el panel de control del personal toda la barra de propiedades de tickets se rellenará con este color (dependiendo del estado del ticket). ¡No se lo pierda!',
    'displayorder'                          => 'Orden de visualización',
    'desc_displayorder'                     => 'Los estados de tickets se visualizan de acuerdo con su orden de visualización, del más bajo al más alto.',
    'displayiconstatus'                     => 'Icono',
    'desc_displayiconstatus'                => 'Suba o enlace un icono para este estado. Puede utilizar {$themepath} para señalar el directorio de imágenes del helpdesk, por ejemplo: <em>{$themepath}icon_statusopen.gif</em>.',
    'statusdep'                             => 'Asociar estado con departamento',
    'desc_statusdep'                        => 'Asocie este estado con un departamento detemrinado. Si no lo hace, este estado estará disponible para todos los departamentos de tickets.',
    'statusalldep'                          => '-- No enlazado --',
    'markasresolved'                        => '¿Es este un estado de tickets<em>resuelto</em>?',
    'desc_markasresolved'                   => 'Si los tickets que obtienen este estado están resueltos o cerrados (es decir, arreglados, resueltos o terminados), active esta opción. Así ocultarán en la lista de tickets abiertos.',
    'displayinmainlist'                     => 'Mostrar en el listado de tickets principal',
    'desc_displayinmainlist'                => 'Los tickets de este estado aparecerán en el listado de tickets principal.',
    'displaycount'                          => 'Mostrar recuento de tickets en el árbol de navegación de tickets',
    'desc_displaycount'                     => 'Si se activa, se mostrará el recuento de tickets en este estado en el árbol de navegación del panel de control del personal. Le recomendamos desactivar esta opción para estados resueltos.',
    'statustype2'                           => 'Tipo de estado',
    'desc_statustype2'                      => '<strong>Público</strong> Los estados son visibles en todas partes y para sus usuarios finales.<br /><strong>Privado</strong> Los estados son accesibles sólo para usuarios del personal.',
    'resetduetime'                          => 'Eliminar fecha límite de respuesta del ticket cuando se encuentre en este estado',
    'desc_resetduetime'                     => 'Cuando un ticket cambia a este estado, el tiempo de vencimiento del ticket se eliminará automáticamente.',
    'dispatchnotification'                  => 'Enviar la notificación al usuario',
    'desc_dispatchnotification'             => 'Si se activa, el sistema enviará una notificación al usuario/creador cuando se active este estado en su ticket.',
    'insertstatus'                          => 'Insertar estado',
    'desc_insertstatus'                     => '',
    'invalidticketstatus'                   => 'No se pudo cargar el estado del ticket.',
    'managestatus'                          => 'Estados',
    'desc_managestatus'                     => '',
    'wineditticketstatus'                   => 'Editar estado: %s',
    'editstatus'                            => 'Editar estado',
    'desc_editstatus'                       => '',
    'taboptions'                            => 'Opciones',
    'titledelticketstatus'                  => 'Estados de tickets eliminados (%d)',
    'msgdelticketstatus'                    => 'Se eliminaron los siguientes estados de tickets:',
    'titleticketstatusinsert'               => 'Estado de ticket %s creado',
    'msgticketstatusinsert'                 => 'El estado de ticket %s se ha creado correctamente.',
    'titleticketstatusupdate'               => 'Estado de ticket %s actualizado',
    'msgticketstatusupdate'                 => 'El estado de ticket %s se ha actualizado correctamente.',
    'titlemasterstatusdelete'               => 'No se pueden eliminar los estados de tickets (%d)',
    'msgmasterstatusdelete'                 => 'Los siguientes estados de tickets principales no pudieron ser eliminados:',
    'tablanguages'                          => 'Idiomas: traducción',
    'title_statusinuse'                     => 'Estado usado',
    'message_statusinuse_editassociation'   => 'Existen tickets en este estado que pertenecen al departamento asociado. Debe cambiar el estado del ticket para poder actualizar el departamento asociado.',
    'message_statusinuse_createassociation' => 'Existen tickets en este estado que pertenecen al departamento asociado. Debe cambiar el estado del ticket para poder actualizar el departamento asociado.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
