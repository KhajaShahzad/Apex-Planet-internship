import React, { useState } from 'react';
import { ShoppingCart, User, LogOut, ShieldCheck, LayoutDashboard, ShoppingBag, ChevronDown } from 'lucide-react';

export default function Navbar({ currentUser, cart, currentTab, setCurrentTab, logout }) {
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const cartItemCount = cart.reduce((sum, item) => sum + item.quantity, 0);

  const handleTabClick = (tabName) => {
    setCurrentTab(tabName);
    setDropdownOpen(false);
  };

  return (
    <nav className="glass-panel" style={{
      position: 'sticky',
      top: 16,
      zIndex: 100,
      margin: '16px 24px',
      padding: '0.75rem 2rem',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'between',
      gap: '1.5rem',
      borderRadius: '16px',
      border: '1px solid var(--border-glass)'
    }}>
      {/* Brand logo */}
      <div 
        onClick={() => handleTabClick('storefront')} 
        style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', cursor: 'pointer' }}
      >
        <div style={{
          width: 40,
          height: 40,
          borderRadius: 10,
          background: 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          boxShadow: 'var(--shadow-neon)'
        }}>
          <ShoppingBag size={20} color="#fff" />
        </div>
        <div>
          <span style={{ fontSize: '1.25rem', fontWeight: 800, fontFamily: 'var(--font-display)', display: 'block' }}>
            ZENITH <span className="text-gradient">GADGETS</span>
          </span>
          <span style={{ fontSize: '0.65rem', color: 'var(--text-muted)', letterSpacing: '0.1em', display: 'block', marginTop: -2 }}>
            APEX INTERNSHIP TASK-4
          </span>
        </div>
      </div>

      {/* Navigation middle links */}
      <div style={{ display: 'flex', gap: '1rem', marginLeft: 'auto', marginRight: '1.5rem' }}>
        <button 
          onClick={() => handleTabClick('storefront')}
          style={{
            border: 'none',
            color: currentTab === 'storefront' ? '#ffffff' : 'var(--text-muted)',
            fontWeight: currentTab === 'storefront' ? 600 : 500,
            padding: '0.5rem 1rem',
            borderRadius: 8,
            cursor: 'pointer',
            background: currentTab === 'storefront' ? 'rgba(255,255,255,0.05)' : 'transparent',
            transition: 'var(--transition-smooth)'
          }}
        >
          Storefront
        </button>

        {currentUser && currentUser.role === 'admin' && (
          <button 
            onClick={() => handleTabClick('admin-dashboard')}
            style={{
              color: currentTab.startsWith('admin') ? '#ffffff' : 'var(--text-muted)',
              fontWeight: currentTab.startsWith('admin') ? 600 : 500,
              padding: '0.5rem 1rem',
              borderRadius: 8,
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              gap: '0.5rem',
              background: currentTab.startsWith('admin') ? 'rgba(99, 102, 241, 0.1)' : 'transparent',
              border: currentTab.startsWith('admin') ? '1px solid rgba(99, 102, 241, 0.2)' : '1px solid transparent',
              transition: 'var(--transition-smooth)'
            }}
          >
            <ShieldCheck size={16} className="text-gradient" />
            Admin Panel
          </button>
        )}
      </div>

      {/* Cart & Auth Controls */}
      <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
        {/* Cart */}
        <button 
          onClick={() => handleTabClick('cart')}
          style={{
            border: 'none',
            color: currentTab === 'cart' ? '#ffffff' : 'var(--text-muted)',
            cursor: 'pointer',
            padding: '0.5rem',
            position: 'relative',
            borderRadius: 8,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: currentTab === 'cart' ? 'rgba(255,255,255,0.05)' : 'transparent'
          }}
        >
          <ShoppingCart size={22} />
          {cartItemCount > 0 && (
            <span style={{
              position: 'absolute',
              top: -4,
              right: -4,
              background: 'linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%)',
              color: '#ffffff',
              fontSize: '0.7rem',
              fontWeight: 700,
              width: 18,
              height: 18,
              borderRadius: '50%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              border: '2px solid var(--bg-deep)'
            }}>
              {cartItemCount}
            </span>
          )}
        </button>

        {/* User Info / Dropdown */}
        {currentUser ? (
          <div style={{ position: 'relative' }}>
            <div 
              onClick={() => setDropdownOpen(!dropdownOpen)}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.5rem',
                cursor: 'pointer',
                padding: '0.25rem 0.75rem',
                borderRadius: 10,
                border: '1px solid var(--border-glass)',
                background: 'rgba(255,255,255,0.02)'
              }}
            >
              <img 
                src={currentUser.avatar} 
                alt={currentUser.name} 
                style={{ width: 28, height: 28, borderRadius: '50%', background: '#1e293b' }} 
              />
              <span style={{ fontSize: '0.85rem', fontWeight: 500, maxWidth: 100, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {currentUser.name.split(' ')[0]}
              </span>
              <ChevronDown size={14} style={{ opacity: 0.6 }} />
            </div>

            {dropdownOpen && (
              <div 
                className="glass-panel" 
                style={{
                  position: 'absolute',
                  top: '120%',
                  right: 0,
                  width: 200,
                  padding: '0.5rem',
                  borderRadius: 12,
                  zIndex: 200,
                  background: 'rgba(15, 23, 42, 0.95)',
                  boxShadow: '0 10px 25px rgba(0, 0, 0, 0.6)'
                }}
              >
                <div style={{ padding: '0.5rem 0.75rem', borderBottom: '1px solid var(--border-glass)', marginBottom: '0.25rem' }}>
                  <span style={{ fontSize: '0.85rem', fontWeight: 600, display: 'block', color: '#fff' }}>{currentUser.name}</span>
                  <span style={{ fontSize: '0.7rem', color: 'var(--text-muted)', display: 'block' }}>{currentUser.email}</span>
                </div>
                
                <button 
                  onClick={() => handleTabClick('dashboard')}
                  style={{
                    width: '100%',
                    background: 'none',
                    border: 'none',
                    color: 'var(--text-main)',
                    textAlign: 'left',
                    padding: '0.5rem 0.75rem',
                    borderRadius: 6,
                    cursor: 'pointer',
                    fontSize: '0.85rem',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.5rem'
                  }}
                  onMouseEnter={(e) => e.target.style.background = 'rgba(255,255,255,0.05)'}
                  onMouseLeave={(e) => e.target.style.background = 'none'}
                >
                  <User size={14} />
                  My Dashboard
                </button>
                
                {currentUser.role === 'admin' && (
                  <button 
                    onClick={() => handleTabClick('admin-dashboard')}
                    style={{
                      width: '100%',
                      background: 'none',
                      border: 'none',
                      color: 'var(--text-main)',
                      textAlign: 'left',
                      padding: '0.5rem 0.75rem',
                      borderRadius: 6,
                      cursor: 'pointer',
                      fontSize: '0.85rem',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '0.5rem'
                    }}
                    onMouseEnter={(e) => e.target.style.background = 'rgba(255,255,255,0.05)'}
                    onMouseLeave={(e) => e.target.style.background = 'none'}
                  >
                    <LayoutDashboard size={14} />
                    Admin Panel
                  </button>
                )}

                <button 
                  onClick={() => {
                    logout();
                    setDropdownOpen(false);
                  }}
                  style={{
                    width: '100%',
                    background: 'none',
                    border: 'none',
                    color: 'var(--danger)',
                    textAlign: 'left',
                    padding: '0.5rem 0.75rem',
                    borderRadius: 6,
                    cursor: 'pointer',
                    fontSize: '0.85rem',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.5rem',
                    marginTop: '0.25rem',
                    borderTop: '1px solid var(--border-glass)'
                  }}
                  onMouseEnter={(e) => e.target.style.background = 'rgba(239, 68, 68, 0.08)'}
                  onMouseLeave={(e) => e.target.style.background = 'none'}
                >
                  <LogOut size={14} />
                  Log Out
                </button>
              </div>
            )}
          </div>
        ) : (
          <button 
            onClick={() => handleTabClick('login')}
            className="btn btn-primary"
            style={{ padding: '0.5rem 1.25rem', fontSize: '0.85rem', height: 36 }}
          >
            Login
          </button>
        )}
      </div>
    </nav>
  );
}
