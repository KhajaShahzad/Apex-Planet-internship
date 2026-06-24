import React, { useState, useEffect } from 'react';
import { ChevronLeft, Star, ShoppingCart, ShieldAlert, Award, RotateCcw } from 'lucide-react';

export default function ProductDetail({ productId, onBack, onAddToCart, showToast }) {
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    const fetchDetail = async () => {
      try {
        const res = await fetch(`http://localhost:5000/api/products/${productId}`);
        if (!res.ok) throw new Error("Product details not found");
        const data = await res.json();
        setProduct(data);
      } catch (err) {
        showToast(err.message || "Failed to load product", "danger");
        onBack();
      } finally {
        setLoading(false);
      }
    };
    fetchDetail();
  }, [productId]);

  if (loading) {
    return (
      <div style={{ maxWidth: 1000, margin: '2rem auto', padding: '0 24px', display: 'flex', flexDirection: 'column', gap: '2rem' }}>
        <button className="btn btn-secondary" style={{ width: 'fit-content' }} disabled><ChevronLeft size={16} /> Back</button>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '3rem' }}>
          <div className="skeleton" style={{ height: 400, borderRadius: 16 }} />
          <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <div className="skeleton" style={{ height: 20, width: '30%', borderRadius: 4 }} />
            <div className="skeleton" style={{ height: 40, width: '90%', borderRadius: 4 }} />
            <div className="skeleton" style={{ height: 24, width: '40%', borderRadius: 4 }} />
            <div className="skeleton" style={{ height: 100, width: '100%', borderRadius: 4 }} />
          </div>
        </div>
      </div>
    );
  }

  if (!product) return null;

  const isOutOfStock = product.stock === 0;

  // Static high-fidelity specifications depending on category
  const getSpecs = () => {
    switch (product.category) {
      case 'Audio':
        return [
          { key: 'Driver Type', val: '40mm Dynamic Neodymium' },
          { key: 'Frequency Response', val: '20Hz - 40,000Hz' },
          { key: 'Battery Life', val: 'Up to 40 Hours (ANC On)' },
          { key: 'Connectivity', val: 'Bluetooth 5.2 / 3.5mm Aux' }
        ];
      case 'Peripherals':
        return [
          { key: 'Switch Type', val: 'Tactile Mechanical Red/Blue' },
          { key: 'Interface', val: 'Wired USB-C / Wireless 2.4GHz' },
          { key: 'Backlight', val: 'Customizable Chroma 16.8M RGB' },
          { key: 'Weight', val: 'Lightweight ergonomic design' }
        ];
      case 'Video':
        return [
          { key: 'Native Resolution', val: '3840 x 2160 (4K UHD)' },
          { key: 'Panel Type', val: 'OLED / Curved Wide' },
          { key: 'Refresh Rate', val: '240Hz / 0.03ms Response' },
          { key: 'Inputs', val: 'HDMI 2.1 x2, DP 1.4 x1, USB-C' }
        ];
      default:
        return [
          { key: 'Model Series', val: 'Zenith Apex Premium' },
          { key: 'Warranty Period', val: '2 Years Manufacturer' },
          { key: 'Build Quality', val: 'High-grade aluminum & reinforced polymer' },
          { key: 'In the Box', val: 'Device, Charging Cable, User Manual' }
        ];
    }
  };

  const handleAddToCart = () => {
    if (quantity > product.stock) {
      showToast("Cannot add more than available stock", "warning");
      return;
    }
    // Call parent cart updater with customized quantity
    onAddToCart(product, quantity);
  };

  return (
    <div style={{ maxWidth: 1200, margin: '2rem auto', padding: '0 24px', display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      {/* Back Button */}
      <button 
        onClick={onBack}
        className="btn btn-secondary"
        style={{ width: 'fit-content', display: 'flex', alignItems: 'center', gap: '0.5rem' }}
      >
        <ChevronLeft size={16} />
        Back to Catalog
      </button>

      {/* Main product columns */}
      <div style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
        gap: '3rem',
        alignItems: 'start'
      }}>
        {/* Left: Image Card */}
        <div className="glass-panel" style={{ padding: '1rem', border: '1px solid var(--border-glass)', borderRadius: 20 }}>
          <img 
            src={product.image} 
            alt={product.name}
            style={{ width: '100%', borderRadius: 12, height: 400, objectFit: 'cover' }}
          />
        </div>

        {/* Right: Details Info */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
          <div>
            <span style={{ fontSize: '0.85rem', color: 'var(--primary)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.1em' }}>
              {product.category}
            </span>
            <h1 style={{ fontSize: '2.5rem', fontWeight: 800, fontFamily: 'var(--font-display)', marginTop: '0.25rem', lineHeight: 1.2 }}>
              {product.name}
            </h1>
          </div>

          {/* Rating */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <div style={{ display: 'flex', gap: '0.1rem' }}>
              {[1, 2, 3, 4, 5].map((s) => (
                <Star 
                  key={s} 
                  size={16} 
                  fill={s <= Math.round(product.rating) ? "#fbbf24" : "none"} 
                  stroke={s <= Math.round(product.rating) ? "#fbbf24" : "rgba(255,255,255,0.2)"} 
                />
              ))}
            </div>
            <span style={{ fontSize: '0.9rem', fontWeight: 700 }}>{product.rating} ★</span>
            <span style={{ color: 'var(--text-muted)' }}>|</span>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>24 customer ratings</span>
          </div>

          {/* Price */}
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '0.75rem' }}>
            <span style={{ fontSize: '2rem', fontWeight: 800, color: '#ffffff', fontFamily: 'var(--font-display)' }}>
              ${product.price.toFixed(2)}
            </span>
            <span style={{ fontSize: '0.9rem', color: 'var(--text-muted)', textDecoration: 'line-through' }}>
              ${(product.price * 1.2).toFixed(2)}
            </span>
            <span style={{ background: 'rgba(16, 185, 129, 0.15)', color: '#34d399', fontSize: '0.75rem', fontWeight: 700, padding: '0.25rem 0.5rem', borderRadius: 4 }}>
              Save 20%
            </span>
          </div>

          {/* Stock state */}
          <div>
            {isOutOfStock ? (
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', color: 'var(--danger)' }}>
                <ShieldAlert size={18} />
                <span style={{ fontWeight: 600 }}>Temporarily Out of Stock</span>
              </div>
            ) : product.stock <= 15 ? (
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', color: 'var(--warning)' }}>
                <ShieldAlert size={18} />
                <span style={{ fontWeight: 600 }}>Hurry! Only {product.stock} units left in stock.</span>
              </div>
            ) : (
              <div style={{ color: 'var(--success)', fontWeight: 600 }}>
                ✓ Item in stock (Ready to ship in 24 hours)
              </div>
            )}
          </div>

          {/* Description */}
          <p style={{ color: 'var(--text-muted)', lineHeight: 1.6, fontSize: '0.95rem' }}>
            {product.description}
          </p>

          {/* Purchase section */}
          {!isOutOfStock && (
            <div className="glass-panel" style={{
              padding: '1.25rem',
              border: '1px solid var(--border-glass)',
              display: 'flex',
              alignItems: 'center',
              flexWrap: 'wrap',
              gap: '1.5rem'
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                <span style={{ fontSize: '0.9rem', color: 'var(--text-muted)' }}>Quantity:</span>
                <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--border-glass)', borderRadius: 8, overflow: 'hidden' }}>
                  <button 
                    onClick={() => setQuantity(q => Math.max(q - 1, 1))}
                    style={{ border: 'none', background: 'none', color: '#fff', width: 32, height: 32, cursor: 'pointer', fontSize: '1.1rem' }}
                  >
                    -
                  </button>
                  <span style={{ width: 40, textAlign: 'center', fontWeight: 600 }}>{quantity}</span>
                  <button 
                    onClick={() => setQuantity(q => Math.min(q + 1, product.stock))}
                    style={{ border: 'none', background: 'none', color: '#fff', width: 32, height: 32, cursor: 'pointer', fontSize: '1.1rem' }}
                  >
                    +
                  </button>
                </div>
              </div>

              <button 
                onClick={handleAddToCart}
                className="btn btn-primary"
                style={{ flexGrow: 1, display: 'flex', justify: 'center', gap: '0.5rem', height: 42 }}
              >
                <ShoppingCart size={18} />
                Add to Cart - ${(product.price * quantity).toFixed(2)}
              </button>
            </div>
          )}

          {/* Bullet points */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginTop: '0.5rem' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
              <RotateCcw size={16} style={{ color: 'var(--primary)' }} />
              30-Day Money Back Guarantee
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
              <Award size={16} style={{ color: 'var(--secondary)' }} />
              Authorized Apex Retailer
            </div>
          </div>
        </div>
      </div>

      {/* Specifications & Review tabs */}
      <div style={{ marginTop: '2.5rem' }}>
        <div style={{ display: 'flex', borderBottom: '1px solid var(--border-glass)', marginBottom: '1.5rem' }}>
          <button style={{
            background: 'none',
            border: 'none',
            color: '#fff',
            fontWeight: 700,
            fontSize: '1.1rem',
            paddingBottom: '0.75rem',
            borderBottom: '2px solid var(--primary)',
            cursor: 'pointer',
            paddingRight: '2rem'
          }}>
            Technical Specifications
          </button>
        </div>

        {/* Specifications Table */}
        <div className="glass-panel" style={{ padding: '1rem 2rem', border: '1px solid var(--border-glass)' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <tbody>
              {getSpecs().map((spec, index) => (
                <tr key={index} style={{ borderBottom: index === getSpecs().length - 1 ? 'none' : '1px solid var(--border-glass)' }}>
                  <td style={{ padding: '1rem 0', fontWeight: 600, color: 'var(--text-muted)', width: '30%', fontSize: '0.9rem' }}>{spec.key}</td>
                  <td style={{ padding: '1rem 0', color: '#ffffff', fontSize: '0.9rem' }}>{spec.val}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
