/* ═══════════════════════════════════════════════
   SASIDA — data.js
   Shared data for all pages
   ⚠️ PLACEHOLDER DATA — will be replaced by database
═══════════════════════════════════════════════ */

// ─── HERO DATA ──────────────────────────────────
const heroData = {
  eyebrow: 'Since 2025 · Premium Lifestyle',
  title: 'Discover Your<br /><em>Signature Style</em>',
  subtitle: 'From cosmetics to fashion, find everything you need<br />to elevate your everyday.',
  primaryCta: 'Shop Now',
  primaryLink: 'shop.php',
  secondaryCta: 'Explore Categories',
  secondaryLink: 'shop.php'
};

// ─── CATEGORIES DATA ────────────────────────────
const categoriesData = [
  { id: 1, name: 'Cosmetics', icon: '💄', color: '#f7e1d7' },
  { id: 2, name: 'Shoes', icon: '👟', color: '#d9e2e8' },
  { id: 3, name: 'Men\'s Clothing', icon: '👔', color: '#d4d9d1' },
  { id: 4, name: 'Women\'s Clothing', icon: '👗', color: '#f2d7d5' },
  { id: 5, name: 'Kids Clothing', icon: '🧸', color: '#d5e3d6' },
  { id: 6, name: 'Bags', icon: '👜', color: '#e6d9c8' },
  { id: 7, name: 'Perfumes', icon: '🧴', color: '#f0d8d4' },
  { id: 8, name: 'Vitamins', icon: '💊', color: '#d1d9d9' }
];

// ─── PRODUCTS DATA (PLACEHOLDER) ────────────────
const productsData = {
  all: [
    { 
      id: 1, 
      name: 'Sample Product 1', 
      price: 2850, 
      oldPrice: null, 
      image: '📦', 
      badge: null, 
      categoryId: 1, 
      rating: 4.5, 
      reviews: 12,
      description: 'This is a premium sample product. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      images: ['📦', '📦', '📦'],
      variants: ['Size: S, M, L', 'Color: Black, White'],
      inStock: true
    },
    { 
      id: 2, 
      name: 'Sample Product 2', 
      price: 4200, 
      oldPrice: null, 
      image: '📦', 
      badge: null, 
      categoryId: 2, 
      rating: 4.2, 
      reviews: 8,
      description: 'Another great sample product. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
      images: ['📦', '📦'],
      variants: ['Size: 38-44', 'Color: Brown, Black'],
      inStock: true
    },
    { 
      id: 3, 
      name: 'Sample Product 3', 
      price: 3850, 
      oldPrice: 5250, 
      image: '📦', 
      badge: null, 
      categoryId: 3, 
      rating: 4.8, 
      reviews: 20,
      description: 'Premium quality with a classic design. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
      images: ['📦', '📦', '📦', '📦'],
      variants: ['Size: S, M, L, XL', 'Color: Navy, Grey'],
      inStock: true
    },
    { 
      id: 4, 
      name: 'Sample Product 4', 
      price: 7350, 
      oldPrice: null, 
      image: '📦', 
      badge: null, 
      categoryId: 4, 
      rating: 4.0, 
      reviews: 15,
      description: 'Elegant and timeless. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
      images: ['📦', '📦'],
      variants: ['One Size', 'Color: Beige, Black'],
      inStock: true
    },
    { 
      id: 5, 
      name: 'Sample Best Seller 1', 
      price: 1450, 
      oldPrice: null, 
      image: '⭐', 
      badge: 'Best Seller', 
      categoryId: 1, 
      rating: 4.9, 
      reviews: 45,
      description: 'Our most popular product! Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      images: ['⭐', '⭐', '⭐'],
      variants: ['Size: One Size', 'Color: Gold, Silver'],
      inStock: true
    },
    { 
      id: 6, 
      name: 'Sample Best Seller 2', 
      price: 6200, 
      oldPrice: null, 
      image: '⭐', 
      badge: 'Best Seller', 
      categoryId: 2, 
      rating: 4.7, 
      reviews: 32,
      description: 'Highly rated by our customers. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
      images: ['⭐', '⭐'],
      variants: ['Size: 39-45', 'Color: Black, White'],
      inStock: true
    },
    { 
      id: 7, 
      name: 'Sample Best Seller 3', 
      price: 3990, 
      oldPrice: null, 
      image: '⭐', 
      badge: 'Best Seller', 
      categoryId: 5, 
      rating: 4.6, 
      reviews: 28,
      description: 'A customer favorite. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
      images: ['⭐', '⭐', '⭐'],
      variants: ['Size: S, M, L', 'Color: Pink, Blue'],
      inStock: true
    },
    { 
      id: 8, 
      name: 'Sample Best Seller 4', 
      price: 2300, 
      oldPrice: null, 
      image: '⭐', 
      badge: 'Best Seller', 
      categoryId: 6, 
      rating: 4.3, 
      reviews: 19,
      description: 'Great value for money. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
      images: ['⭐', '⭐'],
      variants: ['One Size', 'Color: Red, Black'],
      inStock: true
    },
    { 
      id: 9, 
      name: 'Sample New Arrival 1', 
      price: 1450, 
      oldPrice: null, 
      image: '✨', 
      badge: 'New', 
      categoryId: 7, 
      rating: 4.4, 
      reviews: 10,
      description: 'The latest addition to our collection. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
      images: ['✨', '✨', '✨'],
      variants: ['Size: One Size', 'Color: Clear, Amber'],
      inStock: true
    },
    { 
      id: 10, 
      name: 'Sample New Arrival 2', 
      price: 6200, 
      oldPrice: null, 
      image: '✨', 
      badge: 'New', 
      categoryId: 8, 
      rating: 4.1, 
      reviews: 7,
      description: 'Fresh off the shelf. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
      images: ['✨', '✨'],
      variants: ['Size: 39-44', 'Color: White, Grey'],
      inStock: true
    },
    { 
      id: 11, 
      name: 'Sample New Arrival 3', 
      price: 3990, 
      oldPrice: null, 
      image: '✨', 
      badge: 'New', 
      categoryId: 3, 
      rating: 4.5, 
      reviews: 14,
      description: 'Trendy and stylish. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
      images: ['✨', '✨', '✨'],
      variants: ['Size: S, M, L', 'Color: Black, White'],
      inStock: true
    },
    { 
      id: 12, 
      name: 'Sample New Arrival 4', 
      price: 2300, 
      oldPrice: null, 
      image: '✨', 
      badge: 'New', 
      categoryId: 4, 
      rating: 4.2, 
      reviews: 9,
      description: 'Just arrived! Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
      images: ['✨', '✨'],
      variants: ['One Size', 'Color: Gold, Silver'],
      inStock: true
    }
  ],
  featured: [],
  bestsellers: [],
  newArrivals: []
};

