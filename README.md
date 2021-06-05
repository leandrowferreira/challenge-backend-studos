# StudoSlug URL Shortener

Implementação de serviço de encurtar URL como aplicação de candidato a vaga de desenvolvedor na Studos.

## Sumário

  - [O Desafio](#o-desafio)
  - [Funcionalidades](#funcionalidades)
  - [Instalação](#instalação)
    - [Problemas comuns na instalação](#problemas-comuns-na-instalação)
  - [Utilização](#utilização)
    - [Gerando uma URL encurtada](#gerando-uma-url-encurtada)
    - [Acessando uma URL a partir da sua versão encurtada](#acessando-uma-url-a-partir-da-sua-versão-encurtada)
  - [Configurações](#configurações)
    - [URL_CHECK_BEFORE](#url_check_before)
    - [URL_ALLOW_MULTIPLE](#url_allow_multiple)
    - [RENOVATE_ON_ACCESS](#renovate_on_access)
    - [URL_VALID_DAYS](#url_valid_days)
  - [Documentação do *entry point*](#documentação-do-entry-point)
  - [Github Actions](#github-actions)
  - [Detalhamento técnico](#detalhamento-técnico)
    - [Ambiente](#ambiente)
    - [Framework](#framework)
    - [Banco de dados](#banco-de-dados)
    - [PHP Standard Recommendations (PSRs)](#php-standard-recommendations-psrs)
    - [O Model `Url`](#o-model-url)
    - [Testes](#testes)
  - [Limitações](#limitações)
  - [Recursos](#recursos)
  - [Créditos](#créditos)



## O Desafio

O desafio, proposto pela [Studos](https://www.studos.com.br), tem como objetivo avaliar a capacidade do candidato à vaga de desenvolvedor backend/full-stack. Os requisitos detalhados estão [descritos aqui](https://github.com/studos-software/desafios-backend/blob/main/desafios/encurtador-de-url.md).

A aplicação não se trata de uma API REST perfeita, pois, como descrito nos requisitos, ocorre um **redirecionamento** na chamada de acesso à URL encurtada. Uma API forneceria apenas respostas baseadas em códigos HTTP, além de conteúdo em JSON ou XML. Em vez do comportamento descritos nas especificações do projeto, o papel de redirecionamento poderia ser desempenhado por uma *SPA* que consumisse uma API, mas esta configuração está além do escopo deste projeto, cujo foco parece ser tão-somente o desenvolvimento *backend*.

## Funcionalidades

- Dois pontos de entrada:
    - Criar URL encurtada
    - Acessar URL encurtada
- Verificação da validade da URL
- Verificação de URL já presente
- URL completas (incluindo caminhos)


## Instalação

O procedimento descrito a seguir pressupõe o uso do ambiente **Linux** e a distribuição **Ubuntu** ou equivalente. Para outros ambientes, serão necessários alguns ajustes, sobretudo relacionados à linha de comando.

A instalação requer a presença do **Docker** e do **Docker Compose**. Caso não haja estes pré-requisitos, mais informações de como proceder com sua instalação podem ser obtidas [aqui](https://docs.docker.com/engine/install/) e [aqui](https://docs.docker.com/compose/install/).

É importante ter o **git** instalado na máquina. No **Ubuntu** e em outras instalações, é possível instalá-lo com o seguinte comando:

```bash
$ sudo apt install -y git
```

O primeiro passo é **clonar** o repositório com o projeto:

```bash
$ git clone https://github.com/leandrowferreira/challenge-backend-studos
```

> Caso não haja a possibilidade de usar o comando `git`, o repositório completo pode ser obtido [aqui](https://github.com/leandrowferreira/challenge-backend-studos/archive/refs/heads/main.zip), descompactado no computador local e o diretório principal renomeado para `challenge-backend-studos`.


Agora, é necessário montar o ambiente de desenvolvimento baseador em **Docker**:

```bash
$ cd challenge-backend-studos
$ docker-compose build
```
Este procedimento poderá demorar alguns minutos na primeira vez. Ele será o responsável por configurar os contêiners e a rede utilizados no projeto. Informações técnicas são apresentadas mais adiante nesta documentação.

Após a conclusão da montagem, deve-se usar o **Docker Compose** para *levantar* o ambiente:

```bash
$ docker-compose up -d
```
A opção `-d` permite a sua execução como um *daemon*, ou seja, em segundo plano, evitando que a janela do terminal fique bloqueada enquanto o ambiente está em execução.

Agora, é necessário executar alguns procedimentos padrão do framework utilizado. Os comandos necessários estão dentro de um arquivo `.sh`, e deve ser executado desta forma:

```bash
$ .docker/init.sh
```

Este script é o responsável por quatro tarefas básicas:

- instalar as dependências do Composer;
- ajustar os direitos de escrita no diretório `storage`;
- criar o arquivo de definição de ambiente `.env`;
- executar as *migrações* do banco de dados.

Neste momento, o ambiente já deve estar ativo e funcionando. Para encerrar o ambiente, é utilizado o comando a seguir:

```bash
$ docker-compose down
```


### Problemas comuns na instalação

É importante notar que os contêiners **Docker** usam as portas `3306` e `8080` do host. Se estas portas já estão sendo utilizadas por outros serviços (em geral, o servidor MySQL e/ou o Apache e/ou Nginx), será necessário realizar uma das duas atividades:

#### 1. Desativar os serviços no host

Isto é fácilmente executado com um ou mais dos seguintes comandos:

```bash
$ sudo service mysql stop
$ sudo service apache2 stop
$ sudo service nginx stop
```

Algumas vezes, servidores embutidos utilizam a porta `8080`. São exemplos os servidores ativados pelo comando `php -S` ou pelo comando do **Laravel** `php artisan serve`. Pode ser necessário se certificar se há algum destes servidores em execução na porta `8080`.

#### 2. Alterar as portas usadas

As portas usadas nos serviços são definidas no arquivo `docker-compose.yml`. É importante frisar que se a porta do MySQL for alterada, é necessário ajustar o arquivo `.env` para refletir esta alteração.

## Utilização

O *endpoint* da aplicação é configurado por padrão para `http://localhost:8080/`.

A aplicação possui duas funcionalidades: **geração de URL encurtada** e **acesso à URL**, de acordo com a tabela a seguir:


Método | URI           | Ação                 | Exemplo
-------|---------------|----------------------|-------------------
POST   | /{url}        | URL a ser encurtada  | POST http://localhost:8080/laravel.com/api/8.x/Illuminate/Config/Repository.html
GET    | /{slug}       | Acessa URL encurtada | GET http://localhost:8080/abc123ab


### Gerando uma URL encurtada

Para gerar uma URL encurtada, é necessário enviar uma requisição `POST` para o *endpoint*. Isto não pode ser feito diretamente a partir no navegador, mas na linha de comando, pode ser feito usando o `curl`. Por exemplo, para encurtar a URL `laravel.com/api/8.x/Illuminate/Config/Repository.html` da seguinte maneira:

```bash
$ curl --location --request POST 'http://localhost:8080/laravel.com/api/8.x/Illuminate/Config/Repository.html'
```

Esta chamada retorna uma URL encurtada completa, incluindo a base. Um exemplo da resposta seria `http://localhost:8080/abc123ab`.

O código `HTTP` do retorno pode conter um dos seguintes valores:
 - `201` (created) caso a URL esteja sendo encurtada pela primeira vez ou a opção `URL_ALLOW_MULTIPLE` esteja ativada
 - `200` (OK) caso a URL já exista e a opção `URL_ALLOW_MULTIPLE` esteja desativada
 - `422` (Unprocessable Entity) caso a opção `URL_CHECK_BEFORE` esteja ativada e a URL solicitada não seja válida

 As opções citadas acima serão abordada em detalhes adiante.

Outra forma de realizar chamadas post é usando o [**Postman**](https://www.postman.com/) ou, ainda, o [**Thunder Client**](https://www.thunderclient.io/), uma extensão do [**Visual Studio Code**](https://code.visualstudio.com/). Esta extensão é muito útil, pois mantém os testes de chamada no mesmo ambiente de desenvolvimento, caso este seja o Visual Studio Code.



### Acessando uma URL a partir da sua versão encurtada

A URL encurtada retornada a partir do item anterior pode ser inserida diretamente no campo de endereço do navegador. É importante frisar que se a chamada for realizada a partir de um programa de testes de APIs, como o **Postman**, citado no item anterior, um comportamento indesejado irá se manifestar e a URL original será carregada na tela do programa. Como dito anteriormente, Esta aplicação não se trata de uma API.


## Configurações

O arquivo `.env` possui as configurações de todo o ambiente, dentre as quais a conexão com banco de dados, o ambiente (desenvolvimento ou produção) e quatro outras configurações referentes ao funcionamento da aplicação que merecem destaque:

Configuração           | Valores | Descrição
-----------------------|---------|-----------
**URL_CHECK_BEFORE**   | 0 ou 1  | Determina o sistema deve verificar se a URL existe e está respondendo antes de encurtá-la
**URL_ALLOW_MULTIPLE** | 0 ou 1  | Determina se múltiplas solicitações de encurtamento da mesma URL devem retornar resultados diferentes
**RENOVATE_ON_ACCESS** | 0 ou 1  | Determina se o prazo de validade é renovado a cada acesso
**URL_VALID_DAYS**     | 0 ou 1  | Determina o prazo de validade (em dias) de uma URL encurtada

### URL_CHECK_BEFORE

Se esta opção estiver ativa (com valor `1`), uma chamada à URL original é feita pelo sistema antes do encurtamento. Se esta chamada retornar um código `HTTP` maior ou igual a `300`, a URL é considerada inválida e o encurtamento não ocorre. No lugar dele, o sistema retornará ao usuário um código `HTTP` `422` e a mensagem `URL to shorten is invalid`.

Esta chamada pode aumentar o tempo de resposta do serviço, uma vez que dependerá do tempo de resposta da URL que está sendo consultada.

### URL_ALLOW_MULTIPLE

Por padrão, se for solicitado o encurtamento de uma URL que já exista e esteja dentro do prazo de validade, a mesma URL encurtada retorna da chamada, mantendo a validade original.

Se a opção `URL_ALLOW_MULTIPLE` estiver ativa (com valor `1`), é criada uma nova URL encurtada. As URLs encurtadas criadas anteriormente a partir da mesma URL original permanecem funcionando pelo restante do seu prazo de validade.

### RENOVATE_ON_ACCESS

Se esta opção estiver ativa (com valor `1`), a cada vez que a URL encurtada for acessada, o seu prazo de validade será reiniciado. Esta opção é útil caso a aplicação requeira a expiração da URLs encurtadas que não forem acessadas por um determinado período.

### URL_VALID_DAYS

Determina o número de dias em que a URL encurtada é válida a partir de sua criação. Aṕos expirado o prazo, na tentativa de um acesso à URL encurtada, o usuário receberá a mensagem `Not found`, acompanhado do código `HTTP` `404`.

## Documentação do *entry point*

Apesar de, como dito anteriormente, a aplicação não se tratar de uma API, uma documentação simplificada composta via Postman [está disponível aqui](https://documenter.getpostman.com/view/15870781/TzY4gaja).

Além da documentação, uma *collection* do Postman está disponível em [https://www.getpostman.com/collections/52345b2ed4b016bb786a](https://www.getpostman.com/collections/52345b2ed4b016bb786a).

## Github Actions

O [Github Actions](https://github.com/features/actions) permite automatizar várias tarefas, desde a execução do teste até integração e deploy contínuos. É possível, por exemplo, enviar a aplicação por FTP ou SFTP quando publicada no *branch production*.

Não faz parte da especificação do projeto o *deply* da aplicação em algum ambiente "vivo" (como [Heroku](https://www.heroku.com/) ou [AWS](https://aws.amazon.com/pt/) ). Então, como exemplo de *Action*, foi incluída uma ação simples, que realiza os testes a cada *push*.

Mais detalhes sobre essa ação podem ser obtidos [aqui](https://github.com/leandrowferreira/challenge-backend-studos/actions) ou no próprio [arquivo da *action*](https://github.com/leandrowferreira/challenge-backend-studos/blob/main/.github/workflows/laravel.yml).


## Detalhamento técnico

A seguir, são descritos alguns detalhes de nível mais baixo, como a infraestrutura e detalhes das tecnologias utilizadas.

### Ambiente

O ambiente original de desenvolvimento está sobre o **Ubuntu 20.04**, mas tem sua base *conteinerizada* usando o **Docker**, permitindo, teoricamente, a execução em qualquer sistema operacional (a utilização em outros sistemas operacionais requer alguns ajustes). Uma rede simples de dois contêiners foi criada a partir do arquivo `docker-compose.yml`, presente na raiz do projeto. Os contêiners são os seguintes:

- **appslug** montado sobre a imagem `php:7.4.1-apache` do repositório do **Docker**, com pouquíssimas mudanças em seu conteúdo, como, por exemplo, a instalação de algums módulos requeridos pelo Laravel. Este contêiner rodará a aplicação, compartilhando o diretório do projeto com o hospedeiro. As informações necessárias para a correta configuração do contêiner estão presentes no arquivo `.docker/Dockerfile`.

- **dbslug** montado sobre a imagem `mysql:8.0` do repositório do **Docker**, provê o banco de dados do projeto. Este projeto, devido a sua baixa complexidade, seria perfeitamente viável a partir de um banco de dados menor, como o **SQLite**, por exemplo. O **MySQL** foi utilizado para ilustrar a montagem de um ambiente de desenvolvimento baseado no **Docker Compose**.


### Framework

A aplicação em si é muito simples, tendo todas as suas funcionalidades perfeitamente atendidas por um *microframework*. Por esta razão, a base do projeto é o [**Lumen**](https://lumen.laravel.com), a versão reduzida do **Laravel**, ambos baseados na linguagem **PHP**.

Considerou-se que utilizar um *framework* completo (como o Laravel, Laminas, CakePHP ou Codeigniter, por exemplo) incluiria complexidade desnecessária ao ambiente. A completa ausência de *views* contribuiu para a decisão pelo **Lumen**, que simplesmente vai devolver requisições simples como texto ou redirecionamento.

Foi necessário adicionar um módulo nativo no **Laravel**, porém ausente na instalação original do **Lumen**, o [**Guzzle**](https://docs.guzzlephp.org/en/stable), responsável pela requisição de checagem da URL original, caso a opção `URL_CHECK_BEFORE` esteja ativa. Além disso, optou-se por habilitar o [**Eloquent**](https://laravel.com/docs/master/eloquent), o *ORM* (Object Relational Mapping) do **Laravel**.

Algumas configurações foram removidas devido à ausência de funcionalidade no projeto, como por exemplo autenticação, eventos, *middlewares* e *listeners*. Uma especial atenção deve ser dispensada ao fato de as chamadas não serem autenticadas, por este requisito não estar presente na descrição original.

### Banco de dados

Apenas duas tabelas são criadas no banco de dados a partir das *migrations* presentes no diretório `database/migrations`:

- **Urls** para armazenar as URLs originais, o seu *slug* e sua validade.
- **Clicks** para armazenar dados a respeito dos acessos usando as URLs encurtadas. Estes dados incluem data/hora do acesso e IP do usuário.

Uma *factory* foi definida para o *model* *Url*, para ser utilizada nas rotinas de testes. Ela está no diretório `database/factories`.

Ao ser gerado o banco através do script `.docker/init.sh`, ele é alimentado com alguns dados falsos, através da *seeder* presente no diretótio `database/seeders`.

### PHP Standard Recommendations (PSRs)

O desenvolvimento desta aplicação foi realizado procurando se manter fiel às `PSR`s, sobretudo as de número `1`, `4` e `12`, que tratam, respectivamente de:

- **Padrões de código** governa detalhes de formatação do arquivo, efeitos colaterais, namespaces, classes, propriedades e métodos.
- **Autoloading** especifica os requisitos para o correto funcionamento do *autoload*, o que norteia todo o funcionamento do *framework*.
- **Guia de estilo** determina diversos padrões, do obrigatório ao desejável, para a criação do código-fonte.

A `PSR-1` define um pouco da formatação, que é estendida pela `PSR-12` e informações sobre *efeitos colaterais* das funções e métodos, além de padronizar a respeito de algumas estruturas do PHP. Foram poucas as oportunidades de aplicá-la no projeto, mas uma especial atenção foi dada à questão dos efeitos colaterais no model *Url*.

A `PSR-4` é a base da organização das estruturas do **Lumen**, e é requerido pelo *framework* que o desenvolvedor a siga durante todo o desenvolvimento. Uma das grandes vantagens do uso de um *framework* é o direcionamento que ele dá ao comportamento do desenvolvedor, que passa a lidar com naturalidade ao seguir essa recomendação. No arquivo `composer.json` do projeto há mais detalhes sobre o mapeamento dos `namespaces` nas chaves `autoload` e `autoload-dev`.

A `PSR-12` assegura que códigos gerados por diversas pessoas tenham o mínimo necessário de consistência entre si. *IDEs* como o **Visual Studio Code** e ferramentas como o [**PHP-CS-FIXER**](https://github.com/FriendsOfPHP/PHP-CS-Fixer) ajudam bastante no cumprimento das exigências desta recomendação.


### O *Model* `Url`

O Model `Url` é a peça funcional mais importante da aplicação. Nele está concentrado todo o negócio, dada a pequena complexidade do projeto. Abaixo estão listados os seus métodos:

----------

#### createSlug()

Cria uma URL encurtada a partir da URL original.

```php
public static createSlug(string $url, string $baseUrl) : UrlResult
```

  - **Visibilidade:** public
  - **Parâmetros:**
    - **$url** (string) A URL a encurtar
    - **$baseUrl** (string) A base da URL para gerar o caminho completo da URL encurtada
  - **Retorno:** UrlResult

----------

#### showUrl()

Retorna uma URL a partir de seu * slug*.

```php
public static showUrl(string $slug[, string $ip = null ]) : UrlResult
```

  - **Visibilidade:** public
  - **Parâmetros:**
    - **$slug** (string) O slug da URL desejada
    - **$ip** (string) Opcional. O endereço de IP do cliente
  - **Retorno:** UrlResult

----------

#### createNewSlug()

Cria uma *string* ainda não utilizada para ser usada como um *slug*.

```php
protected static createNewSlug() : string
```

  - **Visibilidade:** protected
  - **Retorno:** string

----------

#### expired()

Verifica se a instância atual do model `Url` está expirada.

```php
protected expired() : bool
```

  - **Visibilidade:** protected
  - **Retorno:** bool

----------

#### getSlugFromUrl()

Retorna o *slug* correspondente à URL. Se não existir, retorna `NULL`.

```php
protected static getSlugFromUrl(string $url) : Url|null
```

  - **Visibilidade:** protected
  - **Parâmetros:**
    - **$url** (string) A URL original
  - **Retorno:** Url | null

----------

#### getUrlFromSlug()

Retorna a Url correspontente ao *slug* correspondente à URL. Se não existir, retorna `NULL`.

```php
protected static getUrlFromSlug(string $slug) : Url|null
```

  - **Visibilidade:** protected
  - **Parâmetros:**
    - **$slug** (string) O *slug* da URL
  - **Retorno:** Url | null

----------

#### isValidUrl()

Verifica se a URL informada está publicada e respondendo.

```php
protected static isValidUrl(string $url) : bool
```

  - **Visibilidade:** protected
  - **Parâmetros:**
    - **$url** (string) A URL a ser verificada
  - **Retorno:** bool

----------


### Testes

É preferível que os testes sejam executados dentro do contêiner, eliminando a necessidade de o host possuir as configurações necessárias para realizar esta atividade. Por essa razão, está presente o arquivo `test.sh` na raiz do projeto, que executa os testes (a partir do [**PHPUnit**](https://phpunit.de/)) dentro do contêiner **appslug**.

Para executar os testes, é necessário estar no diretório do projeto e executar:

```bash
$ ./test.sh
```

Os seguintes testes estão presentes no diretório `tests/functional` e são realizados ao ser executado o comando acima:

#### testAppIsOn

Verifica se a chamada `GET` ao *endpoint* retorna as informações sobre a aplicação.

#### testReturnBasicUrlFromSlug

Verifica se uma URL encurtada predefinida no banco (`http://localhost:8080/abc123ab`) retorna a URL esperada (`studos.com.br`).

#### testReturnNewUrlFromSlug

Verifica se uma URL criada diretamente através da `factory` é acessível a partir de sua versão encurtada.

#### testCreateNewSlug

Testa a funcionalidade básica de criar uma URL encurtada a partir do `POST` de uma URL no sistema. Este teste espera que uma URL, mesmo que inválida, gere sua versão encurtada, pois a configuração `URL_CHECK_BEFORE` está desativada.

#### testDontCreateNewSlug

Repete o teste anterior, porém com a configuração `URL_CHECK_BEFORE` ativada. O teste espera que a versão encurtada não seja criada e um código `HTTP` `422` retorne, pois a URL fornecida para o teste é inválida.

#### testReturnSameSlug

Este teste verifica se várias solicitações de encurtamento de uma mesma URL retornam a mesma versão encurtada, caso a configuração `URL_ALLOW_MULTIPLE` esteja desativada.

#### testReturnDifferentSlug

Aqui, ao contrário do ocorrido no teste anterior, a opção `URL_ALLOW_MULTIPLE` está ativada e, portanto, é esperado que diferentes versões encurtadas sejam obtidas a partir de múltiplas chamadas usando uma mesma URL original.

#### testNotChangingExpiration

O teste espera que várias chamadas à mesma URL encurtada não alterem o seu prazo de validade, caso a opção `RENOVATE_ON_ACCESS` esteja desativada.

#### testChangingExpiration

Este teste complementa o anterior, ativando a opção `RENOVATE_ON_ACCESS` e checando se a data de validade da URL encurtada é modificada a cada acesso.

#### testExpiredSlug

Aqui, uma URL encurtada é gerada, testada (esperando obter o código `HTTP` `322`) e depois sua data de validade é alterada para "um segundo atrás". O teste de acesso é repetido, mas desta vez é esperado o código `HTTP` `404`.



## Limitações

As limitações conhecidas do projeto são:

- Não é possível encurtar URLs que contenham *parâmetros query* (presentes após o sinal `?` na URL) ou *fragmentos* (presentes após o sinal `#` na URL).
- Não é possível encurtar URLs cujo protocolo não seja `http://` ou `https://`.


## Recursos

  - [Docker](https://www.docker.com/): Serviço de virtualização por contêiner
  - [Docker Compose](https://docs.docker.com/compose/): Serviço de orquestração de contêiners
  - [Lumen](https://lumen.laravel.com/): *Microframework* PHP
  - [MySQL](https://www.mysql.com/): Sistema de gerenciamento de banco de dados
  - [PHP-CS-FIXER](https://github.com/FriendsOfPHP/PHP-CS-Fixer): Ferramenta de formatação de código para seguir as PSRs
  - [PHP-FIG](https://www.php-fig.org/): Responsável pelas recomendações de padrões PHP (PSRs)
  - [PHPUnit](https://phpunit.de/): Framework de testes
  - [Postman](https://www.postman.com/): Aplicação auxiliar na depuração de APIs
  - [Thunder Client](https://www.thunderclient.io/): Extensão do Visual Studio Code com funcionalidades semelhantes ao Postman
  - [Ubuntu](https://ubuntu.com/): Distribuição Linux
  - [Visual Studio Code](https://code.visualstudio.com/): IDE popular para a criação de código em diversas linguagens


## Créditos

Esta implementação foi fortemente baseada no *framework* **Lumen** e na infraestrutura **Docker**, mas todo o conteúdo da camada de desenvolvimento é original.