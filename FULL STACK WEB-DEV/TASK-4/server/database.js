import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const DB_PATH = path.join(__dirname, 'db.json');

// Read DB helper
function readData() {
  try {
    const raw = fs.readFileSync(DB_PATH, 'utf8');
    return JSON.parse(raw);
  } catch (error) {
    console.error("Database read error, restoring defaults...", error);
    return { users: [], products: [], orders: [] };
  }
}

// Write DB helper
function writeData(data) {
  try {
    fs.writeFileSync(DB_PATH, JSON.stringify(data, null, 2), 'utf8');
    return true;
  } catch (error) {
    console.error("Database write error:", error);
    return false;
  }
}

export const db = {
  // --- USERS ---
  getUsers: () => readData().users,
  
  getUserById: (id) => readData().users.find(u => u.id === Number(id)),
  
  getUserByEmail: (email) => {
    return readData().users.find(u => u.email.toLowerCase() === email.toLowerCase());
  },
  
  createUser: (userData) => {
    const data = readData();
    const newId = data.users.length > 0 ? Math.max(...data.users.map(u => u.id)) + 1 : 1;
    const newUser = {
      id: newId,
      name: userData.name,
      email: userData.email,
      password: userData.password,
      role: userData.role || 'customer',
      status: userData.status || 'active',
      avatar: userData.avatar || `https://api.dicebear.com/7.x/adventurer/svg?seed=${encodeURIComponent(userData.name)}`,
      createdAt: new Date().toISOString()
    };
    data.users.push(newUser);
    writeData(data);
    return newUser;
  },

  updateUser: (id, updates) => {
    const data = readData();
    const idx = data.users.findIndex(u => u.id === Number(id));
    if (idx === -1) return null;
    
    data.users[idx] = {
      ...data.users[idx],
      ...updates,
      id: Number(id) // Ensure ID cannot be changed
    };
    writeData(data);
    return data.users[idx];
  },

  deleteUser: (id) => {
    const data = readData();
    const filtered = data.users.filter(u => u.id !== Number(id));
    if (filtered.length === data.users.length) return false;
    data.users = filtered;
    writeData(data);
    return true;
  },

  // --- PRODUCTS ---
  getProducts: () => readData().products,
  
  getProductById: (id) => readData().products.find(p => p.id === Number(id)),
  
  createProduct: (productData) => {
    const data = readData();
    const newId = data.products.length > 0 ? Math.max(...data.products.map(p => p.id)) + 1 : 1;
    const newProduct = {
      id: newId,
      name: productData.name,
      description: productData.description,
      price: Number(productData.price),
      category: productData.category,
      image: productData.image || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=600&auto=format&fit=crop',
      rating: Number(productData.rating) || 5.0,
      stock: Number(productData.stock) || 0,
      createdAt: new Date().toISOString()
    };
    data.products.push(newProduct);
    writeData(data);
    return newProduct;
  },

  updateProduct: (id, updates) => {
    const data = readData();
    const idx = data.products.findIndex(p => p.id === Number(id));
    if (idx === -1) return null;
    
    data.products[idx] = {
      ...data.products[idx],
      ...updates,
      price: updates.price !== undefined ? Number(updates.price) : data.products[idx].price,
      stock: updates.stock !== undefined ? Number(updates.stock) : data.products[idx].stock,
      rating: updates.rating !== undefined ? Number(updates.rating) : data.products[idx].rating,
      id: Number(id)
    };
    writeData(data);
    return data.products[idx];
  },

  deleteProduct: (id) => {
    const data = readData();
    const filtered = data.products.filter(p => p.id !== Number(id));
    if (filtered.length === data.products.length) return false;
    data.products = filtered;
    writeData(data);
    return true;
  },

  // --- ORDERS ---
  getOrders: () => readData().orders,
  
  getOrderById: (id) => readData().orders.find(o => o.id === Number(id)),
  
  getOrdersByUserId: (userId) => {
    return readData().orders.filter(o => o.userId === Number(userId));
  },
  
  createOrder: (orderData) => {
    const data = readData();
    
    // Validate and update stock
    for (const item of orderData.items) {
      const pIdx = data.products.findIndex(p => p.id === Number(item.productId));
      if (pIdx === -1) throw new Error(`Product ${item.name} not found`);
      if (data.products[pIdx].stock < item.quantity) {
        throw new Error(`Insufficient stock for ${item.name}`);
      }
      data.products[pIdx].stock -= item.quantity;
    }

    const newId = data.orders.length > 0 ? Math.max(...data.orders.map(o => o.id)) + 1 : 1001;
    const newOrder = {
      id: newId,
      userId: Number(orderData.userId),
      userName: orderData.userName,
      items: orderData.items.map(item => ({
        productId: Number(item.productId),
        name: item.name,
        price: Number(item.price),
        quantity: Number(item.quantity)
      })),
      totalAmount: Number(orderData.totalAmount),
      status: orderData.status || 'pending',
      shippingAddress: orderData.shippingAddress,
      paymentMethod: orderData.paymentMethod || 'Credit Card',
      createdAt: new Date().toISOString()
    };
    
    data.orders.push(newOrder);
    writeData(data);
    return newOrder;
  },

  updateOrderStatus: (id, status) => {
    const data = readData();
    const idx = data.orders.findIndex(o => o.id === Number(id));
    if (idx === -1) return null;
    
    data.orders[idx].status = status;
    writeData(data);
    return data.orders[idx];
  },

  deleteOrder: (id) => {
    const data = readData();
    const filtered = data.orders.filter(o => o.id !== Number(id));
    if (filtered.length === data.orders.length) return false;
    data.orders = filtered;
    writeData(data);
    return true;
  }
};
