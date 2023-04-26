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
    // ======= BEGIN v4 LOCALES =======
    'isenabled'                 => 'Regla del analizador de correo electrónico activada',
    'desc_isenabled'            => 'Se ignorarán las reglas de correo electrónico si se desactiva.',
    'insertcriteria'            => 'Introducir criterios',
    'tabactions'                => 'Acciones',
    'titlenoaction'             => 'No se han especificado acciones',
    'msgnoaction'               => 'Se debe especificar por lo menos una acción que ejecutará la egla de correo electrónico (en caso de coincidencia).',
    'if'                        => 'Si',
    'titleinsertrule'           => 'Regla de correo electrónico (%s) creada',
    'msginsertrule'             => 'Regla de correo electrónico (%s) creada correctamente.',
    'titleupdaterule'           => 'Regla de correo electrónico (%s) actualizada',
    'msgupdaterule'             => 'Regla de analizador de correo electrónico "%s" actualizada correctamente.',
    'titledelprules'            => 'Reglas de correo electrónico eliminadas (%d)',
    'msgdelprules'              => 'Se suprimieron las siguientes reglas de correo electrónico:',
    'titleenableprules'         => 'Reglas de correo electrónico activadas (%d)',
    'msgenableprules'           => 'Se activaron las siguientes reglas de correo electrónico:',
    'titledisableprules'        => 'Reglas de correo electrónico desactivadas (%d)',
    'msgdisableprules'          => 'Se desactivaron las siguientes reglas de correo electrónico:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Analizador de correos electrónicos',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Contiene',
    'opnotcontains'             => 'No contiene',
    'opequal'                   => 'Igual',
    'opnotequal'                => 'No es igual',
    'opgreater'                 => 'Mayor que',
    'opless'                    => 'Menor a',
    'opregexp'                  => 'Expresión regular',
    'strue'                     => 'Verdadero',
    'sfalse'                    => 'Falso',

    // Criteria
    'psendername'               => 'Nombre del remitente',
    'desc_psendername'          => 'El nombre del remitente se analiza mediante el "De:" en el encabezado del correo electrónico.',

    'psenderemail'              => 'Dirección de correo electrónico del remitente',
    'desc_psenderemail'         => 'La dirección del remitente del correo electrónico se analiza mediante el "De:" en el encabezado del correo electrónico. Por ejemplo: <em>De: <span class="tabletitle">Nombre del remitente</span> <remitente@domain.com></em>',

    'pdestinationname'          => 'Nombre de destino (destinatario)',
    'desc_pdestinationname'     => 'El nombre de destino se analiza desde el "Para:" en el encabezado del correo electrónico. El nombre de destino suele ser el nombre del usuario del personal que ha respondido al ticket (a menos que la configuración difiera).',

    'pdestinationemail'         => 'Dirección de correo electrónico de destino (destinatario)',
    'desc_pdestinationemail'    => 'La dirección de correo electrónico de destino se analiza desde el "Para:" en el encabezado del correo electrónico. Esto es generalmente la dirección de la cola de correo electrónico a la que se envía la respuesta.',

    'preplytoname'              => 'Nombre al que responder',
    'desc_preplytoname'         => 'El nombre al que responder a se analiza del "Responder a:" en el encabezado del correo electrónico.',

    'preplytoemail'             => 'Dirección a la que responder',
    'desc_preplytoemail'        => 'La dirección a la que responder a se analiza del "Responder a:" en el encabezado del correo electrónico.',

    'psubject'                  => 'Asunto',
    'desc_psubject'             => 'Busca coincidencia con el asunto del correo electrónico.',

    'precipients'               => 'Destinatarios',
    'desc_precipients'          => 'Busca coincidencia con los destinatarios de correos electrónicos. Puede haber varios recipientes a los que se envía un correo electrónico entrante, incluídos en el encabezado del correo electrónico en "Para:" y "CC:".',

    'pbody'                     => 'cuerpo del mensaje',
    'desc_pbody'                => 'Busca coincidencias en el contenido del cuerpo del correo electrónico después de eliminar el HTML (si la configuración no difiere) y los quiebres de líneas.',

    'pbodysize'                 => 'Tamaño del cuerpo',
    'desc_pbodysize'            => 'El tamaño total de los contenidos del cuerpo del mensaje en bytes.',

    'ptextbody'                 => 'Cuerpo de texto llano',
    'desc_ptextbody'            => 'Un correo electrónico puede tener dos tipos de contenido; <em>texto llano</em>, <em>HTML</em> or <em>ambos</em>. Esta regla busca solo en la versión de <em>texto llano</em> del cuerpo. Tenga en cuenta que no todos los correos electrónicos se envían en <em>ambos</em> formatos.',

    'phtmlbody'                 => 'Cuerpo HTML',
    'desc_phtmlbody'            => 'Un correo electrónico puede tener dos tipos de contenido; <em>texto llano</em>, <em>HTML</em> or <em>ambos</em>. Esta regla busca solo en la versión <em>HTML</em> del cuerpo. Tenga en cuenta que no todos los correos electrónicos se envían en <em>ambos</em> formatos.',

    'ptextbodysize'             => 'Tamaño del cuerpo de texto',
    'desc_ptextbodysize'        => 'Un correo electrónico puede tener dos tipos de contenido; <em>texto llano</em>, <em>HTML</em> or <em>ambos</em>. Esta regla busca coincidencias en el tamaño total de la versión de <em>texto llano</em> del contenido del cuerpo del correo en bytes.',

    'phtmlbodysize'             => 'Tamaño del cuerpo HTML',
    'desc_phtmlbodysize'        => 'Un correo electrónico puede tener dos tipos de contenido; <em>texto llano</em>, <em>HTML</em> or <em>ambos</em>. Esta regla busca coincidencias en el tamaño total de la versión <em>HTML</em> del contenido del cuerpo del correo en bytes.',

    'pattachmentname'           => 'Nombres de archivos adjuntos',
    'desc_pattachmentname'      => 'Busca coincidencias con el nombre de los archivos adjuntos aceptados en el helpdesk (como se define en el panel de control de administradores).',

    'pattachmentsize'           => 'Tamaño de archivos adjuntos',
    'desc_pattachmentsize'      => 'Busca coincidencias con el tamaño de los archivos adjuntos aceptados en el helpdesk (como se define en el panel de control de administradores).',

    'ptotalattachmentsize'      => 'Tamaño total de archivos adjuntos',
    'desc_ptotalattachmentsize' => 'Busca coincidencias con el <em>tamaño total</em> (en bytes) de los archivos adjuntos aceptados en el helpdesk (como se define en el panel de control de administradores).',

    'pisreply'                  => 'Es una respuesta de usuario',
    'desc_pisreply'             => 'Si el correo entrante es una respuesta de un usuario final a un ticket existente se aplicará esta regla.',

    'pisthirdparty'             => 'Es una respuesta de un tercer receptor',
    'desc_pisthirdparty'        => 'Si el correo entrante es una respuesta de una dirección añadida como tercer receptor a un ticket existente se aplicará esta regla.',

    'pfloodprotection'          => 'Protección contra inundación iniciada',
    'desc_pfloodprotection'     => 'Esta regla se aplica si se inicia la protección contra inundación del helpdesk.',

    'pisstaffreply'             => 'Es una respuesta del personal',
    'desc_pisstaffreply'        => 'Si el correo entrante es una respuesta de un usuario del personal a un ticket existente se aplicará esta regla.',

    'pticketstatus'             => 'Estado del ticket (post análisis)',
    'desc_pticketstatus'        => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla al estado de ticket.',

    'pticketemailqueue'         => 'Cola de correo electrónico',
    'desc_pticketemailqueue'    => 'Si el correo entrante se ha recuperado de la cola especificada aquí se aplicará esta regla.',

    'ptickettype'               => 'Tipo de ticket (post análisis)',
    'desc_ptickettype'          => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla al tipo de ticket.',

    'pticketpriority'           => 'Prioridad del ticket (post análisis)',
    'desc_pticketpriority'      => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla a la prioridad del ticket.',

    'pticketusergroup'          => 'Grupo de usuarios del ticket (post análisis)',
    'desc_pticketusergroup'     => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla al grupo de usuarios del ticket.',

    'pticketdepartment'         => 'Departamento del ticket (post análisis)',
    'desc_pticketdepartment'    => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla al departamento del ticket.',

    'pticketowner'              => 'Propietario del ticket (post análisis)',
    'desc_pticketowner'         => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla al propietario del ticket.',
    'prunassigned'              => '-- Sin asignar --',

    'pticketflagtype'           => 'Ticket flag (post-parse)',
    'desc_pticketflagtype'      => 'Si el correo entrante es una respuesta a un ticket existente se aplicará esta regla al marcado del ticket.',

    'pbayescategory'            => 'Categoría bayesiana',
    'desc_pbayescategory'       => 'El helpdesk pasa todos los correos entrantes por el filtro bayesiano y asigna al ticket la categoría bayesiana con la puntuación más alta. Puede utilizarlo para filtrar correo electrónico spam o entrenar el sistema para enrutar automáticamente los tickets basándose en su contenido, utilizando categorías bayesianas personalizadas.',

    // Insert Rule
    'insertrule'                => 'Insertar regla',
    'iruletype'                 => 'Tipo de regla de correo electrónico: ',
    'ipreparse'                 => 'Antes de procesar el correo electrónico (pre-análisis)',
    'ipostparse'                => 'Después de procesar el correo electrónico (post-análisis)',

    'paignore'                  => 'Ignorar correo electrónico',
    'desc_paignore'             => 'El correo electrónico será ignorado por el helpdesk y no será procesado a un ticket o una respuesta.',
    'panoautoresp'              => 'No enviar respuesta automática',
    'desc_panoautoresp'         => 'No se enviará una respuesta automática confirmando la recepción del nuevo correo electrónico (como un ticket nuevo o una respuesta).',
    'panoalerts'                => 'No procesar reglas de notificación',
    'desc_panoalerts'           => 'Si se activa, no se procesarán las reglas de notificación del helpdesk.',
    'pnochange'                 => '-- Ningún cambio --',
    'pcstaff'                   => 'Asignar el ticket',
    'desc_pcstaff'              => 'El ticket (nuevo o una respuesta a uno existente) será asignado a este usuario del personal.',
    'pcstatus'                  => 'Cambiar estado del ticket',
    'desc_pcstatus'             => 'El ticket cambiará a este estado.',
    'pcpriority'                => 'Cambiar la prioridad del ticket',
    'desc_pcpriority'           => 'El ticket cambiará a esta prioridad.',
    'pcdepartment'              => 'Mover ticket a departamento',
    'desc_pcdepartment'         => 'Se moverá el ticket a este departamento.',
    'pcslaplan'                 => 'Cambial el plan SLA del ticket',
    'desc_pcslaplan'            => 'Se asignará este plan SLA al ticket.',
    'pcmovetotrash'             => 'Mover a la papelera',
    'desc_pcmovetotrash'        => 'Se moverá el ticket a la papelera',
    'pcflag'                    => 'Marcar ticket',
    'desc_pcflag'               => 'Se marcará el ticket de este color.',
    'paddnotes'                 => 'Añadir una nota al ticket',
    'desc_paddnotes'            => 'Se agregará una nota al ticket.',
    'pcforward'                 => 'Reenviar el correo electrónico',
    'desc_pcforward'            => 'Se reenviará el correo electrónico a esta dirección.',
    'preply'                    => 'Responder al correo electrónico',
    'desc_preply'               => 'El correo electrónico recibirá una respuesta automática con este mensaje.',
    'panoticket'                => 'No procesar como respuesta a un ticket',
    'desc_panoticket'           => 'Aunque el correo electrónico sea una respuesta a un ticket existente, el correo electrónico no se añadirá al ticket existente y se creará un ticket nuevo.',
    'pctickettype'              => 'Cambiar el tipo de ticket',
    'desc_pctickettype'         => 'El tipo de ticket cambiará al que se determine aquí.',
    'pcaddtags'                 => 'Añadir etiquetas al ticket',
    'desc_pcaddtags'            => 'Se añadirán estas etiquetas al ticket.',
    'pcremovetags'              => 'Eliminar etiquetas del ticket',
    'desc_pcremovetags'         => 'Si el ticket tiene alguna de estas etiquetas se eliminarán.',
    'pcprivate'                 => 'Hacer la respuesta al ticket privada',
    'desc_pcprivate'            => 'Esta respuesta al ticket será marcada como privada y solo la podrán ver los demás usuarios del personal (esto se aplica solo a respuestas a tickets del personal).',

    // Edit Rules
    'editrule'                  => 'Editar regla',
    'ptitle'                    => 'Título de la regla de correo electrónico',
    'desc_ptitle'               => 'Por ejemplo, <em>Recibos de PayPal</em>.',
    'pstop'                     => 'Detener procesamiento de otras reglas',
    'desc_pstop'                => 'Si esta regla se aplica primero, no se procesarán otras reglas de correo electrónico después.',

    // Manage Rules
    'managerules'               => 'Reglas',
    'ruletitle'                 => 'Título de la regla',
    'sortorder'                 => 'Orden de ejecución',
    'desc_sortorder'            => 'Es posible que varias reglas de correo electrónico coincidan en el mismo correo. El helpdesk procesará las reglas según el número del orden de ejecución, del más pequeño al más grande.',
    'ruletype'                  => 'Tipo de regla',
    'creationdate'              => 'Creado',

    // Potentialy unused phrases in mailparser_rules.php
    'smatchtype'                => 'Match Type',
    'matchtype'                 => 'Criteria Options',
    'desc_matchtype'            => 'Select the grouping method for the criteria fields.',
    'smatchall'                 => 'Match <b>All</b> Criteria (AND)',
    'smatchany'                 => 'Match <b>Any</b> Criteria (OR)',
    'criteria'                  => 'Criteria',
    'newcriteria'               => 'New Criteria',
    'ruleinsertconfirm'         => 'Parser rule "%s" inserted successfully',
    'ruleupdateconfirm'         => 'Parser rule "%s" updated successfully',
    'invalidrule'               => 'ERROR: Invalid email parser rule',
    'ruledelconfirm'            => 'Parser rule deleted successfully',
    'rulelist'                  => 'Rule List',
    'notapplicable'             => '-- NA --',

);

return $__LANG;
