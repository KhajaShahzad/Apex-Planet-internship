import React, { useState, useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import { OrdersLineChart, CategoryDonutChart } from '../components/AnalyticsCharts';
import { DollarSign, Users, ShoppingBag, Clock, Package } from 'lucide-react';

export default function AdminDashboard({ currentUser, currentTab, setCurrentTab, showToast }) {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchStats = async () => {
    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/admin/stats', {
        headers: {
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        }
      });
      if (!res.ok) throw new Error("Could not load admin stats");
      const data = await res.json();
      setStats(data);
    } catch (err) {
      showToast(err.message || "Failed to load dashboard statistics", "danger");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (currentUser && currentTab === 'admin-dashboard') {
      fetchStats();
    }
  }, [currentTab, currentUser]);

  return (
    <div style={{ display: 'flex', gap: '2rem', maxWidth: 1400, margin: '2rem auto', padding: '0 24px', flexWrap: 'wrap' }}>
      <Sidebar currentTab={currentTab} setCurrentTab={setCurrentTab} role={currentUser.role} />

      {/* Admin Content Workspace */}
      <div style={{ flexGrow: 1, minWidth: '350px', flexBasis: 0 }}>
        <h1 style={{ fontSize: '1.75rem', fontWeight: 800, marginBottom: '2rem', fontFamily: 'var(--font-display)' }}>
          Analytics <span className="text-gradient">Control Center</span>
        </h1>

        {loading ? (
          <div>
            <div className="dashboard-grid">
              {[1, 2, 3, 4].map(i => (
                <div key={i} className="skeleton" style={{ height: 95, borderRadius: 16 }} />
              ))}
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '2rem', marginTop: '2rem' }}>
              <div className="skeleton" style={{ height: 280, borderRadius: 16 }} />
              <div className="skeleton" style={{ height: 280, borderRadius: 16 }} />
            </div>
          </div>
        ) : (
          stats && (
            <div>
              {/* KPI Cards */}
              <div className="dashboard-grid">
                {/* Total Sales */}
                <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                  <div>
                    <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>TOTAL REVENUE</span>
                    <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>${stats.kpis.totalSales.toFixed(2)}</span>
                  </div>
                  <div className="kpi-icon" style={{ background: 'rgba(16, 185, 129, 0.15)', border: '1px solid rgba(16, 185, 129, 0.3)', color: 'var(--success)' }}>
                    <DollarSign size={20} />
                  </div>
                </div>

                {/* Total Users */}
                <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                  <div>
                    <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>REGISTERED USERS</span>
                    <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>{stats.kpis.totalUsers}</span>
                  </div>
                  <div className="kpi-icon" style={{ background: 'rgba(99, 102, 241, 0.15)', border: '1px solid rgba(99, 102, 241, 0.3)', color: 'var(--primary)' }}>
                    <Users size={20} />
                  </div>
                </div>

                {/* Total Orders */}
                <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                  <div>
                    <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>TOTAL SALES ORDERS</span>
                    <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>{stats.kpis.totalOrders}</span>
                  </div>
                  <div className="kpi-icon" style={{ background: 'rgba(139, 92, 246, 0.15)', border: '1px solid rgba(139, 92, 246, 0.3)', color: 'var(--secondary)' }}>
                    <ShoppingBag size={20} />
                  </div>
                </div>

                {/* Pending Orders */}
                <div className="glass-panel kpi-card" style={{ border: '1px solid var(--border-glass)' }}>
                  <div>
                    <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'block', fontWeight: 600 }}>PENDING SHIPMENTS</span>
                    <span style={{ fontSize: '1.75rem', fontWeight: 800, color: '#fff' }}>{stats.kpis.pendingOrders}</span>
                  </div>
                  <div className="kpi-icon" style={{ background: 'rgba(245, 158, 11, 0.15)', border: '1px solid rgba(245, 158, 11, 0.3)', color: 'var(--warning)' }}>
                    <Clock size={20} />
                  </div>
                </div>
              </div>

              {/* Chart panels */}
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(380px, 1fr))', gap: '2rem', marginTop: '2rem' }}>
                {/* Sales Line Graph */}
                <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
                  <h3 style={{ fontSize: '1.1rem', fontWeight: 700, marginBottom: '1.5rem' }}>Order Volume Timeline</h3>
                  <OrdersLineChart data={stats.salesHistory} />
                </div>

                {/* Donut Category Chart */}
                <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
                  <h3 style={{ fontSize: '1.1rem', fontWeight: 700, marginBottom: '1.5rem' }}>Sales Allocations by Category</h3>
                  <CategoryDonutChart data={stats.categoryChart} />
                </div>
              </div>
            </div>
          )
        )}
      </div>
    </div>
  );
}
