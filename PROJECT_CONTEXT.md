# Project Context

Contexto operacional e decisões recentes do projeto para facilitar continuidade em outro ambiente ou máquina.

## Runtime e containers

- Docker Compose sobe `app`, `postgres`, `redis` e `vite`.
- Hot reload do frontend depende do serviço `vite` e do arquivo `public/hot`.
- O build local do frontend no host pode falhar por permissões em `node_modules/.bin` e `node_modules/.vite-temp`; no container `vite` o build já foi validado com sucesso.
- O container `app` foi ajustado para PHP 8.4 porque o `composer.lock` atual exige PHP `>= 8.4.1`.

## Multi-tenancy

- A etapa 3 usa `TenantContext` singleton em `app/Modules/Tenancy`.
- O tenant é resolvido por subdomínio via middleware global.
- Em ambiente local, `localhost` e `127.0.0.1` podem resolver um tenant padrão via `TENANCY_LOCAL_DEFAULT_SUBDOMAIN` para facilitar desenvolvimento do portal público.
- Há proteção adicional para barrar usuário autenticado de outro tenant.
- Models multi-tenant usam a trait `BelongsToTenant` com `TenantScope` global.
- Factories relevantes foram ajustados para depender do contexto de tenant, evitando `tenant_id` hardcoded.

## Reservas e pagamentos

- A etapa 6 adiciona webhook central em `/api/webhooks/payments/{provider}` no domínio central.
- O webhook faz lookup da transação sem scope global e reentra no `TenantContext` pelo tenant da transação.
- A expiração automática usa o comando `bookings:cancel-expired`.
- O scheduler foi configurado em `routes/console.php` com `hourly()`, `withoutOverlapping()` e `onOneServer()`.

## Portal público

- A etapa 7 cria o portal público em Vue 3 + TypeScript via Inertia na rota `/`.
- O portal usa endpoints web de sessão para login e cadastro rápido do tenant.
- O fluxo público reaproveita os controllers de booking e payment no prefixo `/portal`.

## Observações de ambiente

- No host local, Composer ainda pode falhar se o PHP da máquina estiver em 8.3.x.
- Dentro do container `app`, o autoload já foi validado com PHP 8.4.x.
- Para evitar diferenças de ambiente, prefira rodar Composer e Artisan via container.

## Comandos preferenciais

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app composer dump-autoload
docker compose exec app php artisan test
docker compose exec vite npm run build
```