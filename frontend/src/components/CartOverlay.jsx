import CartItem from "./CartItem";

const getPrice = (item) =>
  item.prices?.[0]?.amount ??
  item.product?.prices?.[0]?.amount ??
  0;

export default function CartOverlay({
  cartItems,
  changeQuantity,
  total,
  clearCart,
  onClose,
  placeOrder
}) {
  const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);

  const computedTotal = cartItems.reduce(
    (sum, item) => sum + item.quantity * getPrice(item),
    0
  );

  const handlePlaceOrder = async () => {
    if (!cartItems.length) return;

    const query = `
      mutation CreateOrder($items: [OrderItemInput!]!, $total: Float!) {
        createOrder(items: $items, total: $total) {
          id
          total
        }
      }
    `;

    const orderItems = cartItems.map(item => ({
      productId: item.product.id,
      quantity: item.quantity,
      attributes: (item.selectedAttributes || []).map(attr => ({
        name: attr.name,
        value: attr.value
      }))
    }));

    try {
      const GRAPHQL_URL = import.meta.env.VITE_GRAPHQL_URL || 'http://localhost:8000/graphql';
      const resp = await fetch(GRAPHQL_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          query,
          variables: { items: orderItems, total: computedTotal }
        })
      });

      const json = await resp.json();

      if (json.errors) {
        console.error("GraphQL errors:", JSON.stringify(json.errors, null, 2));
      }

      if (json.data?.createOrder?.id) {
        clearCart();
        onClose();
      } else {
        console.error("Failed to place order", json);
      }
    } catch (err) {
      console.error('Error placing order:', err);
    }
  };

  return (
    <>
      <div className="cart-overlay-backdrop" onClick={onClose} />

      <div className="cart-overlay" data-testid="cart-overlay">
        <p className="cart-title">
          <strong>My Bag</strong>, {totalItems} {totalItems === 1 ? 'item' : 'items'}
        </p>

        {cartItems.length === 0 && (
          <p className="cart-empty">Your cart is empty</p>
        )}

        {cartItems.map((item) => {
          const safeProduct = item.product || {
            id: 'unknown',
            name: 'Unknown Product',
            attributes: [],
            gallery: []
          };

          return (
            <CartItem
              key={
                item.id ||
                `${safeProduct.id}-${(item.selectedAttributes || []).map(a => a.value).join('-')}`
              }
              item={{
                ...item,
                product: safeProduct,
                selectedAttributes: item.selectedAttributes || []
              }}
              changeQuantity={changeQuantity}
            />
          );
        })}

        <div className="cart-total-row">
          <span className="total-label">Total</span>
          <span className="total-value" data-testid="cart-total">
            ${computedTotal.toFixed(2)}
          </span>
        </div>

        <div className="cart-actions">
          <button
            data-testid="place-order"
            disabled={cartItems.length === 0}
            className="place-order-btn"
            onClick={handlePlaceOrder}
          >
            PLACE ORDER
          </button>
        </div>
      </div>
    </>
  );
}