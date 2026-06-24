import React, { useState, useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import { ShoppingBag, DollarSign, Clock, ShieldCheck, CreditCard, MapPin, Edit3 } from 'lucide-react';

export default function Dashboard({ currentUser, currentTab, setCurrentTab, showToast, loginUser }) {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedOrder, setSelectedOrder] = useState(null);

  // Profile Edit fields
  const [name, setName] = useState(currentUser?.name || '');
  const [email, setEmail] = useState(currentUser?.email || '');
  const [password, setPassword] = useState('');
  const [avatar, setAvatar] = useState(currentUser?.avatar || '');
  const [profileLoading, setProfileLoading] = useState(false);

  // Quick avatar generator presets
  const avatarPresets = [
    `https://api.dicebear.com/7.x/adventurer/svg?seed=jane`,
    `https://api.dicebear.com/7.x/adventurer/svg?seed=john`,
    `https://api.dicebear.com/7.x/adventurer/svg?seed=alex`,
    `https://api.dicebear.com/7.x/adventurer/svg?seed=sarah`,
    `https://api.dicebear.com/7.x/bottts/svg?seed=techie`,
    `https://api.dicebear.com/7.x/pixel-art/svg?seed=retro`
  ];

  const fetchOrders = async () => {
    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/orders', {
        headers: {
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        }
      });
      if (!res.ok) throw new Error("Failed to load your orders");
      const data = await res.json();
      setOrders(data);
    } catch (err) {
      showToast(err.message || "Error loading order history", "danger");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (currentUser && currentTab === 'dashboard') {
      fetchOrders();
    }
  }, [currentTab, currentUser]);

  const handleUpdateProfile = async (e) => {
    e.preventDefault();
    setProfileLoading(true);
    try {
      const payload = { name, email, avatar };
      if (password) payload.password = password;

      const res = await fetch('http://localhost:5000/api/auth/profile', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Profile update failed");

      loginUser(data.user); // update global currentUser state
      setPassword('');
      showToast(data.message || "Profile updated successfully!", "success");
    } catch (err) {
      showToast(err.message || "Error updating profile", "danger");
    } finally {
      setProfileLoading(false);
    }
  };

  // Metric summaries
  const totalOrdersCount = orders.length;
  const totalSpent = orders.filter(o => o.status !== 'cancelled').reduce((sum, o) => sum + o.totalAmount, 0);
  const pendingOrdersCount = orders.filter(o => o.status === 'pending' || o.status === 'processing').length;

  const getStatusBadge = (status) => {
    switch (status) {
      case 'pending': return <span className="badge badge-warning">Pending</span>;
      case 'processing': return <span className="badge badge-info">Processing</span>;
      case 'shipped': return <span className="badge badge-info" style={{ borderColor: 'var(--secondary)', color: 'var(--secondary)' }}>Shipped</span>;
      case 'delivered': return <span className="badge badge-success">Delivered</span>;
      case 'cancelled': return <span className="badge badge-danger">Cancelled</span>;
      default: return <span className="badge">{status}</span>;
    }
  };

  return (
    <div style={{ display: 'flex', gap: '2rem', maxWidth: 1400, margin: '2rem auto', padding: '0 24px', flexWrap: 'wrap' }}>
      <Sidebar currentTab={currentTab} setCurrentTab={setCurrentTab} role={currentUser.role} />

      {/* Main Content Pane */}
      <div style={{ flexGrow: 1, minWidth: '350px', flexBasis: 0 }}>
        {currentTab === 'dashboard' ? (
          <div>
            {/* KPI Cards */}
            <div className="dashboard-grid">
              <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                <div>
                  <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>TOTAL ORDERS</span>
                  <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>{totalOrdersCount}</span>
                </div>
                <div className="kpi-icon" style={{ background: 'rgba(139, 92, 246, 0.15)', border: '1px solid rgba(139, 92, 246, 0.3)', color: 'var(--secondary)' }}>
                  <ShoppingBag size={20} />
                </div>
              </div>

              <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                <div>
                  <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>TOTAL SPENT</span>
                  <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>${totalSpent.toFixed(2)}</span>
                </div>
                <div className="kpi-icon" style={{ background: 'rgba(16, 185, 129, 0.15)', border: '1px solid rgba(16, 185, 129, 0.3)', color: 'var(--success)' }}>
                  <DollarSign size={20} />
                </div>
              </div>

              <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                <div>
                  <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>IN-PROGRESS</span>
                  <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>{pendingOrdersCount}</span>
                </div>
                <div className="kpi-icon" style={{ background: 'rgba(245, 158, 11, 0.15)', border: '1px solid rgba(245, 158, 11, 0.3)', color: 'var(--warning)' }}>
                  <Clock size={20} />
                </div>
              </div>
            </div>

            {/* Orders Data Grid */}
            <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
              <h2 style={{ fontSize: '1.25rem', fontWeight: 800, marginBottom: '1.25rem', fontFamily: 'var(--font-display)' }}>
                Order History
              </h2>

              {loading ? (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                  {[1, 2, 3].map(i => (
                    <div key={i} className="skeleton" style={{ height: 50, borderRadius: 8 }} />
                  ))}
                </div>
              ) : orders.length === 0 ? (
                <div style={{ padding: '2rem 1rem', textAlign: 'center', color: 'var(--text-muted)' }}>
                  You haven't placed any orders yet.
                </div>
              ) : (
                <div className="table-container">
                  <table className="custom-table">
                    <thead>
                      <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      {orders.map((order) => (
                        <tr key={order.id}>
                          <td style={{ fontWeight: 700, color: '#ffffff' }}>#{order.id}</td>
                          <td style={{ fontSize: '0.85rem' }}>
                            {new Date(order.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                          </td>
                          <td style={{ maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: '0.85rem' }}>
                            {order.items.map(item => `${item.name} (x${item.quantity})`).join(', ')}
                          </td>
                          <td style={{ fontWeight: 700 }}>${order.totalAmount.toFixed(2)}</td>
                          <td>{getStatusBadge(order.status)}</td>
                          <td>
                            <button
                              onClick={() => setSelectedOrder(order)}
                              className="btn btn-secondary"
                              style={{ padding: '0.25rem 0.5rem', fontSize: '0.75rem', borderRadius: 6 }}
                            >
                              Details
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        ) : (
          /* Profile Settings */
          <div className="glass-panel" style={{ padding: '2rem', border: '1px solid var(--border-glass)' }}>
            <h2 style={{ fontSize: '1.5rem', fontWeight: 800, marginBottom: '1.5rem', fontFamily: 'var(--font-display)', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem' }}>
              Profile Settings
            </h2>

            <form onSubmit={handleUpdateProfile}>
              {/* Avatar presets selection */}
              <div className="form-group">
                <label className="form-label">Profile Avatar</label>
                <div style={{ display: 'flex', gap: '1rem', alignItems: 'center', marginBottom: '1rem' }}>
                  <img 
                    src={avatar} 
                    alt="Active avatar" 
                    style={{ width: 64, height: 64, borderRadius: '50%', background: '#1f2937', border: '2px solid var(--primary)' }} 
                  />
                  <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                    {avatarPresets.map((avUrl, idx) => (
                      <img
                        key={idx}
                        src={avUrl}
                        alt={`Preset ${idx}`}
                        onClick={() => setAvatar(avUrl)}
                        style={{
                          width: 36,
                          height: 36,
                          borderRadius: '50%',
                          background: '#111827',
                          cursor: 'pointer',
                          border: avatar === avUrl ? '2px solid var(--primary)' : '1px solid var(--border-glass)',
                          transition: 'var(--transition-smooth)'
                        }}
                      />
                    ))}
                  </div>
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }} id="profile-grid">
                <style dangerouslySetInnerHTML={{__html: `
                  @media (max-width: 600px) {
                    #profile-grid { grid-template-columns: 1fr !important; }
                  }
                `}} />
                
                <div className="form-group">
                  <label className="form-label">Full Name</label>
                  <input
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="input-field"
                    required
                  />
                </div>

                <div className="form-group">
                  <label className="form-label">Email Address</label>
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="input-field"
                    required
                  />
                </div>
              </div>

              <div className="form-group">
                <label className="form-label">Change Password (Leave blank to keep current)</label>
                <input
                  type="password"
                  placeholder="New Password (Minimum 6 characters)"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="input-field"
                />
              </div>

              <button
                type="submit"
                disabled={profileLoading}
                className="btn btn-primary"
                style={{ marginTop: '1rem', height: 42, padding: '0 2rem' }}
              >
                {profileLoading ? 'Saving changes...' : 'Save Settings'}
              </button>
            </form>
          </div>
        )}
      </div>

      {/* Order Details Modal Overlay */}
      {selectedOrder && (
        <div className="modal-overlay" onClick={() => setSelectedOrder(null)}>
          <div className="glass-panel modal-content" onClick={(e) => e.stopPropagation()} style={{ border: '1px solid var(--border-glass)', padding: '2rem' }}>
            <div style={{ display: 'flex', justify: 'between', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem', marginBottom: '1.25rem' }}>
              <h3 style={{ fontSize: '1.25rem' }}>Order Details #{selectedOrder.id}</h3>
              <button 
                onClick={() => setSelectedOrder(null)}
                style={{ marginLeft: 'auto', border: 'none', background: 'none', color: 'var(--text-muted)', fontSize: '1.2rem', cursor: 'pointer' }}
              >
                ✕
              </button>
            </div>

            {/* Address & Payment Info */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem', marginBottom: '1.5rem' }} id="modal-grid">
              <style dangerouslySetInnerHTML={{__html: `
                @media (max-width: 500px) {
                  #modal-grid { grid-template-columns: 1fr !important; gap: 0.75rem !important; }
                }
              `}} />
              
              <div>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.25rem', marginBottom: '0.25rem' }}>
                  <MapPin size={12} /> SHIPPING ADDRESS
                </span>
                <p style={{ fontSize: '0.85rem', color: '#fff' }}>{selectedOrder.shippingAddress}</p>
              </div>

              <div>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.25rem', marginBottom: '0.25rem' }}>
                  <CreditCard size={12} /> PAYMENT METHOD
                </span>
                <p style={{ fontSize: '0.85rem', color: '#fff' }}>{selectedOrder.paymentMethod}</p>
              </div>
            </div>

            {/* Order Items list */}
            <h4 style={{ fontSize: '0.95rem', marginBottom: '0.75rem' }}>Items Summary</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', marginBottom: '1.5rem' }}>
              {selectedOrder.items.map((item, idx) => (
                <div key={idx} style={{ display: 'flex', justify: 'between', fontSize: '0.85rem', padding: '0.5rem', borderBottom: '1px solid rgba(255,255,255,0.03)' }}>
                  <span>{item.name} <strong style={{ color: 'var(--primary)' }}>x{item.quantity}</strong></span>
                  <span style={{ marginLeft: 'auto', fontWeight: 600 }}>${(item.price * item.quantity).toFixed(2)}</span>
                </div>
              ))}
            </div>

            {/* Totals */}
            <div style={{ display: 'flex', justify: 'between', borderTop: '1px solid var(--border-glass)', paddingTop: '0.75rem', fontSize: '1.1rem', fontWeight: 800 }}>
              <span>Total Price</span>
              <span style={{ marginLeft: 'auto', color: '#fff' }}>${selectedOrder.totalAmount.toFixed(2)}</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
