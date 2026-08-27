<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Provedor padrão de IA
    |--------------------------------------------------------------------------
    |
    | Valores suportados: groq, openai, ollama, mock
    | "mock" é usado automaticamente quando nenhuma chave está configurada
    | e o fallback está desabilitado.
    |
    */
    'default' => env('AI_PROVIDER', 'groq'),

    /*
    |--------------------------------------------------------------------------
    | Fallback entre provedores
    |--------------------------------------------------------------------------
    |
    | Quando true, o AIProviderManager pode tentar o próximo provider da
    | lista fallback em caso de falha. O fallback NUNCA é automático para
    | provedores pagos quando o padrão também é pago — apenas para mock/ollama.
    |
    */
    'fallback_enabled' => env('AI_FALLBACK_ENABLED', false),

    'fallback_chain' => array_filter(explode(',', env('AI_FALLBACK_CHAIN', 'ollama,mock'))),

    /*
    |--------------------------------------------------------------------------
    | Zero Data Retention (ZDR)
    |--------------------------------------------------------------------------
    |
    | Enquanto GROQ_ZDR_CONFIRMED for false, a camada ZeroDataRetention
    | anonimiza ou bloqueia dados reais de pessoas, tarefas e documentos
    | antes de enviar para qualquer API externa.
    |
    */
    'zdr' => [
        'required' => env('GROQ_ZDR_REQUIRED', true),
        'confirmed' => env('GROQ_ZDR_CONFIRMED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provedores
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'groq' => [
            'driver' => 'groq',
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
            'vision_model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
            'timeout' => env('GROQ_TIMEOUT', 30),
            'max_tokens' => env('GROQ_MAX_TOKENS', 1024),
            'temperature' => env('GROQ_TEMPERATURE', 0.5),
        ],

        'openai' => [
            'driver' => 'openai',
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'timeout' => env('OPENAI_TIMEOUT', 30),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 1024),
            'temperature' => env('OPENAI_TEMPERATURE', 0.5),
        ],

        'ollama' => [
            'driver' => 'ollama',
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434/v1'),
            'model' => env('OLLAMA_MODEL', 'llama3.1'),
            'timeout' => env('OLLAMA_TIMEOUT', 60),
            'max_tokens' => env('OLLAMA_MAX_TOKENS', 1024),
            'temperature' => env('OLLAMA_TEMPERATURE', 0.5),
        ],

        'mock' => [
            'driver' => 'mock',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Memória gerencial
    |--------------------------------------------------------------------------
    */
    'knowledge' => [
        'chunk_size' => env('AI_KNOWLEDGE_CHUNK_SIZE', 800),
        'chunk_overlap' => env('AI_KNOWLEDGE_CHUNK_OVERLAP', 80),
        'max_chunks_per_query' => env('AI_KNOWLEDGE_MAX_CHUNKS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Copiloto do Gestor
    |--------------------------------------------------------------------------
    */
    'copilot' => [
        'max_tool_iterations' => env('AI_MAX_TOOL_ITERATIONS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging e auditoria
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'channel' => env('AI_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
    ],

    'max_tool_iterations' => env('AI_MAX_TOOL_ITERATIONS', 3),
];
