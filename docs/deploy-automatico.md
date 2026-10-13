# Deploy automático (Hostinger, por SSH)

O workflow `.github/workflows/deploy.yml` roda a cada merge na `main` (ou em
**Actions → Deploy → Run workflow**): instala as dependências, compila os assets (Vite), envia os
arquivos por rsync e roda `php artisan migrate --force` e os caches. O `.env` e o `storage/` nunca
são enviados nem apagados. Sem `DEPLOY_HABILITADO=true` o workflow não faz nada.

## Configuração (uma vez)

1. **Chave SSH:** a mesma `~/.ssh/hostinger_deploy` dos outros repositórios (ou gere uma:
   `ssh-keygen -t ed25519 -f ~/.ssh/hostinger_deploy -N ""` e autorize a `.pub` em hPanel →
   Avançado → Acesso SSH → Chave SSH).
2. **GitHub** → Settings → Secrets and variables → Actions (secrets não passam de um repo para outro):

| Tipo | Nome | Valor |
|---|---|---|
| Secret | `HOSTINGER_SSH_HOST` | IP do hPanel → Acesso SSH |
| Secret | `HOSTINGER_SSH_PORT` | `65002` |
| Secret | `HOSTINGER_SSH_USER` | usuário SSH (`u123456789`) |
| Secret | `HOSTINGER_SSH_KEY` | saída inteira de `cat ~/.ssh/hostinger_deploy` (a privada) |
| Variable | `DEPLOY_DESTINO` | pasta do projeto no servidor, a que já tem o `.env` |
| Variable | `PHP_BIN` | só se o `php` do SSH não for 8.4+ (ex.: `/opt/alt/php84/usr/bin/php`) |
| Variable | `DEPLOY_MIGRATE` | `false` para não rodar migrations sozinho (padrão: roda) |
| Variable | `DEPLOY_HABILITADO` | `true`, por último |

3. Confira no servidor: `cd ~/<DEPLOY_DESTINO> && ls -a` deve mostrar o `.env` e o `storage/`.
4. Backup do banco e **Actions → Deploy → Run workflow**.

Deploy ruim: reverta o merge. Migration destrutiva: `DEPLOY_MIGRATE=false` antes do merge e rode à mão.
