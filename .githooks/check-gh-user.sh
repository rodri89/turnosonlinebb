#!/bin/sh
# Bloquea commit/push si la cuenta activa de `gh` (GitHub CLI) no es rodri89.
# Esta maquina suele tener logueadas varias cuentas de GitHub (ej. rbanegas-ctrl), y si la
# cuenta activa es otra, un push puede fallar con 403 o, peor, hacerse con la identidad
# equivocada. Ver .githooks/pre-commit y .githooks/pre-push.
set -e

ACCOUNT_ESPERADA="rodri89"

if ! command -v gh >/dev/null 2>&1; then
  echo "ERROR: no se encontro 'gh' (GitHub CLI) para verificar la cuenta activa." >&2
  echo "Instalalo o corre la accion sin este hook (no recomendado)." >&2
  exit 1
fi

cuenta_activa=$(gh api user --jq .login 2>/dev/null || echo "")

if [ -z "$cuenta_activa" ]; then
  echo "ERROR: no se pudo determinar la cuenta activa de 'gh' (¿estas deslogueado?)." >&2
  echo "Corre: gh auth switch --user $ACCOUNT_ESPERADA" >&2
  exit 1
fi

if [ "$cuenta_activa" != "$ACCOUNT_ESPERADA" ]; then
  echo "ERROR: la cuenta activa de gh es '$cuenta_activa', tiene que ser '$ACCOUNT_ESPERADA'." >&2
  echo "Corre: gh auth switch --user $ACCOUNT_ESPERADA" >&2
  exit 1
fi

exit 0
