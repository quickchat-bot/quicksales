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
    'ratings'                     => 'Рейтинги',
    'tabgeneral'                  => 'Общие',
    'tabpermissionsstaff'         => 'Права доступа (персонал)',
    'tabpermissionsuser'          => 'Права доступа (пользователь)',
    'staffgroups'                 => 'Группы сотрудников',
    'usergroups'                  => 'Группы пользователей',
    'ratingticket'                => 'Заявка',
    'ratingticketpost'            => 'Сообщение заявки',
    'ratingchatsurvey'            => 'Опрос чата',
    'ratingchathistory'           => 'История чата',

    'staffvisibilitycustom'       => 'Ограничить возможность изменения рейтингов для определенных групп сотрудников?',
    'desc_staffvisibilitycustom'  => 'Активируйте эту настройку если вы хотите управлять возможностью групп сотрудников (выбранных ниже) <strong>изменять</strong> этот рейтинг.',

    'buservisibilitycustom'       => 'Ограничить рейтинги определенных групп пользователей?',
    'desc_buservisibilitycustom'  => 'Активируйте эту настройку если вы хотите управлять возможностью групп пользователей (выбранных ниже) <strong>видеть и изменять</strong> этот рейтинг.',

    'bstaffvisibilitycustom'      => 'Ограничить рейтинги определенных групп сотрудников?',
    'desc_bstaffvisibilitycustom' => 'Активируйте эту настройку если вы хотите управлять возможностью групп сотрудников (выбранных ниже) <strong>видеть</strong> этот рейтинг.',

    'isclientonly'                => 'Запретить сотрудникам устанавливать и редактировать рейтинги?',
    'desc_isclientonly'           => 'Если эта настройка включена, только пользователи смогут устанавливать и редактировать этот рейтинг. Ваши сотрудники не смогут его менять.',

    'ratingscale'                 => 'Шкала рейтингов',
    'desc_ratingscale'            => 'Выберите максимальный рейтинг, который может быть присвоен (это количество переводится в <em>звёздочки</em>).',

    'ratingtype'                  => 'Вид рейтинга',
    'desc_ratingtype'             => 'Выберите зону в вашей службе поддержки клиентов, для которой необходимо применить этот рейтинг. <br /><br /><strong>Заявка:</strong> Предложить рейтинг для заявки в целом<br /><strong>Сообщение заявки:</strong> Предложить рейтинг для отдельных сообщений заяви<br /><strong>Опрос после чата:</strong> Предложить рейтинг для чата как часть опроса после чата<br /><strong>Запись истории чата:</strong> Предложить рейтинг истории чата на панели управления персонала (предназначено для внутреннего рейтинга и контроля)',

    'ratingtitle'                 => 'Название рейтинга',
    'desc_ratingtitle'            => 'Например, <em>"Общая удовлетворенность"</em> или <em>"Скорость ответа"</em>.',

    'displayorder'                => 'Порядок отображения',
    'desc_displayorder'           => 'Это порядок отображения элементов по умолчанию. Список сортируется в порядке возрастания.',

    'ratingdep'                   => 'Ограничить рейтинг департамента',
    'desc_ratingdep'              => 'Если Вы хотите ограничить этот рейтинг для определенного департамента, выберите департамент здесь.',
    'ratingalldep'                => '-- Все департаменты --',

    'iseditable'                  => 'Можно ли изменить после отправки?',
    'desc_iseditable'             => 'Можно ли изменить этот рейтинг после того, как он был отправлен?',

    'ratingvisibility'            => 'Кто может давать рейтинг?',
    'desc_ratingvisibility'       => '<strong>Общие</strong> рейтинги доступны и вашим пользователям, и сотрудникам. Выберите эту опцию для востребования отзывов от ваших пользователей. <strong>Приватные</strong> рейтинги доступны только вашим сотрудникам для осуществления внутреннего рейтинга и контроля.',

    'ratingvis'                   => 'Доступ',
    'insertrating'                => 'Добавить рейтинг',
    'desc_insertrating'           => '',
    'manageratings'               => 'Управление',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Редактировать рейтинг: %s',
    'editrating'                  => 'Редактировать рейтинг',
    'desc_editrating'             => '',
    'titledelrating'              => 'Удалено "%d" рейтингов',
    'msgdelrating'                => 'Следующие рейтинги были успешно удалены из базы данных:',
    'titleratinginsert'           => 'Рейтинг добавлен',
    'msgratinginsert'             => 'Рейтинг "%s" был успешно добавлен в базу данных.',
    'titleratingupdate'           => 'Рейтинг обновлен',
    'msgratingupdate'             => 'Рейтинг "%s" был успешно обновлен.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'База знаний',
    'ratingtroubleshooter'        => 'Устранение неполадок',
    'ratingnews'                  => 'Новости',
);

return $__LANG;
