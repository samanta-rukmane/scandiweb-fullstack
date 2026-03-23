import { Link } from "react-router-dom";

export default function ProductCard({ product, addToCart }) {
  const image = product.gallery?.[0];

  // Берём первую цену
  const firstPrice = product.prices?.[0];
  const priceSymbol = firstPrice?.currency?.symbol ?? "$";
  const priceAmount = firstPrice?.amount ?? 0;

  const handleQuickAdd = (e) => {
    e.preventDefault();

    const selectedAttributes = product.attributes?.map(attr => ({
      name: attr.name,
      value: attr.items[0].value
    })) || [];

    addToCart({
      ...product,
      selectedAttributes,
      quantity: 1
    });
  };

  return (
    <div
      className="product-card"
      data-testid={`product-${product.id}`}
    >
      <Link
        to={`/product/${product.id}`}
        state={{ category: product.category?.slug || "all" }}
      >
        <div className="product-image-wrapper">
          <img src={image} alt={product.name} className="product-image" />
          {!product.inStock && <div className="out-of-stock">OUT OF STOCK</div>}
        </div>

        <div className="product-info">
          <p className="product-name">{product.name}</p>
          <p className="product-price">
            {priceSymbol}{parseFloat(priceAmount).toFixed(2)}
          </p>
        </div>
      </Link>

      {product.inStock && (
        <button onClick={handleQuickAdd} className="quick-shop-btn">+</button>
      )}
    </div>
  );
}