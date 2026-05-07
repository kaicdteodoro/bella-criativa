<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Funde categorias equivalentes a “Acessórios de Escrita” na categoria Canetas (slug pens).
     */
    public function up(): void
    {
        $pens = Category::query()->firstOrCreate(
            ['slug' => 'pens'],
            ['name' => 'Canetas'],
        );

        $pens->forceFill(['name' => 'Canetas'])->save();

        $badCategories = Category::query()
            ->whereKeyNot($pens->getKey())
            ->where(function ($query): void {
                $query->where('slug', 'acessorios-de-escrita')
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower('Acessórios de Escrita')])
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', ['acessorios de escrita']);
            })
            ->get();

        foreach ($badCategories as $bad) {
            $productIds = $bad->products()->pluck('id');

            foreach ($productIds as $productId) {
                $bad->products()->detach($productId);

                $product = Product::query()->find($productId);

                if ($product && ! $product->categories()->whereKey($pens->id)->exists()) {
                    $product->categories()->attach($pens->id);
                }
            }

            $bad->delete();
        }
    }

    public function down(): void
    {
        // Irreversível sem snapshot dos vínculos antigos.
    }
};
