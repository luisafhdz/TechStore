import unittest

from app.recommender import recommend

CATALOG = [
    {"id": 1, "name": "Teclado Mecánico K552", "brand": "Redragon", "category": "Teclados", "parent_category": "Computación", "price": 899.0, "stock": 25, "description": "Teclado mecánico compacto con switches rojos e iluminación RGB."},
    {"id": 2, "name": "Mouse Gamer G203", "brand": "Logitech", "category": "Mouse", "parent_category": "Computación", "price": 549.0, "stock": 30, "description": "Mouse con sensor de 8000 DPI e iluminación personalizable."},
    {"id": 3, "name": "Mouse Inalámbrico M185", "brand": "Logitech", "category": "Mouse", "parent_category": "Computación", "price": 249.0, "stock": 60, "description": "Mouse inalámbrico compacto con receptor USB."},
    {"id": 4, "name": "Monitor 24\" Full HD", "brand": "Samsung", "category": "Monitores", "parent_category": "Computación", "price": 2799.0, "stock": 12, "description": "Monitor IPS de 24 pulgadas, 75 Hz, entrada HDMI."},
    {"id": 5, "name": "Monitor Curvo 27\"", "brand": "LG", "category": "Monitores", "parent_category": "Computación", "price": 4999.0, "stock": 8, "description": "Monitor curvo de 27 pulgadas, 165 Hz, ideal para gaming."},
    {"id": 6, "name": "Cargador Rápido 20W USB-C", "brand": "Anker", "category": "Cargadores", "parent_category": "Celulares y tablets", "price": 299.0, "stock": 80, "description": "Cargador de pared USB-C con carga rápida de 20W."},
    {"id": 7, "name": "Protector de Pantalla Cristal Templado", "brand": "Spigen", "category": "Protectores", "parent_category": "Celulares y tablets", "price": 199.0, "stock": 100, "description": "Cristal templado 9H con kit de instalación."},
    {"id": 8, "name": "Audífonos Bluetooth Tune 510BT", "brand": "JBL", "category": "Audífonos", "parent_category": "Audio", "price": 799.0, "stock": 35, "description": "Audífonos on-ear inalámbricos con 40 horas de batería."},
    {"id": 9, "name": "Audífonos In-Ear con Micrófono", "brand": "Sony", "category": "Audífonos", "parent_category": "Audio", "price": 349.0, "stock": 50, "description": "Audífonos con cable, graves potentes y micrófono integrado."},
    {"id": 10, "name": "Cámara de Seguridad Wi-Fi 1080p", "brand": "TP-Link Tapo", "category": "Cámaras", "parent_category": "Para el hogar", "price": 599.0, "stock": 20, "description": "Cámara interior con visión nocturna y detección de movimiento."},
    {"id": 11, "name": "Enchufe Inteligente Wi-Fi", "brand": "TP-Link Kasa", "category": "Dispositivos inteligentes", "parent_category": "Para el hogar", "price": 299.0, "stock": 0, "description": "Enchufe controlable desde el celular."},
]


def ids(result):
    return [r["id"] for r in result["recommendations"]]


class RecommenderTest(unittest.TestCase):
    def test_audifonos_baratos_ordena_por_precio(self):
        r = recommend("Busco audífonos baratos", CATALOG)
        self.assertEqual(r["intent"], "recommendation")
        self.assertEqual(ids(r)[:2], [9, 8])  # Sony (349) antes que JBL (799)

    def test_mouse_gaming_prioriza_gamer(self):
        r = recommend("quiero un mouse para gaming", CATALOG)
        self.assertEqual(ids(r)[0], 2)

    def test_cargador_para_celular(self):
        r = recommend("necesito un cargador para mi celular", CATALOG)
        self.assertEqual(ids(r)[0], 6)

    def test_presupuesto_maximo(self):
        r = recommend("monitor por menos de 3000 pesos", CATALOG)
        self.assertEqual(ids(r), [4])

    def test_presupuesto_imposible_informa_el_mas_barato(self):
        r = recommend("monitor menos de $100", CATALOG)
        self.assertEqual(r["intent"], "no_results")
        self.assertIn("$2,799.00", r["reply"])

    def test_solo_presupuesto_lista_por_precio(self):
        r = recommend("algo hasta 250 pesos", CATALOG)
        self.assertEqual(ids(r), [7, 3])  # 199 y 249

    def test_rango_entre(self):
        r = recommend("audífonos entre 300 y 500", CATALOG)
        self.assertEqual(ids(r), [9])

    def test_no_recomienda_productos_agotados(self):
        r = recommend("enchufe inteligente", CATALOG)
        self.assertNotIn(11, ids(r))

    def test_wifi_con_guion_y_sin_acentos(self):
        r = recommend("camara de seguridad wifi", CATALOG)
        self.assertEqual(ids(r)[0], 10)

    def test_saludo_gracias_y_ayuda(self):
        self.assertEqual(recommend("Hola", CATALOG)["intent"], "greeting")
        self.assertEqual(recommend("gracias", CATALOG)["intent"], "thanks")
        self.assertEqual(recommend("ayuda", CATALOG)["intent"], "help")

    def test_sin_coincidencias_sugiere_categorias(self):
        r = recommend("asdfghjk", CATALOG)
        self.assertEqual(r["intent"], "no_results")
        self.assertIn("Audio", r["reply"])

    def test_maximo_cuatro_resultados(self):
        r = recommend("computación", CATALOG)
        self.assertLessEqual(len(r["recommendations"]), 4)


if __name__ == "__main__":
    unittest.main()
