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
    'mpsettings'             => 'Configuración',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Configuración',
    'mailparser'             => 'Analizador de correos electrónicos',


    'titleinsertban'         => 'Dirección de correo electrónico bloqueada',
    'msginsertban'           => 'La dirección de correo %s fue bloqueada y el analizador de correos electrónicos no procesará más correos electrónicos de esta dirección.',
    'titleupdateban'         => 'Bloequeo de la dirección de correo electrónico actualizado',
    'msgupdateban'           => 'El bloqueo de la dirección de correo electrónico para %s se ha actualizado correctamente.',
    'wineditban'             => 'Editar bloqueo del analizador',
    'titledelbans'           => 'Bloqueos de dirección de correo electrónico eliminados (%d)',
    'msgdelbans'             => 'Se eliminaron los siguientes bloqueos de direcciones de correo electrónico:',

    // Email Bans
    'emailbans'              => 'Bloqueo de correo electrónico',
    'emailbanlist'           => 'Lista de bloqueos de correo electrónico',
    'bannedemail'            => 'Dirección de correo electrónico',
    'bannedby'               => 'Bloqueado por',
    'insertban'              => 'Insertar bloqueo',
    'insertnewban'           => 'Insertar nuevo bloqueo',
    'desc_bannedemail'       => 'Introduzca la dirección de correo electrónico a bloquear. Cualquier correo electrónico entrante desde esta dirección será ignorado por el helpdesk. Para bloquear el nombre de un dominio entero escriba <em>@elnombredeldominio.com</em>.',
    'parserbaninsertconfirm' => 'Bloqueo del correo electrónico "%s" insertado correctamente',
    'parserbansdelconfirm'   => 'Bloqueo(s) de correo electrónico eliminado(s) correctamente',
    'invalidemailban'        => 'ID de bloqueo de dirección de correo electrónico especificada no válida',
    'editban'                => 'Editar bloqueo',
    'bandelconfirmmsg'       => '¿Está seguro que desea eliminar esta entrada de bloqueo?  ¡Esta acción es irreversible!',
    'parserbanupdconfirm'    => 'Bloqueo de dirección de correo electrónico para %s actualizada',
    'desc_mailbans'          => 'Puede impedir que usuarios problemáticos envíen tickets bloqueando sus direcciones de correo electrónico. Cuando QuickSupport reciba un correo electrónico comparará la dirección del autor con la lista de bloqueos y si la dirección está bloqueada el mensaje será ignorado sin previo aviso.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Insertar regla catch-all',
    'titlenqcatchall'        => 'Configurar las colas de correo electrónico antes de crear las reglas catch-all',
    'msgnqcatchall'          => 'Las reglas catch-all (atrapar todo) determinan qué sucede con correos electrónicos entrantes en el helpdesk. Sin colas de correo electrónico configuradas el correo electrónico no puede entrar en el helpdesk.',
    'wineditcatchall'        => 'Editar regla catch-all',
    'titledelcatchall'       => 'Reglas catch-all eliminadas (%d)',
    'msgdelcatchall'         => 'Se eliminaron las siguientes reglas catch-all:<br>',
    'titleupdatecatchall'    => 'Regla catch-all (%s) actualizada',
    'msgupdatecatchall'      => 'La regla catch-all (%s) se actualizó correctamente.',
    'titleinsertcatchall'    => 'Regla catch-all creada',
    'msginsertcatchall'      => 'La regla catch-all (%s) se creó correctamente.',
    'catchallrules'          => 'Reglas catch-all',
    'desc_catchallrules'     => 'Las reglas catch-all utilizan expresiones regulares para comprobar la coincidencia con las direcciones de correos electrónicos entrantes. Se pueden enrutar los mensajes a la cola de correo electrónico que desee. Para obtener más información sobre las expresiones regulares, vea <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">preg_match()</a>.',
    'insertrule'             => 'Insertar regla',
    'insertnewrule'          => 'Insertar regla nueva',
    'editrule'               => 'Editar regla',
    'rregexp'                => 'Coincidencia de patrones',
    'desc_rregexp'           => 'Introduzca una expresión regular con la que comparar la dirección de correo electrónico del remitente. Si coinciden se aplicará esta regla catch-all. Por ejemplo:<br /><br /><strong>/^(.*)(\\@sudominio\\.com)$/i</strong> coincidirá con cualquier dirección de correo electrónico del dominio sudomino.com (*@sudominio.com)<br /><strong>/^(soporte\\@)(.*)$/i</strong> Coincide con soporte@*',
    'sortorder'              => 'Orden de ejecución',
    'desc_sortorder'         => 'Las reglas catch-all serán ejecutadas según su número de orden de ejecución, del más bajo al más alto (1 se ejecutará antes que 5).',
    'emailqueue'             => 'Enrutar a la cola de correo electrónico',
    'desc_emailqueue'        => 'Los correos electrónicos entrantes atrapados por esta regla catch-all se enrutarán a esta cola de correo electrónico.',
    'ruletitle'              => 'Título de la regla',
    'desc_ruletitle'         => 'Por ejemplo, <em>regla para atrapar cualquier mensaje de @acme.com</em>.',
    'noemailqueueadd'        => '-- Ninguna cola de correo electrónico configurada --',
    'editcatchallrule'       => 'Editar regla catch-all',
    'emailqueue'             => 'Enrutar a la cola de correo electrónico',

    // Potentialy unused phrases in mailparser_misc.php
    'invalidemailaddress'    => 'Invalid email address specified',

    'errnoqueue'             => 'ERROR: You do not have any email queues in the database. You need to have at least one email queue to utilize catch-all rules.',
    'catchruleinsertconfirm' => 'Catch-all rule "%s" inserted successfully',
    'catchruleupdconfirm'    => 'Catch-all rule "%s" updated successfully',
    'catchallruledelconfirm' => 'Catch-all rule(s) deleted successfully',
    'invalidcatchalrule'     => 'Invalid catch all rule specified',
    'cruledelconfirmmsg'     => 'Are you sure you wish to delete this catch-all rule?  This action is irreversible!',
);

return $__LANG;
