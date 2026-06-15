<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class SeedLandingPages extends Command
{
    protected $signature = 'catalog:seed-pages';

    protected $description = 'Cria (ou atualiza) os registros de Page para Lançamentos e Linha Premium';

    public function handle(): int
    {
        $pages = [
            [
                'title'           => 'Lançamentos',
                'slug'            => 'lancamentos',
                'template'        => 'launches',
                'status'          => 'published',
                'excerpt'         => 'Produtos novos, técnicas novas, possibilidades novas.',
                'seo_title'       => 'Lançamentos | Bella Criativa',
                'seo_description' => 'Novidades do catálogo Bella Criativa. Produtos recém-chegados para empresas que gostam de ser as primeiras a usar.',
            ],
            [
                'title'           => 'Linha Premium',
                'slug'            => 'linha-premium',
                'template'        => 'premium',
                'status'          => 'published',
                'excerpt'         => 'Para quando o acabamento é o detalhe que importa.',
                'seo_title'       => 'Linha Premium | Bella Criativa',
                'seo_description' => 'Seleção premium da Bella Criativa: produtos com materiais superiores e personalização de alta precisão para presentes executivos e brindes de posicionamento.',
            ],
        ];

        foreach ($pages as $data) {
            $page = Page::updateOrCreate(['slug' => $data['slug']], $data);
            $this->line("  <info>ok</info>  {$page->title} (slug: {$page->slug})");
        }

        $this->info('Páginas criadas/atualizadas com sucesso.');

        return self::SUCCESS;
    }
}
