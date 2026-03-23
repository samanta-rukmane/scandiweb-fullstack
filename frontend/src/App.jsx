import React, { useState } from "react";
import { Routes, Route } from "react-router-dom";

import Header from "./components/Header";
import ProductList from "./components/ProductList";
import ProductDetails from "./components/ProductDetails";
import CartOverlay from "./components/CartOverlay";

export default function App() {
  const [cartItems, setCartItems] = useState([]);
  const [isCartOpen, setIsCartOpen] = useState(false);

  const addToCart = (product) => {
    setCartItems((prev) => {
      const existing = prev.find(
        (item) =>
          item.product.id === product.id &&
          JSON.stringify(item.selectedAttributes) ===
          JSON.stringify(product.selectedAttributes)
      );

      if (existing) {
        return prev.map((item) =>
          item === existing
            ? { ...item, quantity: item.quantity + 1 }
            : item
        );
      }

      return [
        ...prev,
        {
          id: crypto.randomUUID(),
          product: { ...product },
          quantity: 1,
          selectedAttributes: product.selectedAttributes
        }
      ];
    });

    setIsCartOpen(true);
  };

  const changeQuantity = (productId, attributes, delta) => {
    setCartItems((prev) =>
      prev
        .map((item) => {
          const sameProduct =
            item.product.id === productId &&
            JSON.stringify(item.selectedAttributes) ===
            JSON.stringify(attributes);

          if (!sameProduct) return item;

          return { ...item, quantity: Math.max(item.quantity + delta, 0) };
        })
        .filter((item) => item.quantity > 0)
    );
  };

  const clearCart = () => setCartItems([]);

  const total = cartItems.reduce(
    (sum, item) => sum + (item.product.prices?.[0]?.amount ?? 0) * item.quantity,
    0
  );

  const totalItems = cartItems.reduce(
    (sum, item) => sum + item.quantity,
    0
  );

  return (
    <>
      <Header
        totalItems={totalItems}
        openCart={() => setIsCartOpen(true)}
      />

      <Routes>
        <Route path="/" element={<ProductList addToCart={addToCart} />} />
        <Route path="/category/:slug" element={<ProductList addToCart={addToCart} />} />
        <Route path="/product/:id" element={<ProductDetails addToCart={addToCart} />} />
      </Routes>

      {isCartOpen && (
        <CartOverlay
          cartItems={cartItems}
          changeQuantity={changeQuantity}
          total={total}
          clearCart={clearCart}
          onClose={() => setIsCartOpen(false)}
        />
      )}
    </>
  );
}