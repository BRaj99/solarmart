const products = [
  { id: 1, name: 'Mono Solar Panel 550W', brand: 'SunPeak', category: 'Panels', price: 28500, stock: 18, image: 'images/mono-solar-panel.svg', desc: 'High efficiency mono panel for home and commercial rooftop systems.' },
  { id: 2, name: 'Hybrid Solar Inverter 5kW', brand: 'VoltEdge', category: 'Inverters', price: 125000, stock: 7, image: 'images/hybrid-solar-inverter.svg', desc: 'Smart hybrid inverter with battery support and LCD monitoring.' },
  { id: 3, name: 'Lithium Battery 48V 100Ah', brand: 'PowerCell', category: 'Batteries', price: 185000, stock: 5, image: 'images/lithium-battery.svg', desc: 'Long-life lithium storage for backup and off-grid power.' },
  { id: 4, name: 'Solar Street Light 120W', brand: 'BrightWay', category: 'Lights', price: 24500, stock: 24, image: 'images/solar-street-light.svg', desc: 'Automatic dusk-to-dawn outdoor lighting with motion sensor.' },
  { id: 5, name: 'Solar Charge Controller MPPT', brand: 'ChargePro', category: 'Accessories', price: 16500, stock: 14, image: 'images/solar-charge-controller.svg', desc: 'MPPT controller to improve charging efficiency and battery safety.' },
  { id: 6, name: 'Solar Water Pump Kit', brand: 'AquaSun', category: 'Kits', price: 92000, stock: 6, image: 'images/solar-water-pump-kit.svg', desc: 'Reliable irrigation and water supply kit powered directly by sunlight.' },
  { id: 7, name: 'Home Solar Kit 3kW', brand: 'EcoHome', category: 'Kits', price: 275000, stock: 4, image: 'images/home-solar-kit.svg', desc: 'Complete rooftop package for small family homes.' },
  { id: 8, name: 'Solar Cable 6mm Bundle', brand: 'SafeWire', category: 'Accessories', price: 8500, stock: 40, image: 'images/solar-cable-bundle.svg', desc: 'UV-resistant solar cable bundle for safer installation.' }
];

const orders = [
  { id: 'ORD-1001', customer: 'Aarav Shrestha', total: 153500, status: 'Processing' },
  { id: 'ORD-1002', customer: 'Nisha Gurung', total: 28500, status: 'Delivered' },
  { id: 'ORD-1003', customer: 'Ramesh KC', total: 275000, status: 'Pending' }
];

const formatRs = value => `Rs ${Number(value).toLocaleString('en-IN')}`;
const getCart = () => JSON.parse(localStorage.getItem('solarCart') || '[]');
const setCart = cart => { localStorage.setItem('solarCart', JSON.stringify(cart)); updateCartCount(); };

