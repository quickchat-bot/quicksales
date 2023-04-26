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
    'titlemasterviewdel'            => 'Невозможно удалить "%d" режимов отображения',
    'msgmasterviewdel'              => 'QuickSupport не может удалить следующие основные с заявок:',
    'manageviews'                   => 'Режимы отображения',
    'insertview'                    => 'Добавить режим отображения',
    'viewtitle'                     => 'Название режима отображения заявок',
    'desc_viewtitle'                => 'Укажите название для режима отображения (используйте только цифры, буквы и пробелы).',
    'viewscope'                     => 'Видимость',
    'createdby'                     => 'Создатель',
    'editview'                      => 'Редактировать режим отображения',
    'titleticketviewinsert'         => 'Создан режим отображения заявки (%s)',
    'msgticketviewinsert'           => 'Режим отображения заявки( %s) успешно создан.',
    'titleticketviewupdate'         => 'Обновлен режим отображения заявки(%s)',
    'msgticketviewupdate'           => 'Режим отображения заявки( %s) успешно обновлен.',
    'titleviewfieldempty'           => 'Колонки режима отображения заявок не указаны',
    'msgviewfieldempty'             => 'Необходимо указать как минимум одну колонку для режима отображения (иначе не будет данных для отображения!).',

    'viewlinkdepartment'            => 'Связать этот режим отображения с департаментами',
    'desc_viewlinkdepartment'       => 'Этот режим отображения может автоматически загружаться при просмотре заявок в одном из здесь указанных департаментов.',

    'taboptions'                    => 'Опции',
    'tabcolumns'                    => 'Колонки',

    'viewscope_global'              => 'Все сотрудники',
    'viewscope_private'             => 'Только я',
    'viewscope_team'                => 'Моя команда',

    'viewunassigned'                => 'Не назначенные',
    'viewassigned'                  => 'Назначенные мне',
    'viewalltickets'                => 'Все заявки',

    'viewassignedfield'             => 'Отобрать заявки, которые назначенные',
    'desc_viewassignedfield'        => '<strong>Не назначенные</strong> Заявки, которые никому не назначены.<br /><strong>Назначенные мне</strong> Заявки, которые назначены Вам.<br /><strong>Все заявки</strong> Все заявки, назначенные Вам, другим сотрудникам и никому не назначенные.',
    'viewscope'                     => 'Видимость',
    'desc_viewscope'                => '<strong>Всем сотрудникам</strong> Все сотрудники могут использовать этот режим отображения.<br /><strong>Только мне</strong> Только у Вас есть доступ к этому режиму отображения.<br /><strong>Моей команде</strong> Доступ к режиму отображения есть у Вас и у членов Вашей команды.',

    'viewselectfields'              => 'Укажите колонки (перетащите и опустите в зону, выделенную желтой рамкой)',
    'viewsortoptions'               => 'Параметры сортировки',
    'viewsortby'                    => 'Сортировать по',
    'desc_viewsortby'               => 'Вид сортировки заявок при этом активном режиме отображения',
    'viewsortorder'                 => 'Порядок сортировки',
    'desc_viewsortorder'            => 'заявки могут быть отсортированы по возрастанию (от маленьких к большим, от новых к более ранним, и т. д.) или по убыванию.',
    'viewticketsperpage'            => 'Количество заявок для отображения на странице',
    'adminviewticket'         => 'The number of tickets per page is too high.',
    'desc_adminviewticket'    => 'Staff ticket views are currently limited to showing %s tickets per page. Contact your administrator to increase this limit.',
    'desc_viewticketsperpage' => 'Specify the number of tickets to display before breaking the view into multiple pages. Note: This value cannot exceed the default maximum your administrator has set for staff views.',    'generaloptions'                => 'Общие опции',
    'viewautorefresh'               => 'Автоматически обновлять список заявок',
    'desc_viewautorefresh'          => 'При загрузке этого режима отображения список заявок может автоматически обновляться. Полезно для использования настенного типа.',
    'autorefresh_disable'           => '-- Отключить автоматическое обновление --',
    'autorefresh_30s'               => 'Обновлять каждые 30 секунд',
    'autorefresh_1m'                => 'Обновлять каждую минуту',
    'autorefresh_5m'                => 'Обновлять каждые 5 минут',
    'autorefresh_15m'               => 'Обновлять каждые 15 минут',
    'autorefresh_30m'               => 'Обновлять каждые 30 минут',
    'autorefresh_1h'                => 'Обновлять каждый час',
    'viewsetasowner'                => 'Автоматически определять владельца заявки как активного сотрудника',
    'desc_viewsetasowner'           => 'При отправке или пересылке заявки система будет автоматически определять владельца заявки как активного сотрудника, если используется этот режим отображения.',
    'viewdefaultstatusonreply'      => 'Статус заявки по умолчанию во время ответа на заявку',
    'desc_viewdefaultstatusonreply' => 'Укажите статус, который будет автоматически присвоен заявке в этом режиме отображения, когда  сотрудник отвечает на заявку или пересылает ее.',
    'defaultstatus_unspecified'     => '-- Не указано --',

    'afterreply_topticketlist'      => 'Список заявок высшего уровня (режим отображения всех заявок)',
    'afterreply_activeticketlist'   => 'Активный список заявок (в рамках текущего департамента или результатов поиска)',
    'afterreply_ticket'             => 'Активная заявка',
    'afterreply_nextticket'         => 'Следующая существующая заявка',
    'viewafterreplyaction'          => 'После того, как на заявку ответили, перенаправить меня в',
    'desc_viewafterreplyaction'     => 'Выберите, куда QuickSupport следует Вас перенаправить (и всех пользователей этого режима отображения) после ответа или пересылки заявки.',

    'sort_asc'                      => 'По возрастанию',
    'sort_desc'                     => 'По убыванию',

    // Potentialy unused phrases in staff_ticketview.php
    'viewfilteroptions'             => 'Filter Options',
    'viewfilterdepartments'         => 'Filter Department(s)',
    'desc_viewfilterdepartments'    => 'QuickSupport by default will display tickets from all/selected department. However, you can use this option to filter the tickets based on the selected departments.',
    'viewfiltertype'                => 'Filter Ticket Type(s)',
    'desc_viewfiltertype'           => 'You can use this option to filter the tickets based on the selected ticket type(s).',
    'viewfilterstatus'              => 'Filter Ticket Status(es)',
    'desc_viewfilterstatus'         => 'You can use this option to filter the tickets based on the selected ticket status(es).',
);


return $__LANG;
