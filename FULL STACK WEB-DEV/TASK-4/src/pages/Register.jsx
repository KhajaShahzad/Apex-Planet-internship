import React, { useState } from 'react';
import { Mail, Lock, User, UserPlus } from 'lucide-react';

export default function Register({ setCurrentTab, loginUser, showToast }) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!name || !email || !password || !confirmPassword) {
      showToast("Please fill in all fields", "warning");
      return;
    }
    if (password !== confirmPassword) {
      showToast("Passwords do not match", "danger");
      return;
    }
    if (password.length < 6) {
      showToast("Password must be at least 6 characters long", "warning");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/auth/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Registration failed");

      loginUser(data.user);
      showToast(`Welcome, ${data.user.name}! Your account is ready.`, "success");
      setCurrentTab('storefront');
    } catch (err) {
      showToast(err.message || "Error creating account", "danger");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: 450, margin: '5rem auto', padding: '0 24px' }}>
      <div className="glass-panel" style={{ padding: '2.5rem', border: '1px solid var(--border-glass)' }}>
        {/* Header */}
        <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
          <h2 style={{ fontSize: '1.75rem', fontWeight: 800, fontFamily: 'var(--font-display)', marginBottom: '0.25rem' }}>
            Create <span className="text-gradient">Account</span>
          </h2>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>Join Zenith Gadgets for premium tech marketplace access.</p>
        </div>

        <form onSubmit={handleSubmit}>
          {/* Full Name input */}
          <div className="form-group">
            <label className="form-label">Full Name</label>
            <div style={{ position: 'relative' }}>
              <input 
                type="text" 
                placeholder="Jane Doe" 
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="input-field" 
                style={{ paddingLeft: '2.5rem', fontSize: '0.9rem' }}
                required
              />
              <User size={16} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-muted)' }} />
            </div>
          </div>

          {/* Email input */}
          <div className="form-group">
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
          <div className="form-group">
            <label className="form-label">Password</label>
            <div style={{ position: 'relative' }}>
              <input 
                type="password" 
                placeholder="•••••••• (Min 6 characters)" 
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="input-field" 
                style={{ paddingLeft: '2.5rem', fontSize: '0.9rem' }}
                required
              />
              <Lock size={16} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-muted)' }} />
            </div>
          </div>

          {/* Confirm Password input */}
          <div className="form-group">
            <label className="form-label">Confirm Password</label>
            <div style={{ position: 'relative' }}>
              <input 
                type="password" 
                placeholder="••••••••" 
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
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
            {loading ? 'Creating Account...' : 'Register Account'}
            <UserPlus size={16} />
          </button>
        </form>

        {/* Footer info */}
        <div style={{ textAlign: 'center', marginTop: '1.5rem', fontSize: '0.85rem', color: 'var(--text-muted)' }}>
          Already have an account?{' '}
          <button 
            onClick={() => setCurrentTab('login')}
            style={{
              background: 'none',
              border: 'none',
              color: 'var(--secondary)',
              fontWeight: 700,
              cursor: 'pointer'
            }}
          >
            Sign in
          </button>
        </div>
      </div>
    </div>
  );
}
