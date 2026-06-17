# API de Encurtador de URLs

API REST para encurtamento de URLs com autenticação JWT, controle de acesso, estatísticas de cliques, expiração automática, geração de QR Code e webhooks.

## Stack

- PHP 8.3+
- Laravel 13
- Pest — test runner
- SQLite (dev) / MySQL ou PostgreSQL (produção)
- `php-open-source-saver/jwt-auth` — autenticação via JWT
- `simplesoftwareio/simple-qrcode` — geração de QR Code

> Projeto criado via [Laravel Herd](https://herd.laravel.com), ambiente de desenvolvimento local para PHP/Laravel no Windows e macOS.

## Instalação

```bash
git clone <https://github.com/GuilhAndrad/shortening-api.git>
cd shortening-api

composer install

cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### Configurando o `.env`

Depois de copiar o `.env.example`, ajuste estas variáveis:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database

WEBHOOK_URL=
```

`APP_URL` é usado para montar `short_url` e `qr_code_url` nas respostas da API — se estiver errado, os links retornados não funcionam. `WEBHOOK_URL` pode ficar vazio; se vazio, o disparo de webhook é simplesmente ignorado.

### Banco de dados

```bash
php artisan migrate
```

### Queue (necessário para o webhook funcionar de forma assíncrona)

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### Webhook (opcional)

Para receber notificações no primeiro acesso de cada URL, configure no `.env`:

```env
WEBHOOK_URL=https://seu-endpoint.com/webhook
```

Se não configurado, o disparo de webhook é simplesmente ignorado.

### Servidor local

```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000/api`.

---

## Autenticação

A maior parte dos endpoints de gerenciamento exige um token JWT, enviado no header:

```
Authorization: Bearer <seu_token>
```

### Registrar usuário

`POST /api/auth/register`

**Request**
```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha12345",
  "password_confirmation": "senha12345"
}
```

**Response** `201 Created`
```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com"
  },
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer"
}
```

### Login

`POST /api/auth/login`

**Request**
```json
{
  "email": "joao@example.com",
  "password": "senha12345"
}
```

**Response** `200 OK`
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Response** `401 Unauthorized` (credenciais inválidas)
```json
{
  "message": "Credenciais inválidas."
}
```

### Usuário autenticado

`GET /api/auth/me` — requer token

**Response** `200 OK`
```json
{
  "id": 1,
  "name": "João Silva",
  "email": "joao@example.com"
}
```

### Renovar token

`POST /api/auth/refresh` — requer token

**Response** `200 OK`
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Logout

`POST /api/auth/logout` — requer token

**Response** `204 No Content`

---

## Endpoints do encurtador

### Encurtar URL

`POST /api/shorten`

Autenticação **opcional**. Se autenticado, a URL fica associada ao usuário e pode ser gerenciada depois. Se anônimo, a URL é criada sem dono e não pode ser editada/removida posteriormente.

**Request**
```json
{
  "url": "https://laravel.com/docs/routing",
  "expires_at": "2026-12-31T23:59:59",
  "custom": "meulink"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `url` | string | sim | URL original a ser encurtada |
| `expires_at` | datetime | não | Data de expiração. Sem expiração se omitido |
| `custom` | string | não | Código customizado (6 a 8 caracteres alfanuméricos) |

**Response** `201 Created`
```json
{
  "data": {
    "code": "meulink",
    "short_url": "http://localhost:8000/meulink",
    "original_url": "https://laravel.com/docs/routing",
    "clicks_count": 0,
    "expires_at": "2026-12-31T23:59:59+00:00",
    "created_at": "2026-06-17T10:00:00+00:00",
    "qr_code_url": "http://localhost:8000/api/meulink/qrcode"
  }
}
```

**Response** `422 Unprocessable Entity` (URL duplicada para o mesmo usuário)
```json
{
  "message": "Os dados fornecidos são inválidos.",
  "errors": {
    "url": ["Você já encurtou esta URL anteriormente."]
  }
}
```

### Redirecionar

`GET /api/{code}`

Redireciona (HTTP 302) para a URL original e registra o acesso.

**Response** `302 Found`
```
Location: https://laravel.com/docs/routing
```

**Response** `404 Not Found` (código não existe)
```json
{
  "message": "Recurso não encontrado."
}
```

**Response** `410 Gone` (URL expirada)
```json
{
  "message": "Este link expirou."
}
```

### Estatísticas de acesso

`GET /api/{code}/stats`

**Response** `200 OK`
```json
{
  "total_clicks": 42,
  "daily": [
    { "date": "2026-06-15", "clicks": 10 },
    { "date": "2026-06-16", "clicks": 32 }
  ],
  "weekly": [
    { "week": "2026-24", "clicks": 42 }
  ],
  "monthly": [
    { "month": "2026-06", "clicks": 42 }
  ]
}
```

### QR Code

`GET /api/{code}/qrcode`

Retorna a imagem do QR Code em SVG apontando para a URL curta.

**Response** `200 OK` — `Content-Type: image/svg+xml`

### Listar URLs do usuário autenticado

`GET /api/me/urls` — requer token

**Response** `200 OK`
```json
{
  "data": [
    {
      "code": "meulink",
      "short_url": "http://localhost:8000/meulink",
      "original_url": "https://laravel.com/docs/routing",
      "clicks_count": 3,
      "expires_at": "2026-12-31T23:59:59+00:00",
      "created_at": "2026-06-17T10:00:00+00:00",
      "qr_code_url": "http://localhost:8000/api/meulink/qrcode"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/me/urls?page=1",
    "last": "http://localhost:8000/api/me/urls?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### Atualizar URL

`PATCH /api/{url}` — requer token, somente o dono

**Request**
```json
{
  "original_url": "https://laravel.com/docs/11.x/routing",
  "expires_at": "2027-01-01T00:00:00"
}
```

Todos os campos são opcionais — envie apenas o que deseja atualizar.

**Response** `200 OK`
```json
{
  "data": {
    "code": "meulink",
    "short_url": "http://localhost:8000/meulink",
    "original_url": "https://laravel.com/docs/11.x/routing",
    "clicks_count": 3,
    "expires_at": "2027-01-01T00:00:00+00:00",
    "created_at": "2026-06-17T10:00:00+00:00",
    "qr_code_url": "http://localhost:8000/api/meulink/qrcode"
  }
}
```

**Response** `403 Forbidden` (não é o dono)
```json
{
  "message": "Você não tem permissão para executar esta ação."
}
```

### Remover URL

`DELETE /api/{code}` — requer token, somente o dono

**Response** `204 No Content`

---

## Regras de negócio

- Código curto: 6 a 8 caracteres alfanuméricos, gerado automaticamente ou customizado via `custom`.
- URLs expiradas retornam `410 Gone` em vez de `404 Not Found`.
- Um mesmo usuário autenticado não pode encurtar a mesma URL original duas vezes.
- Usuários anônimos podem criar URLs encurtadas, mas não podem listá-las, editá-las ou removê-las.
- Rate limit padrão: 10 requisições por minuto por IP em toda a API.
- Rate limit específico de login: 5 requisições por minuto por IP.
- O contador de cliques é incrementado a cada redirecionamento, e o primeiro acesso de cada URL dispara um webhook (se configurado).

## Códigos de status utilizados

| Código | Significado |
|---|---|
| `200` | Sucesso |
| `201` | Recurso criado |
| `204` | Sucesso sem conteúdo de retorno |
| `401` | Não autenticado ou token inválido/expirado |
| `403` | Autenticado, mas sem permissão sobre o recurso |
| `404` | Recurso ou rota não encontrados |
| `405` | Método HTTP não permitido para o endpoint |
| `410` | Recurso existente, porém expirado |
| `422` | Dados de entrada inválidos |
| `429` | Limite de requisições excedido |
| `500` | Erro interno do servidor |

## Licença

Projeto desenvolvido como desafio técnico de estudo. Sem licença comercial associada.