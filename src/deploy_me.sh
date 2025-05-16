#!/bin/bash

#############################
# VARIABLES
#############################

GIT_REPO=$1
GIT_TAG=${2:-'main'} 
BASE_DIR=${3}
DEPLOY_DIR="/var/www/${BASE_DIR}/releases/$(date +%Y%m%d%H%M%S)"
LOG_FILE="../var/log/deploy.log"

RED='\033[31m'
GREEN='\033[32m'
NC='\033[0m'

#############################
# FONCTIONS
#############################

log() {
    echo -e "${GREEN}[INFO]${NC} $1"
    echo "$(date) : $1" >> "$LOG_FILE"
    sync
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
    echo "$(date) : [ERROR] $1" >> "$LOG_FILE"
    sync
    exit 1
}

check_dependencies() {
    for cmd in git composer php ssh; do
        if ! command -v $cmd &>/dev/null; then
            error "La commande '$cmd' est introuvable. Veuillez l'installer."
        fi
    done
}

install_dependencies() {
#veiller à fournir un Makefile qui appelera make install avec les configurations du projet
}

clone_repo() {
    log "Début du clonage du dépôt..."

    {
        git clone --branch "$GIT_TAG" "$GIT_REPO" "$DEPLOY_DIR"
    } >> "$LOG_FILE" 2>&1 || error "Échec du clonage"

    log "Dépôt cloné avec succès dans $DEPLOY_DIR"
}

#############################
# LOGIQUE PRINCIPALE
#############################

log "Début du déploiement du projet sur $GIT_REPO (branche $GIT_TAG)..."

check_dependencies
clone_repo
#

log "Déploiement terminé avec succès dans $DEPLOY_DIR."

exit 0