// Populate homepage subsets
productsData.featured = productsData.all.slice(0, 4);
productsData.bestsellers = productsData.all.filter(function(p) { return p.badge === 'Best Seller'; });
productsData.newArrivals = productsData.all.filter(function(p) { return p.badge === 'New'; });

// ─── PROMOTIONS DATA ────────────────────────────
const promotionsData = [
  { id: 1, title: 'Summer Sale', subtitle: 'Up to 40% off selected items', cta: 'Shop Sale', link: 'shop.php', bg: 'linear-gradient(135deg, #1a1a2e, #16213e)' },
  { id: 2, title: 'New Arrivals', subtitle: 'Fresh styles just landed', cta: 'Explore', link: 'shop.php', bg: 'linear-gradient(135deg, #2d1b1b, #3d2b2b)' }
];

// ─── TESTIMONIALS DATA (PLACEHOLDER) ────────────
const testimonialsData = [
  { id: 1, name: 'Amara Chen', initials: 'AC', avatar: 'av1', rating: 5, text: 'SASIDA offers the best selection of premium products. I love the quality and the fast delivery.', location: 'New York, USA' },
  { id: 2, name: 'Lena Kovacs', initials: 'LK', avatar: 'av2', rating: 5, text: 'The cosmetics range is incredible – my skin has never looked better. Highly recommend!', location: 'Budapest, Hungary' },
  { id: 3, name: 'David Okafor', initials: 'DO', avatar: 'av3', rating: 5, text: 'Great customer service and amazing value for money. I keep coming back for more.', location: 'Lagos, Nigeria' }
];

// ─── CONTACT DATA ───────────────────────────────
const contactData = {
  phone: '+251 911 234 567',
  email: 'info@sasida.com',
  whatsapp: '+251 911 234 567',
  instagram: 'https://instagram.com/sasida_shop',
  instagramText: '@sasida_shop',
  tiktok: 'https://tiktok.com/@sasida_shop',
  tiktokText: '@sasida_shop',
  address: 'Addis Ababa, Ethiopia',
  hours: 'Mon–Sat 9am – 9pm'
};

// ─── ABOUT DATA ──────────────────────────────────
const aboutData = {
  tag: 'Our Story',
  title: 'Elevating Everyday<br /><em>Lifestyle</em>',
  desc1: 'Founded in 2025, SASIDA was born from a passion for curating the finest products across beauty, fashion, and wellness. We believe that everyone deserves access to premium quality at fair prices.',
  desc2: 'Our team travels the world to bring you the best – from artisan perfumes to sustainable fashion. Every product is handpicked to ensure it meets our high standards.',
  rating: '4.9'
};

// ─── HELPER FUNCTIONS ────────────────────────────
function getProductById(id) {
  return productsData.all.find(function(p) { return p.id === parseInt(id); });
}

function getProductsByCategory(categoryId) {
  return productsData.all.filter(function(p) { return p.categoryId === parseInt(categoryId); });
}

function getCategoryById(id) {
  return categoriesData.find(function(c) { return c.id === parseInt(id); });
}