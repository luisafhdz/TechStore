#!/usr/bin/env bash
# Pruebas de integración (humo) de TechStore.
# Comprueban que la tienda web, la base de datos y el microservicio de IA funcionan juntos.
#
# Uso:  bash scripts/smoke-test.sh
# (con "docker compose up -d" corriendo y la base ya sembrada con db:seed)

set -u

WEB="${WEB_URL:-http://localhost:8080}"
IA="${IA_URL:-http://localhost:8002}"
FAILS=0

ok()   { echo "  [OK]    $1"; }
fail() { echo "  [FALLO] $1"; FAILS=$((FAILS + 1)); }

status_is() {   # descripción  url  código_esperado
    local code
    code=$(curl -s -o /dev/null -w '%{http_code}' "$2")
    if [ "$code" = "$3" ]; then ok "$1 (HTTP $code)"; else fail "$1 (esperado $3, recibido $code)"; fi
}

echo "== 1. Servicios arriba =="
status_is "Microservicio IA /health" "$IA/health" 200
status_is "Tienda web /up"           "$WEB/up"    200

echo "== 2. Tienda + base de datos =="
status_is "Catálogo responde" "$WEB/catalogo" 200
if curl -s "$WEB/catalogo" | grep -q 'product-card'; then
    ok "El catálogo muestra productos (lee de MySQL)"
else
    fail "El catálogo no muestra productos (¿se ejecutó db:seed?)"
fi

echo "== 3. Rutas protegidas =="
status_is "Carrito sin sesión redirige al login"  "$WEB/carrito"        302
status_is "Admin sin sesión redirige al login"    "$WEB/admin/products" 302

echo "== 4. Asistente: web -> microservicio IA =="
JAR=$(mktemp)
PAGE=$(curl -s -c "$JAR" -b "$JAR" "$WEB/login")
TOKEN=$(echo "$PAGE" | grep -o 'name="csrf-token" content="[^"]*"' | sed 's/.*content="//; s/"$//')

if [ -z "$TOKEN" ]; then
    fail "No se pudo obtener el token CSRF de la página de login"
else
    RESP=$(curl -s -c "$JAR" -b "$JAR" -X POST "$WEB/asistente" \
        -H "X-CSRF-TOKEN: $TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" \
        -d '{"message":"audifonos baratos"}')
    if echo "$RESP" | grep -q '"products":\[{' && echo "$RESP" | grep -q '"name":"Aud'; then
        ok "El chat devolvió audífonos recomendados"
    else
        fail "El chat no devolvió recomendaciones. Respuesta: $RESP"
    fi
fi

echo "== 5. Inicio de sesión de administrador (sesión en MySQL) =="
if [ -n "$TOKEN" ]; then
    LOGIN=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' -c "$JAR" -b "$JAR" -X POST "$WEB/login" \
        --data-urlencode "_token=$TOKEN" \
        --data-urlencode "email=admin@techstore.test" \
        --data-urlencode "password=Admin12345")
    if echo "$LOGIN" | grep -q '^302 .*/admin/products'; then
        ok "Login de admin redirige al panel ($LOGIN)"
    else
        fail "Login de admin falló ($LOGIN)"
    fi
    status_is "Panel de productos con sesión" "$WEB/admin/products" 200 >/dev/null 2>&1
    CODE=$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" "$WEB/admin/products")
    if [ "$CODE" = "200" ]; then ok "Panel de productos accesible con sesión (HTTP 200)"; else fail "Panel de productos (esperado 200, recibido $CODE)"; fi
fi

rm -f "$JAR"

echo
if [ "$FAILS" -eq 0 ]; then
    echo "RESULTADO: todas las pruebas de integración pasaron."
    exit 0
else
    echo "RESULTADO: $FAILS prueba(s) fallaron."
    exit 1
fi
