<?php

namespace App\Services\Import;

class ProductQualityEvaluator
{
    public function evaluate(CuratedProductData $product, MediaData $media): ProductQualityEvaluation
    {
        $score = 100;
        $notes = [];
        $blocked = false;

        if (mb_strlen($product->title) < 8) {
            $score -= 20;
            $blocked = true;
            $notes[] = 'Titulo muito curto para publicacao automatica.';
        }

        if (! $product->category) {
            $score -= 30;
            $blocked = true;
            $notes[] = 'Categoria ausente.';
        }

        if (! $product->shortDescription) {
            $score -= 20;
            $blocked = true;
            $notes[] = 'Descricao curta ausente.';
        } elseif (mb_strlen(strip_tags($product->shortDescription)) < 50) {
            $score -= 10;
            $notes[] = 'Descricao curta abaixo do ideal.';
        }

        if (! $product->technicalDescription) {
            $score -= 10;
            $notes[] = 'Descricao tecnica ausente.';
        }

        if ($media->featured === '' || count($media->gallery) === 0) {
            $score -= 40;
            $blocked = true;
            $notes[] = 'Produto sem imagem valida.';
        } elseif (count($media->gallery) < 2) {
            $score -= 5;
            $notes[] = 'Galeria com poucas imagens.';
        }

        if ($product->slug === '') {
            $score -= 20;
            $blocked = true;
            $notes[] = 'Slug invalido.';
        }

        $score = max(0, min(100, $score));
        $ready = ! $blocked && $score >= 70;

        if ($ready && $notes === []) {
            $notes[] = 'Curadoria automatica concluida sem pendencias.';
        }

        return new ProductQualityEvaluation(
            score: $score,
            curationStatus: $ready ? 'ready' : 'blocked',
            publicationStatus: $ready ? 'published' : 'draft',
            notes: $notes,
        );
    }
}
