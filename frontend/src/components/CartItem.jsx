export default function CartItem({ item, changeQuantity }) {
  const image = item.product.gallery?.[0] || "/placeholder.png";

  // Получаем цену из массива prices
  const firstPrice = item.prices?.[0] ?? item.product?.prices?.[0];
  const priceSymbol = firstPrice?.currency?.symbol ?? "$";
  const priceAmount = firstPrice?.amount ?? 0;

  const getSelectedValue = (attrName) =>
    item.selectedAttributes?.find(a => a.name === attrName)?.value;

  return (
    <div data-testid="cart-item" className="cart-item">
      <div className="cart-item-info">
        <p className="cart-item-name">{item.product.name}</p>
        <p className="cart-item-price">
          {priceSymbol}{parseFloat(priceAmount).toFixed(2)}
        </p>

        {item.product.attributes?.map((attr) => {
          const selectedValue = getSelectedValue(attr.name);
          const attrNameLower = attr.name.toLowerCase();

          return (
            <div
              key={attr.name}
              className="cart-item-attribute-block"
              data-testid={`product-attribute-${attr.name}`}
            >
              <p className="cart-item-attribute-label">
                {attr.name.charAt(0).toUpperCase() + attr.name.slice(1)}:
              </p>

              <div className="cart-item-attribute-values">
                {attr.type === "swatch"
                  ? attr.items.map((option) => {
                      const isSelected = selectedValue === option.value;
                      return (
                        <div
                          key={option.value}
                          className={`cart-item-color-swatch ${isSelected ? "selected" : ""}`}
                          style={{ backgroundColor: option.value }}
                          data-testid={`product-attribute-${attr.name}-${option.value}${isSelected ? "-selected" : ""}`}
                        />
                      );
                    })
                  : attr.items.map((option) => {
                      const isSelected = selectedValue === option.value;
                      return (
                        <div
                          key={option.value}
                          className={`cart-item-attribute-btn ${isSelected ? "selected" : ""}`}
                          data-testid={`product-attribute-${attr.name}-${option.value}${isSelected ? "-selected" : ""}`}
                        >
                          {option.displayValue || option.value}
                        </div>
                      );
                    })}
              </div>
            </div>
          );
        })}
      </div>

      <div className="cart-item-quantity">
        <button
          className="cart-qty-btn"
          data-testid="cart-item-amount-increase"
          onClick={() => changeQuantity(item.product.id, item.selectedAttributes, 1)}
        >
          +
        </button>

        <span data-testid="cart-item-amount">{item.quantity}</span>

        <button
          className="cart-qty-btn"
          data-testid="cart-item-amount-decrease"
          onClick={() => changeQuantity(item.product.id, item.selectedAttributes, -1)}
        >
          −
        </button>
      </div>

      <div className="cart-item-image-wrapper">
        <img src={image} alt={item.product.name} className="cart-item-image" />
      </div>
    </div>
  );
}