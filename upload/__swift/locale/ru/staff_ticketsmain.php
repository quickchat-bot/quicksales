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
    'tickets'                   => 'Заявки',
    'overdue'                   => 'Просроченные',
    'unassigned'                => '-- Не назначенные --',
    'unassigned2'               => 'Не назначенные',
    'menuviews'                 => 'Вид: %s',
    'merge'                     => 'Объединить',
    'trash'                     => 'В корзину',
    'spam'                      => 'Спам',
    'watch'                     => 'Посмотреть',
    /*
    * BUG FIX - Saloni Dhall
    *
    * SWIFT-3091 Staff CP: laguage display issue (only button reply)
    *
    * Comments: None
    */
    'reply'                     => 'Ответить',
    'qticketpost'               => 'Цитировать это сообщение',

    // Tree
    'treeinbox'                 => 'Входящая почта',
    'treetrash'                 => 'Корзина',
    'treewatched'               => 'Просмотренные',
    'treeflagged'               => 'Отмеченные',
    'treemytickets'             => 'Мои заявки',
    'treeunassigned'            => 'Не назначенные',

    // Grid Titles
    'f_ticketid'                => 'ID заявки',
    'f_subject'                 => 'Тема',
    'f_queue'                   => 'Очередь ожидания Email',
    'f_department'              => 'Департамент',
    'f_ticketstatus'            => 'Статус',
    'f_duedate'                 => 'Ответ',
    'f_lastactivity'            => 'Последняя активность',
    'f_date'                    => 'Дата',
    'f_owner'                   => 'Владелец',
    'f_priority'                => 'Приоритет',
    'f_lastreplier'             => 'Последний ответивший',
    'f_fullname'                => 'Имя',
    'f_timeworked'              => 'Отработанное время',
    'f_email'                   => 'Email',
    'f_totalreplies'            => 'Ответы',
    'f_assignstatus'            => 'Назначено',
    'f_flagtype'                => 'Флаг',
    'f_laststaffreply'          => 'Обновление персонала',
    'f_lastuserreply'           => 'Обновление пользователя',
    'f_tgroup'                  => 'Группа шаблонов',
    'f_slaplan'                 => 'SLA план',
    'f_usergroup'               => 'Группа пользователей',
    'f_userorganization'        => 'Организация',
    'f_escalationrule'          => 'Правило эскалации',
    'f_escalatedtime'           => 'Время эскалации',
    'f_resolutiondue'           => 'Время решения',
    'f_type'                    => 'Тип',
    'f_typeicon'                => 'Тип (Значок)',

    // Creation Modes
    'cm_api'                    => 'API',
    'cm_email'                  => 'Email',
    'cm_sitebadge'              => 'Бадж-значок сайта',
    'cm_staffcp'                => 'ПУ Персонала',
    'cm_supportcenter'          => 'Центр поддержки',

    // Ticket listing icon description tags
    'alt_hasattachments'        => 'С вложениями',
    'alt_isescalated'           => 'Эта заявка просрочена и была передана высшей инстанции',
    'alt_raisedbyemail'         => 'Создана через email',
    'alt_linkedticket'          => 'Эта заявка связана с другой',
    'alt_followupset'           => 'Для этой заявки было запланировано ожидающее действе ',
    'alt_assignedotyou'         => 'Назначено Вам',
    'alt_watchingticket'        => 'Вы просматриваете эту заявку',
    'alt_ticketlocked'          => 'Эту заявку просматривал другой сотрудник',
    'alt_ticketphonetype'       => 'Эта заявка отмечена как заявка телефонного типа',
    'alt_ticketunread'          => 'С момента Вашего последнего входа заявка была обновлена',
    'alt_tickethastimetracking' => 'В этой заявке есть запись об отслеживании времени',
    'alt_tickethasnote'         => 'В этой заявке есть одна или несколько заметок',
    'alt_tickethasbilling'      => 'В этой заявке есть запись об отслеживании времени и биллинге',

    // Potentialy unused phrases in staff_ticketsmain.php
    'filtertickets'             => 'Filter Tickets',

    // Ticket Copy/Split
    'closeold'                  => 'Закрыть старую заявку',
    'closeold_d'                => 'Закройте старую заявку (Сплит от) после разделения.',
    'splitat'                   => 'Разделение в ответе',
    'splitat_d'                 => 'Этот ответ и все ответы, сделанные после него, будут относиться к новой заявке.',
    'split_into'                => 'Разделить в заявке',
    'split_from'                => 'Отделить от заявки',
    'duplicateat'               => 'Дублировать в ответ',
    'duplicateat_d'             => 'Этот ответ и все ответы, сделанные после него, будет дублироваться в новую заявку.',
    'duplicate_into'            => 'Дублировать в заявке',
    'duplicate_from'            => 'Дублировать из заявки',
    'ticketsplitter'            => 'Разделитель заявок',
    'no_such_post'              => 'Не удается идентифицировать сообщение для разделения.',
    'oldticket'                 => 'Старая заявка',
    'newticket'                 => 'Новая заявка',
    'split'                     => 'Разделить',
    'duplicate'                 => 'Дублировать',
    'splitticket'               => 'Разделить заявку',
    'duplicateticket'           => 'Копировать завку'
);


return $__LANG;
