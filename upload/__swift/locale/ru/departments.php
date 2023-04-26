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
    'parentdepartment'          => 'Родительский департамент',
    'desc_parentdepartment'     => 'Выберите родительский департамент. Когда выбран департамент, устанавливается связь текущего департамента с выбранным департаментом, и текущий департамент становится поддепартаментом в центре поддержки клиентов и в интерфейсе для персонала.',
    'naparentdep'               => '-- Выберите департамент --',
    'titledepmodmismatch'       => 'Несовпадение модуля родительского департамента',
    'msgdepmodmismatch'         => 'Модуль департамента, указанного как родительский департамент %s (%s), не совпадает с выбранным приложением (%s)',
    'tabassignments'            => 'Назначенные сотрудники и команды',
    'tabpermissionsclient'      => 'Доступ: Пользователи',
    'tabpermissionsstaff'       => 'Доступ: Персонал',
    'tablanguages'              => 'Языки: Перевод',
    'staffassigndep'            => 'Назначенные сотрудники',
    'groupassigndep'            => 'Назначенные команды',
    'uservisibilitycustom'      => 'Ограничить права доступа для некоторых групп пользователей?',
    'desc_uservisibilitycustom' => 'Если эта опция включена, данный департамент будет виден только указанным группам пользователей в центре поддержки клиентов.',
    'usergroups'                => 'Группы пользователей',
    'assignedteams'             => 'Назначенные: команды',
    'assignedstaff'             => 'Назначенные: сотрудники',
    'deldepartmentstitle'       => 'Удалено департаментов "%d"',
    'deldepartmentsmsg'         => 'Следующие департаменты были удалены из базы данных: <BR />',
    'deporder'                  => 'Порядок',

    'displayorder'              => 'Порядок отображения',
    'desc_displayorder'         => 'Это порядок отображения департаментов на панели управления персонала и в центре поддержки клиентов. Департаменты сортируются в порядке (возрастания), который указан здесь.',
    'departments'               => 'Департаменты',
    'desc_departments'          => '',
    'insertdepartment'          => 'Добавить департамент',
    'deptitle'                  => 'Название',
    'desc_deptitle'             => 'Укажите название департамента. Допускается создавать множество департаментов с одинаковым названием.',
    'deptype'                   => 'Тип',
    'desc_deptype'              => 'Укажите тип департамента. Приватные департаменты не видны пользователям в центре поддержки клиентов.',
    'depapp'                    => 'Модуль',
    'departmentapp'             => 'Модуль',
    'desc_departmentapp'        => 'Департамент может быть или <i>заявкой</i>, или <i>чатом онлайн</i>.',
    'templategroup'             => 'Группа шаблонов',
    'desc_templategroup'        => 'Выберите группы шаблонов, на которые назначен данный департамент. Для того, чтобы выбрать несколько групп, удерживайте клавишу CTRL.',
    'invaliddepartment'         => 'Указан неверный департамент',
    'tabedit'                   => 'Редактировать',
    'tabinsert'                 => 'Добавить',
    'wineditdep'                => 'Редактировать департамент',
    'rtitleinsert'              => 'Добавлен департамент "%s"',
    'rdescinsert'               => 'QuickSupport успешно добавил департамент "%s".',
    'rtitleupdate'              => 'Обновлен департамент "%s"',
    'rdescupdate'               => '"%s" департамент был успешно обновлен.',
    'accessoverview'            => 'Доступ',
    'tabaccessoverview'         => 'Доступ',

    // Potentialy unused phrases in departments.php
    'desc_staffassigndep'       => 'Please select the staff users assigned to this department.',
    'desc_groupassigndep'       => 'Please select the teams assigned to this department.',
    'departmentdetails'         => 'Department Details',
    'defaultdep'                => 'Is Default?',
    'desc_defaultdep'           => 'If set to Yes, this department will become the default.',
    'departmentlist'            => 'Department List',
    'redirect_depinsert'        => 'Department inserted successfully',
    'depinsertconfirmation'     => 'Department "%s" inserted successfully',
    'depdeleteconfirmation'     => 'Department "%s" deleted successfully',
    'depsdeleteconfirmation'    => 'Deleted all selected departments',
    'templategroups'            => 'Template Group',
    'desc_templategroups'       => 'Select the template group(s) to which the department is bound. The department will only be visible within the specified template groups.',
    'depupdateconfirm'          => 'Department "%s" updated successfully',

);


return $__LANG;
