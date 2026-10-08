<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function dadosAutenticacao(array $override = []): array
{
    return array_merge([
        'name' => 'User test',
        'email' => 'user@email.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $override);
}

describe('Registrar usuário', function () {

    it('Criar usuário e retorna token Bearer com status 201', function () {
        $this->postJson('/api/register', dadosAutenticacao())
            ->assertCreated()
            ->assertJsonStructure(['message', 'user', 'token', 'token_type'])
            ->assertJsonPath('message', 'Usuário User test criado com sucesso!')
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseHas('users', [
            'name'  => 'User test',
            'email' => 'user@email.com',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    });

    it('Não armazenar a senha em texto puro', function () {
        $this->postJson('/api/register', dadosAutenticacao())->assertCreated();

        $user = User::where('email', 'user@email.com')->first();

        expect($user->password)->not->toBe('password')
            ->and(Hash::check('password', $user->password))->toBeTrue();
    });

    it('Retorna um token válido para acessar rotas protegidas', function () {
        $token = $this->postJson('/api/register', dadosAutenticacao())
            ->assertCreated()
            ->json('token');

        $this->withToken($token)
            ->getJson('/api/profile')
            ->assertOk();
    });

    it('Retornar erro 422 quando os campos obrigatórios não são enviados', function () {
        $this->postJson('/api/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });

    it('Retornar erro 422 para e-mail já cadastrado (unique:users)', function () {
        User::factory()->create(['email' => 'user@email.com']);

        $this->postJson('/api/register', dadosAutenticacao())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('Validar regras de cada campo', function (array $override, string $campo) {
        $this->postJson('/api/register', dadosAutenticacao($override))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$campo]);
    })->with([
        'nome acima de 255 caracteres' => [['name' => str_repeat('a', 256)], 'name'],
        'e-mail inválido' => [['email' => 'nao-e-email'], 'email'],
        'e-mail acima de 255' => [['email' => str_repeat('a', 250) . '@e.com'], 'email'],
        'senha com menos de 6' => [['password' => '12345', 'password_confirmation' => '12345'], 'password'],
        'confirmação diferente' => [['password_confirmation' => 'outra-senha'], 'password'],
        'confirmação ausente' => [['password_confirmation' => null], 'password'],
    ]);
});

describe('Login de usuário', function () {

    beforeEach(function () {
        $this->user = User::factory()->create([
            'name'     => 'User test',
            'email'    => 'user@email.com',
            'password' => Hash::make('password'),
        ]);
    });

    it('Autenticação com credenciais corretas e retorna token Bearer', function () {
        $this->postJson('/api/login', [
            'email'    => 'user@email.com',
            'password' => 'password',
        ])->assertOk()
          ->assertJsonStructure(['message', 'user', 'token', 'token_type'])
          ->assertJsonPath('message', 'User test realizou login!')
          ->assertJsonPath('token_type', 'Bearer');
    });

    it('Revogar tokens anteriores e gera apenas um novo', function () {
        $this->user->createToken('antigo-1');
        $this->user->createToken('antigo-2');
        expect($this->user->tokens()->count())->toBe(2);

        $this->postJson('/api/login', [
            'email'    => 'user@email.com',
            'password' => 'password',
        ])->assertOk();

        expect($this->user->tokens()->count())->toBe(1);
    });

    it('Retornar erro 422 com mensagem personalizada para senha incorreta', function () {
        $this->postJson('/api/login', [
            'email'    => 'user@email.com',
            'password' => 'senha-errada',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['email'])
          ->assertJsonPath('errors.email.0', 'As credenciais fornecidas estão incorretas.');
    });

    it('Retornar erro 422 para e-mail inexistente', function () {
        $this->postJson('/api/login', [
            'email'    => 'naoexiste@email.com',
            'password' => 'password',
        ])->assertUnprocessable()
          ->assertJsonPath('errors.email.0', 'As credenciais fornecidas estão incorretas.');
    });

    it('Não gerar token quando as credenciais são inválidas', function () {
        $this->postJson('/api/login', [
            'email'    => 'user@email.com',
            'password' => 'senha-errada',
        ])->assertUnprocessable();

        expect($this->user->tokens()->count())->toBe(0);
    });

    it('Validar campos obrigatórios e o formato do e-mail', function (array $dados, array $erros) {
        $this->postJson('/api/login', $dados)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($erros);
    })->with([
        'sem campos'      => [[], ['email', 'password']],
        'e-mail inválido' => [['email' => 'xxx', 'password' => 'password'], ['email']],
        'sem senha'       => [['email' => 'user@email.com'], ['password']],
    ]);
});

describe('Logout de usuário', function () {

    it('Remover apenas o token usado na requisição', function () {
        $user = User::factory()->create(['name' => 'User test']);
        $tokenAtual = $user->createToken('atual')->plainTextToken;
        $user->createToken('outro-dispositivo');

        $this->withToken($tokenAtual)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'User test fez logout!');

        expect($user->tokens()->count())->toBe(1)
            ->and($user->tokens()->first()->name)->toBe('outro-dispositivo');
    });

    it('Retornar erro 401 sem autenticação', function () {
        $this->postJson('/api/logout')->assertUnauthorized();
    });

    it('Retornar erro 401 com token inválido', function () {
        $this->withToken('999|token-invalido')
            ->postJson('/api/logout')
            ->assertUnauthorized();
    });
});


describe('Perfil de usuário', function () {

    it('Retornar o perfil do usuário autenticado', function () {
        $user  = User::factory()->create(['email' => 'user@email.com']);
        $token = $user->createToken('teste')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonStructure(['user'])
            ->assertJsonPath('user.email', 'user@email.com'); // depende do UserResource
    });

    it('Retornar erro 401 sem autenticação', function () {
        $this->getJson('/api/profile')->assertUnauthorized();
    });

});
