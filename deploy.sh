#!/bin/bash
# Script interactivo para Docker y docker-compose con pantalla limpia
set -e

# Colores para mejor visualización
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

show_menu() {
    clear
    echo "===================================="
    echo "    Menú de Docker & Compose      "
    echo "===================================="
    echo "1) Instalar Docker en Ubuntu"
    echo "2) Verificar instalación de Docker y Docker Compose"
    echo "3) Ejecutar 'docker compose build'"
    echo "4) Ejecutar 'docker compose build --no-cache'"
    echo "5) Ejecutar 'docker compose up -d'"
    echo "6) Ver logs de contenedores"
    echo "7) Detener contenedores"
    echo "8) Limpiar sistema Docker"
    echo "9) Salir"
    echo "===================================="
}

pause() {
    echo ""
    read -n 1 -s -r -p "Presiona cualquier tecla para regresar al menú..."
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

install_docker() {
    echo "Instalando Docker..."
    
    # Verificar si ya está instalado
    if command -v docker &> /dev/null; then
        print_warning "Docker ya está instalado"
        docker --version
        return
    fi
    
    sudo apt-get update -y
    sudo apt-get install -y ca-certificates curl gnupg lsb-release
    
    # Crear directorio para keyrings si no existe
    sudo install -m 0755 -d /etc/apt/keyrings
    
    # Descargar GPG key
    sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg
    
    # Agregar repositorio
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Instalar Docker
    sudo apt-get update -y
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    # Agregar usuario al grupo docker
    sudo usermod -aG docker $USER
    
    print_success "Docker instalado correctamente"
    print_warning "Cierra sesión y vuelve a iniciar para aplicar cambios de grupo"
}

verify_docker() {
    echo "Verificando Docker..."
    if command -v docker &> /dev/null; then
        docker --version
        print_success "Docker encontrado"
    else
        print_error "Docker no está instalado"
        return 1
    fi
    
    echo ""
    echo "Verificando Docker Compose..."
    if docker compose version &> /dev/null; then
        docker compose version
        print_success "Docker Compose encontrado"
    else
        print_error "Docker Compose no está instalado"
        return 1
    fi
}

docker_build() {
    if [ -f docker-compose.yml ]; then
        echo "Ejecutando docker compose build..."
        docker compose build
        print_success "Build completado"
    else
        print_error "No se encontró docker-compose.yml en este directorio"
    fi
}

docker_build_no_cache() {
    if [ -f docker-compose.yml ]; then
        echo "Ejecutando docker compose build --no-cache..."
        docker compose build --no-cache
        print_success "Build sin caché completado"
    else
        print_error "No se encontró docker-compose.yml en este directorio"
    fi
}

docker_up() {
    if [ -f docker-compose.yml ]; then
        echo "Iniciando contenedores..."
        docker compose up -d
        print_success "Contenedores iniciados"
        echo ""
        docker compose ps
    else
        print_error "No se encontró docker-compose.yml en este directorio"
    fi
}

docker_logs() {
    if [ -f docker-compose.yml ]; then
        echo "Mostrando logs (Ctrl+C para salir)..."
        docker compose logs -f
    else
        print_error "No se encontró docker-compose.yml en este directorio"
    fi
}

docker_down() {
    if [ -f docker-compose.yml ]; then
        echo "Deteniendo contenedores..."
        docker compose down
        print_success "Contenedores detenidos"
    else
        print_error "No se encontró docker-compose.yml en este directorio"
    fi
}

docker_clean() {
    print_warning "Esto eliminará imágenes, contenedores y volúmenes no utilizados"
    read -p "¿Continuar? (y/n): " confirm
    if [ "$confirm" = "y" ]; then
        docker system prune -a --volumes -f
        print_success "Sistema Docker limpiado"
    fi
}

# Bucle principal
while true; do
    show_menu
    read -p "Elige una opción [1-9]: " option
    clear
    
    case $option in
        1) install_docker; pause ;;
        2) verify_docker; pause ;;
        3) docker_build; pause ;;
        4) docker_build_no_cache; pause ;;
        5) docker_up; pause ;;
        6) docker_logs; pause ;;
        7) docker_down; pause ;;
        8) docker_clean; pause ;;
        9) echo "Saliendo..."; exit 0 ;;
        *) print_error "Opción no válida"; pause ;;
    esac
done