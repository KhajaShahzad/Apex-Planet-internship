import React, { useState, useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import { Eye, Edit3 } from 'lucide-react';

export default function AdminOrders({ currentUser, currentTab, setCurrentTab, showToast }) {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedOrder, setSelectedOrder] = useState(null);

  const fetchOrders = async () => {
    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/orders', {
        headers: {
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        }
      });
      if (!res.ok) throw new Error("Could not fetch orders log");
      const data = await res.json();
      setOrders(data);
    } catch (err) {
      showToast(err.message || "Failed to load orders inventory", "danger");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (currentUser && currentTab === 'admin-orders') {
      fetchOrders();
    }
  }, [currentTab, currentUser]);

  const handleUpdateStatus = async (orderId, newStatus) => {
    try {
      const res = await fetch(`http://localhost:5000/api/orders/${orderId}/status`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        },
        body: JSON.stringify({ status: newStatus })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to update status");

      showToast(`Order #${orderId} status updated to ${newStatus}`, "success");
      
      // Update local state smoothly
      setOrders(prev => prev.map(o => o.id === Number(orderId) ? { ...o, status: newStatus } : o));
      if (selectedOrder && selectedOrder.id === Number(orderId)) {
        setSelectedOrder(prev => ({ ...prev, status: newStatus }));
      }
    } catch (err) {
      showToast(err.message || "Error updating status", "danger");
    }
  };

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

      {/* Admin Content Workspace */}
      <div style={{ flexGrow: 1, minWidth: '350px', flexBasis: 0 }}>
        <h1 style={{ fontSize: '1.75rem', fontWeight: 800, marginBottom: '2rem', fontFamily: 'var(--font-display)' }}>
          Order <span className="text-gradient">Shipment Manager</span>
        </h1>

        {/* Orders Table */}
        <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
          {loading ? (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {[1, 2, 3].map(i => (
                <div key={i} className="skeleton" style={{ height: 60, borderRadius: 8 }} />
              ))}
            </div>
          ) : orders.length === 0 ? (
            <div style={{ padding: '2rem 1rem', textAlign: 'center', color: 'var(--text-muted)' }}>
              No client orders found.
            </div>
          ) : (
            <div className="table-container">
              <table className="custom-table">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status Badge</th>
                    <th>Shipment Action</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {orders.map((order) => (
                    <tr key={order.id}>
                      <td style={{ fontWeight: 700, color: '#fff' }}>#{order.id}</td>
                      <td style={{ fontWeight: 600 }}>{order.userName}</td>
                      <td style={{ fontSize: '0.85rem' }}>
                        {new Date(order.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                      </td>
                      <td style={{ fontWeight: 700 }}>${order.totalAmount.toFixed(2)}</td>
                      <td>{getStatusBadge(order.status)}</td>
                      <td>
                        <select
                          value={order.status}
                          onChange={(e) => handleUpdateStatus(order.id, e.target.value)}
                          className="input-field"
                          style={{
                            padding: '0.25rem 0.5rem',
                            fontSize: '0.8rem',
                            width: 'fit-content',
                            background: 'rgba(15, 23, 42, 0.4)',
                            borderRadius: 6
                          }}
                        >
                          <option value="pending">Pending</option>
                          <option value="processing">Processing</option>
                          <option value="shipped">Shipped</option>
                          <option value="delivered">Delivered</option>
                          <option value="cancelled">Cancelled</option>
                        </select>
                      </td>
                      <td>
                        <button
                          onClick={() => setSelectedOrder(order)}
                          className="btn btn-secondary"
                          style={{ padding: '0.4rem', borderRadius: 6 }}
                          title="View Order Details"
                        >
                          <Eye size={14} />
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

      {/* Order Details Modal Overlay */}
      {selectedOrder && (
        <div className="modal-overlay" onClick={() => setSelectedOrder(null)}>
          <div className="glass-panel modal-content" onClick={(e) => e.stopPropagation()} style={{ border: '1px solid var(--border-glass)', padding: '2rem' }}>
            <div style={{ display: 'flex', justify: 'between', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem', marginBottom: '1.25rem' }}>
              <h3 style={{ fontSize: '1.25rem' }}>Order details #{selectedOrder.id}</h3>
              <button 
                onClick={() => setSelectedOrder(null)}
                style={{ marginLeft: 'auto', border: 'none', background: 'none', color: 'var(--text-muted)', fontSize: '1.2rem', cursor: 'pointer' }}
              >
                ✕
              </button>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem', marginBottom: '1.5rem' }}>
              <div>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600, display: 'block', marginBottom: '0.25rem' }}>
                  CUSTOMER INFORMATION
                </span>
                <p style={{ fontSize: '0.85rem', color: '#fff', fontWeight: 600 }}>{selectedOrder.userName}</p>
                <p style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>User ID: #{selectedOrder.userId}</p>
              </div>

              <div>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600, display: 'block', marginBottom: '0.25rem' }}>
                  DELIVERY STATUS
                </span>
                <div>{getStatusBadge(selectedOrder.status)}</div>
              </div>
            </div>

            <div style={{ marginBottom: '1.5rem' }}>
              <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600, display: 'block', marginBottom: '0.25rem' }}>
                SHIPPING ADDRESS
              </span>
              <p style={{ fontSize: '0.85rem', color: '#fff' }}>{selectedOrder.shippingAddress}</p>
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
