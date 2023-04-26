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
    'troubleshooter'                 => 'Resolução de problemas',

    // Manage Categories
    'categories'                     => 'Categorias',
    'managecategories'               => 'Gerir categorias',
    'desc_troubleshootercat'         => '',
    'tscattitle'                     => 'Título da categoria',
    'desc_tscattitle'                => '',
    'tscatviews'                     => 'Vistas',
    'desc_tscatviews'                => 'O número de visualizações da categoria. Este aumentou automaticamente sempre que um utilizador clica numa categoria.',
    'tscatdisporder'                 => 'Ordem de apresentação',
    'desc_tscatdisporder'            => 'As categorias são ordenadas de acordo com o número da sua ordem de apresentação, do menor para o maior.',
    'tscatlist'                      => 'Lista de categorias',
    'tscatdesc'                      => 'Descrição',
    'desc_tscatdesc'                 => 'É apresentada uma descrição de categoria sob o título. A descrição está limitada a 255 carateres.',
    'steps'                          => 'Passos',

    // Insert Category
    'insertcategory'                 => 'Inserir categoria',
    'tscatdetails'                   => 'Detalhes da categoria',
    'templategroups'                 => 'Grupos de modelos',
    'desc_templategroups'            => 'Selecione os grupos de modelos que apresentarão esta categoria de resolução de problemas no centro de suporte.',
    'inserttscat'                    => 'Inserir categoria',
    'updatetscat'                    => 'Atualizar categoria',
    'selectonetgroup'                => 'ERRO: necessita de selecionar pelo menos um grupo de modelos',
    'troubleshootercatinsertconfirm' => 'Categoria de resolução de problemas criada (%s)',
    'tcatdeleteconfirm'              => 'Categoria de resolução de problemas eliminada com sucesso',
    'tcatsdeleteconfirm'             => 'Categorias de resolução de problemas eliminadas com sucesso',
    'tcatdelconfirm'                 => 'Tem a certeza de que pretende eliminar esta categoria?\\nEliminar uma categoria também elimina todos os passos de resolução de problemas presentes na mesma.',
    'invalidtroubleshootercategory'  => 'Categoria de resolução de problemas inválida',

    // Edit Category
    'editcategory'                   => 'Editar categoria',
    'troubleshootercatupdateconfirm' => 'Categoria de resolução de problemas atualizada (%s)',

    // Manage Steps
    'troubleshootersteps'            => 'Passos da resolução de problemas',
    'managesteps'                    => 'Gerir passos',
    'addstep'                        => 'Adicionar passo',
    'addcategory'                    => 'Adicionar categoria',
    'troubleshooters'                => 'Resoluções de problemas',
    'filter'                         => 'Filtrar',
    'stepdelconfirmmsg'              => 'Tem a certeza de que pretende eliminar este passo? Eliminar um passo resulta na eliminação de todos os respetivos passos subordinados.',
    'tsdelconfirm'                   => 'Passos de resolução de problemas eliminados',
    'stepdeleteconfirm'              => '%s passos de resolução de problemas eliminados',
    'filtertgroupid'                 => 'Grupos de modelos',
    'desc_filtertgroupid'            => 'Filtrar por grupo de modelos. Apenas as categorias de resolução de problemas associadas ao grupo de modelos serão apresentadas.',
    'listview'                       => 'Vista de lista',
    'tssteplist'                     => 'Lista de passos da resolução de problemas',
    'treeview'                       => 'Vista de árvore',

    // Insert Step
    'insertstep'                     => 'Inserir passo',
    'stepdetails'                    => 'Detalhes do passo da resolução de problemas',
    'tssubject'                      => 'Assunto',
    'tsdisporder'                    => 'Ordem de apresentação',
    'desc_tsdisporder'               => 'Os passos de resolução de problemas são ordenados de acordo com o número da sua ordem de apresentação, do menor para o maior.',
    'updatestep'                     => 'Atualizar passo',
    'tslinks'                        => 'Passos principais',
    'selectonelink'                  => 'ERRO: selecione pelo menos um passo principal',
    'tsaddconfirm'                   => 'Passo de resolução de problemas criado (%s)',
    'editstep'                       => 'Editar passo',

    // Edit Step
    'invalidtroubleshooter'          => 'Resolução de problemas inválida',
    'tsupdateconfirm'                => 'Passo de resolução de problemas atualizado (%s)',
    'updatestep'                     => 'Atualizar passo',
    'editstep'                       => 'Editar passo',

    // Comments
    'comments'                       => 'Comentários',
    'legend'                         => 'Legenda: ',

    // Reports
    'views'                          => 'Vistas',
    'steptitle'                      => 'Título do passo',

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
