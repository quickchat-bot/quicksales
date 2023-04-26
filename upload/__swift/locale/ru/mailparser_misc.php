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
    'mpsettings'             => 'Настройки',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Настройки',
    'mailparser'             => 'Парсер почты',


    'titleinsertban'         => 'Запрет парсера добавлен',
    'msginsertban'           => 'Запрет парсера для адреса email "%s" успешно добавлен в базу данных.',
    'titleupdateban'         => 'Запрет парсера обновлен',
    'msgupdateban'           => 'Запрет парсера для адреса email "%s" успешно обновлен.',
    'wineditban'             => 'Редактировать запрет парсера',
    'titledelbans'           => 'Удалено "%d" запретов парсера',
    'msgdelbans'             => 'Следующие заблокированные адреса emails уcпешно удалены из базы данных:',

    // Email Bans
    'emailbans'              => 'Запреты адресов Email',
    'emailbanlist'           => 'Список запрещенных Запреты адресов Email',
    'bannedemail'            => 'Адрес Email',
    'bannedby'               => 'Запрещено',
    'insertban'              => 'Добавить запрет',
    'insertnewban'           => 'Добавить новый запрет',
    'desc_bannedemail'       => 'Укажить адрес email, который нужно запретить. Все сообщения с этого адреса будут без уведомления отклонены. Для того, чтобы полностью заблокировать имя домена, укажите адрес email как "@domain.com".',
    'parserbaninsertconfirm' => 'Запрет адреса Email "%s" успешно добавлен',
    'parserbansdelconfirm'   => 'Запреты адресов Email успешно удалены',
    'invalidemailban'        => 'Указан неверный ID запрета парсера',
    'editban'                => 'Редактировать запрет',
    'bandelconfirmmsg'       => 'Вы уверены, что хотите удалить эту запись о запрете адреса Email?  Последствия этого действия  необратимы!',
    'parserbanupdconfirm'    => 'Запрет адреса Email "%s" успешно обновлен',
    'desc_mailbans'          => 'Вы можете лишить проблемных пользователей возможности создавать заявки, добавив запрет на их адреса email.  Всегда, когда QuickSupport получает email сообщение, адрес его отправителя проверяется на наналичие в списке запрещенных адресов. Если этот адрес запрещен, сообщение будет проигнорировано.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Добавьте общее правило',
    'titlenqcatchall'        => 'Нет очередей ожидания Email',
    'msgnqcatchall'          => 'Вы не можете добавить общее правило, пока есть как минимум одна очередь ожидания Email.',
    'wineditcatchall'        => 'Редактировать общее правило',
    'titledelcatchall'       => 'Удалено "%d" общих правил',
    'msgdelcatchall'         => 'Следующие общие правила успешно удалены из базы данных:<br>',
    'titleupdatecatchall'    => 'Общие правила "%s" обновлены',
    'msgupdatecatchall'      => 'Общее правило "%s" успешно обновлено.',
    'titleinsertcatchall'    => 'Добавлено общее правило',
    'msginsertcatchall'      => 'Общее правило "%s" успешно добавлено в базу данных.',
    'catchallrules'          => 'Общие правила',
    'desc_catchallrules'     => 'Общие правила используют регулярные выражения для того, чтобы совпадать с шаблонами в адресах входящих сообщений.  Потом эти сообщения можно будет направить в любую необходимую Вам очередь ожидания. Для дополнительной информации о регулярных выражениях, <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red">нажмите здесь.</a>',
    'insertrule'             => 'Добавить правило',
    'insertnewrule'          => 'Добавить новое правило',
    'editrule'               => 'Редактировать правило',
    'rregexp'                => 'Регулярное выражение',
    'desc_rregexp'           => 'Укажите регулярное выражение для сопоставления с адресом email отправителя. Примеры:<HR /><b>/^.*\\@yourdomain\\.com$/i</b> - сопоставляется с любым адресом email, у которого домен "yourdomain.com"<BR /><BR /><b>/^support\\@.*$/i</b> - сопоставляется с  support@anydomain.com; где anydomain.com может быть конкретным значением',
    'sortorder'              => 'Порядок выполнения',
    'desc_sortorder'         => 'Парсер почты будет выполнять общие правила в соответствии с этим значением. (Например, <i>правило "1" выполняется перед правилом "2" и т.п.</i>)',
    'emailqueue'             => 'Очередь ожидания Email получателя',
    'desc_emailqueue'        => 'Входящие письма, попадающие под воздействие этого общего правила, будут перенаправлены в указанную здесь очередь ожидания.',
    'ruletitle'              => 'Название правила',
    'desc_ruletitle'         => 'Введите название для этого общего правила.',
    'noemailqueueadd'        => '-- Очередь ожидания Email не добавлена --',
    'editcatchallrule'       => 'Редактировать общее правило',
    'emailqueue'             => 'Очередь ожидания Email получателя',

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
