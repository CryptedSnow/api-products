🖥️ **Preparação do ambiente** 

É necessário ter instalado o **Docker** em seu computador, para criar um ambiente de containers de serviços que são necessários para o funcionamento da aplicação.

1 - Execute os containers:
```
docker-compose up -d
```

2 - Execute o ```composer``` para criar a pasta ```vendor``` da aplicação:
```
docker-compose exec app composer install
```

3 - Crie o arquivo ```.env```:
```
docker-compose exec app cp .env.example .env  
```

4 - Crie a chave encriptada que vai preencher o ```APP_KEY=``` do arquivo ```.env```:
```
docker-compose exec app php artisan key:generate
```

5 - Para criar as ```migrations``` da aplicação, execute o seguinte comando:
```
docker-compose exec app php artisan migrate
```

🔐 **Autenticação** 

A maioria dos endpoints é necessário usar **Laravel Sactum** para conseguir executar os testes.

1 - Para executar os endpoints no ```Swagger``` da aplicação, acesse o seguinte endereço no navegador:

```
http://localhost:8000/api/documentation
```

2 - Em seguida clique em uma das opções:
- ```AuthController -> /register -> Try it out -> Execute```.
- ```AuthController -> /login -> Try it out -> Execute```.

3 - Com isso o valor do **"token"** é revelado no ```Response Body```, o valor deve ser algo parecido com isso: 

- ```"token": "5|g7WtnyKkgO2Lt1EvmcPtiqmNScqVl570Tw8eFmQr17ce4291"```

Pois os dois endpoints mostrados anteriormente são os únicos que são acessíveis para usuários não autenticados.

4 - Copie o valor do **token** sem as aspas (apenas ```5|g7WtnyK...```) e siga para o botão ```Authorize```.

5 - No campo ```Value``` cole o valor do **token** e depois clique no botão ```Authorize```.

6 - Agora todos os endpoints com ícone de cadeado estão acessíveis, pois você realizou a atenticação de usuário.
