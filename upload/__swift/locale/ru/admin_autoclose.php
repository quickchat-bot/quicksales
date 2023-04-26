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
    'autoclose'                    => 'Автоматическое закрытие',
    'manage'                       => 'Управлять',
    'targetstatus'                 => 'Целевой статус',
    'tabgeneral'                   => 'Общие',

    'insertrule'                   => 'Вставить правило',
    'editrule'                     => 'Редактировать правило',

    'ruletitle'                    => 'Название правила',
    'desc_ruletitle'               => 'Введите название вашего правила автоматического закрытия.',
    'targetticketstatus'           => 'Целевой статус',
    'desc_targetticketstatus'      => 'Укажите статус который будет присвоен заявке как только время обозначенное для заявки, истечет.',
    'inactivitythreshold'          => 'Неактивность (количество часов)',
    'desc_inactivitythreshold'     => 'Введите количество часов, по истечению которых заявка перейдет в статус неактивности. Это первый этап атоматического закрытия заявки, который, как правило, используется для предупрежения пользователя о предстоящем закрытии заявки.',
    'closurethreshold'             => 'Закрытие (количество часов)',
    'desc_closurethreshold'        => 'Введите количество часов, по истечению которых <i>неактивная</i> заявка перейдет в статус решенной. Это окончательный этап автоматического закрытия заявки, который инициирует уведомление об окончательном закрытии и изменение статуса заявки.',
    'isenabled'                    => 'Включить?',
    'desc_isenabled'               => 'Переключите действие этого правила автоматического закрытия, включая/выключая эту опцию.',
    'sortorder'                    => 'Порядок сортировки',
    'desc_sortorder'               => 'Укажите порядок сортировки для этого правила. Правила всегда воспроизводятся в порядке возрастания.',
    'sendpendingnotification'      => 'Отправить уведомление о предстоящем закрытии',
    'desc_sendpendingnotification' => 'Если эта опция включена, система предупредит клиента о предстоящем закрытии заявки. Это уведомление инициируется, если заявка не обновлялась количество часов, равных периоду неактивности, указанному выше.',
    'sendfinalnotification'        => 'Отправить уведомление об окончательном закрытии',
    'desc_sendfinalnotification'   => 'Если эта опция включена, система предупредит клиента об окончательном закрытии заявки. Это уведомление инициируется, если заявка неактивна количество часов, указанных выше для закрытия заявки и после того, как статус заявки был изменен.',
    'suppresssurveyemail'          => 'Блокировать рассылку опроса клиентов',
    'desc_suppresssurveyemail'     => 'Если вы активировали рассылку опроса клиентов, когда заявка приобрела статус закрытой, вам может потребоваться отключить эту опцию для автоматически закрытых заявок. Если эта опция активирована, система не будет делать рассылку для автоматически закрытых заявок.',

    'insertcriteria'               => 'Добавить критерии',


    'titleautocloseruledel'        => 'Удалено "%d" правило автоматического закрытия',
    'msgautocloseruledel'          => 'Следующее правило автоматического закрытия было успешно удалено из базы данных:',
    'titleautocloseruleenable'     => 'Активировано "%d" правило автоматического закрытия',
    'msgautocloseruleenable'       => 'Следующее правило автоматического закрытия было успешно активировано:',
    'titleautocloseruledisable'    => 'Деактивировано "%d" правило автоматического закрытия',
    'msgautocloseruledisable'      => 'Следующее правило автоматического закрытия было успешно деактивировано:',
    'titleautocloseruleinsert'     => 'Добавлено правило автоматического закрытия',
    'msgautocloseruleinsert'       => 'Правило автоматическо закрытия "%s" было успешно добавлено в базу данных.',
    'titleautocloseruleupdate'     => 'Применено правило автоматического закрытия',
    'msgautocloseruleupdate'       => 'Правило автоматического закрытия "%s" было успешно применено.',

    'titlenocriteriaadded'         => 'Критерии для закрытия заявки не были созданы',
    'msgnocriteriaadded'           => 'Необходимо добавить хотя бы один критерий для создания/редактирования правила автоматического закрытия.',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- Нет данных --',
    'articketstatusid'             => 'Статус заявки',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Приоритет заявки',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Департамент',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Владелец заявки',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'Почтовая очередь',
    'arusergroupid'                => 'Группа пользователей',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Вид флага',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Категория по Байесу',
    'desc_arbayescategoryid'       => 'Заявки, которые совпали с определенной категорией по Байесу.',
    'arcreator'                    => 'Создатель',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Персонал',
    'creatorclient'                => 'Пользователь',
    'arunassigned'                 => '-- Неназначенный --',
    'artemplategroupid'            => 'Группа шаблонов',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Вид заявки',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
