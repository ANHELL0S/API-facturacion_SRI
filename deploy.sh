#!/bin/bash

# Script interactivo para Docker y docker-compose con pantalla limpia

set -e

show_menu() {
    clear
    echo "===================================="
    echo "       Menú de Docker & Compose      "
    echo "===================================="
    echo "1) Instalar Docker en Ubuntu"
    echo "2) Verificar instalación de Docker y Docker Compose"
    echo "3) Ejecutar 'docker-compose build'"
    echo "4) Ejecutar 'docker-compose build --no-cache'"
    echo "5) Salir"
    echo "===================================="
}

pause() {
    read -n 1 -s -r -p "Presiona cualquier tecla para regresar al menú..."
}

install_docker() {
    echo "Instalando Docker..."
    sudo apt-get update -y
    sudo apt-get install -y ca-certificates curl gnupg lsb-release
    sudo install -m 0755 -d /etc/apt/keyrings
    sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    sudo apt-get update -y
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    echo "Docker instalado correctamente ✅"
}

verify_docker() {
    echo "Verificando Docker..."
    docker --version
    echo "Verificando Docker Compose..."
    docker compose version
}

docker_build() {
    if [ -f docker-compose.yml ]; then
        echo "Ejecutando docker-compose build..."
        docker compose build
    else
        echo "No se encontró docker-compose.yml en este directorio."
    fi
}

docker_build_no_cache() {
    if [ -f docker-compose.yml ]; then
        echo "Ejecutando docker-compose build --no-cache..."
        docker compose build --no-cache
    else
        echo "No se encontró docker-compose.yml en este directorio."
    fi
}

# Bucle principal
while true; do
    show_menu
    read -p "Elige una opción [1-5]: " option
    clear
    case $option in
        1) install_docker; pause ;;
        2) verify_docker; pause ;;
        3) docker_build; pause ;;
        4) docker_build_no_cache; pause ;;
        5) echo "Saliendo..."; exit 0 ;;
        *) echo "Opción no válida"; pause ;;
    esac
done
