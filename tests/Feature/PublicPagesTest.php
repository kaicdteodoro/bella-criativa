<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_with_featured_products_and_categories(): void
    {
        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'template' => 'home',
            'status' => 'published',
        ]);

        Category::query()->create([
            'name' => 'Copos',
            'slug' => 'copos',
            'description' => 'Linha de copos.',
        ]);

        Product::query()->create([
            'sku' => 'SKU-501',
            'title' => 'Copo de Viagem',
            'slug' => 'copo-de-viagem',
            'status' => 'published',
            'featured_image' => 'media/featured/copo-de-viagem.webp',
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Cada detalhe,');
        $response->assertSee('Copos');
        $response->assertSee('Copo de Viagem');
    }

    public function test_home_page_falls_back_to_recent_published_products_when_no_featured_items_exist(): void
    {
        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'template' => 'home',
            'status' => 'published',
        ]);

        Product::query()->create([
            'sku' => 'SKU-504',
            'title' => 'Agenda Executiva',
            'slug' => 'agenda-executiva',
            'status' => 'published',
            'featured_image' => 'media/featured/agenda-executiva.webp',
            'is_featured' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Agenda Executiva');
    }

    public function test_about_page_renders_when_page_exists(): void
    {
        Page::query()->create([
            'title' => 'Sobre',
            'slug' => 'sobre',
            'template' => 'about',
            'status' => 'published',
        ]);

        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSee('Nossa história');
    }

    public function test_contact_page_renders_when_page_exists(): void
    {
        Page::query()->create([
            'title' => 'Contato',
            'slug' => 'contato',
            'template' => 'contact',
            'status' => 'published',
        ]);

        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('Fale com a Bella');
    }

    public function test_launches_page_renders_products(): void
    {
        Page::query()->create([
            'title' => 'Lançamentos',
            'slug' => 'lancamentos',
            'template' => 'launches',
            'status' => 'published',
        ]);

        Product::query()->create([
            'sku' => 'SKU-502',
            'title' => 'Kit Executivo',
            'slug' => 'kit-executivo',
            'status' => 'published',
            'featured_image' => 'media/featured/kit-executivo.webp',
            'is_launch' => true,
        ]);

        $response = $this->get(route('launches'));

        $response->assertOk();
        $response->assertSee('O que chegou');
        $response->assertSee('Kit Executivo');
    }

    public function test_premium_page_renders_products(): void
    {
        Page::query()->create([
            'title' => 'Linha Premium',
            'slug' => 'linha-premium',
            'template' => 'premium',
            'status' => 'published',
        ]);

        Product::query()->create([
            'sku' => 'SKU-503',
            'title' => 'Caneta Premium',
            'slug' => 'caneta-premium',
            'status' => 'published',
            'featured_image' => 'media/featured/caneta-premium.webp',
            'is_premium' => true,
        ]);

        $response = $this->get(route('premium'));

        $response->assertOk();
        $response->assertSee('Linha Premium');
        $response->assertSee('Caneta Premium');
    }
}
