<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default LLM Driver
    |--------------------------------------------------------------------------
    |
    | Driver padrão para chamadas de Inteligência Artificial.
    | Opções suportadas: "openai", "anthropic".
    |
    */

    'default_driver' => env('LLM_DRIVER', 'openai'),

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
    ],
];
