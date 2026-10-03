# Gerador do chamado de implantação (.docx)

Produz o `.docx` do chamado para a equipe de Infra a partir de um texto versionado, sem depender do Word.

```bash
php documentacao/chamados/gerador/gerar-docx.php documentacao/chamados/<AAAAMMDD>-chamado-<assunto>.docx
php documentacao/chamados/gerador/ler-docx.php   documentacao/chamados/<AAAAMMDD>-chamado-<assunto>.docx
```

| Arquivo | O que é |
|---|---|
| `conteudo-chamado.php` | **O texto do chamado.** É aqui que se edita. |
| `gerar-docx.php` | Formatação e empacotamento OOXML, montado do zero (sem modelo). |
| `ler-docx.php` | Imprime o texto do `.docx` gerado, para conferência. |

## Regras de conteúdo

1. **O roteiro sai do código, nunca da memória.** Antes de escrever, varrer:
   - migrations pendentes;
   - `database/seeders` (sempre com `--class`);
   - `app/Console/Commands`;
   - `withSchedule` (cron `schedule:run`);
   - `ShouldQueue` (`queue:work` e, com código novo, `queue:restart`);
   - `Storage::disk('public')` (`storage:link`);
   - `composer.lock` e `package-lock.json`;
   - variáveis de `.env`.
2. **Só o que fazer.** A Infra executa exatamente o que está escrito. O que um chamado anterior já fez não volta ao roteiro.
3. **Banco**: `php artisan migrate --force` aplica só o que está pendente. As subpastas de `database/migrations` estão registradas no `AppServiceProvider`.
4. **Conferência final** com o comando que prova cada passo (ex.: `php artisan migrate:status`).
