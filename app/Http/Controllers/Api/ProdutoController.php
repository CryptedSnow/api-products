<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{ProdutoStoreRequest, ProdutoUpdateRequest};
use App\Http\Resources\ProdutoResource;
use App\Interfaces\ProdutoInterface;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class ProdutoController extends Controller
{
    public function __construct(private ProdutoInterface $produtoInterface) {}

    #[OA\Get(
        path: '/produtos',
        security: [['bearerAuth' => []]],
        summary: 'Listar todos os produtos',
        tags: ['ProdutoController'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Produtos listados'),
            new OA\Response(response: 401, description: 'Usuário não autenticado'),
            new OA\Response(response: 404, description: 'Nenhum produto encontrado'),
        ]
    )]
    public function index(): AnonymousResourceCollection | JsonResponse
    {
        $produtos = $this->produtoInterface->indexProdutos(10);

        if ($produtos->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum produto foi encontrado.'
            ], Response::HTTP_NOT_FOUND);
        }

        return ProdutoResource::collection($produtos);
    }

    #[OA\Post(
        path: '/produtos',
        security: [['bearerAuth' => []]],
        summary: 'Criar um novo produto',
        tags: ['ProdutoController'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nome', 'valor', 'quantidade', 'fora_validade'],
                properties: [
                    new OA\Property(property: 'nome',  type: 'string',  example: 'Notebook Gamer'),
                    new OA\Property(property: 'valor', type: 'number',  format: 'float', example: 3000),
                    new OA\Property(property: 'quantidade', type: 'integer', example: 10),
                    new OA\Property(property: 'fora_validade', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Produto criado'),
            new OA\Response(response: 401, description: 'Usuário não autenticado'),
            new OA\Response(response: 422, description: 'Erro de validação'),
        ]
    )]
    public function store(ProdutoStoreRequest $request): JsonResponse
    {
        $validacoes = $request->validated();

        $produto = $this->produtoInterface->createProduto($validacoes);

        return response()->json([
            'message' => "Produto $produto->nome foi criado.",
            'data'    => new ProdutoResource($produto)
        ], Response::HTTP_CREATED);
    }

    #[OA\Get(
        path: '/buscar-produtos',
        security: [['bearerAuth' => []]],
        summary: 'Buscar produtos por nome',
        tags: ['ProdutoController'],
        parameters: [
            new OA\Parameter(
                name: 'nome',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Resultados encontrados'),
            new OA\Response(response: 401, description: 'Usuário não autenticado'),
            new OA\Response(response: 404, description: 'Nenhum resultado encontrado'),
        ]
    )]
    public function buscarProduto(Request $request): AnonymousResourceCollection | JsonResponse
    {
        $nomeProduto = $request->query('nome');

        if (!$nomeProduto) {
            return response()->json([
                'message' => 'O campo nome está vazio.'
            ], Response::HTTP_NOT_FOUND);
        }

        $produtos = $this->produtoInterface->searchProdutoNome($nomeProduto);

        if ($produtos->isEmpty()) {
            return response()->json([
                'message' => "Nenhum $nomeProduto foi encontrado."
            ], Response::HTTP_NOT_FOUND);
        }

        return ProdutoResource::collection($produtos);
    }

    #[OA\Get(
        path: '/produtos/{id}',
        security: [['bearerAuth' => []]],
        summary: 'Mostrar produto por ID',
        tags: ['ProdutoController'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Produto encontrado'),
            new OA\Response(response: 401, description: 'Usuário não autenticado'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
        ]
    )]
    public function show(int $id): ProdutoResource | JsonResponse
    {
        $produto = $this->produtoInterface->findProdutoId($id);

        if (!$produto) {
            return response()->json([
                'message' => "Produto ID $id não foi encontrado."
            ], Response::HTTP_NOT_FOUND);
        }

        return new ProdutoResource($produto);
    }

    #[OA\Put(
        path: '/produtos/{id}',
        security: [['bearerAuth' => []]],
        summary: 'Atualizar produto',
        tags: ['ProdutoController'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nome',  type: 'string', example: 'Notebook Gamer'),
                    new OA\Property(property: 'valor', type: 'number', format: 'float', example: 4999.90),
                    new OA\Property(property: 'quantidade', type: 'integer', example: 10),
                    new OA\Property(property: 'fora_validade', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 202, description: 'Produto atualizado'),
            new OA\Response(response: 401, description: 'Usuário não autenticado'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
        ]
    )]
    public function update(ProdutoUpdateRequest $request, int $id): JsonResponse
    {
        $produto = $this->produtoInterface->findProdutoId($id);

        if (!$produto) {
            return response()->json([
                'message' => "Produto ID $id não foi encontrado."
            ], Response::HTTP_NOT_FOUND);
        }

        $validacoes = $request->validated();

        $produto = $this->produtoInterface->updateProduto($produto, $validacoes);

        return response()->json([
            'message' => "Produto $produto->nome foi atualizado.",
            'data'    => new ProdutoResource($produto)
        ], Response::HTTP_ACCEPTED);
    }

    #[OA\Delete(
        path: '/produtos/{id}',
        security: [['bearerAuth' => []]],
        summary: 'Deletar produto',
        tags: ['ProdutoController'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Produto deletado'),
            new OA\Response(response: 401, description: 'Usuário não autenticado'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $produto = $this->produtoInterface->findProdutoId($id);

        if (!$produto) {
            return response()->json([
                'message' => "Produto ID $id não foi encontrado."
            ], Response::HTTP_NOT_FOUND);
        }

        $this->produtoInterface->deleteProduto($produto);

        return response()->json([
            'message' => "Produto $produto->nome foi deletado."
        ], Response::HTTP_OK);
    }

}
