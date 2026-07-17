<?php

return [
    'title' => 'Roles profesionales',
    'create' => 'Crear rol profesional',
    'edit' => 'Editar rol profesional',
    'edit_title' => 'Editar rol',
    'information_title' => 'Información',
    'update' => 'Actualizar rol profesional',

    // Fields
    'name' => 'Nombre',
    'code' => 'Código',
    'role_type' => 'Tipo de rol',
    'committee' => 'Comité',
    'committee_all' => 'Todos',

    // Hints
    'code_hint' => 'Identificador único en mayúsculas (p. ej., FINSWIMMINGCOACH)',
    'role_type_hint' => 'Categoría a la que pertenece este rol (afecta a la elegibilidad para inscribirse en eventos)',

    // Filters
    'filter_by_name' => 'Filtrar por nombre...',

    // Info boxes
    'info_title' => 'Información sobre roles profesionales',
    'info_body' => 'Los roles profesionales definen qué actividades puede realizar una persona (p. ej., Entrenador, Árbitro, Juez). El tipo de rol determina cómo trata el sistema este rol para la inscripción en eventos y las comprobaciones de elegibilidad.',
    'edit_info_title' => 'Información',
    'edit_info_body' => 'Los roles profesionales definen las actividades que una persona puede realizar dentro de la federación. El tipo de rol determina la elegibilidad para los eventos: ATHLETE para competidores, COACH para entrenadores de equipo, TECHNICAL_OFFICIAL para árbitros y jueces, INSTRUCTOR para instructores de cursos, DIVER para buceadores recreativos/profesionales, STAFF para apoyo operativo de la federación y FEDERATION_STAFF para roles administrativos de la federación.',

    // Success messages
    'created_successfully' => 'Rol profesional creado correctamente.',
    'updated_successfully' => 'Rol profesional actualizado correctamente.',
    'deleted_successfully' => 'Rol profesional eliminado correctamente.',
    'role_assigned_successfully' => 'Rol profesional asignado correctamente.',
    'role_removed_successfully' => 'Rol profesional eliminado correctamente.',

    // Error messages
    'cannot_delete_has_individuals' => 'No se puede eliminar este rol profesional porque está asignado a personas.',
    'cannot_delete_has_certifications' => 'No se puede eliminar este rol profesional porque está vinculado a certificaciones.',
    'cannot_delete_has_licenses' => 'No se puede eliminar este rol profesional porque está vinculado a licencias.',
    'delete_failed' => 'No se pudo eliminar el rol profesional. Inténtalo de nuevo.',

    // Role types
    'role_types' => [
        'ATHLETE' => 'Deportista',
        'COACH' => 'Entrenador',
        'TECHNICAL_OFFICIAL' => 'Oficial técnico',
        'INSTRUCTOR' => 'Instructor',
        'LEADER' => 'Guía',
        'DIVER' => 'Buceador',
        'STAFF' => 'Personal',
        'DIVINGPROFESSIONAL' => 'Profesional del buceo',
        'FEDERATION_STAFF' => 'Personal de la federación',
    ],

    // Actions
    'manage_roles' => 'Gestionar roles',
];
