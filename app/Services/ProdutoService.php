<?php

namespace App\Services;

use App\Models\Produto;
use App\Interfaces\ProdutoInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProdutoService implements ProdutoInterface
{
    public function indexProdutos(int $perPage = 10): LengthAwarePaginator
    {
        return Produto::paginate($perPage);
    }

    public function findProdutoId(int $id): ?Produto
    {
        return Produto::find($id);
    }

    public function createProduto(array $data): Produto
    {
        return Produto::create($data);
    }

    public function updateProduto(Produto $produto, array $data): Produto
    {
        $produto->update($data);
        return $produto->fresh();
    }

    public function deleteProduto(Produto $produto): bool
    {
        return $produto->delete();
    }

    public function searchProdutoNome(string $nome, int $perPage = 10): LengthAwarePaginator
    {
        return Produto::where('nome', 'LIKE', "%{$nome}%")->paginate($perPage);
    }
}
