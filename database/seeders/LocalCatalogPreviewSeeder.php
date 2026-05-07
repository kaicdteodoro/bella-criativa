<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LocalCatalogPreviewSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            [
                'name' => 'Linha Premium',
                'slug' => 'linha-premium',
                'description' => 'Peças com acabamento superior para presentes corporativos.',
            ],
            [
                'name' => 'Lançamentos',
                'slug' => 'lancamentos',
                'description' => 'Novidades do catálogo para campanhas e ativações.',
            ],
            [
                'name' => 'Brindes Funcionais',
                'slug' => 'brindes-funcionais',
                'description' => 'Itens de uso recorrente com boa exposição de marca.',
            ],
            [
                'name' => 'Escritório',
                'slug' => 'escritorio',
                'description' => 'Produtos para rotina corporativa e kits de onboarding.',
            ],
            [
                'name' => 'Garrafas e Copos',
                'slug' => 'garrafas-copos',
                'description' => 'Opções para uso diário, eventos e ações de relacionamento.',
            ],
        ])->mapWithKeys(function (array $attributes) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );

            return [$category->slug => $category];
        });

        $products = [
            [
                'sku' => 'BC-1001',
                'title' => 'Caderno Executivo Soft Touch',
                'slug' => 'caderno-executivo-soft-touch',
                'status' => 'published',
                'short_description' => 'Caderno com acabamento soft touch e elástico personalizado.',
                'technical_description' => 'Miolo pautado, bolso interno e gravação da marca na capa.',
                'available_colors' => ['#111827', '#8B5E3C', '#C9A227'],
                'materials' => ['couro sintético', 'papel offset'],
                'is_featured' => true,
                'is_launch' => true,
                'is_premium' => true,
                'categories' => ['linha-premium', 'lancamentos', 'escritorio'],
            ],
            [
                'sku' => 'BC-1002',
                'title' => 'Garrafa Térmica Inox 500ml',
                'slug' => 'garrafa-termica-inox-500ml',
                'status' => 'published',
                'short_description' => 'Garrafa inox com tampa rosqueável e personalização a laser.',
                'technical_description' => 'Parede dupla e conservação térmica para uso corporativo.',
                'available_colors' => ['#0F172A', '#D1D5DB', '#DC2626'],
                'materials' => ['inox'],
                'is_featured' => true,
                'is_launch' => false,
                'is_premium' => false,
                'categories' => ['brindes-funcionais', 'garrafas-copos'],
            ],
            [
                'sku' => 'BC-1003',
                'title' => 'Caneta Metálica Premium',
                'slug' => 'caneta-metalica-premium',
                'status' => 'published',
                'short_description' => 'Caneta metálica com estojo para presentes executivos.',
                'technical_description' => 'Gravação a laser no corpo e acabamento acetinado.',
                'available_colors' => ['#1F2937', '#9CA3AF'],
                'materials' => ['metal'],
                'is_featured' => true,
                'is_launch' => false,
                'is_premium' => true,
                'categories' => ['linha-premium', 'escritorio'],
            ],
            [
                'sku' => 'BC-1004',
                'title' => 'Kit Onboarding Corporativo',
                'slug' => 'kit-onboarding-corporativo',
                'status' => 'published',
                'short_description' => 'Kit com caderno, caneta e copo para integração de equipes.',
                'technical_description' => 'Montagem sob demanda com itens personalizáveis por campanha.',
                'available_colors' => ['#2563EB', '#F59E0B', '#10B981'],
                'materials' => ['papel', 'inox', 'plástico'],
                'is_featured' => true,
                'is_launch' => true,
                'is_premium' => false,
                'categories' => ['lancamentos', 'brindes-funcionais', 'escritorio'],
            ],
            [
                'sku' => 'BC-1005',
                'title' => 'Copo Térmico Travel',
                'slug' => 'copo-termico-travel',
                'status' => 'published',
                'short_description' => 'Copo térmico para ações de marca e uso diário.',
                'technical_description' => 'Tampa com vedação e área ampla para personalização.',
                'available_colors' => ['#F3F4F6', '#111827', '#BE123C'],
                'materials' => ['inox', 'plástico'],
                'is_featured' => false,
                'is_launch' => true,
                'is_premium' => false,
                'categories' => ['lancamentos', 'garrafas-copos', 'brindes-funcionais'],
            ],
            [
                'sku' => 'BC-1006',
                'title' => 'Agenda Costurada 2026',
                'slug' => 'agenda-costurada-2026',
                'status' => 'published',
                'short_description' => 'Agenda corporativa com capa rígida e personalização discreta.',
                'technical_description' => 'Ideal para kits de fim de ano e presentes institucionais.',
                'available_colors' => ['#7C2D12', '#0F172A'],
                'materials' => ['papel', 'couro sintético'],
                'is_featured' => false,
                'is_launch' => false,
                'is_premium' => true,
                'categories' => ['linha-premium', 'escritorio'],
            ],
        ];

        foreach ($products as $attributes) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $attributes['sku']],
                collect($attributes)->except('categories')->all(),
            );

            $product->categories()->sync(
                collect($attributes['categories'])
                    ->map(fn (string $slug) => $categories[$slug]->id)
                    ->all(),
            );
        }
    }
}
