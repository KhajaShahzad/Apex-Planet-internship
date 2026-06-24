import React, { useState } from 'react';
import { Mail, Lock, LogIn, ArrowRight } from 'lucide-react';

export default function Login({ setCurrentTab, loginUser, showToast }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!email || !password) {
      showToast("Please fill in all fields", "warning");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to log in");

      loginUser(data.user);
      showToast(`Welcome back, ${data.user.name}!`, "success");
      setCurrentTab('storefront');
    } catch (err) {
      showToast(err.message || "Invalid credentials", "danger");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: 450, margin: '6rem auto', padding: '0 24px' }}>
      <div className="glass-panel" style={{ padding: '2.5rem', border: '1px solid var(--border-glass)' }}>
        {/* Header */}
        <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
          <h2 style={{ fontSize: '1.75rem', fontWeight: 800, fontFamily: 'var(--font-display)', marginBottom: '0.25rem' }}>
            Account <span className="text-gradient">Sign In</span>
          </h2>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>Access your orders, profile, and admin panels.</p>
        </div>

        <form onSubmit={handleSubmit}>
          {/* Email input */}
          <div className="form-group" style={{ position: 'relative' }}>
            <label className="form-label">Email Address</label>
            <div style={{ position: 'relative' }}>
              <input 
                type="email" 
                placeholder="you@example.com" 
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="input-field" 
                style={{ paddingLeft: '2.5rem', fontSize: '0.9rem' }}
                required
              />
              <Mail size={16} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-muted)' }} />
            </div>
          </div>

          {/* Password input */}
          <div className="form-group" style={{ position: 'relative', marginBottom: '0.5rem' }}>
            <div style={{ display: 'flex', justify: 'between', alignItems: 'center', marginBottom: '0.5rem' }}>
              <label className="form-label" style={{ margin: 0 }}>Password</label>
              <button 
                type="button"
                onClick={() => setCurrentTab('forgot-password')}
                style={{
                  background: 'none',
                  border: 'none',
                  color: 'var(--primary)',
                  fontSize: '0.75rem',
                  fontWeight: 600,
                  cursor: 'pointer'
                }}
              >
                Forgot Password?
              </button>
            </div>
            <div style={{ position: 'relative' }}>
              <input 
                type="password" 
                placeholder="••••••••" 
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="input-field" 
                style={{ paddingLeft: '2.5rem', fontSize: '0.9rem' }}
                required
              />
              <Lock size={16} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-muted)' }} />
            </div>
          </div>

          {/* Submit */}
          <button 
            type="submit" 
            disabled={loading}
            className="btn btn-primary"
            style={{ width: '100%', marginTop: '1.5rem', display: 'flex', alignItems: 'center', justify: 'center', gap: '0.5rem' }}
          >
            {loading ? 'Signing In...' : 'Sign In'}
            <LogIn size={16} />
          </button>
        </form>

        {/* Footer info */}
        <div style={{ textAlign: 'center', marginTop: '1.5rem', fontSize: '0.85rem', color: 'var(--text-muted)' }}>
          Don't have an account?{' '}
          <button 
            onClick={() => setCurrentTab('register')}
            style={{
              background: 'none',
              border: 'none',
              color: 'var(--secondary)',
              fontWeight: 700,
              cursor: 'pointer'
            }}
          >
            Create account
          </button>
        </div>
      </div>
    </div>
  );
}
