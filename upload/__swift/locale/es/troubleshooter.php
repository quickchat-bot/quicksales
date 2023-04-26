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
    'troubleshooter'                 => 'Solucionador de problemas',

    // Manage Categories
    'categories'                     => 'Categorías',
    'managecategories'               => 'Administrar categorías',
    'desc_troubleshootercat'         => '',
    'tscattitle'                     => 'Título de la categoría',
    'desc_tscattitle'                => '',
    'tscatviews'                     => 'Visitas',
    'desc_tscatviews'                => 'El número de visitas que ha tenido la categoría. Éstas aumentan automáticamente cada vez que un usuario hace clic en una categoría.',
    'tscatdisporder'                 => 'Orden de visualización',
    'desc_tscatdisporder'            => 'Las categorías se clasifican según su número de orden de visualización, de menor a mayor.',
    'tscatlist'                      => 'Lista de categorías',
    'tscatdesc'                      => 'Descripción',
    'desc_tscatdesc'                 => 'Una descripción de la categoría se muestra debajo del título. La descripción se limita a 255 caracteres.',
    'steps'                          => 'Pasos',

    // Insert Category
    'insertcategory'                 => 'Insertar categoría',
    'tscatdetails'                   => 'Detalles de categoria',
    'templategroups'                 => 'Grupos de plantillas',
    'desc_templategroups'            => 'Seleccione los grupos de plantillas que se mostrarán en esta categoría del solucionador de problemas en el centro de soporte.',
    'inserttscat'                    => 'Insertar categoría',
    'updatetscat'                    => 'Actualizar categoría',
    'selectonetgroup'                => 'ERROR: Debe seleccionar al menos un grupo de plantillas',
    'troubleshootercatinsertconfirm' => 'Categoría del solucionador de problemas (%s) creada',
    'tcatdeleteconfirm'              => 'Categoría de solucionador de problemas eliminada satisfactoriamente',
    'tcatsdeleteconfirm'             => 'Categorías del solucionador de problemas eliminadas satisfactoriamente',
    'tcatdelconfirm'                 => '¿Está seguro de que desea eliminar esta categoría?\\nAl eliminar una categoría se eliminarán todos los pasos del solucionador de problemas dentro de ella.',
    'invalidtroubleshootercategory'  => 'Categoría del solucionador de problemas no válida',

    // Edit Category
    'editcategory'                   => 'Editar categoría',
    'troubleshootercatupdateconfirm' => 'Categoría del solucionador de problemas (%s) actualizada',

    // Manage Steps
    'troubleshootersteps'            => 'Pasos del solucionador de problemas',
    'managesteps'                    => 'Administrar pasos',
    'addstep'                        => 'Añadir paso',
    'addcategory'                    => 'Añadir categoría',
    'troubleshooters'                => 'Solucionadores de problemas',
    'filter'                         => 'Filtro',
    'stepdelconfirmmsg'              => '¿Está seguro de que desea eliminar este paso? Al eliminar un paso se eliminarán todos los pasos subordinados.',
    'tsdelconfirm'                   => 'Pasos del solucionador de problemas eliminados',
    'stepdeleteconfirm'              => 'Se eliminaron %s pasos del solucionador de problemas',
    'filtertgroupid'                 => 'Grupo de plantillas',
    'desc_filtertgroupid'            => 'Filtrar por grupo de plantilla. Se mostrarán solo las categorías del solucionador de problemas en el grupo de la plantillas seleccionado.',
    'listview'                       => 'Vista de lista',
    'tssteplist'                     => 'Lista de pasos del solucionador de problemas',
    'treeview'                       => 'Vista de estructura',

    // Insert Step
    'insertstep'                     => 'Insertar paso',
    'stepdetails'                    => 'Detalles del paso del solucionador de problemas',
    'tssubject'                      => 'Asunto',
    'tsdisporder'                    => 'Orden de visualización',
    'desc_tsdisporder'               => 'Los pasos del solucionador de problemas se muestran según su número del orden de visualización, de menor a mayor.',
    'updatestep'                     => 'Actualizar paso',
    'tslinks'                        => 'Pasos principales',
    'selectonelink'                  => 'ERROR: Por favor, seleccione al menos un paso principal',
    'tsaddconfirm'                   => 'Paso del solucionador de problemas creado (%s)',
    'editstep'                       => 'Editar paso',

    // Edit Step
    'invalidtroubleshooter'          => 'Solucionador de problemas no válido',
    'tsupdateconfirm'                => 'Paso del solucionador de problemas actualizado (%s)',
    'updatestep'                     => 'Actualizar paso',
    'editstep'                       => 'Editar paso',

    // Comments
    'comments'                       => 'Comentarios',
    'legend'                         => 'Leyenda: ',

    // Reports
    'views'                          => 'Visitas',
    'steptitle'                      => 'Título de paso',

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
