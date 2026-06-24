import React, { useState, useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import { Plus, Edit2, Trash2, ShieldAlert } from 'lucide-react';

export default function AdminProducts({ currentUser, currentTab, setCurrentTab, showToast }) {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingProduct, setEditingProduct] = useState(null);

  // Form Fields
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [price, setPrice] = useState('');
  const [category, setCategory] = useState('Peripherals');
  const [image, setImage] = useState('');
  const [stock, setStock] = useState('');

  const fetchProducts = async () => {
    setLoading(true);
    try {
      const res = await fetch('http://localhost:5000/api/products?limit=100'); // Load all for administration
      if (!res.ok) throw new Error("Could not load products inventory");
      const data = await res.json();
      setProducts(data.products);
    } catch (err) {
      showToast(err.message || "Failed to fetch products inventory", "danger");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (currentUser && currentTab === 'admin-products') {
      fetchProducts();
    }
  }, [currentTab, currentUser]);

  const openCreateModal = () => {
    setEditingProduct(null);
    setName('');
    setDescription('');
    setPrice('');
    setCategory('Peripherals');
    setImage('');
    setStock('');
    setShowModal(true);
  };

  const openEditModal = (product) => {
    setEditingProduct(product);
    setName(product.name);
    setDescription(product.description);
    setPrice(product.price);
    setCategory(product.category);
    setImage(product.image);
    setStock(product.stock);
    setShowModal(true);
  };

  const handleSaveProduct = async (e) => {
    e.preventDefault();
    if (!name || !description || !price || !category || stock === '') {
      showToast("Please complete all required fields.", "warning");
      return;
    }

    const payload = {
      name,
      description,
      price: Number(price),
      category,
      image: image || undefined,
      stock: Number(stock)
    };

    const isEdit = !!editingProduct;
    const url = isEdit 
      ? `http://localhost:5000/api/products/${editingProduct.id}` 
      : 'http://localhost:5000/api/products';
    const method = isEdit ? 'PUT' : 'POST';

    try {
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to save product");

      showToast(data.message || "Product saved successfully!", "success");
      setShowModal(false);
      fetchProducts();
    } catch (err) {
      showToast(err.message || "Error saving product", "danger");
    }
  };

  const handleDeleteProduct = async (id) => {
    if (!confirm("Are you sure you want to permanently delete this product? This action cannot be undone.")) return;

    try {
      const res = await fetch(`http://localhost:5000/api/products/${id}`, {
        method: 'DELETE',
        headers: {
          'x-user-id': String(currentUser.id),
          'x-user-role': currentUser.role
        }
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Failed to delete product");

      showToast(data.message || "Product deleted.", "success");
      fetchProducts();
    } catch (err) {
      showToast(err.message || "Error deleting product", "danger");
    }
  };

  return (
    <div style={{ display: 'flex', gap: '2rem', maxWidth: 1400, margin: '2rem auto', padding: '0 24px', flexWrap: 'wrap' }}>
      <Sidebar currentTab={currentTab} setCurrentTab={setCurrentTab} role={currentUser.role} />

      {/* Admin Content Workspace */}
      <div style={{ flexGrow: 1, minWidth: '350px', flexBasis: 0 }}>
        <div style={{ display: 'flex', justify: 'between', alignItems: 'center', marginBottom: '2rem' }}>
          <h1 style={{ fontSize: '1.75rem', fontWeight: 800, margin: 0, fontFamily: 'var(--font-display)' }}>
            Inventory <span className="text-gradient">Products CRUD</span>
          </h1>
          <button 
            onClick={openCreateModal}
            className="btn btn-primary"
            style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginLeft: 'auto' }}
          >
            <Plus size={16} />
            Add New Product
          </button>
        </div>

        {/* Catalog Table */}
        <div className="glass-panel" style={{ padding: '1.5rem', border: '1px solid var(--border-glass)' }}>
          {loading ? (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {[1, 2, 3].map(i => (
                <div key={i} className="skeleton" style={{ height: 60, borderRadius: 8 }} />
              ))}
            </div>
          ) : products.length === 0 ? (
            <div style={{ padding: '2rem 1rem', textAlign: 'center', color: 'var(--text-muted)' }}>
              No products found in database. Create one above.
            </div>
          ) : (
            <div className="table-container">
              <table className="custom-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock Level</th>
                    <th>Rating</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {products.map((prod) => (
                    <tr key={prod.id}>
                      <td>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                          <img src={prod.image} alt={prod.name} style={{ width: 40, height: 40, borderRadius: 6, objectFit: 'cover' }} />
                          <div>
                            <span style={{ fontWeight: 600, display: 'block', color: '#fff', fontSize: '0.9rem' }}>{prod.name}</span>
                            <span style={{ fontSize: '0.7rem', color: 'var(--text-muted)' }}>ID: #{prod.id}</span>
                          </div>
                        </div>
                      </td>
                      <td>{prod.category}</td>
                      <td style={{ fontWeight: 700 }}>${prod.price.toFixed(2)}</td>
                      <td>
                        {prod.stock === 0 ? (
                          <span className="badge badge-danger">Out of Stock</span>
                        ) : prod.stock <= 15 ? (
                          <span className="badge badge-warning">Low: {prod.stock}</span>
                        ) : (
                          <span className="badge badge-success">Stock: {prod.stock}</span>
                        )}
                      </td>
                      <td>{prod.rating} ★</td>
                      <td>
                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                          <button
                            onClick={() => openEditModal(prod)}
                            className="btn btn-secondary"
                            style={{ padding: '0.4rem', borderRadius: 6 }}
                            title="Edit Product"
                          >
                            <Edit2 size={14} />
                          </button>
                          <button
                            onClick={() => handleDeleteProduct(prod.id)}
                            className="btn btn-secondary"
                            style={{ padding: '0.4rem', borderRadius: 6, color: 'var(--danger)', background: 'rgba(239, 68, 68, 0.05)' }}
                            title="Delete Product"
                          >
                            <Trash2 size={14} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Create/Edit Modal Overlay */}
      {showModal && (
        <div className="modal-overlay" onClick={() => setShowModal(false)}>
          <div className="glass-panel modal-content" onClick={(e) => e.stopPropagation()} style={{ border: '1px solid var(--border-glass)', padding: '2rem' }}>
            <div style={{ display: 'flex', justify: 'between', borderBottom: '1px solid var(--border-glass)', paddingBottom: '0.75rem', marginBottom: '1.25rem' }}>
              <h3 style={{ fontSize: '1.25rem' }}>{editingProduct ? 'Edit Product Details' : 'Create New Product'}</h3>
              <button 
                onClick={() => setShowModal(false)}
                style={{ marginLeft: 'auto', border: 'none', background: 'none', color: 'var(--text-muted)', fontSize: '1.2rem', cursor: 'pointer' }}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSaveProduct}>
              {/* Product Name */}
              <div className="form-group">
                <label className="form-label">Product Name</label>
                <input 
                  type="text" 
                  className="input-field" 
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="e.g. Zenith Pro Earbuds"
                  required
                />
              </div>

              {/* Category & Price */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }} id="modal-form-grid-1">
                <style dangerouslySetInnerHTML={{__html: `
                  @media (max-width: 500px) {
                    #modal-form-grid-1 { grid-template-columns: 1fr !important; }
                  }
                `}} />
                
                <div className="form-group">
                  <label className="form-label">Category</label>
                  <select 
                    className="input-field"
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                  >
                    <option value="Peripherals">Peripherals</option>
                    <option value="Audio">Audio</option>
                    <option value="Wearables">Wearables</option>
                    <option value="Video">Video</option>
                    <option value="Accessories">Accessories</option>
                  </select>
                </div>

                <div className="form-group">
                  <label className="form-label">Price ($)</label>
                  <input 
                    type="number" 
                    step="0.01"
                    className="input-field" 
                    value={price}
                    onChange={(e) => setPrice(e.target.value)}
                    placeholder="99.99"
                    required
                  />
                </div>
              </div>

              {/* Stock & Image URL */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }} id="modal-form-grid-2">
                <style dangerouslySetInnerHTML={{__html: `
                  @media (max-width: 500px) {
                    #modal-form-grid-2 { grid-template-columns: 1fr !important; }
                  }
                `}} />
                
                <div className="form-group">
                  <label className="form-label">Initial Stock</label>
                  <input 
                    type="number" 
                    className="input-field" 
                    value={stock}
                    onChange={(e) => setStock(e.target.value)}
                    placeholder="25"
                    required
                  />
                </div>

                <div className="form-group">
                  <label className="form-label">Image URL (Optional)</label>
                  <input 
                    type="url" 
                    className="input-field" 
                    value={image}
                    onChange={(e) => setImage(e.target.value)}
                    placeholder="https://images.unsplash.com/..."
                  />
                </div>
              </div>

              {/* Description */}
              <div className="form-group">
                <label className="form-label">Product Description</label>
                <textarea 
                  rows="3"
                  className="input-field" 
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Detail specifications, build materials, packaging contents..."
                  style={{ resize: 'none' }}
                  required
                />
              </div>

              <button 
                type="submit" 
                className="btn btn-primary"
                style={{ width: '100%', marginTop: '1rem', height: 42 }}
              >
                {editingProduct ? 'Save Product Changes' : 'Publish Product to Catalog'}
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
