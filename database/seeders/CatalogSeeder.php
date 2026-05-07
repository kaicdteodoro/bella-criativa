<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            [
                'name' => 'Linha Premium',
                'slug' => 'linha-premium',
                'description' => 'Peças com acabamento superior, presença editorial e foco em presentes corporativos e ações de marca.',
            ],
            [
                'name' => 'Lançamentos',
                'slug' => 'lancamentos',
                'description' => 'Seleção das novidades que entram primeiro em campanhas, mostruários e ativações comerciais.',
            ],
            [
                'name' => 'Brindes Funcionais',
                'slug' => 'brindes-funcionais',
                'description' => 'Itens de uso recorrente pensados para combinar utilidade, percepção de valor e boa exposição de marca.',
            ],
        ])->mapWithKeys(function (array $attributes) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );

            return [$category->slug => $category];
        });

        $waLink = 'https://wa.me/5516994492382';

        $pages = [
            [
                'title' => 'Bella Criativa',
                'slug' => 'home',
                'template' => 'home',
                'status' => 'published',
                'excerpt' => 'Brindes e produtos personalizados para empresas que se importam com o acabamento.',
                'seo_title' => 'Bella Criativa | Produtos Personalizados',
                'seo_description' => 'Brindes corporativos, kits e produtos personalizados com qualidade artesanal. Sem quantidade mínima em vários produtos. Atendimento pelo WhatsApp.',
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Cada detalhe, do seu jeito.',
                        'position' => 10,
                        'content' => [
                            'eyebrow' => 'Produtos personalizados desde 2014',
                            'text' => 'Brindes, kits e produtos personalizados para empresas que se importam com o acabamento. Sem quantidade mínima em vários produtos.',
                            'primary_label' => 'Ver catálogo',
                            'primary_url' => '/produtos',
                            'secondary_label' => 'Conhecer a Bella',
                            'secondary_url' => '/sobre',
                        ],
                    ],
                    [
                        'type' => 'category_mosaic',
                        'heading' => 'O que você pode personalizar',
                        'position' => 20,
                        'content' => [
                            'text' => '',
                            'categories' => ['linha-premium', 'lancamentos', 'brindes-funcionais'],
                        ],
                    ],
                    [
                        'type' => 'featured_products',
                        'heading' => 'Destaques do catálogo',
                        'position' => 30,
                        'content' => [
                            'text' => '',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'heading' => 'Tem um projeto em mente?',
                        'position' => 40,
                        'content' => [
                            'text' => 'Manda uma mensagem. Sem formulário complicado, sem espera desnecessária.',
                            'primary_label' => 'Falar no WhatsApp',
                            'primary_url' => $waLink,
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Sobre a Bella Criativa',
                'slug' => 'sobre',
                'template' => 'about',
                'status' => 'published',
                'excerpt' => 'Dez anos personalizando marcas — com qualidade artesanal, atendimento direto e sem quantidade mínima em vários produtos.',
                'body' => '<p>A Bella Criativa nasceu em 2014 de uma ideia simples: que produto personalizado de verdade faz diferença. Juliana já trabalhava com sabonetes e tags artesanais quando conheceu Verônica Pauludeto — e juntas transformaram essa ideia em empresa.</p><p>Por sete anos, o foco foi festas infantis. Até que uma cliente abriu um novo caminho: o mercado corporativo. Brindes, kits, produtos com a identidade da empresa do cliente gravada ou impressa com cuidado.</p><p>Hoje a Bella atende empresas de todo o Brasil — de São Paulo ao Amazonas — com o mesmo princípio do início: nenhum pedido é genérico. Cada produto tem a marca de quem pediu.</p>',
                'seo_title' => 'Sobre | Bella Criativa',
                'seo_description' => 'Conheça a história da Bella Criativa: 10 anos personalizando brindes e produtos para empresas em todo o Brasil, com qualidade artesanal e atendimento humanizado.',
                'sections' => [
                    [
                        'type' => 'rich_text',
                        'heading' => 'O que muda quando você trabalha com a gente',
                        'position' => 10,
                        'content' => [
                            'text' => '<p><strong>Sem mínimo em vários produtos.</strong> Você não precisa pedir 200 unidades para ter um brinde com a sua marca.</p><p><strong>Artesanal com escala.</strong> A qualidade de acabamento de um produto feito com atenção — sem abrir mão de prazo.</p><p><strong>10 anos no mercado.</strong> Experiência real com o que funciona em personalização e o que não funciona.</p><p><strong>Atendimento direto.</strong> Você fala com quem executa. Sem intermediários, sem fila de atendimento.</p>',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'heading' => 'Pronto para personalizar?',
                        'position' => 20,
                        'content' => [
                            'text' => 'Manda uma mensagem e a Bella te ajuda a escolher o produto certo para o seu projeto.',
                            'primary_label' => 'Falar no WhatsApp',
                            'primary_url' => $waLink,
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Contato',
                'slug' => 'contato',
                'template' => 'contact',
                'status' => 'published',
                'excerpt' => 'O jeito mais rápido de tirar uma dúvida ou pedir um orçamento é pelo WhatsApp.',
                'body' => '<p>O jeito mais rápido de tirar uma dúvida ou pedir um orçamento é pelo WhatsApp. Se preferir por e-mail, também funciona — mas a resposta costuma ser mais rápida por mensagem.</p><p>A Bella não faz atendimentos presenciais. Todo o atendimento é remoto — e funciona para clientes em todo o Brasil.</p>',
                'seo_title' => 'Contato | Bella Criativa',
                'seo_description' => 'Fale com a Bella Criativa pelo WhatsApp ou Instagram para orçamentos, dúvidas e pedidos de produtos personalizados.',
                'sections' => [
                    [
                        'type' => 'rich_text',
                        'heading' => 'Canais de atendimento',
                        'position' => 10,
                        'content' => [
                            'text' => '<p><strong>WhatsApp:</strong> (16) 99449-2382<br><strong>Instagram:</strong> @bella_dpfc</p>',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Lançamentos',
                'slug' => 'lancamentos',
                'template' => 'launches',
                'status' => 'published',
                'excerpt' => 'Produtos novos, técnicas novas, possibilidades novas.',
                'seo_title' => 'Lançamentos | Bella Criativa',
                'seo_description' => 'Novidades do catálogo Bella Criativa. Produtos recém-chegados para empresas que gostam de ser as primeiras a usar.',
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'O que chegou novo no catálogo.',
                        'position' => 10,
                        'content' => [
                            'eyebrow' => 'Novidades',
                            'text' => 'Produtos novos, técnicas novas, possibilidades novas. Para quem gosta de ser o primeiro a usar.',
                            'primary_label' => 'Ver catálogo completo',
                            'primary_url' => '/produtos',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Linha Premium',
                'slug' => 'linha-premium',
                'template' => 'premium',
                'status' => 'published',
                'excerpt' => 'Para quando o acabamento é o detalhe que importa.',
                'seo_title' => 'Linha Premium | Bella Criativa',
                'seo_description' => 'Seleção premium da Bella Criativa: produtos com materiais superiores e personalização de alta precisão para presentes executivos e brindes de posicionamento.',
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Para quando o acabamento é o detalhe que importa.',
                        'position' => 10,
                        'content' => [
                            'eyebrow' => 'Linha Premium',
                            'text' => 'Seleção de produtos com materiais superiores e personalização de alta precisão. Para presentes executivos, brindes de posicionamento e momentos em que o produto precisa falar pela marca.',
                            'primary_label' => 'Ver catálogo completo',
                            'primary_url' => '/produtos',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $attributes) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                collect($attributes)->except('sections')->all(),
            );

            $page->sections()->delete();

            foreach ($attributes['sections'] as $section) {
                $page->sections()->create($section);
            }
        }
    }
}
