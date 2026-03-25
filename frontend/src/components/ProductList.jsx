import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import ProductCard from "./ProductCard";

const PRODUCT_FIELDS = `
  id
  name
  prices {
    amount
    currency {
      label
      symbol
    }
  }
  gallery
  inStock
  brand
  attributes { name type items { value displayValue } }
`;

export default function ProductList({ addToCart }) {
  const { slug } = useParams();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const categoryName = slug ? slug.toUpperCase() : "ALL";

  useEffect(() => {
    const categorySlug = slug || "all";

    const fetchProducts = async () => {
      try {
        setLoading(true);
        setError(null);

        const query = categorySlug === "all"
          ? `{ products { ${PRODUCT_FIELDS} } }`
          : `{ productsByCategory(category: "${categorySlug}") { ${PRODUCT_FIELDS} } }`;

        const response = await fetch("http://localhost:8000/graphql", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ query }),
        });

        const result = await response.json();

        if (result.errors) {
          console.error("GraphQL errors:", JSON.stringify(result.errors, null, 2));
          throw new Error("Failed to fetch products");
        }

        const fetchedProducts = categorySlug === "all"
          ? result.data.products
          : result.data.productsByCategory;

        setProducts(fetchedProducts || []);
      } catch (err) {
        console.error(err);
        setError("Something went wrong while loading products.");
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, [slug]);

  if (loading) return <div className="products-state">Loading products...</div>;
  if (error) return <div className="products-state error">{error}</div>;
  if (!products.length) return <div className="products-state">No products found.</div>;

  return (
    <div className="product-list-container">
      <div className="current-category">{categoryName}</div>
      <div className="product-list">
        {products.map((product) => (
          <ProductCard key={product.id} product={product} addToCart={addToCart} />
        ))}
      </div>
    </div>
  );
}