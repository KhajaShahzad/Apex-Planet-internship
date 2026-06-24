import React, { useState, useEffect } from 'react';
import ProductCard from '../components/ProductCard';
import { Search, SlidersHorizontal, RefreshCw, ChevronLeft, ChevronRight, X } from 'lucide-react';

export default function Home({ currentUser, onAddToCart, onViewDetails, showToast }) {
  const [products, setProducts] = useState([]);
  const [categories] = useState(['All', 'Peripherals', 'Audio', 'Wearables', 'Video', 'Accessories']);
  const [pagination, setPagination] = useState({ currentPage: 1, totalPages: 1, totalItems: 0 });
  
  // Search & Filter state
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [minPrice, setMinPrice] = useState('');
  const [maxPrice, setMaxPrice] = useState('');
  const [minRating, setMinRating] = useState('');
  const [sortBy, setSortBy] = useState('createdAt');
  const [sortOrder, setSortOrder] = useState('desc');
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);
  const [showMobileFilters, setShowMobileFilters] = useState(false);

  const fetchProducts = async () => {
    setLoading(true);
    try {
      let url = `http://localhost:5000/api/products?page=${page}&limit=6&sortBy=${sortBy}&order=${sortOrder}`;
      
      if (search) url += `&search=${encodeURIComponent(search)}`;
      if (selectedCategory && selectedCategory !== 'All') url += `&category=${encodeURIComponent(selectedCategory)}`;
      if (minPrice) url += `&minPrice=${minPrice}`;
      if (maxPrice) url += `&maxPrice=${maxPrice}`;
      if (minRating) url += `&rating=${minRating}`;

      const res = await fetch(url);
      if (!res.ok) throw new Error("Failed to load catalog");
      
      const data = await res.json();
      setProducts(data.products);
      setPagination(data.pagination);
    } catch (err) {
      console.error(err);
      showToast(err.message || "Error loading products", "danger");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, [page, selectedCategory, sortBy, sortOrder]); // Re-fetch automatically on these changes

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    setPage(1);
    fetchProducts();
  };

  const handleResetFilters = () => {
    setSearch('');
    setSelectedCategory('All');
    setMinPrice('');
    setMaxPrice('');
    setMinRating('');
    setSortBy('createdAt');
    setSortOrder('desc');
    setPage(1);
  };

  return (
    <div className="storefront-layout">
      {/* Sidebar Filters */}
      <aside className="glass-panel" style={{
        padding: '1.5rem',
        height: 'fit-content',
        position: 'sticky',
        top: 100,
        display: showMobileFilters ? 'block' : 'none', // Controlled responsive visibility
        border: '1px solid var(--border-glass)',
        '@media (min-width: 969px)': { display: 'block' } // Handled below in CSS override style
      }} id="desktop-filters">
        <style dangerouslySetInnerHTML={{__html: `
          @media (min-width: 969px) {
            #desktop-filters { display: block !important; }
            #filter-toggle-btn { display: none !important; }
          }
        `}} />
        
        <div style={{ display: 'flex', alignItems: 'center', justify: 'between', marginBottom: '1.5rem', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem' }}>
          <h3 style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '1.1rem' }}>
            <SlidersHorizontal size={18} className="text-gradient" />
            Filters
          </h3>
          <button 
            onClick={handleResetFilters}
            style={{
              marginLeft: 'auto',
              background: 'none',
              border: 'none',
              color: 'var(--primary)',
              fontSize: '0.8rem',
              fontWeight: 600,
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              gap: '0.25rem'
            }}
          >
            <RefreshCw size={12} />
            Reset
          </button>
        </div>

        {/* Categories */}
        <div className="form-group">
          <label className="form-label">Category</label>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.4rem' }}>
            {categories.map(cat => (
              <button
                key={cat}
                onClick={() => { setSelectedCategory(cat); setPage(1); }}
                style={{
                  padding: '0.5rem 0.75rem',
                  borderRadius: 8,
                  border: '1px solid',
                  borderColor: selectedCategory === cat ? 'rgba(99,102,241,0.3)' : 'var(--border-glass)',
                  background: selectedCategory === cat ? 'rgba(99,102,241,0.1)' : 'transparent',
                  color: selectedCategory === cat ? '#ffffff' : 'var(--text-muted)',
                  fontSize: '0.85rem',
                  fontWeight: selectedCategory === cat ? 600 : 500,
                  textAlign: 'left',
                  cursor: 'pointer',
                  transition: 'var(--transition-smooth)'
                }}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Price Range */}
        <div className="form-group" style={{ marginTop: '1.5rem' }}>
          <label className="form-label">Price Range ($)</label>
          <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'center' }}>
            <input 
              type="number" 
              placeholder="Min" 
              value={minPrice} 
              onChange={(e) => setMinPrice(e.target.value)}
              className="input-field" 
              style={{ padding: '0.5rem' }}
            />
            <span style={{ color: 'var(--text-muted)' }}>-</span>
            <input 
              type="number" 
              placeholder="Max" 
              value={maxPrice} 
              onChange={(e) => setMaxPrice(e.target.value)}
              className="input-field" 
              style={{ padding: '0.5rem' }}
            />
          </div>
        </div>

        {/* Rating Filter */}
        <div className="form-group" style={{ marginTop: '1.5rem' }}>
          <label className="form-label">Min Rating</label>
          <select 
            value={minRating} 
            onChange={(e) => setMinRating(e.target.value)}
            className="input-field"
          >
            <option value="">All Ratings</option>
            <option value="4.5">4.5 ★ & Above</option>
            <option value="4.0">4.0 ★ & Above</option>
            <option value="3.5">3.5 ★ & Above</option>
            <option value="3.0">3.0 ★ & Above</option>
          </select>
        </div>

        <button 
          onClick={() => { setPage(1); fetchProducts(); setShowMobileFilters(false); }}
          className="btn btn-primary" 
          style={{ width: '100%', marginTop: '1rem' }}
        >
          Apply Filters
        </button>
      </aside>

      {/* Products Grid Area */}
      <div>
        {/* Search, Sort and Layout controls */}
        <div className="glass-panel" style={{
          padding: '1.25rem',
          marginBottom: '1.5rem',
          display: 'flex',
          flexWrap: 'wrap',
          gap: '1rem',
          alignItems: 'center',
          justifyContent: 'between',
          border: '1px solid var(--border-glass)'
        }}>
          {/* Search Form */}
          <form onSubmit={handleSearchSubmit} style={{ display: 'flex', flexGrow: 1, maxWidth: 500, position: 'relative' }}>
            <input 
              type="text" 
              placeholder="Search products..." 
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="input-field"
              style={{ paddingRight: '2.5rem' }}
            />
            <button 
              type="submit"
              style={{
                position: 'absolute',
                right: 4,
                top: 4,
                bottom: 4,
                width: 36,
                border: 'none',
                background: 'none',
                color: 'var(--text-muted)',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}
            >
              <Search size={18} />
            </button>
          </form>

          {/* Mobile filter toggler */}
          <button 
            id="filter-toggle-btn"
            onClick={() => setShowMobileFilters(!showMobileFilters)}
            className="btn btn-secondary"
            style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', height: 42 }}
          >
            <SlidersHorizontal size={16} />
            Filters
          </button>

          {/* Sort selection */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginLeft: 'auto' }}>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', whiteSpace: 'nowrap' }}>Sort By</span>
            <select
              value={`${sortBy}-${sortOrder}`}
              onChange={(e) => {
                const [field, ord] = e.target.value.split('-');
                setSortBy(field);
                setSortOrder(ord);
                setPage(1);
              }}
              className="input-field"
              style={{ width: 'fit-content', padding: '0.5rem 2rem 0.5rem 1rem' }}
            >
              <option value="createdAt-desc">Newest Arrivals</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="rating-desc">Highest Rated</option>
              <option value="name-asc">Alphabetical A-Z</option>
            </select>
          </div>
        </div>

        {/* Category Header */}
        <h2 style={{ fontSize: '1.5rem', fontWeight: 800, marginBottom: '1.5rem', fontFamily: 'var(--font-display)' }}>
          Catalog: <span className="text-gradient">{selectedCategory === 'All' ? 'All Products' : selectedCategory}</span>
          {pagination.totalItems > 0 && (
            <span style={{ fontSize: '0.9rem', fontWeight: 500, color: 'var(--text-muted)', marginLeft: '0.75rem' }}>
              ({pagination.totalItems} items found)
            </span>
          )}
        </h2>

        {/* Loading skeleton */}
        {loading ? (
          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
            gap: '1.5rem'
          }}>
            {[1, 2, 3, 4, 5, 6].map(i => (
              <div key={i} className="glass-panel" style={{ height: 400, padding: '1rem', display: 'flex', flexDirection: 'column', gap: '1rem', border: '1px solid var(--border-glass)' }}>
                <div className="skeleton" style={{ height: 180, borderRadius: 10 }} />
                <div className="skeleton" style={{ height: 20, width: '40%', borderRadius: 4 }} />
                <div className="skeleton" style={{ height: 24, width: '90%', borderRadius: 4 }} />
                <div className="skeleton" style={{ height: 40, width: '100%', borderRadius: 4 }} />
                <div style={{ display: 'flex', justify: 'between', marginTop: 'auto' }}>
                  <div className="skeleton" style={{ height: 35, width: '30%', borderRadius: 8 }} />
                  <div className="skeleton" style={{ height: 35, width: '40%', borderRadius: 8, marginLeft: 'auto' }} />
                </div>
              </div>
            ))}
          </div>
        ) : products.length === 0 ? (
          <div className="glass-panel" style={{
            padding: '4rem 2rem',
            textAlign: 'center',
            border: '1px solid var(--border-glass)',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '1rem'
          }}>
            <p style={{ fontSize: '1.25rem', color: 'var(--text-muted)', fontWeight: 500 }}>
              No products found matching your filter criteria.
            </p>
            <button 
              onClick={handleResetFilters}
              className="btn btn-secondary"
            >
              Reset Search & Filters
            </button>
          </div>
        ) : (
          /* Real Products Grid */
          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
            gap: '1.5rem'
          }}>
            {products.map(product => (
              <ProductCard 
                key={product.id} 
                product={product} 
                onAddToCart={onAddToCart} 
                onViewDetails={onViewDetails} 
              />
            ))}
          </div>
        )}

        {/* Pagination Section */}
        {pagination.totalPages > 1 && (
          <div style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '0.75rem',
            marginTop: '2.5rem'
          }}>
            <button
              onClick={() => setPage(p => Math.max(p - 1, 1))}
              disabled={page === 1}
              className="btn btn-secondary"
              style={{ padding: '0.5rem 0.75rem', opacity: page === 1 ? 0.5 : 1, cursor: page === 1 ? 'not-allowed' : 'pointer' }}
            >
              <ChevronLeft size={16} />
              Prev
            </button>
            
            {Array.from({ length: pagination.totalPages }, (_, i) => i + 1).map(pageNum => (
              <button
                key={pageNum}
                onClick={() => setPage(pageNum)}
                style={{
                  width: 36,
                  height: 36,
                  borderRadius: 8,
                  border: pageNum === page ? '1px solid var(--primary)' : '1px solid var(--border-glass)',
                  background: pageNum === page ? 'rgba(99, 102, 241, 0.15)' : 'transparent',
                  color: pageNum === page ? '#ffffff' : 'var(--text-muted)',
                  fontWeight: pageNum === page ? 700 : 500,
                  cursor: 'pointer',
                  transition: 'var(--transition-smooth)'
                }}
              >
                {pageNum}
              </button>
            ))}

            <button
              onClick={() => setPage(p => Math.min(p + 1, pagination.totalPages))}
              disabled={page === pagination.totalPages}
              className="btn btn-secondary"
              style={{ padding: '0.5rem 0.75rem', opacity: page === pagination.totalPages ? 0.5 : 1, cursor: page === pagination.totalPages ? 'not-allowed' : 'pointer' }}
            >
              Next
              <ChevronRight size={16} />
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
