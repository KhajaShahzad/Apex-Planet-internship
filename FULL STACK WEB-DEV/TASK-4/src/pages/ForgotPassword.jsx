import React, { useState } from 'react';
import { Mail, Lock, KeyRound, ArrowLeft } from 'lucide-react';

export default function ForgotPassword({ setCurrentTab, showToast }) {
  const [email, setEmail] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!email || !newPassword || !confirmPassword) {
      showToast("Please fill in all fields", "warning");
      return;
    }
    if (newPassword !== confirmPassword) {
      showToast("Passwords do not match", "danger");
      return;
    }
    if (newPassword.length < 6) {
      showToast("Password must be at least 6 characters", "warning");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, newPassword })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to update password");

      showToast(data.message || "Password updated successfully!", "success");
      setCurrentTab('login');
    } catch (err) {
      showToast(err.message || "Error updating password", "danger");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: 450, margin: '5rem auto', padding: '0 24px' }}>
      <div className="glass-panel" style={{ padding: '2.5rem', border: '1px solid var(--border-glass)' }}>
        {/* Back navigation */}
        <button 
          onClick={() => setCurrentTab('login')}
          style={{
            background: 'none',
            border: 'none',
            color: 'var(--text-muted)',
            fontSize: '0.8rem',
            fontWeight: 500,
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            gap: '0.25rem',
            marginBottom: '1rem'
          }}
          onMouseEnter={(e) => e.target.style.color = '#ffffff'}
          onMouseLeave={(e) => e.target.style.color = 'var(--text-muted)'}
        >
          <ArrowLeft size={14} />
          Back to Sign In
        </button>

        {/* Header */}
        <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
          <h2 style={{ fontSize: '1.75rem', fontWeight: 800, fontFamily: 'var(--font-display)', marginBottom: '0.25rem' }}>
            Reset <span className="text-gradient">Password</span>
          </h2>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem' }}>Set a new password for your registered email account.</p>
        </div>

        <form onSubmit={handleSubmit}>
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

          {/* New Password input */}
          <div className="form-group">
            <label className="form-label">New Password</label>
            <div style={{ position: 'relative' }}>
              <input 
                type="password" 
                placeholder="••••••••" 
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                className="input-field" 
                style={{ paddingLeft: '2.5rem', fontSize: '0.9rem' }}
                required
              />
              <Lock size={16} style={{ position: 'absolute', left: 14, top: 14, color: 'var(--text-muted)' }} />
            </div>
          </div>

          {/* Confirm New Password input */}
          <div className="form-group">
            <label className="form-label">Confirm New Password</label>
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
            {loading ? 'Resetting...' : 'Update Password'}
            <KeyRound size={16} />
          </button>
        </form>
      </div>
    </div>
  );
}
