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

    'tabsettings'                   => 'Configuración',
    'templates'                     => 'Plantillas',

    // Header Logos (nee Personalize)
    'personalizationerrmsg' => 'Debe proveer por lo menos un logo de cabecera',
    'titlepersonalization'          => 'Logos de cabecera actualizados',
    'msgpersonalization'            => 'Se han guardados sus logos de cabecera. Si alguno ha cambiado, debe actualizar su página para ver los cambios.',
    'tabpersonalize'                => 'Logos del encabezado',
    'generalinformation'            => 'Información general',
    'companyname'                   => 'Nombre de la empresa',
    'desc_companyname'              => 'El nombre que se utiliza para personalizar la interfaz del soporte a los clientes y los correos electrónicos salientes.',
    'defaultreturnemail'            => 'Dirección de correo electrónico de retorno por defecto',
    'desc_defaultreturnemail'       => 'Esta dirección se usa como la dirección "de" predeterminada en correos salientes. Esta dirección debe corresponderse con una cola de correo electrónico activa para poder aceptar respuestas de los clientes.',
    'logoimages'                    => 'Imágenes de cabecera',
    'supportcenterlogo'             => 'Logo de cabecera del centro de apoyo',
    'desc_supportcenterlogo'        => 'Este logo se muestra en la parte del cliente final del centro de soporte. Recomendamos que el logo quepa en 150px (ancho) y 40px (alto)',
    'stafflogo'                     => 'Logo de la cabecera del panel de control',
    'desc_stafflogo'                => 'Este logo se muestra en la cabecera de los paneles de control (arriba a la izda.). El logo <em>debe caber en</em> <strong>150px</strong> (ancho) por <strong>24px</strong> (alto).',
    'personalize'                   => 'Logos de cabecera',

    // Import and export
    'tabexport'                     => 'Exportar',
    'export'                        => 'Exportar',
    'tabimport'                     => 'Importar',
    'import'                        => 'Importar',
    'result'                        => 'Resultado',
    'exporthistory'                 => 'Exportar también historial de plantillas',
    'desc_exporthistory'            => 'Además de las versiones más recientes de las plantillas, también se exportarán las versiones anteriores.',
    'mergeoptions'                  => 'Opciones de fusión',
    'addtohistory'                  => 'Mantener historial de revisiones de plantillas',
    'desc_addtohistory'             => 'Si la fusión de grupo de plantillas anula cualquier plantilla existente, se conservarán las plantillas anuladas en el historial de plantillas.',
    'titleversioncheckfail'         => 'Este grupo de plantillas está desactualizado',
    'msgversioncheckfail'           => 'No se pudo importar este grupo de plantillas porque se generó utilizando una versión anterior de QuickSupport y es posible que falten plantillas. Si desea anular la comprobación de versión, active <em>ignorar versión de grupo de plantillas</em>.',
    'importexport'                  => 'Importar/Exportar',
    'exporttgroup'                  => 'Grupo de plantillas que exportar',
    'desc_exporttgroup'             => 'El grupo de plantillas que exportar como archivo XML.',
    'exportoptions'                 => 'Tipo exportación',
    'desc_exportoptions'            => 'El tipo de plantillas que exportar.',
    'exportalltemplates'            => 'Exportar todas las plantillas',
    'exportmodifications'           => 'Exportar solo plantillas modificadas',
    'templatefile'                  => 'Archivo XML de grupo de plantillas',
    'desc_templatefile'             => 'Seleccione un archivo XML de grupo de plantillas de su ordenador.',
    'createnewgroup'                => '-- Crear nuevo grupo de plantillas --',
    'mergewith'                     => 'Fusionar plantillas importadas con',
    'desc_mergewith'                => 'Elija si crear un nuevo grupo de plantillas usando el contenido del archivo, o fusionar solo las plantillas modificadas con un grupo de plantillas existente.',
    'ignoreversion'                 => 'Ignorar versión de grupo de plantillas',
    'desc_ignoreversion'            => 'Si se selecciona, se ignorará la versión del archivo que se importa. Se aconseja no activar esta opción ya que puede ocasionar errores en el centro de soporte del cliente.',
    'titletemplateimportfailed'     => 'Se detectó un problema con el archivo de grupo de plantillas',
    'msgtemplateimportfailed'       => 'El archivo del grupo de plantillas subido no pudo ser procesado. Puede contener datos erróneos.',
    'titletgroupmerge'              => 'Fusionar archivo de grupo de plantillas importado con %s',
    'msgtgroupmerge'                => 'El archivo de grupo de plantillas %s fue importado y fusionado con el grupo de plantillas %s correctamente.',
    'titletgroupimport'             => 'Grupo de plantillas %s importado',
    'msgtgroupimport'               => 'El archivo de grupo de plantillas %s fue importado y el grupo de plantillas %s creado correctamente.',

    // Templates
    'changegroup'                   => 'Cambiar grupo de plantillas',
    'restoretemplates'              => 'Restaurar plantillas',
    'desc_restoretemplates'         => '',
    'moditgroup'                    => 'Grupo de plantillas que buscar',
    'desc_moditgroup'               => 'The templates of this template group will be checked for errors.',
    'tabgeneral'                    => 'General',
    'restoretgroup'                 => 'Restaurar plantillas a última versión original: %s',
    'tabrestore'                    => 'Restaurar plantillas',
    'findtemplates'                 => 'Encontrar plantillas',
    'titlerestoretemplates'         => 'Plantillas restauradas (%d)',
    'msgrestoretemplates'           => 'Se restauraron las siguientes plantillas:',
    'tabdiagnostics'                => 'Diagnóstico',
    'tabsearch'                     => 'Buscar plantillas',
    'titletgrouprestorecat'         => 'Categoría del grupo de plantillas restaurada',
    'msgtgrouprestorecat'           => 'Las plantillas en la categoría %s de %s (%s) fueron restauradas correctamente.',
    'expandcontract'                => 'Ampliar/contratar',
    'tabhistory'                    => 'Historial',
    'templateversion'               => 'Número de versión de la plantilla',
    'saveasnewversion'              => 'Guardar una nueva versión de la plantilla',
    'titletemplaterestore'          => '%s restaurado',
    'msgtemplaterestore'            => 'La plantilla %s fue restaurada a su estado original.',
    'titletemplateupdate'           => '%s actualizado',
    'msgtemplateupdate'             => 'Se guardaron correctamente los cambios de la plantilla %s.',
    'tabedittemplate'               => 'Plantilla: %s (%s)',
    'titlenohistory'                => 'No hay historial de plantilla',
    'msgnohistory'                  => 'No hay revisiones anteriores de esta plantilla, por lo que no hay nada que mostrar.',
    'historydescription'            => 'Cambios',
    'historyitemlist'               => '%s: %s (%s) Notas: <em>%s</em>',
    'system'                        => '(Sistema)',
    'historyitemcurrent'            => '%s: <em><strong>Actual</strong></em> (%s)',
    'compare'                       => 'Comparar',
    'current'                       => 'Actual',
    'notcurrenttemp'                => 'Versión antigua',
    'exportdiff'                    => 'Exportar archivo diff',
    'tabcomparison'                 => 'Comparar versiones',
    'changelognotes'                => 'Describa sus cambios',
    'desc_changelognotes'           => 'Si está realizando cambios en esta plantilla, añada una breve nota aquí para poder seguir los cambios en la pestaña <strong>Historial</strong>.',
    'none'                          => 'Ninguno',
    'inserttemplate'                => 'Insertar plantilla',
    'inserttgroup'                  => 'Grupo de plantillas',
    'desc_inserttgroup'             => 'Por favor, seleccione un grupo de plantillas para esta plantilla.',
    'templateeditingguideline'      => 'Buenas prácticas de edición de plantillas',
    'desc_templateeditingguideline' => 'Con el editor de plantillas puede personalizar el aspecto y la funcionalidad de su centro de soporte. Si una futura actualización de QuickSupport contuviera cambios a esta plantilla, se le pedirá que la restaure a la última versión original. Esto borrará sus cambios en las plantillas y los deberá volver a realizar.<br><br>Para minimizar posibles quebraderos de cabeza, eche un vistazo a la <a href="https://go.opencart.com.vn/?pageid=NBTHelpDeskTemplates" target="_blank" rel="noopener noreferrer">guía de buenas prácticas de edición de plantillas</a> antes de personalizar su centro de soporte.',
    'restoreconfirmaskcat'          => '¿Está seguro de que desea restaurar las plantillas en esta categoría?\\nEsta acción es irreversible; ¡Restaurar las plantillas puede conllevar una pérdida de todos los cambios UI que haya realizado a las plantillas existentes!',
    'inserttemplatetgroup'          => 'Grupo de plantillas',
    'inserttemplatetcategory'       => 'Categoría de la plantillas',
    'inserttemplatename'            => 'Nombre de plantilla',
    'desc_inserttemplatename'       => 'Introduzca un nombre para la plantilla con caracteres alfanuméricos. Por ejemplo, <em>Texto de cabecera</em> o <em>bienvenidoalcentrodesoporte</em>.',
    'titleinserttemplatedupe'       => 'El nombre de la plantilla ya está en uso',
    'msginserttemplatedupe'         => 'Este grupo de plantillas ya tiene una plantilla con el mismo nombre; por favor, seleccione otro.',
    'titleinserttemplatechar'       => 'El nombre de la plantilla contiene caracteres no válidos',
    'msginserttemplatechar'         => 'El nombre de la plantilla solo puede contener caracteres alfanuméricos (letras y números).',
    'titleinserttemplate'           => 'Plantilla %s creada',
    'msginserttemplate'             => 'Se creó la plantilla %s en el grupo de plantillas %s.',
    'titletemplatedel'              => 'Plantilla eliminada',
    'msgtemplatedel'                => 'Se eliminó la plantilla %s.',

    // Template group
    'titleisenabledprob'            => 'No se puede desactivar el grupo de plantillas predeterminado',
    'msgisenabledprob'              => 'Este grupo de plantillas está configurado como predeterminado para el helpdesk y no puede ser eliminado.',
    'useloginshare'                 => 'Usar LoginShare para autenticar a usuarios',
    'desc_useloginshare'            => 'Los usuarios que inicien sesión en el helpdesk cuando este grupo de plantillas esté activado serán autenticados mediante la API LoginShare.',
    'groupusername'                 => 'Nombre de usuario',
    'desc_groupusername'            => 'Introduzca un nombre de usuario para activar la protección por contraseña para este grupo de plantillas.',
    'passwordprotection'            => 'Protección por contraseña',
    'enablepassword'                => 'Activar protección por contraseña',
    'desc_enablepassword'           => 'Se pedirá a los usuarios finales que introduzcan nombre de usuario y contraseña para abrir el centro de soporte.',
    'password'                      => 'Contraseña',
    'desc_password'                 => 'Introduzca una contraseña para activar la protección por contraseña para este grupo de plantillas.',
    'passwordconfirm'               => 'Vuelva a escribir la contraseña',
    'desc_passwordconfirm'          => 'Confirme la contraseña para evitar erratas.',
    'tabsettings_tickets'           => 'Configuración: Tickets',
    'tabsettings_livechat'          => 'Configuración: Live Chat',
    'isenabled'                     => 'El grupo de plantillas está activado',
    'desc_isenabled'                => 'Si un grupo de plantillas está desactivado, no se activará y los usuarios finales no podrán acceder a él.',
    'titlepwnomatch'                => 'Las contraseñas no coinciden',
    'msgpwnomatch'                  => 'The passwords entered do not match. Please try again.',
    'titleinvalidgrouptitle'        => 'El nombre del grupo de plantillas contiene caracteres no válidos',
    'msginvalidgrouptitle'          => 'El nombre de los grupos de plantillas solo puede contener caracteres alfanuméricos.',
    'titlegrouptitleexists'         => 'El nombre del grupo de plantillas ya está siendo usado',
    'msggrouptitleexists'           => 'Otro grupo de plantillas utiliza este título. Por favor, escoja otro.',
    'winedittemplategroup'          => 'Editar grupo de plantillas: %s',
    'tabpermissions'                => 'Permisos',
    'titletgroupupdate'             => 'Grupo de plantillas %s actualizado',
    'msgtgroupupdate'               => 'El grupo de plantillas %s se ha actualizado correctamente.',
    'titletgroupinsert'             => 'Grupo de plantillas %s creado',
    'msgtgroupinsert'               => 'El grupo de plantillas %s se ha creado correctamente.',
    'titletgroupnodel'              => 'No se pudo eliminar el grupo de plantillas',
    'msgtgroupnodel'                => 'Este grupo principal de plantillas no pudo ser eliminado:',
    'titletgroupdel'                => 'Grupos de plantillas eliminados (%d)',
    'msgtgroupdel'                  => 'Los siguientes grupos de plantillas fueron eliminados:',
    'titletgrouprestore'            => 'Grupos de plantillas restaurados (%d)',
    'msgtgrouprestore'              => 'Los siguientes grupos de plantillas y sus plantillas fueron restaurados a su estado original:',
    'insertemplategroup'            => 'Insertar grupo de plantillas',
    'tgrouptitle'                   => 'Nombre del grupo de plantillas',
    'desc_tgrouptitle'              => 'El nombre de los grupos de plantillas solo pueden contener caracteres alfanuméricos.',
    'gridtitle_companyname'         => 'Nombre de la organización',
    'companyname'                   => 'Nombre de la empresa',
    'desc_companyname'              => 'El nombre que se utiliza para personalizar la interfaz del soporte a los clientes y los correos electrónicos salientes.',
    'generaloptions'                => 'Opciones generales',
    'defaultlanguage'               => 'Idioma predeterminado',
    'desc_defaultlanguage'          => 'El idioma que el helpdesk utilizará por defecto para este grupo de plantillas.',
    'usergroups'                    => 'Roles de grupos de usuarios',
    'guestusergroup'                => 'Grupo de usuarios invitados (no conectados)',
    'desc_guestusergroup'           => 'Este grupo de usuarios determina los permisos y la configuración para cualquiera que visite el centro de soporte y <strong>no haya iniciado sesión</strong>.',
    'regusergroup'                  => 'Grupo de usuarios registrados (conectados)',
    'desc_regusergroup'             => 'Este grupo de usuarios determina los permisos y la configuración para cualquiera que visite el centro de soporte y <strong>haya iniciado sesión</strong>.',
    'restrictgroups'                => 'Restringir a grupo de usuarios registrados',
    'desc_restrictgroups'           => 'Solo los usuarios pertenecientes al grupo de usuarios especificado arriba podrán iniciar sesión en el centro de soporte con este grupo de plantillas.',
    'copyfrom'                      => 'Copiar plantillas de grupo de plantillas',
    'desc_copyfrom'                 => 'Las plantillas del grupo plantillas aquí seleccionado se copiarán en este nuevo grupo de plantillas.',
    'promptticketpriority'          => 'El usuario puede seleccionar la prioridad del ticket',
    'desc_promptticketpriority'     => 'Cuando crea un ticket, el usuario puede seleccionar una prioridad del ticket. Si no, se utilizará la prioridad predeterminada.',
    'prompttickettype'              => 'El usuario puede seleccionar el tipo de ticket',
    'desc_prompttickettype'         => 'Cuando crea un ticket, el usuario puede seleccionar el tipo de ticket. Si no, se utilizará el tipo predeterminado.',
    'tickettype'                    => 'Tipo de ticket predeterminado',
    'desc_tickettype'               => 'Los tickets creados a partir de este grupo de plantillas utilizarán este tipo predeterminado.',
    'ticketstatus'                  => 'Estado de ticket por defecto',
    'desc_ticketstatus'             => 'Los tickets creados o respondidos a partir de este grupo de plantillas obtendrán este estado. Si un usuario responde a un ticket asociado a este grupo de plantillas, el ticket cambiará a este estado.',
    'ticketpriority'                => 'Prioridad del ticket por defecto',
    'desc_ticketpriority'           => 'Los tickets creados a partir de este grupo de plantillas reciben esta prioridad por defecto.',
    'ticketdep'                     => 'Departamento por defecto',
    'desc_ticketdep'                => 'Este departamento se seleccionará de forma predeterminada en la página de <em>enviar ticket</em> en el centro de soporte de este grupo de plantillas.',
    'livechatdep'                   => 'Departamento por defecto',
    'desc_livechatdep'              => 'Este departamento se seleccionará de forma predeterminada en el formulario de solicitud de Live Chat de este grupo de plantillas.',
    'ticketsdeptitle'               => '%s (Tickets)',
    'livesupportdeptitle'           => '%s (Soporte en vivo)',
    'isdefault'                     => 'Este grupo de plantillas es el predeterminado del helpdesk',
    'desc_isdefault'                => 'Siempre se utilizará el grupo de plantillas predeterminado de un helpdesk a no ser que se especifique otro.',
    'loginshare'                    => 'LoginShare',

    // Manage template groups
    'grouptitle'                    => 'Título de grupo',
    'glanguage'                     => 'Idioma',
    'managegroups'                  => 'Administrar grupos',
    'templategroups'                => 'Grupos de plantillas',
    'desc_templategroups'           => '',
    'grouplist'                     => 'Lista de grupos',
    'restore'                       => 'Restaurar',
    'export'                        => 'Exportar',
    'restoreconfirmask'             => '¿Está seguro de que desea restaurar las plantillas de este grupo a su estado original? Se perderán todas las modificaciones de las plantillas.',
    'restoreconfirm'                => 'Las plantillas del grupo %s fueron restauradas a su estado original',
    'inserttemplategroup'           => 'Insertar grupo',
    'edittemplategroup'             => 'Editar Grupo',

    // ======= MANAGE TEMPLATES =======
    'desc_templates'                => '',
    'managetemplates'               => 'Administrar plantillas',
    'templatetitle'                 => 'Plantillas: %s',
    'expand'                        => 'Expandir',
    'notmodified'                   => 'Original',
    'modified'                      => 'Modificado',
    'upgrade'                       => 'Desactualizado',
    'expandall'                     => 'Expandir todo',
    'jump'                          => 'Saltar',
    'templategroup'                 => 'Grupo de plantillas',
    'desc_templategroup'            => '',
    'edittemplate'                  => 'Editar plantilla',
    'edittemplatetitle'             => 'Plantilla: %s (Grupo: %s)',
    'templatedata'                  => 'Contenido de plantilla',
    'savetemplate'                  => 'Guardar',
    'saveandreload'                 => 'Guardar &amp; Volver a cargar',
    'restore'                       => 'Restaurar',
    'templatestatus'                => 'Estado de la plantilla',
    'desc_templatestatus'           => '',
    'tstatus'                       => '<img src="%s" align="absmiddle" border="0" /> %s', // Switch position for RTL language
    'dateadded'                     => 'Modificado por última vez',
    'desc_dateadded'                => '',
    'contents'                      => '',
    'desc_contents'                 => '',


    // Diagnostics
    'diagnostics'                   => 'Diagnóstico',
    'moditgroup'                    => 'Grupo de plantillas que buscar',
    'desc_moditgroup'               => 'The templates of this template group will be checked for errors.',
    'list'                          => 'Lista',
    'diagtgroup'                    => 'Grupo de plantillas',
    'desc_diagtgroup'               => '',
    'diagnose'                      => 'Diagnosticar',
    'templatename'                  => 'Nombre de la plantilla',
    'status'                        => 'Estado',
    'compiletime'                   => 'Tiempo de compilación',
    'diagnosetemplategroup'         => 'Diagnosticar plantillas: %s',

    // Search
    'search'                        => 'Buscar',
    'searchtemplates'               => 'Buscar plantillas',
    'query'                         => 'Buscar',
    'desc_query'                    => 'Texto que buscar en las plantillas.',
    'searchtgroup'                  => 'Buscar en grupo de plantillas',
    'desc_searchtgroup'             => 'Se buscarán en las plantillas de este grupo de plantillas.',
    'searchtemplategroup'           => 'Buscar plantillas: %s',

    // Template categories
    'template_general'              => 'General',
    'template_chat'                 => 'Soporte en vivo',
    'template_troubleshooter'       => 'Solucionador de problemas',
    'template_news'                 => 'Noticias',
    'template_knowledgebase'        => 'Base de conocimientos',
    'template_tickets'              => 'Tickets',
    'template_reports'              => 'Informes',

    // Potentialy unused phrases in templates.php
    'desc_importexport'             => '',
    'restoretemplatestatus'         => 'Template Status',
    'restoresubmitquestion'         => 'Are you sure you wish to restore the selected templates?\\nThis action cannot be reversed, you will loose all modifications carried out in the selected templates.',
    'desc_diagnostics'              => '',
    'desc_search'                   => '',
    'tabplugins'                    => 'Plugins',
    'ls_app'                        => 'LoginShare Plugin',
    'wineditls'                     => 'Edit LoginShare Plugin: %s',
    'invalidloginshareplugin'       => 'Invalid LoginShare Plugin, Please make sure the LoginShare plugin exists in the database.',
    'lsnotitle'                     => 'No Settings Available',
    'lsnomsg'                       => 'There are no settings available for the LoginShare plugin <b>"%s"</b>.',
    'loginsharefile'                => 'LoginShare XML File',
    'desc_loginsharefile'           => 'Upload the LoginShare XML File',
    'titlenoelevatedls'             => 'Unable to Import LoginShare XML',
    'msgnoelevatedls'               => 'QuickSupport is unable to import the LoginShare XML file as it is required that you login with a staff user that has elevated rights. You can add your user to elevated right list in config/config.php file of the package.',
    'titlelsversioncheckfail'       => 'Version Check Failed',
    'msglsversioncheckfail'         => 'QuickSupport is unable to import the LoginShare Plugin as the plugin was created for an older version of QuickSupport',
    'titlelsinvaliduniqueid'        => 'Duplicate Unique ID Error',
    'msglsinvaliduniqueid'          => 'QuickSupport is unable to import the LoginShare Plugin due to a conflict in Unique ID. This usually means that the plugin has already been imported into the database.',
    'titlelsinvalidxml'             => 'Invalid XML File',
    'msglsinvalidxml'               => 'QuickSupport is unable to import the LoginShare Plugin as the XML file corrupt or contains invalid data.',
    'titlelsimported'               => 'Imported LoginShare Plugin',
    'msglsimported'                 => 'QuickSupport has successfully imported the %s LoginShare Plugin.',
    'titlelsdeleted'                => 'Deleted LoginShare Plugin',
    'msglsdeleted'                  => 'Successfully deleted the "%s" LoginShare Plugin from the database.',
    'tgroupjump'                    => 'Template Group: %s',
    'desc_templateversion'          => '',
    'desc_changelognotes'           => 'Si está realizando cambios en esta plantilla, añada una breve nota aquí para poder seguir los cambios en la pestaña <strong>Historial</strong>.',
    'desc_inserttgroup'             => 'Por favor, seleccione un grupo de plantillas para esta plantilla.',
    'titlelsupdate'                 => 'LoginShare Update',
    'msglsupdate'                   => 'Successfully updated "%s" LoginShare settings',
    'exporttemplates'               => 'Export Templates',
    'exportxml'                     => 'Export XML',
    'filename'                      => 'Filename',
    'desc_filename'                 => 'Specify the Export Filename.',
    'importtemplates'               => 'Import Templates',
    'importxml'                     => 'Import XML',
    'tgroupmergeconfirm'            => 'Template Group "%s" merged with import file',
    'versioncheckfailed'            => 'Version Check Failed: The uploaded template pack was created using older version of QuickSupport',
    'tgroupnewimportconfirm'        => 'Template Group "%s" imported successfully',
    'templategroupdetails'          => 'Template Group Details',
    'passworddontmatch'             => 'ERROR: Passwords don\'t match',
    'invalidgrouptitle'             => 'ERROR: Only alphanumeric characters can be used in the Template Group Title',
    'grouptitleexists'              => 'ERROR: Invalid Group Title. There is another Template Group with the same title; please choose a different title.',
    'desc_loginshare'               => 'Specify the LoginShare App to use to authenticate the visitors under this Template Group. Make sure you have updated the settings for this app under Templates &gt; LoginShare.',
    'groupinsertconfirm'            => 'Template Group "%s" inserted successfully',
    'groupdelconfirm'               => 'Template Group "%s" deleted successfully',
    'invalidgroup'                  => 'Invalid Template Group',
    'groupupdateconfirm'            => 'Template Group "%s" updated successfully',
    'templatecategories'            => 'Template Categories',
    'groupjump'                     => 'Group Jump',
    'legend'                        => 'Legend: ',
    'invalidtemplate'               => 'Invalid Template',
    'generalinfo'                   => 'General Information',
    'preview'                       => 'Preview',
    'copyclipboard'                 => 'Copy to Clipboard',
    'templateupdateconfirm'         => 'Template "%s" updated successfully',
    'templaterestoreconfirm'        => 'Templates "%s" restored to original contents',
    'templatesrestoreconfirm'       => '%s Templates restored to original contents',
    'clipboardconfirm'              => 'The Template contents have been copied to your clipboard. You can now paste the contents in your favorite HTML editor.',
    'clipboardconfirmmoz'           => 'The text to be copied has been selected. Press Ctrl+C to copy the text to the clipboard.',
    'listmodified'                  => 'List Modified Templates',
    'listtorestore'                 => 'List Templates to Restore',
    'diagnosesmarty'                => 'Diagnose Smarty Template Engine Errors',
    'modifiedtemplates'             => 'Modified Templates (Group: %s)',
    'listtemplates'                 => 'List of Templates (Group: %s)',
    'diagnoseerrors'                => 'Diagnose Errors (Group: %s)',
    'searchqueryd'                  => 'Search Query: %s',
    'pluginlist'                    => 'Plugin List',
    'hostname'                      => 'Hostname',
    'dbname'                        => 'DB Name',
    'dbuser'                        => 'DB User',
    'dbpass'                        => 'DB Password',
    'tableprefix'                   => 'Tabe Prefix',
    'ldaphostname'                  => 'Active Directory Host',
    'ldapport'                      => 'Port (Default: 389)',
    'ldapbasedn'                    => 'Base DN',
    'ldaprdn'                       => 'RDN',
    'ldappassword'                  => 'Password',
    'hsphostserver'                 => 'Server Hostname',
    'hspport'                       => 'Server Port',
    'hspurl'                        => 'XML API URL',
    'hspconnectfail'                => 'Could not connect to server. Try again later.',
    'template_parser'               => 'Email Parser',
    'loginapi_modernbill'           => 'ModernBill',
    'loginapi_ipb'                  => 'Invision Power Board',
    'loginapi_vb'                   => 'vBulletin',
    'loginapi_osc'                  => 'osCommerce',
    'loginapi_iono'                 => 'IONO License Manager',
    'loginapi_plexum'               => 'Plexum',
    'loginapi_awbs'                 => 'AWBS',
    'loginapi_phpaudit'             => 'PHPAudit v2',
    'loginapi_whmautopilot'         => 'WHMAP v3',
    'loginapi_activedirectory'      => 'Active Directory/LDAP',
    'loginapi_activedirectoryssl'   => 'Active Directory/LDAP (SSL)',
    'loginapi_ticketpurchaser'      => 'Ticker Purchaser',
    'loginapi_xcart'                => 'X-Cart',
    'loginapi_phpbb'                => 'PHPBB',
    'loginapi_smf'                  => 'Simple Machines Forum',
    'loginapi_mybb'                 => 'MyBB',
    'loginapi_xmb'                  => 'XMB',
    'loginapi_clientexec'           => 'Clientexec',
    'loginapi_joomla'               => 'Joomla CMS',
    'loginapi_hsphere'              => 'H-Sphere XML-API',
    'loginapi_phpprobid'            => 'PHPProBid',
    'loginapi_cubecart'             => 'CubeCart',
    'loginapi_modernbillv5'         => 'ModernBill v5',
    'loginapi_cscart'               => 'CS-Cart',
    'loginapi_fsr'                  => 'FSRevolution',
    'loginapi_viper'                => 'Viper Cart',
    'loginapi_xoops'                => 'XOOPS',
    'loginapi_whmcsintegration'     => 'WHMCS - Integration Placeholder Only (Not for direct logins)',
);


return $__LANG;
