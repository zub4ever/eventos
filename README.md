# MFeventos SaaS

MVP de um SaaS para agendamento de eventos e piscinas, com arquitetura de monolito modular e base multi-tenant por isolamento logico (`tenant_id`) em banco compartilhado.

## Stack

- PHP 8.3+
- Laravel 12
- Vue.js 3 (Composition API)
- Inertia.js
- PostgreSQL
- Redis

## Dominio

- Dominio principal: `saas.com.br`
- Exemplo de tenants por subdominio:
	- `cliente1.saas.com.br`
	- `cliente2.saas.com.br`

## Status atual

Etapa 1 concluida:

- Projeto Laravel inicializado
- Inertia + Vue 3 configurados
- Dependencias de auth (Breeze) adicionadas
- PostgreSQL configurado como banco padrao no `.env.example`
- Redis configurado para cache, filas e sessao no `.env.example`
- Estrutura inicial de monolito modular criada
- Rotas iniciais de teste criadas (`/` e `/health`)

## Contexto compartilhado

Para continuidade do projeto em outro ambiente ou outra maquina, consulte [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md).

## Estrutura modular

```text
app/
├── Modules/
│   ├── Tenancy/
│   │   ├── Actions/
│   │   ├── Contracts/
│   │   ├── DTOs/
│   │   ├── Events/
│   │   ├── Exceptions/
│   │   ├── Http/
│   │   ├── Models/
│   │   └── Services/
│   ├── Booking/
│   ├── Payment/
│   ├── Administration/
│   └── Shared/
├── Http/
├── Models/
├── Providers/
└── Console/
```

## Requisitos locais

- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 15+
- Redis 7+

### Extensoes PHP recomendadas

No Linux/Ubuntu:

```bash
sudo apt update
sudo apt install -y php8.3-xml php8.3-pgsql php8.3-redis
```

> Observacao: sem `php8.3-xml` (DOM/XML), alguns comandos do Composer/Artisan podem falhar durante scripts de pos-instalacao.

## Instalacao

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Configuracao de ambiente

O arquivo `.env.example` ja contem os parametros iniciais para:

- URL/base de dominio (`APP_URL`, `APP_DOMAIN`)
- PostgreSQL (`DB_CONNECTION=pgsql`)
- Redis (`CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`)
- Provedores de pagamento (AbacatePay e Asaas)

Valores principais esperados:

```env
APP_URL=http://saas.local
APP_DOMAIN=saas.local

DB_CONNECTION=pgsql

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

PAYMENT_PROVIDER=abacatepay

ABACATEPAY_API_URL=
ABACATEPAY_API_KEY=
ABACATEPAY_WEBHOOK_SECRET=

ASAAS_API_URL=
ASAAS_API_KEY=
ASAAS_WEBHOOK_TOKEN=
```

## Executando em desenvolvimento

Com Docker Compose:

```bash
docker compose up -d
```

Isso sobe:

- Laravel/Apache em `http://localhost:8080`
- Vite HMR em `http://localhost:5173`

As alteracoes em arquivos Vue, JS e CSS passam a refletir automaticamente no navegador via hot reload.

Sem Docker:

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Opcional (build de producao):

```bash
npm run build
```

## Rotas iniciais

- `/`: pagina Vue renderizada via Inertia para validar integracao Laravel + Inertia + Vue
- `/health`: endpoint JSON simples de healthcheck

## Proximas etapas

- Implementar multi-tenancy por subdominio e `tenant_id`
- Implementar dominio de reservas/agendamentos
- Implementar fluxo de pagamentos
- Expandir autenticacao e autorizacao por perfil
