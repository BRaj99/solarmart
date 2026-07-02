const fallbackProducts = [
  { id: 1, name: 'Mono Solar Panel 550W', brand: 'SunPeak', category: 'Panels', price: 28500, stock: 18, image: 'images/mono-solar-panel.svg', desc: 'High efficiency mono panel for home and commercial rooftop systems.' },
  { id: 2, name: 'Hybrid Solar Inverter 5kW', brand: 'VoltEdge', category: 'Inverters', price: 125000, stock: 7, image: 'images/hybrid-solar-inverter.svg', desc: 'Smart hybrid inverter with battery support and LCD monitoring.' },
  { id: 3, name: 'Lithium Battery 48V 100Ah', brand: 'PowerCell', category: 'Batteries', price: 185000, stock: 5, image: 'images/lithium-battery.svg', desc: 'Long-life lithium storage for backup and off-grid power.' },
  { id: 4, name: 'Solar Street Light 120W', brand: 'BrightWay', category: 'Lights', price: 24500, stock: 24, image: 'images/solar-street-light.svg', desc: 'Automatic dusk-to-dawn outdoor lighting with motion sensor.' },
  { id: 5, name: 'Solar Charge Controller MPPT', brand: 'ChargePro', category: 'Accessories', price: 16500, stock: 14, image: 'images/solar-charge-controller.svg', desc: 'MPPT controller to improve charging efficiency and battery safety.' },
  { id: 6, name: 'Solar Water Pump Kit', brand: 'AquaSun', category: 'Kits', price: 92000, stock: 6, image: 'images/solar-water-pump-kit.svg', desc: 'Reliable irrigation and water supply kit powered directly by sunlight.' },
  { id: 7, name: 'Home Solar Kit 3kW', brand: 'EcoHome', category: 'Kits', price: 275000, stock: 4, image: 'images/home-solar-kit.svg', desc: 'Complete rooftop package for small family homes.' },
  { id: 8, name: 'Solar Cable 6mm Bundle', brand: 'SafeWire', category: 'Accessories', price: 8500, stock: 40, image: 'images/solar-cable-bundle.svg', desc: 'UV-resistant solar cable bundle for safer installation.' }
];

const products = Array.isArray(window.SOLAR_PRODUCTS) && window.SOLAR_PRODUCTS.length ? window.SOLAR_PRODUCTS : fallbackProducts;
const orders = [
  { id: 'ORD-1001', customer: 'Aarav Shrestha', total: 153500, status: 'Processing' },
  { id: 'ORD-1002', customer: 'Nisha Gurung', total: 28500, status: 'Delivered' },
  { id: 'ORD-1003', customer: 'Ramesh KC', total: 275000, status: 'Pending' }
];

const formatRs = value => `Rs ${Number(value || 0).toLocaleString('en-IN')}`;

function escapeHtml(value) {
  return String(value || '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));
}


function getCart() {
  try {
    const cart = JSON.parse(localStorage.getItem('solarCart') || '[]');
    return Array.isArray(cart) ? cart.filter(item => Number(item.id) > 0 && Number(item.qty) > 0) : [];
  } catch (e) {
    return [];
  }
}

function saveCartCookie(cart) {
  document.cookie = 'solarCart=' + encodeURIComponent(JSON.stringify(cart)) + '; path=/; max-age=86400; SameSite=Lax';
}

function setCart(cart) {
  localStorage.setItem('solarCart', JSON.stringify(cart));
  saveCartCookie(cart);
  updateCartCount();
}

function syncCheckoutCartData() {
  const cart = getCart();
  saveCartCookie(cart);
  const input = document.querySelector('#cartDataInput');
  if (input) input.value = JSON.stringify(cart);
  return cart;
}

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
  const count = getCart().reduce((sum, item) => sum + Number(item.qty || 0), 0);
  document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
}

