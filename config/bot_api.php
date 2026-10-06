<?php

return [
    'token' => env('BOT_API_TOKEN'),
    'confirmation_minutes' => 20,
    'services' => [
        'consulta' => [
            'name' => 'Consulta veterinaria',
            'description' => 'Evaluación de tu mascota por el personal veterinario.',
            'price' => null,
        ],
    ],
];
