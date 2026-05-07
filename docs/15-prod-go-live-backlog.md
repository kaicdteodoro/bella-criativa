# Backlog — Go-Live Produção (próximos passos)

## Objetivo

Fechar o deploy de produção em cPanel com segurança, previsibilidade e rotina de operação.

## Prioridade P0 (bloqueia go-live)

- [ ] Configurar secrets do GitHub Actions:
  - [ ] `CPANEL_HOST`
  - [ ] `CPANEL_SSH_USER`
  - [ ] `CPANEL_SSH_PRIVATE_KEY`
  - [ ] `CPANEL_PROJECT_PATH`
- [ ] Validar acesso SSH no cPanel com a chave usada no Actions.
- [ ] Revisar `.env` de produção:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL` com domínio final HTTPS
  - [ ] `SESSION_SECURE_COOKIE=true`
  - [ ] credenciais reais de banco e fornecedores
- [ ] Confirmar document root apontando para `public/`.
- [ ] Executar primeiro deploy manual via `workflow_dispatch`.

## Prioridade P1 (go-live assistido)

- [ ] Rodar import inicial em produção (amostra + carga principal).
- [ ] Validar páginas críticas:
  - [ ] Home
  - [ ] Catálogo
  - [ ] Página de produto
  - [ ] Contato
  - [ ] Login admin Filament
- [ ] Validar SEO técnico:
  - [ ] `robots.txt`
  - [ ] `/sitemap.xml`
  - [ ] metatags OG em produto (preview WhatsApp)
- [ ] Confirmar headers de segurança em produção (com HTTPS ativo).

## Prioridade P2 (estabilização pós-go-live)

- [ ] Monitorar logs de `csp-report` por 24-72h.
- [ ] Reduzir gradualmente violações CSP restantes.
- [ ] Definir política de backup:
  - [ ] banco diário
  - [ ] `storage/app/public/media`
  - [ ] `.env`
- [ ] Definir rotina de rollback (revert commit + redeploy automático).

## Entregáveis de amanhã

1. Pipeline de deploy automático configurado e testado no ambiente real.
2. Checklist de go-live P0/P1 concluído.
3. Produção publicada com validação funcional e SEO básico.

