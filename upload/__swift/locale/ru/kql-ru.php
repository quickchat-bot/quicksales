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
    'kqlparseerror'                        => 'Ошибка парсинга: %s',

    /**
     * ---------------------------------------------
     * Table Labels
     * ---------------------------------------------
     */
    'departments'                          => 'Департаменты',
    'staff'                                => 'Персонал',
    'teams'                                => 'Команды',
    'userorganizations'                    => 'Организации пользователей',
    'usergroups'                           => 'Группы пользователей',
    'users'                                => 'Пользователи',
    'useremails'                           => 'Email пользователей',
    'ratings'                              => 'Рейтинги',
    'ratingresults'                        => 'Результаты рейтингов',
    'taglinks'                             => 'Ссылки тегов',
    'tags'                                 => 'Теги',

    /**
     * ---------------------------------------------
     * Table Aliases
     * ---------------------------------------------
     */
    'staffgroup'                           => 'Группа персонала',

    /**
     * ---------------------------------------------
     * Custom Labels
     * ---------------------------------------------
     */
    'custom_tickets'                       => 'Заявки',
    'custom_livechat'                      => 'Чат онлайн',
    'custom_public'                        => 'Публичный',
    'custom_private'                       => 'Приватный',
    'custom_shared'                        => 'Совместный',
    'custom_restricted'                    => 'Закрытый',
    'custom_guest'                         => 'Гость',
    'custom_registered'                    => 'Зарегистрированный',
    'custom_user'                          => 'Пользователь',
    'custom_manager'                       => 'Менеджер',
    'custom_nosalutation'                  => '',
    'custom_mr'                            => 'Господин',
    'custom_ms'                            => 'Госпожа',
    'custom_mrs'                           => 'Госпожа',
    'custom_dr'                            => 'Доктор',

    /**
     * ---------------------------------------------
     * Field Labels
     * ---------------------------------------------
     */

    // Departments
    'departments_departmentid'             => 'ID департамента',
    'departments_title'                    => 'Департамент',
    'departments_departmentapp'            => 'Модуль',
    'departments_displayorder'             => 'Порядок отображения',
    'departments_parentdepartmentid'       => 'Родительский департамент',
    'departments_departmenttype'           => 'Тип',

    // Staff
    'staff_staffid'                        => 'ID сотрудника',
    'staff_firstname'                      => 'Имя',
    'staff_lastname'                       => 'Фамилия',
    'staff_fullname'                       => 'Имя и фамилия',
    'staff_username'                       => 'Имя пользователя',
    'staff_designation'                    => 'Designation',
    'staff_email'                          => 'Email',
    'staff_lastvisit'                      => 'Последнее посещение',
    'staff_isenabled'                      => 'Активирован',
    'staff_staffgroup'                     => 'Команда',

    // StaffGroup/Team
    'staffgroup_staffgroupid'              => 'ID команды',
    'staffgroup_title'                     => 'Команда',
    'staffgroup_isadmin'                   => 'Является администратором',

    // UserOrganizations
    'userorganizations_userorganizationid' => 'ID организации пользователей',
    'userorganizations_organizationname'   => 'Название',
    'userorganizations_organizationtype'   => 'Тип',
    'userorganizations_address'            => 'Адрес',
    'userorganizations_city'               => 'Город',
    'userorganizations_state'              => 'Область',
    'userorganizations_postalcode'         => 'Почтовый код',
    'userorganizations_country'            => 'Страна',
    'userorganizations_phone'              => 'Телефон',
    'userorganizations_fax'                => 'Факс',
    'userorganizations_website'            => 'Вебсайт',
    'userorganizations_dateline'           => 'Дата создания',
    'userorganizations_lastupdate'         => 'Последнее обновление',
    'userorganizations_slaplanid'          => 'SLA план',
    'userorganizations_slaexpirytimeline'  => 'Истечение срока действия SLA',
    'userorganizations_usergroupid'        => 'Группа пользователей',

    // UserGroups
    'usergroups_usergroupid'               => 'ID группы пользователей',
    'usergroups_title'                     => 'Группа пользователей',
    'usergroups_grouptype'                 => 'Тип группы пользователей',

    // Users
    'users_userid'                         => 'ID пользователя',
    'users_usergroupid'                    => 'Группа пользователей',
    'users_userorganizationid'             => 'Организация пользователей',
    'users_userrole'                       => 'Роль',
    'users_salutation'                     => 'Приветствие',
    'users_fullname'                       => 'Имя и фамилия',
    'users_userdesignation'                => 'Должность',
    'users_phone'                          => 'Телефон',
    'users_dateline'                       => 'Дата создания',
    'users_lastupdate'                     => 'Последнее обновление',
    'users_lastvisit'                      => 'Последнее посещение',
    'users_slaplanid'                      => 'SLA план',
    'users_slaexpirytimeline'              => 'Истечение срока действия SLA',
    'users_userexpirytimeline'             => 'Истечение срока действия',
    'users_isvalidated'                    => 'Утвержден',

    // UserEmails
    'useremails_useremailid'               => 'ID адресов Email пользователя',
    'useremails_linktypeid'                => 'Пользователь',
    'useremails_email'                     => 'Email',

    // Ratings
    'custom_benchtickets'                  => 'Заявки',
    'custom_benchticketposts'              => 'Сообщения в заявке',
    'custom_benchchatsurvey'               => 'Опрос в чате',
    'custom_benchchathistory'              => 'История чата',
    'ratings_ratingid'                     => 'ID рейтинга',
    'ratings_ratingtitle'                  => 'Название',
    'ratings_ratingtype'                   => 'Тип',
    'ratings_ratingvisibility'             => 'Видимость',

    // RatingResults
    'ratingresults_ratingresultid'         => 'ID результата рейтинга',
    'ratingresults_dateline'               => 'Дата рейтинга',
    'ratingresults_ratingresult'           => 'Счет',
    'ratingresults_ratingid'               => 'Рейтинг',

    // TagLinks
    'taglinks_taglinkid'                   => 'ID ссылки тега',
    'taglinks_tagid'                       => 'Тег',

    // Tags
    'tags_tagid'                           => 'ID тега',
    'tags_tagname'                         => 'Название',

);

return $__LANG;
