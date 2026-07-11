<?php

/**
 * Bagisto admin "Configure" schema. Registers under Configure > Sales >
 * Courier Settings (nested because every 'key' below starts with "sales.").
 *
 * All fields live in ONE section ('sales.courier.general') because
 * Bagisto's `depends` feature can only reference a sibling field within
 * the same section — this is what makes only the selected courier's
 * fields show/hide dynamically.
 */
return [
    [
        'key'  => 'sales.courier',
        'name' => 'Courier Settings',
        'info' => 'Configure the couriers used for order fulfillment.',
        'icon' => 'settings/settings.svg',
        'sort' => 12,
    ], [
        'key'    => 'sales.courier.general',
        'name'   => 'Courier Configuration',
        'info'   => "Select a courier and enter its credentials. Only the selected courier's fields will be shown.",
        'sort'   => 1,
        'fields' => [
            [
                'name'       => 'default_courier',
                'title'      => 'Default Courier',
                'type'       => 'select',
                'validation' => 'required',
                'default'    => 'steadfast',
                'options'    => [
                    ['title' => 'SteadFast', 'value' => 'steadfast'],
                    ['title' => 'Pathao', 'value' => 'pathao'],
                ],
            ],

            // ---- SteadFast (default_courier = steadfast হলে দেখাবে) ----
            [
                'name'    => 'steadfast_active',
                'title'   => 'Enable SteadFast',
                'type'    => 'boolean',
                'default' => 0,
                'depends' => 'default_courier:steadfast',
            ], [
                'name'       => 'steadfast_api_key',
                'title'      => 'API Key',
                'type'       => 'password',
                'validation' => 'required_if:default_courier,steadfast',
                'depends'    => 'default_courier:steadfast',
            ], [
                'name'       => 'steadfast_secret_key',
                'title'      => 'Secret Key',
                'type'       => 'password',
                'validation' => 'required_if:default_courier,steadfast',
                'depends'    => 'default_courier:steadfast',
            ], [
                'name'    => 'steadfast_base_url',
                'title'   => 'Base URL',
                'type'    => 'text',
                'default' => 'https://portal.packzy.com/api/v1',
                'depends' => 'default_courier:steadfast',
            ], [
                'name'    => 'steadfast_sandbox',
                'title'   => 'Sandbox Mode',
                'type'    => 'boolean',
                'default' => 0,
                'depends' => 'default_courier:steadfast',
            ],

            // ---- Pathao (default_courier = pathao হলে দেখাবে) ----
            [
                'name'    => 'pathao_active',
                'title'   => 'Enable Pathao',
                'type'    => 'boolean',
                'default' => 0,
                'depends' => 'default_courier:pathao',
            ], [
                'name'       => 'pathao_client_id',
                'title'      => 'Client ID',
                'type'       => 'text',
                'validation' => 'required_if:default_courier,pathao',
                'depends'    => 'default_courier:pathao',
            ], [
                'name'       => 'pathao_client_secret',
                'title'      => 'Client Secret',
                'type'       => 'password',
                'validation' => 'required_if:default_courier,pathao',
                'depends'    => 'default_courier:pathao',
            ], [
                'name'       => 'pathao_username',
                'title'      => 'Username',
                'type'       => 'text',
                'validation' => 'required_if:default_courier,pathao',
                'depends'    => 'default_courier:pathao',
            ], [
                'name'       => 'pathao_password',
                'title'      => 'Password',
                'type'       => 'password',
                'validation' => 'required_if:default_courier,pathao',
                'depends'    => 'default_courier:pathao',
            ], [
                'name'    => 'pathao_store_id',
                'title'   => 'Store ID',
                'type'    => 'text',
                'depends' => 'default_courier:pathao',
            ], [
                'name'    => 'pathao_base_url',
                'title'   => 'Base URL',
                'type'    => 'text',
                'default' => 'https://api-hermes.pathao.com',
                'depends' => 'default_courier:pathao',
            ], [
                'name'    => 'pathao_sandbox',
                'title'   => 'Sandbox Mode',
                'type'    => 'boolean',
                'default' => 0,
                'depends' => 'default_courier:pathao',
            ],
        ],
    ],
];
