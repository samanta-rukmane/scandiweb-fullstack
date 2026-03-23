import React, { useEffect, useState } from "react";
import { Link, useLocation } from "react-router-dom";

export default function CategoryList() {
  const [categories, setCategories] = useState([]);
  const location = useLocation();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const currentSlug =
    location.state?.category ||
    (location.pathname.startsWith("/category/")
      ? location.pathname.replace("/category/", "")
      : "all");

      useEffect(() => {
        const GRAPHQL_URL = import.meta.env.VITE_GRAPHQL_URL || "http://localhost:8000/graphql";

        fetch(GRAPHQL_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ query: `{ categories { id name slug } }` }),
        })
          .then(res => res.json())
          .then(result => {
            setCategories(result?.data?.categories || []);
            setLoading(false);
          })
          .catch(err => {
            console.error(err);
            setError("Failed to load categories");
            setLoading(false);
          });
      }, []);

      if (loading) return <p className="products-state">Loading categories…</p>;
      if (error) return <p className="products-state error">{error}</p>;

  return (
    <nav className="category-nav">
      {categories.map((c) => {
        const isActive = currentSlug === c.slug;
        return (
          <Link
            key={c.id}
            to={`/category/${c.slug}`}
            className={isActive ? "active-category" : ""}
            data-testid={isActive ? "active-category-link" : "category-link"}
          >
            {c.name.toUpperCase()}
          </Link>
        );
      })}
    </nav>
  );
}