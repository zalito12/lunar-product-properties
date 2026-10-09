<?php

return [
    'label' => 'Propiedad',
    'label_plural' => 'Propiedades',
    'form' => [
        'handle' => [
            'label' => 'Identificador',
        ],
        'label' => [
            'label' => 'Etiqueta',
        ],
        'property' => [
            'label' => 'Propiedad',
        ],
    ],
    'relations' => [
        'values' => [
            'title_plural' => 'Valores',
            'actions' => [
                'create' => [
                    'label' => 'Crear valor',
                ],
            ],
        ],
    ],
    'notifications' => [
        'has_values' => [
            'title' => 'Propiedad en uso',
            'body' => 'Elimina sus valores antes de eliminar la propiedad.',
        ],
    ],
];
