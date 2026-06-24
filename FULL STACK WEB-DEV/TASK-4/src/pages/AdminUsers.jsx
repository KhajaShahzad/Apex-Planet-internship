import React, { useState, useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import { UserCheck, ShieldAlert, Trash2, ArrowUpCircle } from 'lucide-react';

export default function AdminUsers({ currentUser, currentTab, setCurrentTab, showToast }) {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchUsers = async () => {
    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/admin/users', {
        headers: {
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        }
      });
      if (!res.ok) throw new Error("Could not fetch user inventory");
      const data = await res.json();
      setUsers(data);
    } catch (err) {
      showToast(err.message || "Failed to load user accounts", "danger");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (currentUser && currentTab === 'admin-users') {
      fetchUsers();
    }
  }, [currentTab, currentUser]);

  const handleToggleStatus = async (userId, currentStatus) => {
    const nextStatus = currentStatus === 'active' ? 'suspended' : 'active';
    try {
      const res = await fetch(`http://localhost:5000/api/admin/users/${userId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        },
        body: JSON.stringify({ status: nextStatus })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to toggle user status");

      showToast(`User account status updated to ${nextStatus}`, "success");
      setUsers(prev => prev.map(u => u.id === Number(userId) ? { ...u, status: nextStatus } : u));
    } catch (err) {
      showToast(err.message || "Error updating user status", "danger");
    }
  };

  const handleToggleRole = async (userId, currentRole) => {
    const nextRole = currentRole === 'admin' ? 'customer' : 'admin';
    if (!confirm(`Are you sure you want to change this user's role to ${nextRole}?`)) return;

    try {
      const res = await fetch(`http://localhost:5000/api/admin/users/${userId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        },
        body: JSON.stringify({ role: nextRole })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to update user role");

      showToast(`User role updated to ${nextRole}`, "success");
      setUsers(prev => prev.map(u => u.id === Number(userId) ? { ...u, role: nextRole } : u));
    } catch (err) {
      showToast(err.message || "Error updating role", "danger");
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!confirm("Are you sure you want to permanently delete this user account? All transaction records will remain, but the account will be deleted.")) return;

    try {
      const res = await fetch(`http://localhost:5000/api/admin/users/${userId}`, {
        method: 'DELETE',
        headers: {
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        }
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to delete user");

      showToast("User account deleted successfully.", "success");
      fetchUsers();
    } catch (err) {
      showToast(err.message || "Error deleting user", "danger");
    }
  };

  return (
    <div style={{ display: 'flex', gap: '2rem', maxWidth: 1400, margin: '2rem auto', padding: '0 24px', flexWrap: 'wrap' }}>
      <Sidebar currentTab={currentTab} setCurrentTab={setCurrentTab} role={currentUser.role} />

      {/* Admin Content Workspace */}
      <div style={{ flexGrow: 1, minWidth: '350px', flexBasis: 0 }}>
        <h1 style={{ fontSize: '1.75rem', fontWeight: 800, marginBottom: '2rem', fontFamily: 'var(--font-display)' }}>
          Member <span className="text-gradient">Accounts Manager</span>
        </h1>

        {/* Users Table */}
        <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
          {loading ? (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {[1, 2, 3].map(i => (
                <div key={i} className="skeleton" style={{ height: 60, borderRadius: 8 }} />
              ))}
            </div>
          ) : users.length === 0 ? (
            <div style={{ padding: '2rem 1rem', textAlign: 'center', color: 'var(--text-muted)' }}>
              No registered user accounts found in database.
            </div>
          ) : (
            <div className="table-container">
              <table className="custom-table">
                <thead>
                  <tr>
                    <th>User Profile</th>
                    <th>Email Address</th>
                    <th>Account Role</th>
                    <th>Membership Status</th>
                    <th>Created On</th>
                    <th>Control Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {users.map((user) => {
                    const isSelf = user.id === currentUser.id;
                    return (
                      <tr key={user.id}>
                        <td>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                            <img src={user.avatar} alt={user.name} style={{ width: 32, height: 32, borderRadius: '50%', background: '#1e293b' }} />
                            <div>
                              <span style={{ fontWeight: 600, display: 'block', color: '#fff', fontSize: '0.9rem' }}>
                                {user.name} {isSelf && <span style={{ fontSize: '0.7rem', color: 'var(--primary)', fontStyle: 'italic' }}>(You)</span>}
                              </span>
                              <span style={{ fontSize: '0.7rem', color: 'var(--text-muted)' }}>ID: #{user.id}</span>
                            </div>
                          </div>
                        </td>
                        <td style={{ fontSize: '0.85rem' }}>{user.email}</td>
                        <td>
                          <span className={`badge ${user.role === 'admin' ? 'badge-info' : 'badge-secondary'}`} style={user.role !== 'admin' ? { background: 'rgba(255,255,255,0.05)', color: 'var(--text-muted)' } : {}}>
                            {user.role}
                          </span>
                        </td>
                        <td>
                          {user.status === 'active' ? (
                            <span className="badge badge-success">Active</span>
                          ) : (
                            <span className="badge badge-danger">Suspended</span>
                          )}
                        </td>
                        <td style={{ fontSize: '0.85rem' }}>
                          {new Date(user.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                        </td>
                        <td>
                          <div style={{ display: 'flex', gap: '0.5rem' }}>
                            {/* Toggle Suspend */}
                            <button
                              onClick={() => handleToggleStatus(user.id, user.status)}
                              disabled={isSelf}
                              className="btn btn-secondary"
                              style={{ 
                                padding: '0.4rem 0.6rem', 
                                borderRadius: 6, 
                                fontSize: '0.75rem',
                                color: user.status === 'active' ? 'var(--danger)' : 'var(--success)',
                                opacity: isSelf ? 0.3 : 1,
                                cursor: isSelf ? 'not-allowed' : 'pointer'
                              }}
                              title={user.status === 'active' ? 'Suspend Account' : 'Activate Account'}
                            >
                              {user.status === 'active' ? 'Suspend' : 'Activate'}
                            </button>
                            
                            {/* Toggle Role */}
                            <button
                              onClick={() => handleToggleRole(user.id, user.role)}
                              disabled={isSelf}
                              className="btn btn-secondary"
                              style={{ 
                                padding: '0.4rem', 
                                borderRadius: 6,
                                opacity: isSelf ? 0.3 : 1,
                                cursor: isSelf ? 'not-allowed' : 'pointer'
                              }}
                              title="Toggle admin/customer role"
                            >
                              <ArrowUpCircle size={14} style={{ color: 'var(--primary)' }} />
                            </button>

                            {/* Delete User */}
                            <button
                              onClick={() => handleDeleteUser(user.id)}
                              disabled={isSelf}
                              className="btn btn-secondary"
                              style={{ 
                                padding: '0.4rem', 
                                borderRadius: 6, 
                                color: 'var(--danger)', 
                                background: 'rgba(239, 68, 68, 0.05)',
                                opacity: isSelf ? 0.3 : 1,
                                cursor: isSelf ? 'not-allowed' : 'pointer'
                              }}
                              title="Delete Account"
                            >
                              <Trash2 size={14} />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
