import React from 'react';
import { BarChart3, Package, ShoppingBag, Users, User, Settings, Shield } from 'lucide-react';

export default function Sidebar({ currentTab, setCurrentTab, role }) {
  const isAdmin = role === 'admin';

  const adminMenuItems = [
    { id: 'admin-dashboard', label: 'Analytics Overview', icon: BarChart3 },
    { id: 'admin-products', label: 'Manage Products', icon: Package },
    { id: 'admin-orders', label: 'Manage Orders', icon: ShoppingBag },
    { id: 'admin-users', label: 'Manage Users', icon: Users },
  ];

  const userMenuItems = [
    { id: 'dashboard', label: 'Order History', icon: ShoppingBag },
    { id: 'profile-settings', label: 'Profile Settings', icon: User },
  ];

  const menuItems = isAdmin && currentTab.startsWith('admin') ? adminMenuItems : userMenuItems;

  return (
    <aside className="glass-panel" style={{
      width: 260,
      padding: '1.5rem 1rem',
      display: 'flex',
      flexDirection: 'column',
      gap: '1.5rem',
      height: 'fit-content',
      position: 'sticky',
      top: 100,
      border: '1px solid var(--border-glass)'
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', padding: '0 0.5rem 1rem 0.5rem', borderBottom: '1px solid var(--border-glass)' }}>
        <div style={{
          width: 32,
          height: 32,
          borderRadius: 8,
          background: isAdmin && currentTab.startsWith('admin') ? 'rgba(99, 102, 241, 0.15)' : 'rgba(139, 92, 246, 0.15)',
          border: isAdmin && currentTab.startsWith('admin') ? '1px solid rgba(99, 102, 241, 0.3)' : '1px solid rgba(139, 92, 246, 0.3)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          {isAdmin && currentTab.startsWith('admin') ? (
            <Shield size={16} className="text-gradient" />
          ) : (
            <User size={16} style={{ color: 'var(--secondary)' }} />
          )}
        </div>
        <div>
          <span style={{ fontSize: '0.85rem', fontWeight: 700, letterSpacing: '0.05em', color: '#ffffff', display: 'block' }}>
            {isAdmin && currentTab.startsWith('admin') ? 'ADMINISTRATION' : 'MY DASHBOARD'}
          </span>
          <span style={{ fontSize: '0.65rem', color: 'var(--text-muted)', display: 'block' }}>
            {isAdmin && currentTab.startsWith('admin') ? 'Control Center' : 'Account Details'}
          </span>
        </div>
      </div>

      <nav style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
        {menuItems.map((item) => {
          const Icon = item.icon;
          const isActive = currentTab === item.id;
          
          return (
            <button
              key={item.id}
              onClick={() => setCurrentTab(item.id)}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.75rem',
                width: '100%',
                padding: '0.75rem 1rem',
                border: 'none',
                borderRadius: 10,
                background: isActive 
                  ? (isAdmin && currentTab.startsWith('admin') 
                      ? 'linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%)' 
                      : 'linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(217, 70, 239, 0.1) 100%)')
                  : 'transparent',
                borderLeft: isActive 
                  ? (isAdmin && currentTab.startsWith('admin') ? '3px solid var(--primary)' : '3px solid var(--secondary)')
                  : '3px solid transparent',
                color: isActive ? '#ffffff' : 'var(--text-muted)',
                fontWeight: isActive ? 600 : 500,
                textAlign: 'left',
                cursor: 'pointer',
                transition: 'var(--transition-smooth)'
              }}
              onMouseEnter={(e) => {
                if (!isActive) {
                  e.target.style.background = 'rgba(255, 255, 255, 0.03)';
                  e.target.style.color = '#ffffff';
                }
              }}
              onMouseLeave={(e) => {
                if (!isActive) {
                  e.target.style.background = 'transparent';
                  e.target.style.color = 'var(--text-muted)';
                }
              }}
            >
              <Icon size={18} style={{ color: isActive ? (isAdmin && currentTab.startsWith('admin') ? 'var(--primary)' : 'var(--secondary)') : 'inherit' }} />
              <span style={{ fontSize: '0.85rem' }}>{item.label}</span>
            </button>
          );
        })}
      </nav>
    </aside>
  );
}
