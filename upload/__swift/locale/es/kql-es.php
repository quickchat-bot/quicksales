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
    'kqlparseerror'                        => 'Error de análisis: %s',

    /**
     * ---------------------------------------------
     * Table Labels
     * ---------------------------------------------
     */
    'departments'                          => 'Departamentos',
    'staff'                                => 'Personal',
    'teams'                                => 'Equipos',
    'userorganizations'                    => 'Organizaciones de usuarios',
    'usergroups'                           => 'Grupos de usuarios',
    'users'                                => 'Usuarios',
    'useremails'                           => 'Correo electrónico del usuario',
    'ratings'                              => 'Calificaciones',
    'ratingresults'                        => 'Resultados de calificación',
    'taglinks'                             => 'Enlaces de etiquetas',
    'tags'                                 => 'Etiquetas',

    /**
     * ---------------------------------------------
     * Table Aliases
     * ---------------------------------------------
     */
    'staffgroup'                           => 'Grupo de personal',

    /**
     * ---------------------------------------------
     * Custom Labels
     * ---------------------------------------------
     */
    'custom_tickets'                       => 'Tickets',
    'custom_livechat'                      => 'Live Chat',
    'custom_public'                        => 'Público',
    'custom_private'                       => 'Privado',
    'custom_shared'                        => 'Compartido',
    'custom_restricted'                    => 'Restringido',
    'custom_guest'                         => 'Invitado',
    'custom_registered'                    => 'Registrado',
    'custom_user'                          => 'Usuario',
    'custom_manager'                       => 'Manager',
    'custom_nosalutation'                  => '',
    'custom_mr'                            => 'Sr.',
    'custom_ms'                            => 'Sra.',
    'custom_mrs'                           => 'Sra.',
    'custom_dr'                            => 'Dr.',

    /**
     * ---------------------------------------------
     * Field Labels
     * ---------------------------------------------
     */

    // Departments
    'departments_departmentid'             => 'ID de departamento',
    'departments_title'                    => 'Departamento',
    'departments_departmentapp'            => 'Aplicación',
    'departments_displayorder'             => 'Orden de visualización',
    'departments_parentdepartmentid'       => 'Departamento superior',
    'departments_departmenttype'           => 'Tipo',

    // Staff
    'staff_staffid'                        => 'ID del personal',
    'staff_firstname'                      => 'Nombre',
    'staff_lastname'                       => 'Apellido',
    'staff_fullname'                       => 'Nombre completo',
    'staff_username'                       => 'Nombre de usuario',
    'staff_designation'                    => 'Designation',
    'staff_email'                          => 'Correo electrónico',
    'staff_lastvisit'                      => 'Última visita',
    'staff_isenabled'                      => 'Está activado',
    'staff_staffgroup'                     => 'Equipo',

    // StaffGroup/Team
    'staffgroup_staffgroupid'              => 'ID de equipo',
    'staffgroup_title'                     => 'Equipo',
    'staffgroup_isadmin'                   => 'Es administrador',

    // UserOrganizations
    'userorganizations_userorganizationid' => 'ID de organización de usuario',
    'userorganizations_organizationname'   => 'Nombre',
    'userorganizations_organizationtype'   => 'Tipo',
    'userorganizations_address'            => 'Dirección',
    'userorganizations_city'               => 'Ciudad',
    'userorganizations_state'              => 'Provincia',
    'userorganizations_postalcode'         => 'Código postal',
    'userorganizations_country'            => 'País',
    'userorganizations_phone'              => 'Teléfono',
    'userorganizations_fax'                => 'Fax',
    'userorganizations_website'            => 'Sitio web',
    'userorganizations_dateline'           => 'Fecha de creación',
    'userorganizations_lastupdate'         => 'Última actualización',
    'userorganizations_slaplanid'          => 'Plan de SLA',
    'userorganizations_slaexpirytimeline'  => 'Caducidad de SLA',
    'userorganizations_usergroupid'        => 'Grupo de usuarios',

    // UserGroups
    'usergroups_usergroupid'               => 'ID de grupo de usuario',
    'usergroups_title'                     => 'Grupo de usuarios',
    'usergroups_grouptype'                 => 'Tipo de grupo de usuario',

    // Users
    'users_userid'                         => 'ID de usuario',
    'users_usergroupid'                    => 'Grupo de usuarios',
    'users_userorganizationid'             => 'Organización de usuarios',
    'users_userrole'                       => 'Función',
    'users_salutation'                     => 'Saludo',
    'users_fullname'                       => 'Nombre completo',
    'users_userdesignation'                => 'Designación',
    'users_phone'                          => 'Teléfono',
    'users_dateline'                       => 'Fecha de creación',
    'users_lastupdate'                     => 'Última actualización',
    'users_lastvisit'                      => 'Última visita',
    'users_slaplanid'                      => 'Plan de SLA',
    'users_slaexpirytimeline'              => 'Caducidad de SLA',
    'users_userexpirytimeline'             => 'Caducidad',
    'users_isvalidated'                    => 'Validado',

    // UserEmails
    'useremails_useremailid'               => 'ID de correo electrónico de usuario',
    'useremails_linktypeid'                => 'Usuario',
    'useremails_email'                     => 'Correo electrónico',

    // Ratings
    'custom_benchtickets'                  => 'Tickets',
    'custom_benchticketposts'              => 'Entradas de tickets',
    'custom_benchchatsurvey'               => 'Encuesta sobre chat',
    'custom_benchchathistory'              => 'Historial del chat',
    'ratings_ratingid'                     => 'ID de calificación',
    'ratings_ratingtitle'                  => 'Título',
    'ratings_ratingtype'                   => 'Tipo',
    'ratings_ratingvisibility'             => 'Visibilidad',

    // RatingResults
    'ratingresults_ratingresultid'         => 'ID del resultado de calificación',
    'ratingresults_dateline'               => 'Fecha de calificación',
    'ratingresults_ratingresult'           => 'Puntuación',
    'ratingresults_ratingid'               => 'Calificación',

    // TagLinks
    'taglinks_taglinkid'                   => 'ID de etiqueta de enlace',
    'taglinks_tagid'                       => 'Etiqueta',

    // Tags
    'tags_tagid'                           => 'ID de etiqueta',
    'tags_tagname'                         => 'Nombre',

);

return $__LANG;
