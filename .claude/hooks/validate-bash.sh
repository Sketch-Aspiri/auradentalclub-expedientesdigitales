#!/usr/bin/env bash
# PreToolUse hook (matcher: Bash). Guardarraíl determinista contra comandos
# destructivos/irreversibles, coherente con el Git Safety Protocol del usuario
# y CLAUDE.md de este proyecto (datos clínicos = sin margen de error).
# Recibe el JSON del hook por stdin y busca patrones peligrosos en crudo
# (no requiere jq/python — no están garantizados en todos los entornos).
set -u

input="$(cat)"

block() {
  printf 'Bloqueado por validate-bash.sh: %s\n' "$1" >&2
  printf 'Este comando coincide con un patron destructivo/irreversible prohibido para este proyecto. Si de verdad lo necesitas, ejecutalo manualmente fuera de Claude Code.\n' >&2
  exit 2
}

echo "$input" | grep -qiE -- '--no-verify([^a-z-]|$)' && block "--no-verify (salta git hooks)"
echo "$input" | grep -qiE -- '--no-gpg-sign|commit\.gpgsign=false' && block "--no-gpg-sign / gpgsign=false (evita firmar commits)"
echo "$input" | grep -qiE 'git[[:space:]]+push[^"]*(--force([^-]|$)|--force-with-lease|[[:space:]]-f([[:space:]]|$|"))' && block "git push --force/-f (force push prohibido)"
echo "$input" | grep -qiE 'git[[:space:]]+reset[[:space:]]+--hard' && block "git reset --hard (descarta cambios sin confirmar)"
echo "$input" | grep -qiE 'git[[:space:]]+clean[[:space:]]+-[a-zA-Z]*f' && block "git clean -f (borra archivos no rastreados)"
echo "$input" | grep -qiE 'git[[:space:]]+(checkout|restore)[[:space:]]+(--[[:space:]]+)?\.([[:space:]]|"|$)' && block "git checkout/restore . (descarta cambios locales)"
echo "$input" | grep -qiE 'git[[:space:]]+branch[[:space:]]+-D' && block "git branch -D (borrado forzado de rama)"
echo "$input" | grep -qiE 'rm[[:space:]]+-[a-zA-Z]*[rf][a-zA-Z]*[rf][a-zA-Z]*[[:space:]]+(/|~|\.|\.\.|\*)([[:space:]]|"|$)' && block "rm -rf sobre una ruta raiz/comodin peligrosa (/, ~, ., .., *)"
echo "$input" | grep -qiE 'rm[[:space:]]+-[a-zA-Z]*[rf][a-zA-Z]*[rf][a-zA-Z]*[[:space:]]+\.git([[:space:]]|"|$)' && block "rm -rf .git (destruye el historial del repo)"
echo "$input" | grep -qiE 'DROP[[:space:]]+(DATABASE|TABLE)' && block "DROP DATABASE/TABLE (irreversible sobre datos clinicos)"
echo "$input" | grep -qiE 'migrate:fresh' && block "migrate:fresh (borra todas las tablas, incluidos datos de pacientes)"

exit 0
