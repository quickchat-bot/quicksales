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
    'mpsettings'             => 'Definições',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Definições',
    'mailparser'             => 'Analisador de email',


    'titleinsertban'         => 'Endereço de email banido',
    'msginsertban'           => 'O endereço de email %s foi banido e o analisador de email já não processará emails deste endereço.',
    'titleupdateban'         => 'Banimento de endereço de email atualizado',
    'msgupdateban'           => 'O banimento de endereço de email para %s foi atualizado com sucesso.',
    'wineditban'             => 'Editar banimento do analisador',
    'titledelbans'           => 'Banimentos de endereço de email suspensos (%d)',
    'msgdelbans'             => 'Os seguintes banimentos de endereço de email foram eliminados:',

    // Email Bans
    'emailbans'              => 'Banimentos de email',
    'emailbanlist'           => 'Lista de banimentos de email',
    'bannedemail'            => 'Endereço de email',
    'bannedby'               => 'Banido por',
    'insertban'              => 'Inserir banimento',
    'insertnewban'           => 'Inserir novo banimento',
    'desc_bannedemail'       => 'Introduza o endereço de email a banir. Quaisquer mensagens de email recebidas deste endereço serão ignoradas pelo suporte técnico. Para banir um nome de domínio, introduza <em>@thedomainname.com</em>.',
    'parserbaninsertconfirm' => 'Banimento de email "%s" inserido com sucesso',
    'parserbansdelconfirm'   => 'Banimento(s) de email eliminados com sucesso',
    'invalidemailban'        => 'ID de banimento de endereço de email inválida especificada',
    'editban'                => 'Editar banimento',
    'bandelconfirmmsg'       => 'Tem a certeza de que pretende eliminar esta entrada de banimento de email? Esta ação é irreversível!',
    'parserbanupdconfirm'    => 'Banimento de endereço de email para %s atualizado',
    'desc_mailbans'          => 'Pode evitar problemas resultantes da submissão de pedidos de suporte de utilizadores ao adicionar um banimento de email ao seu endereço. Sempre que a QuickSupport receber uma mensagem de email, comparará o endereço do autor com a lista de banimento e, caso o endereço esteja banido, a mensagem será ignorada sem aviso prévio.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Inserir regra catch-all',
    'titlenqcatchall'        => 'Configurar filas de email antes de criar regras catch-all',
    'msgnqcatchall'          => 'As regras catch-all determinam o que acontece ao email recebido pelo suporte técnico. Sem quaisquer filas de email configuradas, o email não pode entrar no suporte técnico.',
    'wineditcatchall'        => 'Editar regra catch-all',
    'titledelcatchall'       => 'Regras catch-all eliminadas (%d)',
    'msgdelcatchall'         => 'As seguintes regras catch-all foram eliminadas:<br>',
    'titleupdatecatchall'    => 'Regra catch-all (%s) atualizada',
    'msgupdatecatchall'      => 'A regra catch-all (%s) foi atualizada com sucesso.',
    'titleinsertcatchall'    => 'Regra catch-all criada',
    'msginsertcatchall'      => 'A regra catch-all (%s) foi criada com sucesso.',
    'catchallrules'          => 'Regras catch-all',
    'desc_catchallrules'     => 'As regras catch-all utilizam expressões regulares para corresponder ao endereços das mensagens de email recebidas. As mensagens podem, então, ser encaminhadas para a fila de email que pretender. Para obter mais informações sobre expressões regulares, consulte <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">preg_match()</a>.',
    'insertrule'             => 'Inserir regra',
    'insertnewrule'          => 'Inserir nova regra',
    'editrule'               => 'Editar regra',
    'rregexp'                => 'Correspondência por padrão',
    'desc_rregexp'           => 'Introduza uma expressão regular para corresponder ao endereço de email do remetente. Se corresponder, esta regra catch-all será acionada. Por exemplo:<br /><br /><strong>/^(.*)(\\@yourdomain\\.com)$/i</strong> Corresponde a qualquer endereço de email que possua um domínio de yourdomain.com (*@yourdomain.com)<br /><strong>/^(support\\@)(.*)$/i</strong> Corresponde a support@*',
    'sortorder'              => 'Ordem de execução',
    'desc_sortorder'         => 'As regras catch-all serão executadas de acordo com o número da ordem de execução, do menor para o maior (1 será executado antes de 5).',
    'emailqueue'             => 'Encaminhar para fila de email',
    'desc_emailqueue'        => 'Os emails recebidos detetados por esta regra catch-all serão encaminhados para esta fila de email.',
    'ruletitle'              => 'Título da regra',
    'desc_ruletitle'         => 'Por exemplo, <em>Regrar para detetar tudo o que contiver @acme.com</em>.',
    'noemailqueueadd'        => '-- Nenhuma fila de email configurada --',
    'editcatchallrule'       => 'Editar regra catch-all',
    'emailqueue'             => 'Encaminhar para fila de email',

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
