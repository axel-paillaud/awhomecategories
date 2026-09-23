#!/bin/bash

# NOTE: You have to be in the bin directory to run this script
# cd <presta-root>/modules/<module-name>/bin

MODULE_DIR="$(dirname "$PWD")"
MODULE_NAME="$(basename "$MODULE_DIR")"
BUILD_IGNORE_FILE="$MODULE_DIR/.buildignore"

# BUILD_DIR="/tmp/${MODULE_NAME}"
BUILD_ROOT="$HOME/.module_builds"
BUILD_DIR="$BUILD_ROOT/$MODULE_NAME"
RELEASE_DIR="$HOME/Nextcloud/prestashop/modules-zip"

# Check bash shell
if [ -z "$BASH_VERSION" ]; then
  echo -e "\033[31m\033[1m\n✖ ERREUR : Ce script doit être exécuté avec bash.\033[0m" >&2
  echo -e "\033[31mShell détecté : ${SHELL:-inconnu}\033[0m" >&2
  echo -e "\033[31mUtilisez : bash build-zip.sh ou ./build-zip.sh\033[0m" >&2
  exit 1
fi

# --- Console color ---
if command -v tput >/dev/null 2>&1; then
  RED="$(tput setaf 1)"
  YELLOW="$(tput setaf 3)"
  BOLD="$(tput bold)"
  RESET="$(tput sgr0)"
else
  RED=$'\033[31m'
  YELLOW=$'\033[33m'
  BOLD=$'\033[1m'
  RESET=$'\033[0m'
fi

# Check .buildignore file
if [ ! -f "$BUILD_IGNORE_FILE" ]; then
  echo -e "${RED}${BOLD}\n✖ ERREUR : Le fichier '.buildignore' est requis mais introuvable.\n${RESET}" >&2
  echo -e "${RED}Chemin attendu : $BUILD_IGNORE_FILE${RESET}" >&2
  echo -e "${RED}Veuillez créer ce fichier avec les patterns à exclure du build.${RESET}" >&2
  exit 1
fi

# Check composer
if ! command -v composer >/dev/null 2>&1; then
  echo -e "${RED}${BOLD}\n✖ ERREUR : Composer n'est pas installé ou introuvable dans le PATH.\n${RESET}" >&2
  echo -e "${RED}Veuillez installer Composer puis relancer ce script.${RESET}" >&2
  exit 1
fi

# Check zip
if ! command -v zip >/dev/null 2>&1; then
  echo -e "${RED}${BOLD}✖ ERREUR : La commande 'zip' est requise.${RESET}" >&2
  echo -e "${RED}Veuillez installer le paquet 'zip' puis relancer ce script.${RESET}" >&2
  exit 1
fi

# Check release directory (a missing one usually means Nextcloud is not mounted)
if [ ! -d "$RELEASE_DIR" ]; then
  echo -e "${RED}${BOLD}\n✖ ERREUR : Le dossier de releases est introuvable.\n${RESET}" >&2
  echo -e "${RED}Chemin attendu : $RELEASE_DIR${RESET}" >&2
  echo -e "${RED}Nextcloud est-il bien synchronisé sur cette machine ?${RESET}" >&2
  exit 1
fi

# --- Version ---
# The one declared in the main class is proposed as default
DEFAULT_VERSION="$(grep -oE "this->version = '[^']+'" "$MODULE_DIR/$MODULE_NAME.php" 2>/dev/null | grep -oE "[0-9]+(\.[0-9]+)*" | head -n 1)"

read -r -p "Version du module [${DEFAULT_VERSION:-ex. 1.0.0}] : " VERSION
VERSION="${VERSION:-$DEFAULT_VERSION}"

if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo -e "${RED}${BOLD}\n✖ ERREUR : Version invalide : '${VERSION}'. Format attendu : 1.0.0${RESET}" >&2
  exit 1
fi

if [ -n "$DEFAULT_VERSION" ] && [ "$VERSION" != "$DEFAULT_VERSION" ]; then
  echo -e "${YELLOW}⚠ La version saisie ($VERSION) diffère de celle déclarée dans $MODULE_NAME.php ($DEFAULT_VERSION).${RESET}"
  read -r -p "Continuer quand même ? [o/N] " CONFIRM
  if ! [[ "$CONFIRM" =~ ^[oOyY]$ ]]; then
    echo "Abandon."
    exit 1
  fi
fi

OUTPUT_ZIP="$RELEASE_DIR/v${VERSION}-${MODULE_NAME}.zip"

# Never overwrite a release silently
if [ -f "$OUTPUT_ZIP" ]; then
  echo -e "${YELLOW}⚠ L'archive existe déjà : $OUTPUT_ZIP${RESET}"
  read -r -p "L'écraser ? [o/N] " CONFIRM
  if ! [[ "$CONFIRM" =~ ^[oOyY]$ ]]; then
    echo "Abandon."
    exit 1
  fi
fi

# Clean before
rm -rf "$BUILD_DIR" "$OUTPUT_ZIP"
mkdir -p "$BUILD_ROOT"

echo "📦 Copie du module vers $BUILD_DIR ..."
rsync -av --quiet \
  --exclude-from="$BUILD_IGNORE_FILE" \
  "$MODULE_DIR/" "$BUILD_DIR/"

cd "$BUILD_DIR" || exit 1

echo "📦 Installation des dépendances de production..."
composer install --no-dev --quiet

echo "📦 Optimisation de l'autoloader..."
composer dump-autoload --optimize --no-dev --quiet

echo "📦 Création de l’archive zip finale..."
cd "$BUILD_ROOT"
zip -r "$OUTPUT_ZIP" "$(basename "$BUILD_DIR")" > /dev/null

echo "🧹 Nettoyage..."
rm -rf "$BUILD_DIR"
rmdir "$BUILD_ROOT" 2>/dev/null || true

echo "✅ Archive créée : $OUTPUT_ZIP"
