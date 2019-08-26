<?php

return [
    'startup' => [

        /** SITES **/

        'sites' => [
            'site1' => [
                'name' => 'SiteA',
                'location' => 'LocationA',
                'lat' => 0.0,
                'lng' => 0.0,
                'addresses' => [
                    'address1' => [
                        'street'     => 'StreetA - A',
                        'city'       => 'CityA - A',
                        'post_code'  => 'AAAAA',
                        'country'    => 'IT', // ISO-3166-2 or ISO-3166-3 country code
                    ],
                    'address2' => [
                        'street'     => 'StreetA - B',
                        'city'       => 'CityA - B',
                        'post_code'  => 'AAAAB',
                        'country'    => 'IT', // ISO-3166-2 or ISO-3166-3 country code
                    ],
                ]
            ],
            'site2' => [
                'name' => 'SiteB',
                'location' => 'LocationB',
                'lat' => 0.0,
                'lng' => 0.0,
                'addresses' => [
                    'address1' => [
                        'street'     => 'StreetB - A',
                        'city'       => 'CityB - A',
                        'post_code'  => 'BBBBB',
                        'country'    => 'IT', // ISO-3166-2 or ISO-3166-3 country code
                    ],
                    'address2' => [
                        'street'     => 'StreetB - B',
                        'city'       => 'CityB - B',
                        'post_code'  => 'BBBBA',
                        'country'    => 'IT', // ISO-3166-2 or ISO-3166-3 country code
                    ],
                ]
            ],
        ],

        /** PROFESSIONS **/

        'professions' => [
            'prof1' => [
                'name' => 'ProfA',
                'is_storm' => 0
            ],
            'prof2' => [
                'name' => 'ProfB',
                'is_storm' => 0
            ],
            'profn' => [
                'name' => 'ProfN',
                'is_storm' => 0
            ],
        ],

        /** TASK INTERVENT TYPES **/

        'task_intervent_types' => [
            'type1' => [
                'name' => TASK_INTERVENT_TYPE_DAMAGED,
            ],
            'type2' => [
                'name' => TASK_INTERVENT_TYPE_CORROSION,
            ],
            'type3' => [
                'name' => TASK_INTERVENT_TYPE_OTHER,
            ],
        ]


    ]
];
