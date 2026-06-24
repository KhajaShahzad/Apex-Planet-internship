import React, { useState, useEffect } from 'react';
import Navbar from './components/Navbar';
import Home from './pages/Home';
import ProductDetail from './pages/ProductDetail';
import Cart from './pages/Cart';
import Login from './pages/Login';
import Register from './pages/Register';
import ForgotPassword from './pages/ForgotPassword';
import Dashboard from './pages/Dashboard';
import AdminDashboard from './pages/AdminDashboard';
import AdminProducts from './pages/AdminProducts';
import AdminOrders from './pages/AdminOrders';
import AdminUsers from './pages/AdminUsers';
import { AlertCircle, CheckCircle2, Info } from 'lucide-react';

export default function App() {
  // Load initial states from localStorage
  const [currentUser, setCurrentUser] = useState(() => {
    const saved = localStorage.getItem('zenith_user');
    return saved ? JSON.parse(saved) : null;
  });

  const [cart, setCart] = useState(() => {
    const saved = localStorage.getItem('zenith_cart');
    return saved ? JSON.parse(saved) : [];
  });

  const [currentTab, setCurrentTab] = useState('storefront');
  const [selectedProductId, setSelectedProductId] = useState(null);
  
  // Toast notifications state
  const [toast, setToast] = useState(null);
  const [toastTimeoutId, setToastTimeoutId] = useState(null);

  // Sync state to localStorage
  useEffect(() => {
    if (currentUser) {
      localStorage.setItem('zenith_user', JSON.stringify(currentUser));
    } else {
      localStorage.removeItem('zenith_user');
    }
  }, [currentUser]);

  useEffect(() => {
    localStorage.setItem('zenith_cart', JSON.stringify(cart));
  }, [cart]);

  // Toast trigger
  const showToast = (message, type = 'success') => {
    if (toastTimeoutId) clearTimeout(toastTimeoutId);
    setToast({ message, type });
    const id = setTimeout(() => {
      setToast(null);
    }, 4000);
    setToastTimeoutId(id);
  };

  // Auth Operations
  const loginUser = (user) => {
    setCurrentUser(user);
  };

  const logout = () => {
    setCurrentUser(null);
    setCart([]);
    setCurrentTab('storefront');
    showToast("Successfully logged out.", "info");
  };

  // Cart Operations
  const onAddToCart = (product, quantity = 1) => {
    setCart(prevCart => {
      const existingIdx = prevCart.findIndex(item => item.id === product.id);
      
      if (existingIdx > -1) {
        const newQty = prevCart[existingIdx].quantity + quantity;
        if (newQty > product.stock) {
          showToast(`Cannot add more. Only ${product.stock} units available in stock.`, "warning");
          const updated = [...prevCart];
          updated[existingIdx].quantity = product.stock;
          return updated;
        }
        const updated = [...prevCart];
        updated[existingIdx].quantity = newQty;
        showToast(`Updated quantity for ${product.name} in cart!`, "success");
        return updated;
      } else {
        if (quantity > product.stock) {
          showToast(`Cannot add. Only ${product.stock} units available in stock.`, "warning");
          return prevCart;
        }
        showToast(`Added ${product.name} to cart!`, "success");
        return [...prevCart, { ...product, quantity }];
      }
    });
  };

  const updateCartQuantity = (productId, quantity) => {
    if (quantity <= 0) {
      removeFromCart(productId);
      return;
    }

    // Check inventory availability from database
    fetch(`http://localhost:5000/api/products/${productId}`)
      .then(res => res.json())
      .then(product => {
        setCart(prevCart => {
          return prevCart.map(item => {
            if (item.id === productId) {
              if (quantity > product.stock) {
                showToast(`Only ${product.stock} units available. Stock limit reached.`, "warning");
                return { ...item, quantity: product.stock };
              }
              return { ...item, quantity };
            }
            return item;
          });
        });
      })
      .catch(err => {
        showToast("Error checking item stock levels.", "danger");
      });
  };

  const removeFromCart = (productId) => {
    setCart(prevCart => prevCart.filter(item => item.id !== productId));
    showToast("Removed item from cart.", "info");
  };

  const checkoutOrder = (order) => {
    setCart([]); // Clear Cart
    setCurrentTab('dashboard'); // Redirect to user order dashboard
  };

  // View item detail routing helper
  const onViewDetails = (productId) => {
    setSelectedProductId(productId);
    setCurrentTab('product-detail');
  };

  // Router View dispatcher
  const renderView = () => {
    switch (currentTab) {
      case 'storefront':
        return (
          <Home 
            currentUser={currentUser}
            onAddToCart={onAddToCart} 
            onViewDetails={onViewDetails} 
            showToast={showToast} 
          />
        );
      case 'product-detail':
        return (
          <ProductDetail 
            productId={selectedProductId}
            onBack={() => setCurrentTab('storefront')}
            onAddToCart={onAddToCart}
            showToast={showToast}
          />
        );
      case 'cart':
        return (
          <Cart 
            cart={cart}
            updateCartQuantity={updateCartQuantity}
            removeFromCart={removeFromCart}
            currentUser={currentUser}
            checkoutOrder={checkoutOrder}
            onBackToStore={() => setCurrentTab('storefront')}
            showToast={showToast}
          />
        );
      case 'login':
        return (
          <Login 
            setCurrentTab={setCurrentTab}
            loginUser={loginUser}
            showToast={showToast}
          />
        );
      case 'register':
        return (
          <Register 
            setCurrentTab={setCurrentTab}
            loginUser={loginUser}
            showToast={showToast}
          />
        );
      case 'forgot-password':
        return (
          <ForgotPassword 
            setCurrentTab={setCurrentTab}
            showToast={showToast}
          />
        );
      case 'dashboard':
      case 'profile-settings':
        if (!currentUser) {
          setCurrentTab('login');
          return null;
        }
        return (
          <Dashboard 
            currentUser={currentUser}
            currentTab={currentTab}
            setCurrentTab={setCurrentTab}
            showToast={showToast}
            loginUser={loginUser}
          />
        );
      case 'admin-dashboard':
        if (!currentUser || currentUser.role !== 'admin') {
          setCurrentTab('storefront');
          return null;
        }
        return (
          <AdminDashboard 
            currentUser={currentUser}
            currentTab={currentTab}
            setCurrentTab={setCurrentTab}
            showToast={showToast}
          />
        );
      case 'admin-products':
        if (!currentUser || currentUser.role !== 'admin') {
          setCurrentTab('storefront');
          return null;
        }
        return (
          <AdminProducts 
            currentUser={currentUser}
            currentTab={currentTab}
            setCurrentTab={setCurrentTab}
            showToast={showToast}
          />
        );
      case 'admin-orders':
        if (!currentUser || currentUser.role !== 'admin') {
          setCurrentTab('storefront');
          return null;
        }
        return (
          <AdminOrders 
            currentUser={currentUser}
            currentTab={currentTab}
            setCurrentTab={setCurrentTab}
            showToast={showToast}
          />
        );
      case 'admin-users':
        if (!currentUser || currentUser.role !== 'admin') {
          setCurrentTab('storefront');
          return null;
        }
        return (
          <AdminUsers 
            currentUser={currentUser}
            currentTab={currentTab}
            setCurrentTab={setCurrentTab}
            showToast={showToast}
          />
        );
      default:
        return <Home onAddToCart={onAddToCart} onViewDetails={onViewDetails} showToast={showToast} />;
    }
  };

  const getToastIcon = (type) => {
    switch (type) {
      case 'success': return <CheckCircle2 size={18} />;
      case 'danger': return <AlertCircle size={18} />;
      default: return <Info size={18} />;
    }
  };

  const getToastColor = (type) => {
    switch (type) {
      case 'success': return 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
      case 'danger': return 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
      case 'warning': return 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
      default: return 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      {/* Navbar Header */}
      <Navbar 
        currentUser={currentUser} 
        cart={cart} 
        currentTab={currentTab}
        setCurrentTab={setCurrentTab}
        logout={logout}
      />

      {/* Main Routing Screen */}
      <main style={{ flexGrow: 1, paddingBottom: '3rem' }}>
        {renderView()}
      </main>

      {/* Toast Notification Panel */}
      {toast && (
        <div 
          className="toast"
          style={{
            background: getToastColor(toast.type),
            border: '1px solid rgba(255,255,255,0.1)',
            backdropFilter: 'blur(8px)'
          }}
        >
          {getToastIcon(toast.type)}
          <span>{toast.message}</span>
        </div>
      )}

      {/* Footer Branding Notice */}
      <footer style={{
        padding: '2rem',
        textAlign: 'center',
        borderTop: '1px solid var(--border-glass)',
        marginTop: 'auto',
        fontSize: '0.8rem',
        color: 'var(--text-muted)'
      }}>
        <div style={{ maxWidth: 600, margin: '0 auto' }}>
          <p>© 2026 Zenith Gadgets. All rights reserved. Designed for Apex Planet Full-Stack Internship.</p>
          <p style={{ marginTop: '0.25rem', opacity: 0.6 }}>Features: React 18, Express.js REST API, JSON DB driver, custom dark HSL typography & inline charts.</p>
        </div>
      </footer>
    </div>
  );
}
