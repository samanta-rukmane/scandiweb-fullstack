import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import parse from "html-react-parser";

export default function ProductDetails({ addToCart }) {
  const { id } = useParams();
  const productId = id;

  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(false);
  const [mainImage, setMainImage] = useState("");
  const [selectedAttributes, setSelectedAttributes] = useState({});

  useEffect(() => {
    if (!productId) return;

    const fetchProduct = async () => {
      try {
        setLoading(true);
        const query = `
        {
          product(id: "${productId}"){
            id
            name
            brand
            inStock
            description
            gallery
            prices {
              amount
              currency {
                label
                symbol
              }
            }
            attributes {
              name
              type
              items {
                value
                displayValue
              }
            }
          }
        }`;

        const response = await fetch(import.meta.env.VITE_GRAPHQL_URL || "http://localhost:8000/graphql", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ query }),
        });

        const result = await response.json();
        const fetchedProduct = result?.data?.product;
        setProduct(fetchedProduct);
        setMainImage(fetchedProduct?.gallery?.[0] || "");
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    };

    fetchProduct();
  }, [productId]);

  if (loading)
    return <div className="text-center py-20 text-gray-500">Loading product...</div>;
  if (!product)
    return <div className="text-center py-20 text-red-500">Product not found</div>;

  const handleSelect = (attrName, value) => {
    setSelectedAttributes((prev) => ({ ...prev, [attrName]: value }));
  };

  const attributes = product.attributes || [];
  const allSelected = attributes.every((attr) => selectedAttributes[attr.name]);

  const handleAddToCart = () => {
    const attributesArray = Object.entries(selectedAttributes).map(([name, value]) => ({
      name,
      value,
    }));
    addToCart({ ...product, selectedAttributes: attributesArray });
  };

  const showPrev = () => {
    if (!product?.gallery) return;
    const index = product.gallery.indexOf(mainImage);
    const prevIndex = index === 0 ? product.gallery.length - 1 : index - 1;
    setMainImage(product.gallery[prevIndex]);
  };

  const showNext = () => {
    if (!product?.gallery) return;
    const index = product.gallery.indexOf(mainImage);
    const nextIndex = (index + 1) % product.gallery.length;
    setMainImage(product.gallery[nextIndex]);
  };

  // Берём первую цену (USD)
  const firstPrice = product.prices?.[0];
  const priceSymbol = firstPrice?.currency?.symbol ?? "$";
  const priceAmount = firstPrice?.amount ?? 0;

  return (
    <div className="product-page">
      <div className="product-gallery" data-testid="product-gallery">
        {product.gallery?.map((img) => (
          <img
            key={img}
            src={img}
            alt={product.name}
            className={`gallery-thumbnail ${mainImage === img ? "border-2 border-black" : ""}`}
            onClick={() => setMainImage(img)}
          />
        ))}
      </div>

      <div className="product-main-image-wrapper">
        <img src={mainImage} alt={product.name} className="product-main-image" />
        <button className="gallery-arrow left" onClick={showPrev}>‹</button>
        <button className="gallery-arrow right" onClick={showNext}>›</button>
      </div>

      <div>
        <h2 className="text-2xl font-semibold">{product.brand}</h2>
        <h3 className="text-xl mb-6">{product.name}</h3>

        {attributes.map((attr) => (
          <div
            key={attr.name}
            className="attribute-container"
            data-testid={`product-attribute-${attr.name}`}
          >
            <strong className="attribute-label">{attr.name}:</strong>
            <div className="flex gap-2">
              {attr.type === "swatch"
                ? attr.items.map((item) => (
                    <button
                      key={item.value}
                      data-testid={`product-attribute-${attr.name}-${item.value}`}
                      style={{ backgroundColor: item.value }}
                      className={`color-swatch ${
                        selectedAttributes[attr.name] === item.value ? "selected" : ""
                      }`}
                      onClick={() => handleSelect(attr.name, item.value)}
                    />
                  ))
                : attr.items.map((item) => (
                    <button
                      key={item.value}
                      data-testid={`product-attribute-${attr.name}-${item.value}`}
                      className={`attribute-btn ${
                        selectedAttributes[attr.name] === item.value ? "selected" : ""
                      }`}
                      onClick={() => handleSelect(attr.name, item.value)}
                    >
                      {item.displayValue || item.value}
                    </button>
                  ))}
            </div>
          </div>
        ))}

        <p className="price-label">PRICE:</p>
        <p className="price-value">
          {priceSymbol}{parseFloat(priceAmount).toFixed(2)}
        </p>

        <button
          data-testid="add-to-cart"
          disabled={!allSelected || !product.inStock}
          className="add-to-cart-btn"
          onClick={handleAddToCart}
        >
          ADD TO CART
        </button>

        <div className="mt-6 text-sm" data-testid="product-description">
          {product.description ? parse(product.description) : null}
        </div>
      </div>
    </div>
  );
}