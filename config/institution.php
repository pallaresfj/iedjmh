<?php

return [
    'name' => 'IED Agropecuaria José María Herrera',
    'display_name' => 'IED JOSÉ MARÍA HERRERA',
    'short_name' => 'IEDJMH',
    'city' => 'Pivijay',
    'department' => 'Magdalena',
    'address' => env('INSTITUTION_ADDRESS', 'Pivijay, Magdalena, Colombia'),
    'phone' => env('INSTITUTION_PHONE'),
    'email' => env('INSTITUTION_EMAIL'),
    'seo' => [
        'default_description' => 'Sitio institucional oficial de la IED Agropecuaria José María Herrera de Pivijay, Magdalena.',
        'default_image' => '/apple-touch-icon.png',
    ],
    'govbar' => [
        'label' => 'GOV.CO',
    ],
    'allies' => [
        ['label' => 'MinEducación', 'url' => '#'],
        ['label' => 'Gobernación', 'url' => '#'],
        ['label' => 'Alcaldía Pivijay', 'url' => '#'],
    ],

    'navigation' => [
        'primary' => [
            [
                'label' => 'Institución',
                'route' => 'institucion.index',
                'children' => [
                    ['label' => 'Historia', 'route' => 'institucion.historia', 'icon' => 'history'],
                    ['label' => 'Misión y visión', 'route' => 'institucion.mision-vision', 'icon' => 'shield'],
                    ['label' => 'Símbolos', 'route' => 'institucion.simbolos', 'icon' => 'workspace_premium'],
                    ['label' => 'Equipo institucional', 'route' => 'institucion.equipo-institucional', 'icon' => 'groups'],
                    ['label' => 'Sedes', 'route' => 'institucion.sedes', 'icon' => 'domain'],
                ],
            ],
            [
                'label' => 'Académico',
                'route' => 'academico.index',
                'children' => [
                    ['label' => 'Niveles educativos', 'route' => 'academico.niveles-educativos', 'icon' => 'school'],
                    ['label' => 'Modalidad', 'route' => 'academico.modalidad', 'icon' => 'agriculture'],
                    ['label' => 'Planes de área', 'route' => 'academico.planes-area', 'icon' => 'menu_book'],
                    ['label' => 'Sistema de evaluación', 'route' => 'academico.sistema-evaluacion', 'icon' => 'checklist'],
                    ['label' => 'Proyectos pedagógicos', 'route' => 'academico.proyectos-pedagogicos', 'icon' => 'science'],
                    ['label' => 'Calendario académico', 'route' => 'academico.calendario-academico', 'icon' => 'calendar_month'],
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
                'label' => 'Atención al Ciudadano',
                'route' => 'atencion.index',
                'children' => [
                    ['label' => 'Contáctenos', 'route' => 'atencion.contactenos', 'icon' => 'contact_support'],
                    ['label' => 'PQRS', 'route' => 'atencion.pqrs', 'icon' => 'forum'],
                    ['label' => 'Trámites y servicios', 'route' => 'atencion.tramites', 'icon' => 'assignment'],
                    ['label' => 'Preguntas frecuentes', 'route' => 'atencion.faq', 'icon' => 'help'],
                    ['label' => 'Portal de egresados', 'route' => 'egresados.index', 'icon' => 'school'],
                ],
            ],
            [
                'label' => 'Noticias',
                'route' => 'noticias.index',
            ],
        ],
        'footer' => [
            ['label' => 'PQRS', 'route' => 'atencion.pqrs'],
            ['label' => 'Trámites y servicios', 'route' => 'atencion.tramites'],
            ['label' => 'Preguntas frecuentes', 'route' => 'atencion.faq'],
            ['label' => 'Mapa del sitio', 'route' => 'atencion.mapa-sitio'],
            ['label' => 'Participación', 'route' => 'atencion.participacion'],
        ],
    ],

    'sections' => [
        'institucion' => [
            'title' => 'Institución',
            'description' => 'Información institucional, dirección estratégica y documentos de identidad institucional.',
        ],
        'academico' => [
            'title' => 'Académico',
            'description' => 'Oferta académica, lineamientos pedagógicos y recursos para la comunidad educativa.',
        ],
        'noticias' => [
            'title' => 'Noticias',
            'description' => 'Novedades institucionales de interés para estudiantes, familias, egresados y comunidad educativa.',
        ],
        'transparencia' => [
            'title' => 'Transparencia',
            'description' => 'Información pública institucional para consulta ciudadana y rendición de cuentas.',
        ],
        'atencion' => [
            'title' => 'Atención al Ciudadano',
            'description' => 'Canales de contacto, PQRS, trámites y orientación a la ciudadanía.',
        ],
    ],
];
