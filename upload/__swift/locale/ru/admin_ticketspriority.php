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
    'insertpriority'            => 'Добавить приоритет',
    'tabgeneral'                => 'Общие',
    'tabpermissions'            => 'Доступ',
    'tablanguages'              => 'Языки',
    'prioritytitle'             => 'Название',
    'desc_prioritytitle'        => 'Введите название приоритета для заявки',
    'prioritytype'              => 'Тип',
    'desc_prioritytype'         => 'Выберите тип приоритета. Пользователи не могут видеть приватные приоритеты.',
    'forecolor'                 => 'Цвет текста',
    'desc_forecolor'            => 'Если Вы хотите задать цвет для текста заявки с данным приоритетом, выберите необходимый цвет.',
    'bgcolor'                   => 'Цвет фона',
    'desc_bgcolor'              => 'Если Вы хотите задать цвет для фона заявки с данным приоритетом, выберите необходимый цвет.',
    'displayorder'              => 'Порядок отображения',
    'desc_displayorder'         => 'Это порядок отображения элементов по умолчанию. Список элментов отображается в порядке возрастания.',
    'displayiconpriority'       => 'Значок',
    'desc_displayiconpriority'  => 'Загрузите изображение или введите полный URL изображения, которое будет отображаться как индикатор этого приоритета. Вы можете использовать {$themepath} как переменную замены для полного URL в каталоге. Рекомендуемый размер изображения - 16x16 пикселей.',
    'uservisibilitycustom'      => 'Ограничить доступ?',
    'desc_uservisibilitycustom' => 'Если эта опция включена, только указанные группы пользователей смогут видеть этот приоритет.',
    'usergroups'                => 'Группы пользователей',
    'invalidticketpriority'     => 'Недопустимый приоритет для заявки. Пожалуйста, убедитесь, что данный приоритет существует в базе данных.',
    'editpriority'              => 'Редактировать приоритете',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Приоритеты',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Редактировать приоритет для заявки: %s',
    'titledelticketpriority'    => 'Удалено "%d" приоритетов для заявок',
    'msgdelticketpriority'      => 'Следующие приоритеты для заявок были успешно удалены с базы данных:',
    'titleticketpriorityinsert' => 'Добавлен приоритет для заявки "%s"',
    'msgticketpriorityinsert'   => 'Приоритет для заявки "%s" был успешно добавлен в базу данных.',
    'titleticketpriorityupdate' => 'Обновлен приоритет для заявки "%s"',
    'msgticketpriorityupdate'   => 'Приоритет для заявки "%s" был успешно обновлен.',
    'titlemasterprioritydelete' => 'Невозможно удалить "%d" приоритетов для заявок',
    'msgmasterprioritydelete'   => 'Kayako не может удалить следующие основные приоритеты для заявок:',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
