<?php
/* ═══════════════════════════════════════════════
   SASIDA — data.php
   Dynamic Javascript configurations served from MySQL
   ═══════════════════════════════════════════════ */

header('Content-Type: application/javascript');

require_once __DIR__ . '/includes/auth_helper.php';

$db_products = get_js_products_data();
$db_categories = get_js_categories_data();
?>

const categoriesData = <?php echo json_encode($db_categories); ?>;
const productsData = {
  all: <?php echo json_encode($db_products); ?>,
  featured: [],
  bestsellers: [],
  newArrivals: []
};

// Populate subset arrays for client script logic
productsData.featured = productsData.all.slice(0, 4);
productsData.bestsellers = productsData.all.filter(function(p) { return p.badge === 'Best Seller'; });
productsData.newArrivals = productsData.all.filter(function(p) { return p.badge === 'New' || p.badge === 'New Arrival'; });

const promotionsData = [
  { id: 1, title: 'Summer Sale', subtitle: 'Up to 40% off selected items', cta: 'Shop Sale', link: 'shop.php', bg: 'linear-gradient(135deg, #1a1a2e, #16213e)' },
  { id: 2, title: 'New Arrivals', subtitle: 'Fresh styles just landed', cta: 'Explore', link: 'shop.php', bg: 'linear-gradient(135deg, #2d1b1b, #3d2b2b)' }
];

const testimonialsData = [
  { id: 1, name: 'Amara Chen', initials: 'AC', avatar: 'av1', rating: 5, text: 'SASIDA offers the best selection of premium products. I love the quality and the fast delivery.', location: 'New York, USA' },
  { id: 2, name: 'Lena Kovacs', initials: 'LK', avatar: 'av2', rating: 5, text: 'The cosmetics range is incredible – my skin has never looked better. Highly recommend!', location: 'Budapest, Hungary' },
  { id: 3, name: 'David Okafor', initials: 'DO', avatar: 'av3', rating: 5, text: 'Great customer service and amazing value for money. I keep coming back for more.', location: 'Lagos, Nigeria' }
];

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

const aboutData = {
  tag: 'Our Story',
  title: 'Elevating Everyday<br /><em>Lifestyle</em>',
  desc1: 'Founded in 2025, SASIDA was born from a passion for curating the finest products across beauty, fashion, and wellness. We believe that everyone deserves access to premium quality at fair prices.',
  desc2: 'Our team travels the world to bring you the best – from artisan perfumes to sustainable fashion. Every product is handpicked to ensure it meets our high standards.',
  rating: '4.9'
};
