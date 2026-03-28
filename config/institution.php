<?php

return [
    'name' => 'IED Agropecuaria Jose Maria Herrera',
    'display_name' => 'IED JOSÉ MARÍA HERRERA',
    'short_name' => 'IEDJMH',
    'city' => 'Pivijay',
    'department' => 'Magdalena',
    'address' => env('INSTITUTION_ADDRESS', 'Pivijay, Magdalena, Colombia'),
    'phone' => env('INSTITUTION_PHONE'),
    'email' => env('INSTITUTION_EMAIL'),
    'seo' => [
        'default_description' => 'Sitio institucional oficial de la IED Agropecuaria Jose Maria Herrera de Pivijay, Magdalena.',
        'default_image' => '/apple-touch-icon.png',
    ],
    'govbar' => [
        'label' => 'GOV.CO',
    ],
    'allies' => [
        ['label' => 'MinEducacion', 'url' => '#'],
        ['label' => 'Gobernacion', 'url' => '#'],
        ['label' => 'Alcaldia Pivijay', 'url' => '#'],
    ],

    'navigation' => [
        'primary' => [
            [
                'label' => 'Institucion',
                'route' => 'institucion.index',
                'children' => [
                    ['label' => 'Historia', 'route' => 'institucion.historia', 'icon' => 'history'],
                    ['label' => 'Mision y vision', 'route' => 'institucion.mision-vision', 'icon' => 'shield'],
                    ['label' => 'Simbolos', 'route' => 'institucion.simbolos', 'icon' => 'workspace_premium'],
                    ['label' => 'Equipo institucional', 'route' => 'institucion.equipo-institucional', 'icon' => 'groups'],
                    ['label' => 'Sedes', 'route' => 'institucion.sedes', 'icon' => 'domain'],
                ],
            ],
            [
                'label' => 'Academico',
                'route' => 'academico.index',
                'children' => [
                    ['label' => 'Niveles educativos', 'route' => 'academico.niveles-educativos', 'icon' => 'school'],
                    ['label' => 'Modalidad', 'route' => 'academico.modalidad', 'icon' => 'agriculture'],
                    ['label' => 'Planes de area', 'route' => 'academico.planes-area', 'icon' => 'menu_book'],
                    ['label' => 'Sistema de evaluacion', 'route' => 'academico.sistema-evaluacion', 'icon' => 'checklist'],
                    ['label' => 'Calendario academico', 'route' => 'academico.calendario-academico', 'icon' => 'calendar_month'],
                ],
            ],
            [
                'label' => 'Transparencia',
                'route' => 'transparencia.index',
                'children' => [
                    ['label' => 'Documentos', 'route' => 'transparencia.documentos', 'icon' => 'description'],
                    ['label' => 'Contratación', 'route' => 'transparencia.contratacion.index', 'icon' => 'work'],
                ],
            ],
            [
                'label' => 'Atencion al Ciudadano',
                'route' => 'atencion.index',
                'children' => [
                    ['label' => 'Contactenos', 'route' => 'atencion.contactenos', 'icon' => 'contact_support'],
                    ['label' => 'PQRS', 'route' => 'atencion.pqrs', 'icon' => 'forum'],
                    ['label' => 'Consulta PQRS', 'route' => 'atencion.pqrs.track', 'icon' => 'search'],
                    ['label' => 'Tramites y servicios', 'route' => 'atencion.tramites', 'icon' => 'assignment'],
                    ['label' => 'Preguntas frecuentes', 'route' => 'atencion.faq', 'icon' => 'help'],
                ],
            ],
            [
                'label' => 'Proyectos',
                'route' => 'proyectos.index',
            ],
            [
                'label' => 'Noticias',
                'route' => 'noticias.index',
            ],
        ],
        'footer' => [
            ['label' => 'PQRS', 'route' => 'atencion.pqrs'],
            ['label' => 'Tramites y servicios', 'route' => 'atencion.tramites'],
            ['label' => 'Preguntas frecuentes', 'route' => 'atencion.faq'],
            ['label' => 'Mapa del sitio', 'route' => 'atencion.mapa-sitio'],
            ['label' => 'Participacion', 'route' => 'atencion.participacion'],
        ],
    ],

    'sections' => [
        'institucion' => [
            'title' => 'Institucion',
            'description' => 'Informacion institucional, direccion estrategica y documentos de identidad institucional.',
        ],
        'academico' => [
            'title' => 'Academico',
            'description' => 'Oferta academica, lineamientos pedagogicos y recursos para la comunidad educativa.',
        ],
        'proyectos' => [
            'title' => 'Proyectos',
            'description' => 'Iniciativas institucionales de impacto pedagogico, ambiental y comunitario.',
        ],
        'noticias' => [
            'title' => 'Noticias',
            'description' => 'Novedades institucionales de interes para estudiantes, familias, egresados y comunidad educativa.',
        ],
        'transparencia' => [
            'title' => 'Transparencia',
            'description' => 'Informacion publica institucional para consulta ciudadana y rendicion de cuentas.',
        ],
        'atencion' => [
            'title' => 'Atencion al Ciudadano',
            'description' => 'Canales de contacto, PQRS, tramites y orientacion a la ciudadania.',
        ],
    ],
];
