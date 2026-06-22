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

A autenticação por meio do **Laravel Sanctum** é necessária para acessar e executar quase todos os endpoints da API.

1 - Para executar os endpoints no ```Swagger``` da aplicação, acesse o seguinte endereço no navegador:

```
http://localhost:8000/api/documentation
```

![](https://raw.githubusercontent.com/CryptedSnow/api-products/refs/heads/main/public/img/01.png)

2 - Em seguida clique em uma das opções:
- ```AuthController -> /register -> Try it out -> Execute```.
- ```AuthController -> /login -> Try it out -> Execute```.

3 - Com isso o valor do **token** é gerado no ```Response body```, o valor deve ser algo parecido com isso: 

- ```"token": "11|YVwjMIIG62PHMmM0B3oZuDf2GBQyyHG16BmgujZC1d2a7609",```

![](https://raw.githubusercontent.com/CryptedSnow/api-products/refs/heads/main/public/img/02.png)

Pois os dois endpoints mostrados anteriormente são os únicos que são acessíveis para usuários não autenticados.

4 - Copie o valor gerado do **token** sem as aspas (apenas ```11|YVwjMII...```) e siga para o botão ```Authorize 🔒```.

![](https://raw.githubusercontent.com/CryptedSnow/api-products/refs/heads/main/public/img/03.png)

5 - No campo ```Value``` cole o valor do **token** e depois clique no botão ```Authorize```.

![](https://raw.githubusercontent.com/CryptedSnow/api-products/refs/heads/main/public/img/04.png)

6 - Agora todos os endpoints com ícone de cadeado estão acessíveis, pois você realizou a autenticação de usuário através do **Laravel Sanctum**.
