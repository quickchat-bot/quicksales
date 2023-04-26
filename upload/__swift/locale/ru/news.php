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
    'news'                 => 'Новости',
    'managenews'           => 'Управление новостями',
    'managesubscribers'    => 'Управление подписчиками',
    'insertnews'           => 'Добавить новость',
    'importexport'         => 'Импорт/экспорт',

    // Manage Subscribers
    'subscribers'          => 'Подписчики',
    'dlsubscriberconfirm'  => 'Подписчик успешно удален',
    'subemail'             => 'Email адрес',
    'desc_subemail'        => 'Укажите email адрес подписчика.',
    'substatus'            => 'Статус валидации',
    'subdate'              => 'Дата',
    'sublist'              => 'Список подписчиков',
    'legend'               => 'Легенда: ',
    'subinsertconfirm'     => 'Подписчик на новости "%s" успешно добавлен',

    // Insert Subscriber
    'insertsubscriber'     => 'Добавить подписчика',

    // Edit Subscriber
    'invalidsubscriber'    => 'Неверный подписчик',
    'editsubscriber'       => 'Редактировать подписчика',
    'subscriberdetails'    => 'Информация о подписчике',
    'subupdateconfirm'     => 'Подписчик на новости "%s" успешно обновлен',

    // Manage News
    'newssubject'          => 'Тема новости',
    'newsdescription'      => 'Описание',
    'desc_newsdescription' => 'Укажите краткое описание новости. Максимальное количество допустимых символов: <b>255</b>.',
    'newscontents'         => 'Содержание',
    'newstype'             => 'Тип новости',
    'desc_newstype'        => '<i>Публичный:</i> Новости этого типа доступны персоналу и пользователям в центре поддержки.<br><br><i>Приватный:</i> Новости этого типа доступны только сотрудникам на панели управления персонала.',
    'newsexpiry'           => 'Дата истечения срока',
    'newsformexpiry'       => 'Дата истечения срока (не обязательно)',
    'desc_newsformexpiry'  => 'По истечению срока новость перестает отображаться. Тем не менее, новость не удаляется и может быть в любой момент восстановлена.',
    'manage'               => 'Управление',
    'publicnews'           => 'Публичные новости',
    'privatenews'          => 'Приватные новости',
    'newsdelconfirm'       => 'Новость успешно удалена',

    // Insert News
    'contents'             => 'Содержание',
    'newsdetails'          => 'Информация о новости',
    'newsoptions'          => 'Параметры новости',
    'sendemail'            => 'Отправить Email',
    'desc_sendemail'       => 'Отправить новость всем подписчикам?',
    'emailsubject'         => 'Тема Email',
    'desc_emailsubject'    => 'Если тема не указана, будет использована тема новости.',
    'newstgroup'           => 'Группа шаблонов',
    'desc_newstgroup'      => 'Группа шаблонов, в которой будет отображаться новость.',
    'newsinsertconfirm'    => 'Новость "%s" успешно добавлена',
    'unsubscribe'          => 'Отказаться от подписки: ',
    'clickhere'            => 'Кликните здесь',
    'fromname'             => 'Имя отправителя',
    'desc_fromname'        => 'Это заголовок email сообщения <i>имя отправителя<i>, который будет использоваться для отправленных подписчикам сообщений email.',
    'fromemail'            => 'Email адрес отправителя',
    'desc_fromemail'       => 'Это <i>Email адрес отправителя<i>, который будет использоваться для отправленных подписчикам сообщений email. Обратите внимание, что это может быть адрес, на который пользователи будут отвечать (если они отвечают на сообщения email с новостями).',
    'updateprefix'         => 'ОБНОВИТЬ: ',

    // Edit News
    'updatenews'           => 'Обновить новость',
    'invalidnewsitem'      => 'Неверная новость',
    'newsupdateconfirm'    => 'Новость "%s" успешно обновлена',
    'exportpdf'            => 'Экспортировать PDF',
    'deletenews'           => 'Удалить новость',
    'newsoptions'          => 'Параметры новости',
    'nwpostedby'           => 'Отправлено ',
    'nwposted'             => 'Сообщение ',
    'nwpostedon'           => '  ',
    'nwread'               => 'Прочитано',

    // Comments
    'comments'             => 'Комментарии',

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
