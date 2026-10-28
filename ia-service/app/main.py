from typing import List, Optional

from fastapi import FastAPI
from pydantic import BaseModel, Field

from .recommender import recommend

app = FastAPI(
    title="TechStore IA",
    description="Microservicio de recomendaciones y asistente virtual de TechStore.",
    version="1.0.0",
)


class Product(BaseModel):
    id: int
    name: str
    brand: str = ""
    category: str = ""
    parent_category: Optional[str] = None
    price: float
    stock: int = 0
    description: str = ""


class RecommendRequest(BaseModel):
    message: str = Field(min_length=1, max_length=300, description="Mensaje del cliente")
    products: List[Product] = Field(description="Catálogo actual enviado por la tienda")


class Recommendation(BaseModel):
    id: int
    score: float
    reason: str


class RecommendResponse(BaseModel):
    reply: str
    intent: str
    recommendations: List[Recommendation]


@app.get("/health")
def health():
    return {"status": "ok", "service": "techstore-ia"}


@app.post("/recommend", response_model=RecommendResponse)
def recommend_products(request: RecommendRequest):
    products = [p.model_dump() for p in request.products]
    return recommend(request.message, products)
