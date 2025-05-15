#!/bin/bash

#############################
# VARIABLES
#############################

GIT_REPO=$1  
GIT_BRANCH=${2:-'main'} 
BASE_DIR=${3}
DEPLOY_DIR="${BASE_DIR}/releases/$(date +%Y%m%d%H%M%S)"
PHP_FPM_SERVICE='php8.1-fpm'
SSH_USER=$4
SSH_PASSWORD=$5

LOG_FILE="/var/log/deploy.log"

RED='\033[31m'
GREEN='\033[32m'
NC='\033[0m'

#############################
# FONCTIONS
#############################

log() {
    echo -e "${GREEN}[INFO]${NC} $1"
    echo "$(date) : $1" >> $LOG_FILE
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
    echo "$(date) : [ERROR] $1" >> $LOG_FILE
    exit 1
}

check_dependencies() {
    for cmd in git composer php ssh sshpass; do
        if ! command -v $cmd &>/dev/null; then
            error "La commande '$cmd' est introuvable. Veuillez l'installer."
        fi
    done
}

clone_repo() {
    log "Clonage du dépôt $GIT_REPO (branche $GIT_BRANCH)..."
    git clone --branch $GIT_BRANCH $GIT_REPO $DEPLOY_DIR || error "Échec du clonage du dépôt."
    #sshpass -p "$SSH_PASSWORD" ssh "$SSH_USER"@ssh.thewhiterabbit.fr ""
}

install_dependencies() {
    log "Installation des dépendances avec Composer..."
    cd $DEPLOY_DIR && make install #on suppose que le Makefile est présent
    # && composer install --no-dev --optimize-autoloader || error "Échec de l'installation des dépendances."
}
    #sshpass -p "$SSH_PASSWORD" ssh "$SSH_USER"@ssh.thewhiterabbit.fr "

restart_services() {
    log "Redémarrage du service PHP-FPM..."
}

#############################
# LOGIQUE PRINCIPALE
#############################

log "Début du déploiement du projet sur $GIT_REPO (branche $GIT_BRANCH)..."

check_dependencies
clone_repo
install_dependencies
#restart_services

log "Déploiement terminé avec succès dans $DEPLOY_DIR."

exit 0
