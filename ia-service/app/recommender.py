"""
Motor de recomendación de TechStore (basado en reglas, sin servicios externos).

Recibe el mensaje del cliente y el catálogo, y devuelve una respuesta en español
con los productos que mejor coinciden. Es una función pura: no usa base de datos
ni red, por lo que es fácil de probar (ver tests/test_recommender.py).
"""
import re
import unicodedata

MAX_RESULTS = 4

STOPWORDS = {
    "un", "una", "unos", "unas", "el", "la", "los", "las", "de", "del", "al", "y", "o", "a",
    "en", "para", "por", "con", "sin", "que", "quiero", "busco", "necesito", "me", "te", "se",
    "lo", "mi", "tu", "su", "es", "son", "hay", "tienen", "tienes", "tiene", "algo", "algun",
    "alguna", "alguno", "recomienda", "recomiendame", "recomiendas", "recomendar", "muestrame",
    "dame", "ver", "mostrar", "comprar", "compro", "comprarme", "como", "cual", "cuales", "mas",
    "muy", "bien", "buen", "buena", "bueno", "favor", "pesos", "precio", "costo", "cuesta",
    "menos", "menor", "hasta", "maximo", "minimo", "desde", "entre", "mayor", "debajo",
    "arriba", "unos", "estoy", "buscando", "puedes", "puede", "quisiera", "opcion", "opciones",
}

GREETINGS = {"hola", "buenas", "buenos", "hey", "saludos", "buenas tardes", "buenos dias"}
THANKS = {"gracias", "thanks", "grax"}
HELP = {"ayuda", "ayudar", "ayudame", "puedes", "haces", "funcionas", "opciones", "menu"}

CHEAP_WORDS = {"barato", "baratos", "barata", "baratas", "economico", "economicos", "economica",
               "economicas", "accesible", "accesibles"}
PREMIUM_WORDS = {"premium", "caro", "caros", "profesional", "profesionales"}

# Grupos de sinónimos (ya sin acentos). Se les aplica el mismo stemming que al texto.
_SYNONYM_GROUPS = [
    {"audifonos", "auriculares", "headphones", "earbuds", "audifono"},
    {"celular", "celulares", "telefono", "smartphone", "movil", "phone"},
    {"bocina", "altavoz", "parlante", "speaker"},
    {"mouse", "raton"},
    {"teclado", "keyboard"},
    {"monitor", "display"},
    {"camara", "vigilancia", "seguridad", "camera"},
    {"foco", "luz", "lampara", "bombilla", "led"},
    {"cargador", "cargar", "carga"},
    {"bateria", "powerbank", "power", "bank"},
    {"microfono", "mic", "streaming", "podcast"},
    {"laptop", "computadora", "pc", "computo", "computacion", "ordenador"},
    {"gamer", "gaming", "juego", "videojuego", "jugar"},
    {"inalambrico", "bluetooth", "wireless"},
    {"cable", "cableado"},
    {"adaptador", "convertidor"},
    {"hub", "concentrador", "puerto"},
    {"soporte", "base", "stand"},
    {"protector", "cristal", "templado", "mica", "funda"},
    {"tablet", "ipad"},
    {"hogar", "casa", "inteligente", "smart", "domotica"},
]


def _strip_accents(text: str) -> str:
    text = unicodedata.normalize("NFD", text.lower())
    return "".join(c for c in text if unicodedata.category(c) != "Mn")


def _normalize(text: str) -> str:
    return _strip_accents(text).replace("wi-fi", "wifi")


def _stem(word: str) -> str:
    """Quita plurales básicos del español (luces->luz, monitores->monitor, cables->cable)."""
    if len(word) > 4 and word.endswith("ces"):
        return word[:-3] + "z"
    if len(word) > 4 and word.endswith("es") and word[-3] in "rlndz":
        return word[:-2]
    if len(word) > 3 and word.endswith("s"):
        return word[:-1]
    return word


def _words(text: str) -> list:
    return re.findall(r"[a-z0-9]+", _normalize(text))


def _stems(text: str) -> set:
    return {_stem(w) for w in _words(text) if w not in STOPWORDS and not w.isdigit()}


_SYNONYMS = {}
for _group in _SYNONYM_GROUPS:
    _stemmed = {_stem(w) for w in _group}
    for _w in _stemmed:
        _SYNONYMS.setdefault(_w, set()).update(_stemmed)


def _expand(token: str) -> set:
    return _SYNONYMS.get(token, {token})


# ---------------------------------------------------------------- precio
_NUM = r"\$?\s*([\d][\d,]*(?:\.\d+)?)"
_RE_BETWEEN = re.compile(r"entre\s*" + _NUM + r"\s*y\s*" + _NUM)
_RE_MAX = re.compile(r"(?:menos de|menos que|menor a|menor que|hasta|maximo|no mas de|debajo de|por debajo de|bajo)\s*" + _NUM)
_RE_MIN = re.compile(r"(?:mas de|mayor a|mayor que|desde|minimo|por encima de|arriba de)\s*" + _NUM)


def _to_number(raw: str):
    try:
        return float(raw.replace(",", ""))
    except ValueError:
        return None


