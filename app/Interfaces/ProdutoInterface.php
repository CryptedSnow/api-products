<?php

namespace App\Interfaces;

use App\Models\Produto;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProdutoInterface
{
    public function indexProdutos(int $perPage = 10): LengthAwarePaginator;
    public function findProdutoId(int $id): ?Produto;
    public function createProduto(array $data): Produto;
    public function updateProduto(Produto $produto, array $data): Produto;
    public function deleteProduto(Produto $produto): bool;
    public function searchProdutoNome(string $nome, int $perPage = 10): LengthAwarePaginator;
}
