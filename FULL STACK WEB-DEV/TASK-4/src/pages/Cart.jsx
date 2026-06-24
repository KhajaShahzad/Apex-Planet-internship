import React, { useState } from 'react';
import { Trash2, CreditCard, Truck, ArrowRight, ShieldCheck, ShoppingBag } from 'lucide-react';

export default function Cart({ cart, updateCartQuantity, removeFromCart, currentUser, checkoutOrder, onBackToStore, showToast }) {
  const [shippingAddress, setShippingAddress] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('Credit Card');
  const [loading, setLoading] = useState(false);

  const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  const shipping = subtotal > 150 || subtotal === 0 ? 0 : 15;
  const tax = subtotal * 0.08; // 8% tax
  const total = subtotal + shipping + tax;

  const handleCheckout = async (e) => {
    e.preventDefault();
    if (!currentUser) {
      showToast("Please login or register to checkout.", "warning");
      return;
    }
    if (cart.length === 0) {
      showToast("Your cart is empty.", "warning");
      return;
    }
    if (!shippingAddress.trim()) {
      showToast("Please provide a shipping address.", "warning");
      return;
    }

    setLoading(true);
    try {
      const orderPayload = {
        userId: currentUser.id,
        userName: currentUser.name,
        items: cart.map(item => ({
          productId: item.id,
          name: item.name,
          price: item.price,
          quantity: item.quantity
        })),
        totalAmount: Math.round(total * 100) / 100,
        shippingAddress,
        paymentMethod
      };

      const res = await fetch('http://localhost:5000/api/orders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        },
        body: JSON.stringify(orderPayload)
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to place order");

      checkoutOrder(data.order);
      showToast("Order placed successfully! Track status in your dashboard.", "success");
    } catch (err) {
      showToast(err.message || "Checkout failed", "danger");
    } finally {
      setLoading(false);
    }
  };

  if (cart.length === 0) {
    return (
      <div style={{ maxWidth: 800, margin: '4rem auto', padding: '0 24px', textAlign: 'center' }}>
        <div className="glass-panel" style={{ padding: '4rem 2rem', border: '1px solid var(--border-glass)', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '1.5rem' }}>
          <div style={{
            width: 80,
            height: 80,
            borderRadius: '50%',
            background: 'rgba(255,255,255,0.03)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: 'var(--text-muted)'
          }}>
            <ShoppingBag size={40} />
          </div>
          <div>
            <h2 style={{ fontSize: '1.75rem', fontWeight: 800, fontFamily: 'var(--font-display)', marginBottom: '0.5rem' }}>Your Cart is Empty</h2>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.95rem' }}>Explore our premium tech-gadgets catalog and add items to your cart.</p>
          </div>
          <button onClick={onBackToStore} className="btn btn-primary" style={{ display: 'inline-flex', alignItems: 'center', gap: '0.5rem' }}>
            Browse Catalog
            <ArrowRight size={16} />
          </button>
        </div>
      </div>
    );
  }

  return (
    <div style={{ maxWidth: 1200, margin: '2rem auto', padding: '0 24px' }}>
      <h1 style={{ fontSize: '2rem', fontWeight: 800, marginBottom: '2rem', fontFamily: 'var(--font-display)' }}>
        Your <span className="text-gradient">Shopping Cart</span>
      </h1>

      <div style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
        gap: '2rem',
        alignItems: 'start'
      }}>
        {/* Left: Items list */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {cart.map((item) => (
            <div 
              key={item.id}
              className="glass-panel" 
              style={{
                padding: '1rem 1.25rem',
                border: '1px solid var(--border-glass)',
                display: 'flex',
                alignItems: 'center',
                gap: '1.25rem'
              }}
            >
              <img 
                src={item.image} 
                alt={item.name} 
                style={{ width: 70, height: 70, borderRadius: 10, objectFit: 'cover' }} 
              />
              <div style={{ flexGrow: 1, minWidth: 0 }}>
                <h3 style={{ fontSize: '1rem', fontWeight: 700, margin: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  {item.name}
                </h3>
                <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', margin: '0.25rem 0' }}>
                  Category: {item.category}
                </span>
                <span style={{ fontSize: '0.95rem', fontWeight: 800, color: '#ffffff' }}>
                  ${item.price.toFixed(2)}
                </span>
              </div>

              {/* Quantity Counter */}
              <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--border-glass)', borderRadius: 8, overflow: 'hidden', height: 32 }}>
                <button 
                  onClick={() => updateCartQuantity(item.id, item.quantity - 1)}
                  style={{ border: 'none', background: 'none', color: '#fff', width: 28, height: '100%', cursor: 'pointer' }}
                >
                  -
                </button>
                <span style={{ width: 28, textAlign: 'center', fontSize: '0.85rem', fontWeight: 600 }}>{item.quantity}</span>
                <button 
                  onClick={() => updateCartQuantity(item.id, item.quantity + 1)}
                  style={{ border: 'none', background: 'none', color: '#fff', width: 28, height: '100%', cursor: 'pointer' }}
                >
                  +
                </button>
              </div>

              {/* Remove Button */}
              <button 
                onClick={() => removeFromCart(item.id)}
                style={{
                  border: 'none',
                  background: 'rgba(239, 68, 68, 0.08)',
                  color: 'var(--danger)',
                  padding: '0.5rem',
                  borderRadius: 8,
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  transition: 'var(--transition-smooth)'
                }}
                onMouseEnter={(e) => e.target.style.background = 'rgba(239, 68, 68, 0.15)'}
                onMouseLeave={(e) => e.target.style.background = 'rgba(239, 68, 68, 0.08)'}
              >
                <Trash2 size={16} />
              </button>
            </div>
          ))}

          {/* Secure transaction notice */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.8rem', color: 'var(--text-muted)', padding: '0.5rem' }}>
            <ShieldCheck size={18} style={{ color: 'var(--success)' }} />
            Secure 256-bit SSL encrypted transaction verification.
          </div>
        </div>

        {/* Right: Checkout details and summary */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          {/* Order Summary */}
          <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
            <h2 style={{ fontSize: '1.25rem', fontWeight: 800, marginBottom: '1.25rem', fontFamily: 'var(--font-display)', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem' }}>
              Order Summary
            </h2>
            
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem', fontSize: '0.9rem' }}>
              <div style={{ display: 'flex', justify: 'between' }}>
                <span style={{ color: 'var(--text-muted)' }}>Subtotal</span>
                <span style={{ marginLeft: 'auto', fontWeight: 600 }}>${subtotal.toFixed(2)}</span>
              </div>
              <div style={{ display: 'flex', justify: 'between' }}>
                <span style={{ color: 'var(--text-muted)' }}>Shipping</span>
                <span style={{ marginLeft: 'auto', fontWeight: 600 }}>
                  {shipping === 0 ? <span style={{ color: 'var(--success)' }}>FREE</span> : `$${shipping.toFixed(2)}`}
                </span>
              </div>
              <div style={{ display: 'flex', justify: 'between' }}>
                <span style={{ color: 'var(--text-muted)' }}>Estimated Tax (8%)</span>
                <span style={{ marginLeft: 'auto', fontWeight: 600 }}>${tax.toFixed(2)}</span>
              </div>
              
              {shipping > 0 && (
                <span style={{ fontSize: '0.75rem', color: 'var(--warning)', marginTop: -4 }}>
                  Add ${(150 - subtotal).toFixed(2)} more to qualify for FREE shipping!
                </span>
              )}

              <div style={{ display: 'flex', justify: 'between', borderTop: '1px solid var(--border-glass)', paddingTop: '0.75rem', marginTop: '0.5rem', fontSize: '1.1rem', fontWeight: 800 }}>
                <span>Total Amount</span>
                <span style={{ marginLeft: 'auto', color: '#ffffff', fontFamily: 'var(--font-display)' }}>
                  ${total.toFixed(2)}
                </span>
              </div>
            </div>
          </div>

          {/* Checkout Details Form */}
          <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
            <h2 style={{ fontSize: '1.25rem', fontWeight: 800, marginBottom: '1.25rem', fontFamily: 'var(--font-display)', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem' }}>
              Shipping & Payment
            </h2>

            <form onSubmit={handleCheckout}>
              <div className="form-group">
                <label className="form-label">Delivery Address</label>
                <textarea 
                  rows="3"
                  placeholder="Street Address, City, State, ZIP Code"
                  value={shippingAddress}
                  onChange={(e) => setShippingAddress(e.target.value)}
                  className="input-field"
                  style={{ resize: 'none', fontSize: '0.85rem' }}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label">Payment Method</label>
                <select 
                  value={paymentMethod}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                  className="input-field"
                  style={{ fontSize: '0.85rem' }}
                >
                  <option value="Credit Card">💳 Credit Card (Visa/Mastercard)</option>
                  <option value="PayPal">🅿️ PayPal Account</option>
                  <option value="Crypto">🪙 Cryptocurrency (BTC/ETH)</option>
                </select>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="btn btn-primary"
                style={{ width: '100%', marginTop: '1rem', height: 45 }}
              >
                {loading ? 'Processing...' : currentUser ? 'Place Order Now' : 'Login to Place Order'}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