function showToast(message) {
  let toast = document.querySelector('.toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2200);
}

function updateCartCount() {
  const count = getCart().reduce((sum, item) => sum + item.qty, 0);
  document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
}

function addToCart(id, qty = 1) {
  const product = products.find(p => p.id === Number(id));
  if (!product) return;
  const cart = getCart();
  const existing = cart.find(item => item.id === product.id);
  if (existing) existing.qty += qty;
  else cart.push({ id: product.id, qty });
  setCart(cart);
  showToast(`${product.name} added to cart`);
}

function productCard(product) {
  return `<div class="pro" data-category="${product.category}">
    <div class="product-img"><img src="${product.image}" alt="${product.name}" loading="lazy"></div>
    <div class="des">
      <span>${product.brand} • ${product.category}</span>
      <h5>${product.name}</h5>
      <div class="star"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-stroke"></i></div>
      <p>${product.desc}</p>
      <h4>${formatRs(product.price)}</h4>
    </div>
    <button class="cart add-cart" data-id="${product.id}" aria-label="Add to cart"><i class="fa-solid fa-cart-shopping"></i></button>
  </div>`;
}

function renderProducts(list = products, selector = '#product-list') {
  const container = document.querySelector(selector);
  if (!container) return;
  container.innerHTML = list.map(productCard).join('') || '<p>No products found.</p>';
}

function initShop() {
  renderProducts(products, '#product-list');
  const search = document.querySelector('#searchProducts');
  const category = document.querySelector('#categoryFilter');
  const sort = document.querySelector('#sortProducts');
  const apply = () => {
    let list = [...products];
    const term = (search?.value || '').toLowerCase();
    const cat = category?.value || 'All';
    if (term) list = list.filter(p => `${p.name} ${p.brand} ${p.category}`.toLowerCase().includes(term));
    if (cat !== 'All') list = list.filter(p => p.category === cat);
    if (sort?.value === 'low') list.sort((a, b) => a.price - b.price);
    if (sort?.value === 'high') list.sort((a, b) => b.price - a.price);
    renderProducts(list, '#product-list');
  };
  [search, category, sort].forEach(el => el && el.addEventListener('input', apply));
}

function renderCart() {
  const body = document.querySelector('#cartBody');
  const summary = document.querySelector('#cartSummary');
  if (!body || !summary) return;
  const cart = getCart();
  if (!cart.length) {
    body.innerHTML = '<tr><td colspan="5">Your cart is empty. Visit the shop to add solar products.</td></tr>';
    summary.innerHTML = '<h3>Cart Total</h3><p>No items yet.</p><a class="primary-btn" href="shop.php">Shop Now</a>';
    return;
  }
  let subtotal = 0;
  body.innerHTML = cart.map(item => {
    const product = products.find(p => p.id === item.id);
    if (!product) return '';
    const total = product.price * item.qty; subtotal += total;
    return `<tr><td>${product.name}</td><td>${formatRs(product.price)}</td><td><button class="qty-btn" data-action="minus" data-id="${product.id}">-</button> ${item.qty} <button class="qty-btn" data-action="plus" data-id="${product.id}">+</button></td><td>${formatRs(total)}</td><td><button class="qty-btn" data-action="remove" data-id="${product.id}"><i class="fa fa-trash"></i></button></td></tr>`;
  }).join('');
  const shipping = subtotal > 200000 ? 0 : 1500;
  const tax = Math.round(subtotal * 0.13);
  const grand = subtotal + shipping + tax;
  summary.innerHTML = `<h3>Cart Total</h3><p>Subtotal: <strong>${formatRs(subtotal)}</strong></p><p>Shipping: <strong>${shipping ? formatRs(shipping) : 'Free'}</strong></p><p>VAT Estimate: <strong>${formatRs(tax)}</strong></p><h3>${formatRs(grand)}</h3><a class="primary-btn" href="checkout.php">Proceed to Checkout</a>`;
}

function changeCart(id, action) {
  let cart = getCart();
  const item = cart.find(x => x.id === Number(id));
  if (!item) return;
  if (action === 'plus') item.qty += 1;
  if (action === 'minus') item.qty -= 1;
  if (action === 'remove' || item.qty <= 0) cart = cart.filter(x => x.id !== Number(id));
  setCart(cart); renderCart();
}

function renderAdmin() {
  const productTable = document.querySelector('#adminProducts');
  const orderTable = document.querySelector('#adminOrders');
  if (productTable) productTable.innerHTML = products.map(p => `<tr><td>${p.name}</td><td>${p.category}</td><td>${formatRs(p.price)}</td><td>${p.stock}</td><td><button class="outline-btn">Edit</button></td></tr>`).join('');
  if (orderTable) orderTable.innerHTML = orders.map(o => `<tr><td>${o.id}</td><td>${o.customer}</td><td>${formatRs(o.total)}</td><td>${o.status}</td></tr>`).join('');
  const revenue = orders.reduce((s, o) => s + o.total, 0);
  const set = (id, val) => { const el = document.querySelector(id); if (el) el.textContent = val; };
  set('#metricProducts', products.length); set('#metricOrders', orders.length); set('#metricRevenue', formatRs(revenue)); set('#metricCustomers', 16);
}

function initForms() {
  document.querySelectorAll('form[data-demo]').forEach(form => form.addEventListener('submit', e => {
    e.preventDefault();
    if (form.id === 'checkoutForm') { localStorage.removeItem('solarCart'); updateCartCount(); }
    showToast(form.dataset.message || 'Submitted successfully');
    form.reset();
    if (form.id === 'checkoutForm') setTimeout(() => location.href = 'index.php', 900);
  }));
}

document.addEventListener('click', e => {
  const add = e.target.closest('.add-cart');
  if (add) addToCart(add.dataset.id);
  const qty = e.target.closest('.qty-btn');
  if (qty) changeCart(qty.dataset.id, qty.dataset.action);
});

function initProfileLivebar() {
  const toggles = document.querySelectorAll('.profile-toggle');
  const desktopPanel = document.querySelector('.profile-menu-wrap .profile-livebar');
  const mobilePanel = document.querySelector('.mobile-profile-panel');

  toggles.forEach(toggle => {
    toggle.addEventListener('click', e => {
      e.preventDefault();
      e.stopPropagation();

      const isMobileToggle = toggle.classList.contains('mobile-profile');
      const panel = isMobileToggle ? mobilePanel : desktopPanel;
      if (!panel) return;

      const isOpen = panel.classList.toggle('active');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

      if (isMobileToggle && desktopPanel) desktopPanel.classList.remove('active');
      if (!isMobileToggle && mobilePanel) mobilePanel.classList.remove('active');
    });
  });

  document.addEventListener('click', e => {
    if (!e.target.closest('.profile-menu-wrap') && !e.target.closest('.mobile-profile-panel') && !e.target.closest('.mobile-profile')) {
      document.querySelectorAll('.profile-livebar.active').forEach(panel => panel.classList.remove('active'));
      document.querySelectorAll('.profile-toggle').forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const bar = document.getElementById('bar');
  const close = document.getElementById('close');
  const nav = document.getElementById('navbar');
  if (bar && nav) bar.addEventListener('click', () => nav.classList.add('active'));
  if (close && nav) close.addEventListener('click', () => nav.classList.remove('active'));
  updateCartCount(); initShop(); renderCart(); renderAdmin(); initForms(); initProfileLivebar();
});
