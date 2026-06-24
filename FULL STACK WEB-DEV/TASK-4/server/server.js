import express from 'express';
import cors from 'cors';
import { db } from './database.js';

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

// Log Middleware
app.use((req, res, next) => {
  console.log(`[${new Date().toLocaleTimeString()}] ${req.method} ${req.url}`);
  next();
});

// Auth Middleware (Simulation)
const authenticate = (req, res, next) => {
  const userId = req.headers['x-user-id'];
  if (!userId) {
    return res.status(401).json({ message: "Unauthorized. Please log in." });
  }
  const user = db.getUserById(userId);
  if (!user) {
    return res.status(401).json({ message: "User session not found." });
  }
  if (user.status === 'suspended') {
    return res.status(403).json({ message: "Your account is suspended." });
  }
  req.user = user;
  next();
};

const requireAdmin = (req, res, next) => {
  authenticate(req, res, () => {
    if (req.user.role !== 'admin') {
      return res.status(403).json({ message: "Access denied. Admin role required." });
    }
    next();
  });
};

// ================= AUTH ROUTES =================

app.post('/api/auth/register', (req, res) => {
  const { name, email, password } = req.body;
  if (!name || !email || !password) {
    return res.status(400).json({ message: "Name, email, and password are required." });
  }
  
  const existing = db.getUserByEmail(email);
  if (existing) {
    return res.status(400).json({ message: "Email is already registered." });
  }

  const newUser = db.createUser({ name, email, password, role: 'customer', status: 'active' });
  // Omit password from response
  const { password: _, ...userWithoutPassword } = newUser;
  res.status(201).json({ user: userWithoutPassword });
});

app.post('/api/auth/login', (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(400).json({ message: "Email and password are required." });
  }

  const user = db.getUserByEmail(email);
  if (!user || user.password !== password) {
    return res.status(400).json({ message: "Invalid email or password." });
  }

  if (user.status === 'suspended') {
    return res.status(403).json({ message: "Your account has been suspended." });
  }

  const { password: _, ...userWithoutPassword } = user;
  res.json({ user: userWithoutPassword });
});

app.post('/api/auth/forgot-password', (req, res) => {
  const { email, newPassword } = req.body;
  if (!email || !newPassword) {
    return res.status(400).json({ message: "Email and new password are required." });
  }

  const user = db.getUserByEmail(email);
  if (!user) {
    return res.status(404).json({ message: "No user found with this email." });
  }

  db.updateUser(user.id, { password: newPassword });
  res.json({ message: "Password updated successfully. You can now log in." });
});

app.put('/api/auth/profile', authenticate, (req, res) => {
  const { name, email, password, avatar } = req.body;
  
  if (email && email !== req.user.email) {
    const existing = db.getUserByEmail(email);
    if (existing) {
      return res.status(400).json({ message: "Email is already taken by another account." });
    }
  }

  const updates = {};
  if (name) updates.name = name;
  if (email) updates.email = email;
  if (password) updates.password = password;
  if (avatar) updates.avatar = avatar;

  const updatedUser = db.updateUser(req.user.id, updates);
  const { password: _, ...userWithoutPassword } = updatedUser;
  res.json({ user: userWithoutPassword, message: "Profile updated successfully." });
});


// ================= PRODUCT ROUTES =================

app.get('/api/products', (req, res) => {
  let products = db.getProducts();
  const { category, search, minPrice, maxPrice, rating, sortBy, order, page, limit } = req.query;

  // Search
  if (search) {
    const q = search.toLowerCase();
    products = products.filter(p => 
      p.name.toLowerCase().includes(q) || 
      p.description.toLowerCase().includes(q) ||
      p.category.toLowerCase().includes(q)
    );
  }

  // Category
  if (category && category !== 'All') {
    products = products.filter(p => p.category.toLowerCase() === category.toLowerCase());
  }

  // Min Price
  if (minPrice) {
    products = products.filter(p => p.price >= Number(minPrice));
  }

  // Max Price
  if (maxPrice) {
    products = products.filter(p => p.price <= Number(maxPrice));
  }

  // Rating
  if (rating) {
    products = products.filter(p => p.rating >= Number(rating));
  }

  // Sorting
  if (sortBy) {
    products.sort((a, b) => {
      let fieldA = a[sortBy];
      let fieldB = b[sortBy];
      
      if (typeof fieldA === 'string') {
        fieldA = fieldA.toLowerCase();
        fieldB = fieldB.toLowerCase();
      }

      if (fieldA < fieldB) return order === 'desc' ? 1 : -1;
      if (fieldA > fieldB) return order === 'desc' ? -1 : 1;
      return 0;
    });
  }

  // Pagination
  const totalItems = products.length;
  const p = Number(page) || 1;
  const l = Number(limit) || 6;
  const startIndex = (p - 1) * l;
  const paginated = products.slice(startIndex, startIndex + l);

  res.json({
    products: paginated,
    pagination: {
      currentPage: p,
      totalPages: Math.ceil(totalItems / l),
      totalItems,
      limit: l
    }
  });
});

app.get('/api/products/:id', (req, res) => {
  const product = db.getProductById(req.params.id);
  if (!product) {
    return res.status(404).json({ message: "Product not found." });
  }
  res.json(product);
});

