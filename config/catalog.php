<?php

return [
    'import' => [
        'quality' => env('IMPORT_IMAGE_QUALITY', 80),
        'timeout' => env('IMPORT_DOWNLOAD_TIMEOUT', 30),
        'max_attempts' => env('IMPORT_DOWNLOAD_ATTEMPTS', 3),
    ],
    'ai_curation' => [
        'enabled' => env('CATALOG_AI_CURATION_ENABLED', false),
        'provider' => env('CATALOG_AI_PROVIDER', 'ollama'),
        'model' => env('CATALOG_AI_MODEL', 'gemma3:4b'),
        'batch_size' => env('CATALOG_AI_BATCH_SIZE', 20),
        'temperature' => (float) env('CATALOG_AI_TEMPERATURE', 0.2),
        'timeout' => (int) env('CATALOG_AI_TIMEOUT', 120),
        'max_tokens' => (int) env('CATALOG_AI_MAX_TOKENS', 4000),
    ],
    'suppliers' => [
        'default' => env('CATALOG_DEFAULT_SUPPLIER', 'xbz'),
        'xbz' => [
            'cnpj' => env('XBZ_CNPJ'),
            'token' => env('XBZ_TOKEN'),
        ],
        'asia' => [
            'api_key' => env('ASIA_IMPORT_API_KEY'),
            'secret_key' => env('ASIA_IMPORT_SECRET_KEY'),
        ],
    ],
    'api_sync' => [
        'category_search_presets' => [
            'caneta' => ['caneta'],
            'canetas' => ['caneta'],
            'caderno' => ['caderno'],
            'cadernos' => ['caderno'],
            'caneca' => ['caneca'],
            'canecas' => ['caneca'],
            'copo' => ['copo'],
            'copos' => ['copo'],
            'garrafa' => ['garrafa', 'squeeze'],
            'garrafas' => ['garrafa', 'squeeze'],
            'mochila' => ['mochila'],
            'mochilas' => ['mochila'],
            'ecobag' => ['sacola', 'ecobag', 'sacola ecologica'],
            'ecobags' => ['sacola', 'ecobag', 'sacola ecologica'],
            'bolsa-termica' => ['bolsa termica', 'bolsa térmica', 'termica', 'térmica'],
            'bolsas-termicas' => ['bolsa termica', 'bolsa térmica', 'termica', 'térmica'],
            'kit-vinho' => ['kit vinho', 'vinho'],
            'kits-vinho' => ['kit vinho', 'vinho'],
            'kits-churrasco' => ['kit churrasco', 'churrasco'],
            'kits-queijo' => ['kit queijo', 'queijo'],
            'chaveiro' => ['chaveiro'],
            'chaveiros' => ['chaveiro'],
            'caixa-de-som' => ['caixa de som', 'speaker'],
            'caixas-de-som' => ['caixa de som', 'speaker'],
            'agenda' => ['agenda'],
            'agendas' => ['agenda'],
            'bloquinho' => ['bloquinho', 'bloco de notas', 'bloco'],
            'bloquinhos' => ['bloquinho', 'bloco de notas', 'bloco'],
            'moleskine' => ['moleskine'],
            'pastas' => ['pasta', 'pastas'],
            'guarda-chuvas' => ['guarda-chuva', 'guarda chuva', 'sombrinha'],
            'necessaires' => ['necessaire', 'estojo', 'porta-cosmeticos', 'porta cosmeticos'],
            'bones' => ['bone', 'boné', 'bones', 'bonés', 'bone brinde', 'chapeu'],
            'mouse-pad' => ['mouse pad', 'mousepad', 'pad mouse'],
            'porta-cracha' => ['porta cracha', 'porta crachá', 'lanyard', 'cordao cracha', 'cracha'],
            'sacochila' => ['sacochila', 'mochila cordao', 'mochila de cordão', 'mochila saco'],
            'fones-de-ouvido' => ['fone de ouvido', 'fone bluetooth', 'headphone', 'earphone', 'earbuds', 'fone'],
            'power-bank' => ['power bank', 'powerbank', 'carregador portatil', 'bateria portatil'],
            'squeezes' => ['squeeze', 'squeeze esportivo'],
            'almofadas' => ['almofada', 'almofadao'],
        ],
    ],
    'term_map' => [
        'Kits Churrasco' => 'bbq-kits',
        'Kits Queijo' => 'cheese-kits',
        'Canetas' => 'pens',
    ],
    /*
    | Normaliza rótulos vindos da IA/import antes do slug (chaves em minúsculas).
    */
    'category_canonical_names' => [
        'acessórios de escrita' => 'Canetas',
        'acessorios de escrita' => 'Canetas',
        'writing accessories' => 'Canetas',
        'canetas e lapiseiras' => 'Canetas',
    ],
    /*
    | Rótulo exibido nos chips do catálogo quando o nome salvo no BD não ajuda.
    */
    'category_display_labels' => [
        'pens' => 'Canetas',
        'acessorios-de-escrita' => 'Canetas',
        'bbq-kits' => 'Kits churrasco',
        'cheese-kits' => 'Kits queijo',
    ],
    /*
    | Ordem dos chips no catálogo (demais categorias por ordem alfabética do rótulo).
    */
    'category_filter_priority' => [
        'linha-premium',
        'lancamentos',
        'brindes-funcionais',
        'pens',
        'bbq-kits',
        'cheese-kits',
    ],
    'color_names' => [
        '#000000' => 'Preto',
        '#FFFFFF' => 'Branco',
        '#C0C0C0' => 'Prata',
        '#0057FF' => 'Azul',
        '#FF0000' => 'Vermelho',
        '#FFD700' => 'Amarelo',
        '#008000' => 'Verde',
        '#FF6600' => 'Laranja',
        '#FF69B4' => 'Rosa',
    ],
    'whatsapp_number' => env('WHATSAPP_NUMBER'),

    /*
    | Plano de curadoria: categorias que entram no catálogo da Bella e sua cota máxima.
    | Usado pelo comando catalog:sync-curated. Ajuste as cotas conforme o espaço disponível.
    | Total padrão: ~415 produtos (~500 MB em disco).
    */
    'curated_plan' => [
        'canetas'        => 75,
        'cadernos'       => 50,
        'garrafas'       => 45,
        'canecas'        => 40,
        'mochilas'       => 30,
        'ecobags'        => 30,
        'agendas'        => 17,
        'bloquinhos'     => 3,
        'kits-churrasco' => 25,
        'kits-vinho'     => 15,
        'chaveiros'      => 25,
        'copos'          => 20,
        'bolsas-termicas'=> 20,
        'kits-queijo'    => 13,
        'caixas-de-som'  => 20,
        'moleskine'      => 0,
        'pastas'         => 15,
        'guarda-chuvas'  => 34,
        'necessaires'    => 44,
        'bones'          => 8,
        'mouse-pad'      => 12,
        'porta-cracha'   => 11,
        'sacochila'      => 12,
        'fones-de-ouvido'=> 20,
        'power-bank'     => 15,
        'squeezes'       => 20,
        'almofadas'      => 15,
    ],
];
