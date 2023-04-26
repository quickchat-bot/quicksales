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
    'parentdepartment'          => 'Departamento superior',
    'desc_parentdepartment'     => 'Este departamento puede colocarse debajo de otro, el departamento superior. Para no complicarnos, esto solo es recomendable si tiene muchos departamentos.',
    'naparentdep'               => '-- Seleccionar departamento --',
    'titledepmodmismatch'       => 'Tipo equivocado de departamento superior',
    'msgdepmodmismatch'         => 'El canal del departamento superior %s (%s) no coincide con el canal seleccionado (%s) para este departamento. Sólo puede vincular departamentos del mismo canal.',
    'tabassignments'            => 'Encargos del personal',
    'tabpermissionsclient'      => 'Permisos: Usuarios',
    'tabpermissionsstaff'       => 'Permisos: Personal',
    'tablanguages'              => 'Idiomas: Traducción',
    'staffassigndep'            => 'Personal asignado',
    'groupassigndep'            => 'Equipos asignados',
    'uservisibilitycustom'      => 'Restringir el departamento a determinados grupos de usuarios',
    'desc_uservisibilitycustom' => 'Si se activa, solo los grupos de usuarios seleccionados abajo podrán ver y seleccionar este departamento (cuándo envíen tickets o soliciten un Live Chat).',
    'usergroups'                => 'Grupos de usuarios',
    'assignedteams'             => 'Encargos de equipos del personal',
    'assignedstaff'             => 'Encargos de personal individual',
    'deldepartmentstitle'       => 'Departamentos eliminados (%d)',
    'deldepartmentsmsg'         => 'Se eliminaron los siguientes departamentos: <BR />',
    'deporder'                  => 'Orden',

    'displayorder'              => 'Orden de visualización',
    'desc_displayorder'         => 'Los departamentos se muestran según su número de orden de visualización, del más pequeño al más grande.',
    'departments'               => 'Departamentos',
    'desc_departments'          => '',
    'insertdepartment'          => 'Insertar departamento',
    'deptitle'                  => 'Nombre del departamento',
    'desc_deptitle'             => 'Por ejemplo, <em>soporte</em> o <em>ventas</em>.',
    'deptype'                   => 'Tipo',
    'desc_deptype'              => '<strong>Público</strong> Departamentos disponibles en todos los lugares.<br /><strong>Privado</strong> Departmentos solo disponibles para usuarios del personal.',
    'depapp'                    => 'Canal',
    'departmentapp'             => 'Canal',
    'desc_departmentapp'        => 'Un departamento puede ser un departamento de <strong>tickets</strong> o un departamento de <strong>soporte en vivo</strong>.',
    'templategroup'             => 'Restringir a determinados grupos de plantillas',
    'desc_templategroup'        => 'Se pueden registringir los departamentos a determinados grupos de plantillas.',
    'invaliddepartment'         => 'Se detectó un problema (departamento especificado no válido)',
    'tabedit'                   => 'Editar',
    'tabinsert'                 => 'Insertar',
    'wineditdep'                => 'Editar departamento',
    'rtitleinsert'              => 'Departamento %s creado',
    'rdescinsert'               => 'El departamento %s fue creado correctamente.',
    'rtitleupdate'              => 'Departamento %s actualizado',
    'rdescupdate'               => 'El departamento %s fue actualizado correctamente.',
    'accessoverview'            => 'Resumen de acceso',
    'tabaccessoverview'         => 'Resumen de acceso',

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
