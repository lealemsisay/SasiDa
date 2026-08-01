<?php
/* ═══════════════════════════════════════════════
   SASIDA — data.php
   Dynamic Javascript configurations served from MySQL
   ═══════════════════════════════════════════════ */

header('Content-Type: application/javascript');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/includes/auth_helper.php';

$db_products = get_js_products_data();
$db_categories = get_js_categories_data();
?>

<?php
$settings = get_all_settings();
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
  { id: 2, title: 'New Collection', subtitle: 'Fresh styles just landed', cta: 'Explore', link: 'shop.php', bg: 'linear-gradient(135deg, #2d1b1b, #3d2b2b)' }
];

const testimonialsData = [
  { id: 1, name: 'Amara Chen', initials: 'AC', avatar: 'av1', rating: 5, text: 'SASIDA offers the best selection of premium products. I love the quality and the fast delivery.', location: 'Addis Ababa, Ethiopia' },
  { id: 2, name: 'Lena Kovacs', initials: 'LK', avatar: 'av2', rating: 5, text: 'The cosmetics range is incredible – my skin has never looked better. Highly recommend!', location: 'Addis Ababa, Ethiopia' },
  { id: 3, name: 'David Okafor', initials: 'DO', avatar: 'av3', rating: 5, text: 'Great customer service and amazing value for money. I keep coming back for more.', location: 'Addis Ababa, Ethiopia' }
];

const windowSettings = <?php echo json_encode($settings); ?>;

const contactData = {
  phone: <?php echo json_encode($settings['contact_phone']); ?>,
  email: <?php echo json_encode($settings['contact_email']); ?>,
  whatsapp: <?php echo json_encode($settings['contact_whatsapp']); ?>,
  instagram: <?php echo json_encode($settings['contact_instagram_url'] ?: 'https://instagram.com'); ?>,
  instagramText: <?php echo json_encode($settings['contact_instagram']); ?>,
  tiktok: <?php echo json_encode($settings['contact_tiktok_url'] ?: 'https://tiktok.com'); ?>,
  tiktokText: <?php echo json_encode($settings['contact_tiktok']); ?>,
  address: <?php echo json_encode($settings['contact_address']); ?>,
  hours: <?php echo json_encode($settings['business_hours']); ?>
};

const aboutData = {
  tag: <?php echo json_encode($settings['about_tag']); ?>,
  title: <?php echo json_encode($settings['about_title']); ?>,
  desc1: <?php echo json_encode($settings['about_desc1']); ?>,
  desc2: <?php echo json_encode($settings['about_desc2']); ?>,
  story: <?php echo json_encode($settings['about_story']); ?>,
  mission: <?php echo json_encode($settings['about_mission']); ?>,
  vision: <?php echo json_encode($settings['about_vision']); ?>,
  rating: <?php echo json_encode($settings['about_rating']); ?>
};
