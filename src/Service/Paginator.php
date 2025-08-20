<?php
namespace App\Service;

class Paginator
{
    public function paginate(
        array $items,
        int $page = 1,
        int $limit = 10,
        int $statusCode = 200
    ): PaginationResult {
        $total = count($items);
        $page = max($page, 1);
        $limit = max($limit, 1);

        // Calcul des indices
        $startIndex = ($page - 1) * $limit;
        $endIndex = $startIndex + $limit;

        // Si l'index de départ dépasse le total, retourner un tableau vide
        if ($startIndex >= $total) {
            $paginatedData = [];
        } else {
            $paginatedData = array_slice($items, $startIndex, $limit);
        }

        return new PaginationResult(
            $paginatedData,
            $total,
            $page,
            $limit,
            $statusCode
        );
    }
}