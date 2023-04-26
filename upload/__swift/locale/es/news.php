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
    'news'                 => 'Noticias',
    'managenews'           => 'Administrar noticias',
    'managesubscribers'    => 'Administrar suscriptores',
    'insertnews'           => 'Insertar noticias',
    'importexport'         => 'Importar/Exportar',

    // Manage Subscribers
    'subscribers'          => 'Suscriptores',
    'dlsubscriberconfirm'  => 'Suscriptor eliminado',
    'subemail'             => 'Dirección de correo electrónico',
    'desc_subemail'        => 'Introduzca la dirección de correo electrónico del suscriptor.',
    'substatus'            => 'Estado de validación',
    'subdate'              => 'Fecha',
    'sublist'              => 'Lista de suscriptores',
    'legend'               => 'Leyenda: ',
    'subinsertconfirm'     => '%s ha sido registrado',

    // Insert Subscriber
    'insertsubscriber'     => 'Introducir suscriptor',

    // Edit Subscriber
    'invalidsubscriber'    => 'Suscriptor no válido',
    'editsubscriber'       => 'Editar suscriptor',
    'subscriberdetails'    => 'Datos del suscriptor',
    'subupdateconfirm'     => 'Suscriptor %s actualizado',

    // Manage News
    'newssubject'          => 'Asunto de noticia',
    'newsdescription'      => 'Descripción',
    'desc_newsdescription' => 'Introduzca una breve descripción de la noticia. Este campo está limitado a <strong>255</strong> caracteres.',
    'newscontents'         => 'Contenidos',
    'newstype'             => 'Tipo de noticias',
    'desc_newstype'        => '<strong>Público</strong> Sólo se publicará en el lado del cliente del centro de soporte. <br /><strong>Privado</strong> Solo se publicará en el panel de control del personal y para sus usuarios finales.',
    'newsexpiry'           => 'Fecha de Caducidad',
    'newsformexpiry'       => 'Caduca (opcional)',
    'desc_newsformexpiry'  => 'Cuando haya pasado la fecha de caducidad el artículo no se mostrará (aunque puede reactivarse en cualquier momento).',
    'manage'               => 'Administrar',
    'publicnews'           => 'Noticias públicas',
    'privatenews'          => 'Noticias privadas',
    'newsdelconfirm'       => 'Artículo eliminado',

    // Insert News
    'contents'             => 'Contenidos',
    'newsdetails'          => 'Detalles de noticias',
    'newsoptions'          => 'Opciones de noticias',
    'sendemail'            => 'Enviar correo electrónico a suscriptores',
    'desc_sendemail'       => '¿Debe enviarse la noticia en un correo electrónico a todos los suscriptores?',
    'emailsubject'         => 'Asunto de correo electrónico personalizado',
    'desc_emailsubject'    => 'Si no se especifica un asunto aquí se utilizará el título de la noticia en su lugar.',
    'newstgroup'           => 'Grupo de plantillas',
    'desc_newstgroup'      => 'Los grupos de plantillas bajo los cuales se publicará la noticia.',
    'newsinsertconfirm'    => 'Artículo de noticias (%s) creado correctamente',
    'unsubscribe'          => 'Anular suscripción: ',
    'clickhere'            => 'Haga clic aquí',
    'fromname'             => 'Nombre de',
    'desc_fromname'        => 'El <em>Nombre de</em> se utilizará para los correos electrónicos enviados a suscriptores, si se habilita.',
    'fromemail'            => 'Dirección de correo electrónico de',
    'desc_fromemail'       => 'El <em>correo de</em> se utilizará para los correos electrónicos enviados a suscriptores, si se habilita.',
    'updateprefix'         => 'ACTUALIZAR: ',

    // Edit News
    'updatenews'           => 'Actualizar noticias',
    'invalidnewsitem'      => 'Artículos de noticias no válidos',
    'newsupdateconfirm'    => 'Artículo de noticias (%s) actualizado correctamente',
    'exportpdf'            => 'Exportar PDF',
    'deletenews'           => 'Eliminar noticias',
    'newsoptions'          => 'Opciones de noticias',
    'nwpostedby'           => 'Publicado por ',
    'nwposted'             => 'Publicado ',
    'nwpostedon'           => ' en ',
    'nwread'               => 'Leer',

    // Comments
    'comments'             => 'Comentarios',

    // Potentialy unused phrases in news.php
    'subisvalidated'       => 'Is Validated?',
    'desc_subisvalidated'  => 'If set to <i>No</i>, the provided email address will be sent an activation link.',
    'validated'            => 'Validated',
    'notvalidated'         => 'Not Validated',
    'exportfilename'       => 'File name',
    'desc_exportfilename'  => 'Enter the filename for the export of data.',
    'export'               => 'Export',
    'exportsubscriberdata' => 'Export Subscriber Data',
    'importsubscribers'    => 'Import Subscribers',
    'import'               => 'Import',
    'importdesc'           => 'You can import all news subscribers by entering their email addresses separated by new lines below.',
    'importconfirm'        => 'Subscriber import process completed!<BR />Total: %s<BR />Imported: %s<BR />Failed: %s<BR />Addresses that failed to be imported: %s',
    'none'                 => 'None',
    'desc_newssubject'     => 'Enter a subject for the news item.',
);

return $__LANG;
