# TechStore IA (microservicio FastAPI)

Asistente virtual y motor de recomendaciones. Recibe el mensaje del cliente y el catálogo
(enviado por Laravel) y responde con los productos que mejor coinciden. No usa servicios externos.

## Ejecutar (Windows, PowerShell)
```
cd ia-service
python -m venv venv
.\venv\Scripts\Activate.ps1
pip install -r requirements.txt
uvicorn app.main:app --port 8001 --reload
```
- Salud del servicio: http://127.0.0.1:8001/health
- Documentación interactiva (Swagger): http://127.0.0.1:8001/docs

## Pruebas unitarias
```
python -m unittest discover -s tests -v
```

## Contrato
`POST /recommend`
```json
{ "message": "audífonos baratos",
  "products": [{"id":1,"name":"...","brand":"...","category":"...","parent_category":"...","price":349.0,"stock":10,"description":"..."}] }
```
Respuesta:
```json
{ "reply": "Encontré 2 opciones ...", "intent": "recommendation",
  "recommendations": [{"id": 9, "score": 3.0, "reason": "Coincide con: audifonos"}] }
```
`intent` puede ser: `recommendation`, `no_results`, `greeting`, `thanks`, `help`.
