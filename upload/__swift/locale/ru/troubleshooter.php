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
    'troubleshooter'                 => 'Устранение неполадок',

    // Manage Categories
    'categories'                     => 'Категории',
    'managecategories'               => 'Управление категориями',
    'desc_troubleshootercat'         => '',
    'tscattitle'                     => 'Название',
    'desc_tscattitle'                => '',
    'tscatviews'                     => 'Просмотры',
    'desc_tscatviews'                => 'Количество просмотров категории. Количество автоматически увеличивается с каждым кликом пользователя по категории.',
    'tscatdisporder'                 => 'Порядок отображения',
    'desc_tscatdisporder'            => 'Категории сортируются в соответствии с номером отображения, от маленького до большого.',
    'tscatlist'                      => 'Список категорий',
    'tscatdesc'                      => 'Описание',
    'desc_tscatdesc'                 => 'Описание категории отображается под ее названием. Максимально допустимое количество символов - 255.',
    'steps'                          => 'Шаги',

    // Insert Category
    'insertcategory'                 => 'Добавить категорию',
    'tscatdetails'                   => 'Информация о категории',
    'templategroups'                 => 'Группа шаблонов',
    'desc_templategroups'            => 'Выберите группы шаблонов, которые будут отображать эту категорию устранения неполадок в центре поддержки.',
    'inserttscat'                    => 'Добавить категорию',
    'updatetscat'                    => 'Обновить категорию',
    'selectonetgroup'                => 'ОШИБКА: Необходимо выбрать как минимум одну группу шаблонов',
    'troubleshootercatinsertconfirm' => 'Создана категория устранения неполадок (%s)',
    'tcatdeleteconfirm'              => 'Категория устранения неполадок успешно удалена',
    'tcatsdeleteconfirm'             => 'Категории устранения неполадок успешно удалены',
    'tcatdelconfirm'                 => 'Вы уверены, что хотите удалить эту категорию?\\nВ результат данного действия все шаги по устранению неполадок в этой категории будут удалены.',
    'invalidtroubleshootercategory'  => 'Неверная категория устранения неполадок',

    // Edit Category
    'editcategory'                   => 'Редактировать категорию',
    'troubleshootercatupdateconfirm' => 'Категория устранения неполадок обновлена (%s)',

    // Manage Steps
    'troubleshootersteps'            => 'Шаги по устранению неполадок',
    'managesteps'                    => 'Управление шагами',
    'addstep'                        => 'Добавить шаг',
    'addcategory'                    => 'Редактировать категорию',
    'troubleshooters'                => 'Устранение неполадок',
    'filter'                         => 'Фильтр',
    'stepdelconfirmmsg'              => 'Вы уверены, что хотите удалить этот шаг? В результате этого действия все дочерние шаги также будут удалены.',
    'tsdelconfirm'                   => 'Шаги по устранению неполадок удалены',
    'stepdeleteconfirm'              => 'Удалены %s шаги по устранению неполадок',
    'filtertgroupid'                 => 'Группа шаблонов',
    'desc_filtertgroupid'            => 'Фильтровать по группе шаблонов. Отобразятся только те категории устранения неполадок, которые входят в выбранную группу шаблонов.',
    'listview'                       => 'Последний просмотр',
    'tssteplist'                     => 'Список шагов по устранению неполадок',
    'treeview'                       => 'Структура',

    // Insert Step
    'insertstep'                     => 'Добавить шаг',
    'stepdetails'                    => 'Информация о шагах по устранению неполадок',
    'tssubject'                      => 'Тема',
    'tsdisporder'                    => 'Порядок отображения',
    'desc_tsdisporder'               => 'Шаги по устранению неполадок сортируются в соответствии с номером отображения, от маленького до большого.',
    'updatestep'                     => 'Обновить шаг',
    'tslinks'                        => 'Родительские шаги',
    'selectonelink'                  => 'ОШИБКА: Необходимо выбрать как минимум один родительский шаг',
    'tsaddconfirm'                   => 'Создан шаг по устранению неполадок (%s)',
    'editstep'                       => 'Редактировать шаг',

    // Edit Step
    'invalidtroubleshooter'          => 'Неверное устранение неполадок',
    'tsupdateconfirm'                => 'Обновлен шаг по устранению неполадок (%s)',
    'updatestep'                     => 'Обновить шаг',
    'editstep'                       => 'Редактировать шаг',

    // Comments
    'comments'                       => 'Комментарии',
    'legend'                         => 'Легенда: ',

    // Reports
    'views'                          => 'Просмотры',
    'steptitle'                      => 'Название шага',

    // Potentialy unused phrases in troubleshooter.php
    'desc_tslinks'                   => 'Select the Parent Steps for this Step. The Troubleshooter works in a tree-based navigational manner and revolves around parent-child relationships. You can select multiple Parent Steps by pressing the CTRL Key and clicking on the Step title.',
    'importexport'                   => 'Import/Export',
    'export'                         => 'Export',
    'exportxml'                      => 'Export XML',
    'exportcat'                      => 'Troubleshooter Categories',
    'desc_exportcat'                 => 'Select the Troubleshooter Categories to Export. Only the Steps under the Selected Categories will be exported.',
    'importtroubleshooter'           => 'Import Troubleshooter',
    'troubleshooterfile'             => 'Import File',
    'desc_troubleshooterfile'        => 'Please select the XML file to import',
    'importxml'                      => 'Import XML',
    'importconfirm'                  => 'Imported data from XML file',
    'popularcategories'              => 'Pouplar Categories',
    'popularsteps'                   => 'Popular Steps',
);

return $__LANG;
