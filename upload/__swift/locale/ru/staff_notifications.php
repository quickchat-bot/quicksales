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
    'notifications'                  => 'Уведомления',
    'manage'                         => 'Управление',
    'ruletitle'                      => 'Название правила',
    'desc_ruletitle'                 => 'Укажите название для правила уведомления.',
    'isenabled'                      => 'Включено правило уведомления',
    'desc_isenabled'                 => 'Переключите выполнение этого правила (включить/выключить).',
    'ruletype'                       => 'Тип',
    'desc_ruletype'                  => 'Правило уведомления может быть ассоциировано с одним из следующих уведомлений.',
    'next'                           => 'Далее',
    'creator'                        => 'Создатель',
    'wininsertnotification'          => 'Добавить уведомление',

    'unassigned'                     => '-- Не назначено --',

    'tabgeneral'                     => 'Общие',
    'tabactions'                     => 'Действия',
    'insertcriteria'                 => 'Добавить критерий',

    'na_email'                       => 'Email',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Приложение QuickSupport Desktop',
    'na_staff'                       => 'Личная учетная запись сотрудника',
    'na_staffgroup'                  => 'Команда',
    'na_department'                  => 'Департамент',
    'na_user'                        => 'Пользователь',
    'nactionemaildispatch'           => 'Отправить письмо владельцу заявки...',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Отправить письмо на другие email адреса',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Отправить письмо другому сотруднику',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Отправить уведомление владельцу активной заявки...',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Персонал',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Выберите тип правила уведомления.',
    'titlenocriteria'                => 'Критерии не заданы',
    'msgnocriteria'                  => 'Необходим как минимум один критерий для создания правила уведомления.',
    'titlenotificationinsert'        => 'Создано правило уведомления (%s)',
    'msgnotificationinsert'          => 'Правило уведомления (%s) успешно создано.',
    'titlenotificationupdate'        => 'Обновлено правило уведомления (%s)',
    'msgnotificationupdate'          => 'Правило уведомления (%s) успешно обновлено.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Email',
    'titleenablenotification'        => 'Правила уведомления включены (%d)',
    'msgenablenotification'          => 'Следующие правила уведомления активированы:<br />%s',
    'titledisablenotification'       => 'Правила уведомления отключены (%d)',
    'msgdisablenotification'         => 'Следующие правила уведомления деактивированы:<br />%s',

    'emailprefix'                    => 'Префикс темы Email',
    'desc_emailprefix'               => 'Укажите префикс темы Email для удобства определения писем, оправленных по этому правилу уведомления.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Чат',
    'nrule_message'                  => 'Офлайн сообщение',
    'nrule_survey'                   => 'Опрос в чате',
    'nrule_ticket'                   => 'Заявка',
    'nrule_user'                     => 'Пользователь',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Тип заявки',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Категория по Байесу',
    'desc_nbayesian'                 => 'Заявки, которые совпали с определенной категорией по Байесу.',
    'notapplicable'                  => '-- Не доступно --',
    'nticketstatus'                  => 'Статус заявки',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Приоритет заявки',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Группа пользователей',
    'desc_nusergroup'                => 'Поиск заявок по получателям, которые относятся к определенной группе пользователей.',
    'ndepartment'                    => 'Департамент заявки',
    'desc_ndepartment'               => 'Заявки, которые относятся к одному из департаментов.',
    'nowner'                         => 'Владелец заявки',
    'desc_nowner'                    => 'Заявки, которые относятся к определенному сотруднику.',
    'nunassigned'                    => '-- Не назначено --',
    'nactivestaff'                   => '-- Активные сотрудники --',
    'nemailqueue'                    => 'Очередь ожидания Email',
    'desc_nemailqueue'               => 'Заявки, которые были созданы или на которые ответили письмом с использованием очереди ожидания Email.',
    'nflagtype'                      => 'Флаг заявки',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Заявка создана',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Персонал',
    'creatorclient'                  => 'Пользователь',
    'nslaplan'                       => 'SLA план',
    'desc_nslaplan'                  => 'Заявки, которые на данный момент относятся к определенному SLA плану.',
    'ndaterange'                     => 'Дата создания заявки <range>',
    'desc_ndaterange'                => 'Заявки, которые были созданы в указанный промежуток времени.',
    'nlastactivityrange'             => 'Последнее обновление заявки <range>',
    'desc_nlastactivityrange'        => 'Заявки, которые были обновлены в указанный промежуток времени (то есть, на них ответили или произошло другое событие, вызывающее обновление).',
    'nlaststaffreplyrange'           => 'Последний ответ сотрудника <range>',
    'desc_nlaststaffreplyrange'      => 'Заявки, на которые в указанный промежуток времени ответил один из сотрудников.',
    'nlastuserreplyrange'            => 'Последний ответ пользователя <range>',
    'desc_nlastuserreplyrange'       => 'Заявки, на которые пользователь в указанный промежуток времени ответил.',
    'nduerange'                      => 'Крайний срок отправления ответа <range>',
    'desc_nduerange'                 => 'Заявки, у которых в указанный промежуток времени наступает крайний срок отправления ответа.',
    'nisoverdue'                     => 'Просрочено: ответ просрочен',
    'desc_nisoverdue'                => 'Просроченные заявки (заявки, на которые не ответили до истечения определенного срока).',
    'nresolutionduerange'            => 'Крайний срок решения <range>',
    'desc_nresolutionduerange'       => 'Заявки, у которых в указанный промежуток времени наступает крайний срок решения.',
    'nisresolutionoverdue'           => 'Просрочено: просроченное решение',
    'desc_nisresolutionoverdue'      => 'Просроченные заявки (заявки, которые не были решены до истечения срока решения).',
    'ntemplategroup'                 => 'Группа шаблонов',
    'desc_ntemplategroup'            => 'Заявки, которые относятся к определенной группе шаблонов.',
    'ntimeworked'                    => 'Отработанное  время (в минутах)',
    'desc_ntimeworked'               => 'Заявки, которые подходят по параметру <strong>отработанное  время</strong> (составляющая биллинга заявки и регистрации учета времени).',
    'ntotalreplies'                  => 'Общее количество ответов',
    'desc_ntotalreplies'             => 'Заявки с указанным количеством ответов.',
    'npendingfollowups'              => 'Ожидающие действия',
    'desc_npendingfollowups'         => 'Заявки с запланированными действиями.',
    'nipaddress'                     => 'IP адрес',
    'desc_nipaddress'                => 'Если ответ на заявку отправлен с <strong>центра поддержки</strong>, система службы поддержки могла зарегистрировать IP адрес пользователя.',
    'nisemailed'                     => 'Отправлено с email адреса',
    'desc_nisemailed'                => 'Заявки, которые были отправлены с email адреса.',
    'nisedited'                      => 'Было отредактировано',
    'desc_nisedited'                 => 'Заявки, которые были отредактированы.',
    'nhasnotes'                      => 'С заметками',
    'desc_nhasnotes'                 => 'Заявки, в которых есть заметки.',
    'nhasattachments'                => 'С вложением',
    'desc_nhasattachments'           => 'Заявки, в которых есть прикрепленные файлы.',
    'nisescalated'                   => 'Был передан высшей инстанции',
    'desc_nisescalated'              => 'Заявки, которые были переданы высшей инстанции как минимум один раз.',
    'nhasdraft'                      => 'С черновиком',
    'desc_nhasdraft'                 => 'Заявки, в которых сохранен черновой вариант ответа.',
    'nhasbilling'                    => 'С биллингом и регистрацией учета времени',
    'desc_nhasbilling'               => 'Заявки с записями о биллинге и регистрацией учета времени.',
    'nisphonecall'                   => 'Заявка по телефону',
    'desc_nisphonecall'              => 'Заявки, которые отмечены как <em>заявка по телефону</em>.',
    'nislabeled'                     => 'Отмеченные',
    'desc_nislabeled'                => 'Заявки с метками.',

    'nticketevent'                   => 'События',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Создана новая заявка',
    'tevent_newticketnotes'          => 'Добавлена новая заметка к заявке',
    'tevent_newclientreply'          => 'Новый ответ от пользователя',
    'tevent_newclientsurvey'         => 'Отправка нового опроса',
    'tevent_newstaffreply'           => 'Новый ответ сотрудника',
    'tevent_ticketassigned'          => 'Назначенная заявка',
    'tevent_newuser'                 => 'Регистрация нового пользователя',

    'nuserevent'                     => 'Событие',
    'desc_nuserevent'                => 'Это правило запускает в действие это событие.',
);

return $__LANG;