def _extract_price(norm_text: str):
    """Devuelve (precio_min, precio_max, texto_sin_las_frases_de_precio)."""
    low = high = None
    m = _RE_BETWEEN.search(norm_text)
    if m:
        a, b = _to_number(m.group(1)), _to_number(m.group(2))
        if a is not None and b is not None:
            low, high = min(a, b), max(a, b)
        norm_text = _RE_BETWEEN.sub(" ", norm_text)
    m = _RE_MAX.search(norm_text)
    if m:
        high = _to_number(m.group(1))
        norm_text = _RE_MAX.sub(" ", norm_text)
    m = _RE_MIN.search(norm_text)
    if m:
        low = _to_number(m.group(1))
        norm_text = _RE_MIN.sub(" ", norm_text)
    return low, high, norm_text


# ---------------------------------------------------------------- scoring
def _score(product: dict, tokens: list):
    name = _stems(product.get("name", ""))
    brand = _stems(product.get("brand", ""))
    cats = _stems(product.get("category", "")) | _stems(product.get("parent_category") or "")
    desc = _stems(product.get("description", ""))

    total = 0.0
    matched = []
    for original, token in tokens:
        group = _expand(token)
        if group & name or group & cats or group & brand:
            weight = 3.0
        elif group & desc:
            weight = 1.0
        else:
            continue
        total += weight
        matched.append(original)
    return total, matched


def _categories(products: list) -> list:
    names = {p.get("parent_category") or p.get("category") for p in products}
    return sorted(n for n in names if n)


def _fmt(price: float) -> str:
    return f"${price:,.2f}"


def recommend(message: str, products: list) -> dict:
    norm = _normalize(message)
    words = _words(message)
    low, high, text_wo_price = _extract_price(norm)

    word_set = set(words)
    sort_cheap = bool(word_set & CHEAP_WORDS)
    sort_premium = bool(word_set & PREMIUM_WORDS)

    # (palabra original, raíz) de los términos que realmente describen un producto
    tokens = []
    for w in re.findall(r"[a-z0-9]+", text_wo_price):
        if w in STOPWORDS or w.isdigit() or w in CHEAP_WORDS or w in PREMIUM_WORDS:
            continue
        if w in GREETINGS or w in THANKS or w in HELP:
            continue
        tokens.append((w, _stem(w)))

    has_budget = low is not None or high is not None
    categories = _categories(products)
    cat_text = ", ".join(categories) if categories else "nuestras categorías"

    # ---- mensajes que no son búsquedas
    if not tokens and not has_budget and not sort_cheap and not sort_premium:
        if word_set & THANKS:
            return {"reply": "¡Con gusto! Si necesitas algo más, pregúntame.", "intent": "thanks", "recommendations": []}
        if word_set & GREETINGS:
            return {"reply": "¡Hola! Soy el asistente de TechStore. Dime qué buscas, por ejemplo: "
                             "«audífonos baratos» o «mouse para gaming».", "intent": "greeting", "recommendations": []}
        return {"reply": "Puedo recomendarte productos del catálogo. Cuéntame qué buscas, tu presupuesto o la "
                         f"categoría que te interesa: {cat_text}.", "intent": "help", "recommendations": []}

    available = [p for p in products if p.get("stock", 0) > 0]

    # ---- coincidencias por texto
    scored = []
    for p in available:
        if tokens:
            score, matched = _score(p, tokens)
            if score <= 0:
                continue
        else:  # solo presupuesto u orden por precio: se consideran todos los productos
            score, matched = 1.0, []
        scored.append((p, score, matched))

    in_budget = [
        item for item in scored
        if (low is None or item[0]["price"] >= low) and (high is None or item[0]["price"] <= high)
    ]

    if not in_budget:
        if scored and has_budget:
            cheapest = min(item[0]["price"] for item in scored)
            return {"reply": f"Encontré productos relacionados, pero ninguno dentro de tu presupuesto. "
                             f"El más económico cuesta {_fmt(cheapest)}.",
                    "intent": "no_results", "recommendations": []}
        return {"reply": "No encontré productos que coincidan con tu búsqueda. "
                         f"Prueba con una categoría: {cat_text}.",
                "intent": "no_results", "recommendations": []}

    if sort_cheap:
        in_budget.sort(key=lambda it: (-it[1], it[0]["price"]))
        if tokens:
            in_budget.sort(key=lambda it: it[0]["price"])
    elif sort_premium:
        in_budget.sort(key=lambda it: (-it[1], -it[0]["price"]))
    else:
        in_budget.sort(key=lambda it: (-it[1], it[0]["price"], it[0]["name"]))
    if has_budget and not tokens:
        in_budget.sort(key=lambda it: it[0]["price"])

    top = in_budget[:MAX_RESULTS]
    recs = []
    for product, score, matched in top:
        if matched:
            reason = "Coincide con: " + ", ".join(dict.fromkeys(matched))
        else:
            reason = "Dentro de tu presupuesto"
        recs.append({"id": product["id"], "score": score, "reason": reason})

    n = len(recs)
    if n == 1:
        reply = "Encontré 1 opción que puede servirte"
    else:
        reply = f"Encontré {n} opciones que pueden servirte"
    if sort_cheap or (has_budget and not tokens):
        reply += ", de menor a mayor precio"
    elif sort_premium:
        reply += ", de mayor a menor precio"
    if high is not None and low is not None:
        reply += f" (entre {_fmt(low)} y {_fmt(high)})"
    elif high is not None:
        reply += f" (hasta {_fmt(high)})"
    elif low is not None:
        reply += f" (desde {_fmt(low)})"
    reply += ":"

    return {"reply": reply, "intent": "recommendation", "recommendations": recs}
