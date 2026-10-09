<?php

return [
    'label' => 'Property',
    'label_plural' => 'Properties',
    'form' => [
        'handle' => [
            'label' => 'Handle',
        ],
        'label' => [
            'label' => 'Label',
        ],
        'property' => [
            'label' => 'Property',
        ],
    ],
    'relations' => [
        'values' => [
            'title_plural' => 'Values',
            'actions' => [
                'create' => [
                    'label' => 'Create value',
                ],
            ],
        ],
    ],
    'notifications' => [
        'has_values' => [
            'title' => 'Property in use',
            'body' => 'Delete its values before deleting the property.',
        ],
    ],
];
