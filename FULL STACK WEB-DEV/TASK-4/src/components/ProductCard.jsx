import React from 'react';
import { Star, ShoppingCart, Eye } from 'lucide-react';

export default function ProductCard({ product, onAddToCart, onViewDetails }) {
  const isOutOfStock = product.stock === 0;
  const isLowStock = product.stock > 0 && product.stock <= 15;

  return (
    <div 
      className="glass-panel glass-panel-interactive"
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        overflow: 'hidden',
        border: '1px solid var(--border-glass)',
        position: 'relative'
      }}
    >
      {/* Category Tag (Top Right) */}
      <span style={{
        position: 'absolute',
        top: 12,
        right: 12,
        background: 'rgba(15, 23, 42, 0.75)',
        backdropFilter: 'blur(4px)',
        border: '1px solid var(--border-glass)',
        padding: '0.25rem 0.6rem',
        borderRadius: 6,
        fontSize: '0.7rem',
        fontWeight: 600,
        color: 'var(--text-main)',
        zIndex: 2
      }}>
        {product.category}
      </span>

      {/* Product Image Container */}
      <div 
        onClick={() => onViewDetails(product.id)}
        style={{
          height: 180,
          overflow: 'hidden',
          position: 'relative',
          cursor: 'pointer',
          background: 'rgba(15, 23, 42, 0.4)'
        }}
      >
        <img 
          src={product.image} 
          alt={product.name}
          style={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            transition: 'transform 0.5s ease'
          }}
          onMouseEnter={(e) => e.target.style.transform = 'scale(1.08)'}
          onMouseLeave={(e) => e.target.style.transform = 'scale(1)'}
        />
        <div style={{
          position: 'absolute',
          bottom: 0,
          left: 0,
          right: 0,
          background: 'linear-gradient(to top, rgba(7, 10, 19, 0.8), transparent)',
          height: 40,
          pointerEvents: 'none'
        }} />
      </div>

      {/* Product Body */}
      <div style={{ padding: '1.25rem', display: 'flex', flexDirection: 'column', flexGrow: 1, gap: '0.5rem' }}>
        {/* Rating */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
          <Star size={14} fill="#fbbf24" stroke="#fbbf24" />
          <span style={{ fontSize: '0.75rem', fontWeight: 600, color: '#ffffff' }}>{product.rating}</span>
          <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>({Math.floor(product.rating * 15)} reviews)</span>
        </div>

        {/* Title */}
        <h3 
          onClick={() => onViewDetails(product.id)}
          style={{
            fontSize: '1.1rem',
            fontWeight: 700,
            fontFamily: 'var(--font-display)',
            cursor: 'pointer',
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            display: '-webkit-box',
            WebkitLineClamp: 1,
            WebkitBoxOrient: 'vertical',
            lineHeight: 1.3
          }}
          onMouseEnter={(e) => e.target.style.color = 'var(--primary)'}
          onMouseLeave={(e) => e.target.style.color = '#ffffff'}
        >
          {product.name}
        </h3>

        {/* Description */}
        <p style={{
          fontSize: '0.8rem',
          color: 'var(--text-muted)',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
          display: '-webkit-box',
          WebkitLineClamp: 2,
          WebkitBoxOrient: 'vertical',
          height: 38,
          lineHeight: 1.4
        }}>
          {product.description}
        </p>

        {/* Stock Level Warning */}
        <div style={{ marginTop: 'auto', paddingTop: '0.5rem' }}>
          {isOutOfStock ? (
            <span className="badge badge-danger">Out of Stock</span>
          ) : isLowStock ? (
            <span className="badge badge-warning">Only {product.stock} left</span>
          ) : (
            <span className="badge badge-success">In Stock: {product.stock}</span>
          )}
        </div>

        {/* Price & Action Row */}
        <div style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'between',
          marginTop: '0.5rem',
          gap: '1rem',
          borderTop: '1px solid var(--border-glass)',
          paddingTop: '0.75rem'
        }}>
          <div>
            <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', display: 'block', marginBottom: -2 }}>Price</span>
            <span style={{ fontSize: '1.25rem', fontWeight: 800, color: '#ffffff', fontFamily: 'var(--font-display)' }}>
              ${product.price.toFixed(2)}
            </span>
          </div>

          <div style={{ display: 'flex', gap: '0.4rem', marginLeft: 'auto' }}>
            <button
              onClick={() => onViewDetails(product.id)}
              className="btn btn-secondary"
              title="View Details"
              style={{ padding: '0.5rem', borderRadius: 8 }}
            >
              <Eye size={16} />
            </button>
            <button
              onClick={() => onAddToCart(product)}
              disabled={isOutOfStock}
              className="btn btn-primary"
              style={{
                padding: '0.5rem 0.75rem',
                borderRadius: 8,
                opacity: isOutOfStock ? 0.5 : 1,
                cursor: isOutOfStock ? 'not-allowed' : 'pointer'
              }}
            >
              <ShoppingCart size={16} />
              <span style={{ fontSize: '0.8rem' }}>Add</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
