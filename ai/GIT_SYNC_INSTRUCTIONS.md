# Instruções para Sincronização do Git (Laptop Casa)

Existem commits locais e trabalho não salvo que precisam ser enviados para o GitHub. Como o ambiente do Agente não permite a inserção manual de senhas de SSH, você deve executar os seguintes comandos no seu terminal (PowerShell ou Bash) onde a senha possa ser solicitada.

## 1. Enviar Commits da Branch de Melhorias
Esta branch contém melhorias de UX já finalizadas e commitadas localmente.
```powershell
git push origin feature/navbar-fixo-csrf-token-refresh
```

## 2. Enviar Trabalho em Progresso (WIP)
Esta branch contém as correções recentes do Seletor de PEI e refatorações do Dashboard que acabamos de salvar.
```powershell
git push origin recovery/claude-home-wip
```

## 3. Próximos Passos (No Trabalho)
Após realizar os pushes acima, no seu computador do trabalho, execute:
```powershell
# Baixar as atualizações
git fetch origin

# Integrar as melhorias na main (se desejar)
git checkout main
git merge origin/feature/navbar-fixo-csrf-token-refresh
git merge origin/recovery/claude-home-wip
```

---
**Nota:** Caso o comando `git push` falhe, verifique se a chave SSH está carregada (`ssh-add`) ou use a URL HTTPS se preferir.
