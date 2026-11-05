# Reglas del proyecto

## Commit y push: solo con la cuenta de GitHub `rodri89`

Esta máquina suele tener logueadas varias cuentas de `gh` (GitHub CLI) a la vez (ej.
`rbanegas-ctrl`). Todo `git commit` y `git push` en este repo tiene que hacerse con la cuenta
activa de `gh` en **`rodri89`** — es la dueña del remoto (`github.com/rodri89/turnosonlinebb`).

Antes de commitear o pushear, verificar la cuenta activa y cambiarla si hace falta:

```bash
gh auth status            # ver cual esta activa
gh auth switch --user rodri89
```

Esto está reforzado con un git hook real en `.githooks/` (`pre-commit` y `pre-push`), que
bloquea la operación si la cuenta activa no es `rodri89`. Si cloná este repo en otra máquina,
el hook no se activa solo — hay que correr una vez:

```bash
git config core.hooksPath .githooks
```
