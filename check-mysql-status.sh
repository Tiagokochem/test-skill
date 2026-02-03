#!/bin/bash

echo "=== Verificando estado do MySQL ==="
echo ""

echo "1. Verificando se o container MySQL está rodando:"
docker compose ps mysql
echo ""

echo "2. Verificando bancos de dados existentes:"
docker compose exec mysql mysql -u root -proot -e "SHOW DATABASES;" 2>/dev/null || echo "Erro ao conectar"
echo ""

echo "3. Verificando usuários MySQL:"
docker compose exec mysql mysql -u root -proot -e "SELECT User, Host FROM mysql.user WHERE User LIKE 'task%';" 2>/dev/null || echo "Erro ao conectar"
echo ""

echo "4. Verificando permissões do usuário task_user:"
docker compose exec mysql mysql -u root -proot -e "SHOW GRANTS FOR 'task_user'@'%';" 2>/dev/null || echo "Usuário não existe ou erro"
echo ""

echo "5. Tentando conectar com task_user:"
docker compose exec mysql mysql -u task_user -proot -e "SELECT 1;" 2>&1
echo ""

echo "6. Verificando .env no container app:"
docker compose exec app cat .env 2>/dev/null | grep -E "^DB_" || echo "Erro ao ler .env"
echo ""

echo "=== Fim da verificação ==="
