<?php

namespace App\Service;

class PaginationResult
{
    private int $statusCode;
    private array $data;
    private int $total;
    private int $page;
    private int $limit;
    private int $totalPages;

    public function __construct(
        array $data,
        int $total,
        int $page,
        int $limit,
        int $statusCode = 200
    ) {
        $this->data = $data;
        $this->total = $total;
        $this->page = $page;
        $this->limit = $limit;
        $this->statusCode = $statusCode;
        $this->totalPages = (int) ceil($total / max($limit, 1));
    }

    public function toArray(): array
    {
        return [
            'statusCode' => $this->statusCode,
            'data' => $this->data,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
            'totalPages' => $this->totalPages,
        ];
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function getTotal(): int
    {
        return $this->total;
    }
    public function getPage(): int
    {
        return $this->page;
    }
    public function getLimit(): int
    {
        return $this->limit;
    }
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
}