function addToCart(id, qty = 1) {
  const product = products.find(p => Number(p.id) === Number(id));
  if (!product) return;
  const cart = getCart();
  const existing = cart.find(item => Number(item.id) === Number(product.id));
  if (existing) existing.qty = Number(existing.qty) + Number(qty);
  else cart.push({ id: Number(product.id), qty: Number(qty) });
  setCart(cart);
  showToast(`${product.name} added to cart`);
}

function productCard(product) {
  const description = escapeHtml(product.desc || 'Product description will be updated soon.');
  const shortDescription = description.length > 120 ? description.slice(0, 120) + '...' : description;

  return `<div class="pro" data-category="${escapeHtml(product.category)}">
    <a class="product-card-link" href="product.php?id=${Number(product.id)}">
      <div class="product-img"><img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name || 'Product')}" loading="lazy"></div>
      <div class="des">
        <span>${escapeHtml(product.brand)} • ${escapeHtml(product.category)}</span>
        <h5>${escapeHtml(product.name)}</h5>
        <p class="product-card-description">${shortDescription}</p>
        <h4>${formatRs(product.price)}</h4>
      </div>
    </a>
    <button class="cart add-cart" data-id="${Number(product.id)}" aria-label="Add to cart"><i class="fa-solid fa-cart-shopping"></i></button>
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
    if (term) list = list.filter(p => `${p.name} ${p.brand} ${p.category} ${p.desc || ''}`.toLowerCase().includes(term));
    if (cat !== 'All') list = list.filter(p => p.category === cat);
    if (sort?.value === 'low') list.sort((a, b) => Number(a.price) - Number(b.price));
    if (sort?.value === 'high') list.sort((a, b) => Number(b.price) - Number(a.price));
    renderProducts(list, '#product-list');
  };
  [search, category, sort].forEach(el => el && el.addEventListener('input', apply));
}

function renderCart() {
  const body = document.querySelector('#cartBody');
  const summary = document.querySelector('#cartSummary');
  if (!body || !summary) return;

  const cart = getCart();
  saveCartCookie(cart);

  if (!cart.length) {
    body.innerHTML = '<tr><td colspan="5"><div class="empty-cart"><i class="fa-solid fa-cart-shopping"></i><h3>Your cart is empty</h3><p>Visit the shop to add solar products.</p><a class="primary-btn" href="shop.php">Shop Now</a></div></td></tr>';
    summary.innerHTML = '<h3>Cart Total</h3><p>No items yet.</p><a class="primary-btn cart-checkout-btn" href="shop.php">Shop Now</a>';
    return;
  }

  let subtotal = 0;
  body.innerHTML = cart.map(item => {
    const product = products.find(p => Number(p.id) === Number(item.id));
    if (!product) return '';
    const qty = Number(item.qty);
    const total = Number(product.price) * qty;
    subtotal += total;
    return `<tr class="cart-row">
      <td><div class="cart-product-cell"><img src="${product.image || ''}" alt="${product.name || 'Product'}"><div><strong>${product.name || ''}</strong><span>${product.brand || ''} • ${product.category || ''}</span></div></div></td>
      <td>${formatRs(product.price)}</td>
      <td><div class="qty-control"><button class="qty-btn" data-action="minus" data-id="${product.id}">-</button><strong>${qty}</strong><button class="qty-btn" data-action="plus" data-id="${product.id}">+</button></div></td>
      <td><strong>${formatRs(total)}</strong></td>
      <td><button class="qty-btn remove-btn" data-action="remove" data-id="${product.id}"><i class="fa fa-trash"></i></button></td>
    </tr>`;
  }).join('');

  const shipping = subtotal > 200000 ? 0 : 1500;
  const grand = subtotal + shipping;
  summary.innerHTML = `<h3>Cart Total</h3>
    <div class="summary-line"><span>Subtotal</span><strong>${formatRs(subtotal)}</strong></div>
    <div class="summary-line"><span>Shipping</span><strong>${shipping ? formatRs(shipping) : 'Free'}</strong></div>
    <div class="summary-grand"><span>Cart Total</span><strong>${formatRs(grand)}</strong></div>
    <a class="primary-btn cart-checkout-btn" href="checkout.php"><i class="fa-solid fa-credit-card"></i> Proceed to Checkout</a>`;
}

function renderCheckoutSummary() {
  const box = document.querySelector('#checkoutSummary');
  if (!box) return;

  const cart = syncCheckoutCartData();
  const submit = document.querySelector('#checkoutForm button[type="submit"]');

  if (!cart.length) {
    box.innerHTML = '<p>Your cart is empty.</p><a class="primary-btn" href="shop.php">Back to Shop</a>';
    if (submit) submit.disabled = true;
    return;
  }

  let subtotal = 0;
  const lines = cart.map(item => {
    const product = products.find(p => Number(p.id) === Number(item.id));
    if (!product) return '';
    const qty = Number(item.qty);
    const total = Number(product.price) * qty;
    subtotal += total;
    return `<div class="checkout-line"><span>${product.name} × ${qty}</span><strong>${formatRs(total)}</strong></div>`;
  }).join('');

  if (subtotal <= 0) {
    box.innerHTML = '<p>Your cart products could not be found. Please add products again from Shop.</p><a class="primary-btn" href="shop.php">Back to Shop</a>';
    if (submit) submit.disabled = true;
    return;
  }

  if (submit) submit.disabled = false;
  const shipping = subtotal > 200000 ? 0 : 1500;
  const tax = Math.round(subtotal * 0.13);
  const grand = subtotal + shipping + tax;
  box.innerHTML = `${lines}<hr>
    <div class="checkout-line"><span>Subtotal</span><strong>${formatRs(subtotal)}</strong></div>
    <div class="checkout-line"><span>Shipping</span><strong>${shipping ? formatRs(shipping) : 'Free'}</strong></div>
    <div class="checkout-line"><span>VAT</span><strong>${formatRs(tax)}</strong></div>
    <div class="checkout-total"><span>Grand Total</span><strong>${formatRs(grand)}</strong></div>`;
}

function changeCart(id, action) {
  let cart = getCart();
  const item = cart.find(x => Number(x.id) === Number(id));
  if (!item) return;
  if (action === 'plus') item.qty = Number(item.qty) + 1;
  if (action === 'minus') item.qty = Number(item.qty) - 1;
  if (action === 'remove' || Number(item.qty) <= 0) cart = cart.filter(x => Number(x.id) !== Number(id));
  setCart(cart);
  renderCart();
  renderCheckoutSummary();
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
    if (form.id === 'checkoutForm') { localStorage.removeItem('solarCart'); document.cookie='solarCart=; path=/; max-age=0'; updateCartCount(); }
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

document.addEventListener('submit', e => {
  const form = e.target.closest('#checkoutForm');
  if (form) {
    const cart = syncCheckoutCartData();
    if (!cart.length) {
      e.preventDefault();
      showToast('Your cart is empty. Please add products before checkout.');
    }
  }
});

function initProfileLivebar() {
  const toggles = document.querySelectorAll('.profile-toggle');
  const desktopPanel = document.querySelector('.profile-menu-wrap .profile-livebar');
  const mobilePanel = document.querySelector('.mobile-profile-panel');
  toggles.forEach(toggle => {
    toggle.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      const panel = toggle.classList.contains('mobile-profile') ? mobilePanel : desktopPanel;
      if (!panel) return;
      const isOpen = panel.classList.toggle('active');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      if (toggle.classList.contains('mobile-profile') && desktopPanel) desktopPanel.classList.remove('active');
      if (!toggle.classList.contains('mobile-profile') && mobilePanel) mobilePanel.classList.remove('active');
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
  syncCheckoutCartData();
  updateCartCount();
  initShop();
  renderCart();
  renderCheckoutSummary();
  renderAdmin();
  initForms();
  initProfileLivebar();
});
