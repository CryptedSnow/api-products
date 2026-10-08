<?php

use App\Models\{Produto, User};
use Laravel\Sanctum\Sanctum;

function dadosProduto(array $override = []): array
{
    return array_merge([
        'nome' => 'Notebook Gamer',
        'valor' => 3000,
        'quantidade' => 10,
        'fora_validade' => false,
    ], $override);
}

describe('Rotas sem autenticação', function () {

    it('Retorna 401 em todas as rotas de produto', function (string $metodo, string $uri) {
        $this->json($metodo, $uri)->assertUnauthorized();
    })->with([
        'index'   => ['GET',    '/api/produtos'],
        'store'   => ['POST',   '/api/produtos'],
        'buscar'  => ['GET',    '/api/buscar-produtos'],
        'show'    => ['GET',    '/api/produtos/1'],
        'update'  => ['PUT',    '/api/produtos/1'],
        'destroy' => ['DELETE', '/api/produtos/1'],
    ]);
});

describe('Rotas com autenticação', function () {

    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create());
    });

    describe('Listagem de produtos', function () {

        it('Listar produtos com status 200', function () {
            Produto::factory()->count(3)->create();

            $this->getJson('/api/produtos')
                ->assertOk()
                ->assertJsonCount(3, 'data');
        });

        it('Retornar erro 404 com mensagem quando não há produtos', function () {
            $this->getJson('/api/produtos')
                ->assertNotFound()
                ->assertExactJson(['message' => 'Nenhum produto foi encontrado.']);
        });

        it('Paginação de resultados de 10 em 10', function () {
            Produto::factory()->count(11)->create();

            $this->getJson('/api/produtos')
                ->assertOk()
                ->assertJsonCount(10, 'data');

            $this->getJson('/api/produtos?page=2')
                ->assertOk()
                ->assertJsonCount(1, 'data');
        });

        it('Não listar produtos excluídos (soft delete)', function () {
            Produto::factory()->count(2)->create();
            Produto::factory()->create()->delete();

            $this->getJson('/api/produtos')
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });
    });

    describe('Criação de produtos', function () {

        it('Criar produto e retornar mensagem', function () {
            $this->postJson('/api/produtos', dadosProduto())
                ->assertCreated()
                ->assertJsonPath('message', 'Produto Notebook Gamer foi criado.')
                ->assertJsonStructure(['message', 'data'])
                ->assertJsonPath('data.nome', 'Notebook Gamer');

            $this->assertDatabaseHas('produtos', [
                'nome'          => 'Notebook Gamer',
                'quantidade'    => 10,
                'fora_validade' => false,
            ]);
        });

        it('Salvar fora_validade como verdadeiro', function () {
            $this->postJson('/api/produtos', dadosProduto(['fora_validade' => true]))
                ->assertCreated();

            $this->assertDatabaseHas('produtos', [
                'nome'          => 'Notebook Gamer',
                'fora_validade' => true,
            ]);
        });

        it('Retornar erro 422 quando os campos obrigatórios não são enviados', function () {
            $this->postJson('/api/produtos', [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['nome', 'valor', 'quantidade', 'fora_validade']);

            $this->assertDatabaseCount('produtos', 0);
        });

        it('Validar cada campo obrigatório individualmente', function (string $campo) {
            $dados = dadosProduto();
            unset($dados[$campo]);

            $this->postJson('/api/produtos', $dados)
                ->assertUnprocessable()
                ->assertJsonValidationErrors([$campo]);
        })->with(['nome', 'valor', 'quantidade', 'fora_validade']);
    });

    describe('Buscar produtos', function () {

        it('Retornar erro 404 quando o parâmetro nome não é informado', function () {
            $this->getJson('/api/buscar-produtos')
                ->assertNotFound()
                ->assertExactJson(['message' => 'O campo nome está vazio.']);
        });

        it('Retornar erro 404 quando o parâmetro nome está vazio', function () {
            $this->getJson('/api/buscar-produtos?nome=')
                ->assertNotFound()
                ->assertExactJson(['message' => 'O campo nome está vazio.']);
        });

        it('Retornar produtos que correspondem ao nome', function () {
            Produto::factory()->create(['nome' => 'Notebook Gamer']);
            Produto::factory()->create(['nome' => 'Mouse Sem Fio']);

            $this->getJson('/api/buscar-produtos?nome=Notebook')
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.nome', 'Notebook Gamer');
        });

        it('Retornar erro 404 com mensagem quando nada é encontrado', function () {
            Produto::factory()->create(['nome' => 'Notebook Gamer']);

            $this->getJson('/api/buscar-produtos?nome=Inexistente')
                ->assertNotFound()
                ->assertExactJson(['message' => 'Nenhum Inexistente foi encontrado.']);
        });
    });

    describe('Visualização de produtos', function () {

        it('Retornar produto pelo ID', function () {
            $produto = Produto::factory()->create(['nome' => 'Notebook Gamer']);

            $this->getJson("/api/produtos/{$produto->id}")
                ->assertOk()
                ->assertJsonPath('data.nome', 'Notebook Gamer');
        });

        it('Retornar erro 404 com mensagem para ID inexistente', function () {
            $this->getJson('/api/produtos/9999')
                ->assertNotFound()
                ->assertExactJson(['message' => 'Produto ID 9999 não foi encontrado.']);
        });

        it('Retornar erro 404 para produto excluído (soft delete)', function () {
            $produto = Produto::factory()->create();
            $produto->delete();

            $this->getJson("/api/produtos/{$produto->id}")
                ->assertNotFound();
        });
    });

    describe('Atualização de produtos', function () {

        it('Atualizar produto e retorna 202 com mensagem', function () {
            $produto = Produto::factory()->create(['nome' => 'Antigo']);

            $this->putJson("/api/produtos/{$produto->id}", dadosProduto([
                'nome'  => 'Notebook Atualizado',
                'valor' => 4999.90,
            ]))->assertAccepted()
               ->assertJsonPath('message', 'Produto Notebook Atualizado foi atualizado.')
               ->assertJsonPath('data.nome', 'Notebook Atualizado');

            $this->assertDatabaseHas('produtos', [
                'id'   => $produto->id,
                'nome' => 'Notebook Atualizado',
            ]);
        });

        it('Retornar erro 404 com mensagem para ID inexistente', function () {
            $this->putJson('/api/produtos/9999', dadosProduto())
                ->assertNotFound()
                ->assertExactJson(['message' => 'Produto ID 9999 não foi encontrado.']);
        });
    });

    describe('Exclusão de produtos', function () {

        it('Excluir produto e retorna 200 com mensagem', function () {
            $produto = Produto::factory()->create(['nome' => 'Notebook Gamer']);

            $this->deleteJson("/api/produtos/{$produto->id}")
                ->assertOk()
                ->assertExactJson(['message' => 'Produto Notebook Gamer foi deletado.']);

            $this->assertSoftDeleted($produto);
        });

        it('Retornar erro 404 com mensagem para ID inexistente', function () {
            $this->deleteJson('/api/produtos/9999')
                ->assertNotFound()
                ->assertExactJson(['message' => 'Produto ID 9999 não foi encontrado.']);
        });

        it('Retornar erro 404 ao excluir um produto já excluído', function () {
            $produto = Produto::factory()->create();
            $produto->delete();

            $this->deleteJson("/api/produtos/{$produto->id}")
                ->assertNotFound();
        });
    });
});