// Admin Product CRUD
app.post('/api/products', requireAdmin, (req, res) => {
  const { name, description, price, category, image, stock } = req.body;
  if (!name || !description || !price || !category || stock === undefined) {
    return res.status(400).json({ message: "Missing required product details." });
  }

  try {
    const newProduct = db.createProduct({ name, description, price, category, image, stock });
    res.status(201).json({ product: newProduct, message: "Product created successfully." });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
});

app.put('/api/products/:id', requireAdmin, (req, res) => {
  const { name, description, price, category, image, stock, rating } = req.body;
  
  const updated = db.updateProduct(req.params.id, { name, description, price, category, image, stock, rating });
  if (!updated) {
    return res.status(404).json({ message: "Product not found or failed to update." });
  }
  res.json({ product: updated, message: "Product updated successfully." });
});

app.delete('/api/products/:id', requireAdmin, (req, res) => {
  const success = db.deleteProduct(req.params.id);
  if (!success) {
    return res.status(404).json({ message: "Product not found." });
  }
  res.json({ message: "Product deleted successfully." });
});


// ================= ORDER ROUTES =================

app.post('/api/orders', authenticate, (req, res) => {
  const { items, totalAmount, shippingAddress, paymentMethod } = req.body;

  if (!items || !items.length || !totalAmount || !shippingAddress) {
    return res.status(400).json({ message: "Invalid order details." });
  }

  try {
    const order = db.createOrder({
      userId: req.user.id,
      userName: req.user.name,
      items,
      totalAmount,
      shippingAddress,
      paymentMethod,
      status: 'pending'
    });
    res.status(201).json({ order, message: "Order placed successfully." });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
});

app.get('/api/orders', authenticate, (req, res) => {
  if (req.user.role === 'admin') {
    res.json(db.getOrders());
  } else {
    res.json(db.getOrdersByUserId(req.user.id));
  }
});

// Admin Update Order Status
app.put('/api/orders/:id/status', requireAdmin, (req, res) => {
  const { status } = req.body;
  const validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
  if (!validStatuses.includes(status)) {
    return res.status(400).json({ message: "Invalid status code." });
  }

  const updatedOrder = db.updateOrderStatus(req.params.id, status);
  if (!updatedOrder) {
    return res.status(404).json({ message: "Order not found." });
  }
  res.json({ order: updatedOrder, message: `Order status updated to ${status}.` });
});


// ================= ADMIN ANALYTICS & USER MANAGEMENT =================

app.get('/api/admin/stats', requireAdmin, (req, res) => {
  const users = db.getUsers();
  const products = db.getProducts();
  const orders = db.getOrders();

  // Basic KPI calculations
  const totalSales = orders
    .filter(o => o.status !== 'cancelled')
    .reduce((sum, o) => sum + o.totalAmount, 0);
  
  const activeUsersCount = users.filter(u => u.status === 'active').length;
  const pendingOrdersCount = orders.filter(o => o.status === 'pending').length;

  // Orders per day (last 7 entries)
  const ordersPerDay = {};
  orders.forEach(o => {
    const dateStr = new Date(o.createdAt).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    ordersPerDay[dateStr] = (ordersPerDay[dateStr] || 0) + 1;
  });
  const salesHistory = Object.entries(ordersPerDay).map(([date, count]) => ({ date, count }));

  // Sales per Category
  const categorySales = {};
  orders.filter(o => o.status !== 'cancelled').forEach(o => {
    o.items.forEach(item => {
      // Find category of item
      const product = products.find(p => p.id === item.productId);
      const category = product ? product.category : 'Other';
      categorySales[category] = (categorySales[category] || 0) + (item.price * item.quantity);
    });
  });
  const categoryChart = Object.entries(categorySales).map(([category, amount]) => ({
    category,
    amount: Math.round(amount * 100) / 100
  }));

  res.json({
    kpis: {
      totalSales: Math.round(totalSales * 100) / 100,
      totalUsers: users.length,
      activeUsers: activeUsersCount,
      totalOrders: orders.length,
      pendingOrders: pendingOrdersCount,
      totalProducts: products.length
    },
    salesHistory: salesHistory.slice(-7), // Send last 7 days with data
    categoryChart
  });
});

app.get('/api/admin/users', requireAdmin, (req, res) => {
  const users = db.getUsers().map(u => {
    const { password, ...userWithoutPassword } = u;
    return userWithoutPassword;
  });
  res.json(users);
});

app.put('/api/admin/users/:id', requireAdmin, (req, res) => {
  const { role, status } = req.body;
  const user = db.getUserById(req.params.id);
  if (!user) {
    return res.status(404).json({ message: "User not found." });
  }

  // Prevent admin from disabling their own account
  if (Number(req.params.id) === req.user.id && status === 'suspended') {
    return res.status(400).json({ message: "You cannot suspend your own admin account!" });
  }

  const updates = {};
  if (role) updates.role = role;
  if (status) updates.status = status;

  const updatedUser = db.updateUser(req.params.id, updates);
  const { password: _, ...userWithoutPassword } = updatedUser;
  res.json({ user: userWithoutPassword, message: "User updated successfully." });
});

app.delete('/api/admin/users/:id', requireAdmin, (req, res) => {
  if (Number(req.params.id) === req.user.id) {
    return res.status(400).json({ message: "You cannot delete your own admin account!" });
  }
  const success = db.deleteUser(req.params.id);
  if (!success) {
    return res.status(404).json({ message: "User not found." });
  }
  res.json({ message: "User deleted successfully." });
});


// Start server
app.listen(PORT, () => {
  console.log(`\n========================================`);
  console.log(` Zenith Backend Server running on port ${PORT}`);
  console.log(`========================================\n`);
});
