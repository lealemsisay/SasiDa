<?php
/* ═══════════════════════════════════════════════
   SASIDA — index.php
   Landing page
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';

$pageSettings = get_all_settings();

include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== HERO ====== -->
    <section class="hero" id="home">
      <div class="hero-bg">
        <div class="hero-gradient"></div>
        <div class="hero-overlay"></div>
        <div class="hero-grain"></div>
      </div>
      <div class="hero-content container">
        <p class="hero-eyebrow reveal">Since 2025 · Premium Lifestyle</p>
        <h1 class="hero-heading reveal">Discover Your<br /><em>Signature Style</em></h1>
        <p class="hero-sub reveal">From cosmetics to fashion, find everything you need<br />to elevate your everyday.</p>
        <div class="hero-cta reveal">
          <a href="shop.php" class="btn btn-primary">Shop Now</a>
          <a href="#categories" class="btn btn-ghost">Explore Categories</a>
        </div>
      </div>
      <div class="scroll-hint">
        <div class="scroll-line"></div>
        <span>scroll</span>
      </div>
    </section>

    <!-- ====== CATEGORIES ====== -->
    <section class="categories section" id="categories">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">Shop by Category</span>
          <h2 class="section-title">Featured Categories</h2>
          <p class="section-sub">Explore our curated collections.</p>
        </div>
        <div class="categories-grid">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

    <!-- ====== FEATURED PRODUCTS ====== -->
    <section class="products section" id="shop">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">Bestsellers</span>
          <h2 class="section-title">Featured Products</h2>
          <p class="section-sub">Our most loved items, just for you.</p>
        </div>
        <div class="products-grid" id="featuredProducts">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

    <!-- ====== BEST SELLERS ====== -->
    <section class="products section" id="bestsellers" style="background:var(--bg);">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">Top Rated</span>
          <h2 class="section-title">Best Sellers</h2>
          <p class="section-sub">Customer favorites that never disappoint.</p>
        </div>
        <div class="products-grid" id="bestsellerProducts">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

    <!-- ====== NEW ARRIVALS ====== -->
    <section class="products section" id="newarrivals" style="background:var(--bg-alt);">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">Fresh Picks</span>
          <h2 class="section-title">New Arrivals</h2>
          <p class="section-sub">Discover the latest additions to our collection.</p>
        </div>
        <div class="products-grid" id="newArrivalProducts">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

    <!-- ====== PROMOTIONS ====== -->
    <section class="promotions section" id="promotions" style="background:var(--bg); padding-block:80px;">
      <div class="container">
        <div class="promotions-grid">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

    <!-- ====== TESTIMONIALS ====== -->
    <section class="testimonials section" id="testimonials">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">Testimonials</span>
          <h2 class="section-title">What Our Customers Say</h2>
        </div>
        <div class="testimonial-track" id="testimonialTrack">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

    <!-- ====== DYNAMIC ABOUT SECTION ====== -->
    <section class="about section" id="about">
      <div class="container about-grid">
        <div class="about-visual reveal">
          <div class="about-img-main"></div>
          <div class="about-img-accent"></div>
          <div class="about-badge">
            <span class="about-badge-num" id="aboutRating"><?php echo sanitize($pageSettings['about_rating'] ?? '4.9'); ?></span>
            <span class="about-badge-txt">★ Rating</span>
          </div>
        </div>
        <div class="about-content reveal">
          <span class="section-tag" id="aboutTag"><?php echo sanitize($pageSettings['about_tag'] ?? 'Our Story'); ?></span>
          <h2 class="section-title" id="aboutTitle"><?php echo sanitize($pageSettings['about_title'] ?? 'Elevating Everyday Lifestyle'); ?></h2>
          
          <p id="aboutDesc1"><?php echo sanitize($pageSettings['about_desc1'] ?? ''); ?></p>
          <p id="aboutDesc2"><?php echo sanitize($pageSettings['about_desc2'] ?? ''); ?></p>

          <?php if (!empty($pageSettings['about_mission'])): ?>
            <div style="margin-top:16px;padding:12px 16px;background:var(--surface);border-left:3px solid var(--gold);border-radius:4px;">
              <strong style="display:block;color:var(--gold);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Our Mission</strong>
              <p id="aboutMission" style="margin:0;font-size:0.9rem;color:var(--text-muted);"><?php echo sanitize($pageSettings['about_mission']); ?></p>
            </div>
          <?php endif; ?>

          <?php if (!empty($pageSettings['about_vision'])): ?>
            <div style="margin-top:12px;padding:12px 16px;background:var(--surface);border-left:3px solid var(--gold);border-radius:4px;">
              <strong style="display:block;color:var(--gold);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Our Vision</strong>
              <p id="aboutVision" style="margin:0;font-size:0.9rem;color:var(--text-muted);"><?php echo sanitize($pageSettings['about_vision']); ?></p>
            </div>
          <?php endif; ?>

          <div style="margin-top:24px;">
            <a href="#contact" class="btn btn-primary">Get In Touch</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ====== DYNAMIC CONTACT SECTION ====== -->
    <section class="newsletter section" id="contact">
      <div class="container newsletter-wrap glass">
        <div class="newsletter-content">
          <span class="section-tag">Get in Touch</span>
          <h2>Contact Us</h2>

          <div class="contact-info-grid">
            <div>
              <strong>Phone</strong>
              <span id="contactPhone" data-contact="phone"><?php echo sanitize($pageSettings['contact_phone'] ?? ''); ?></span>
            </div>
            <div>
              <strong>Email</strong>
              <span id="contactEmail" data-contact="email"><?php echo sanitize($pageSettings['contact_email'] ?? ''); ?></span>
            </div>
            <div>
              <strong>WhatsApp</strong>
              <span id="contactWhatsApp" data-contact="whatsapp"><?php echo sanitize($pageSettings['contact_whatsapp'] ?? ''); ?></span>
            </div>
            <div>
              <strong>Instagram</strong>
              <a href="<?php echo sanitize($pageSettings['contact_instagram_url'] ?: '#'); ?>" id="contactInstagram" data-contact="instagram" target="_blank" rel="noopener"><?php echo sanitize($pageSettings['contact_instagram'] ?? '@sasida_shop'); ?></a>
            </div>
            <div>
              <strong>TikTok</strong>
              <a href="<?php echo sanitize($pageSettings['contact_tiktok_url'] ?: '#'); ?>" id="contactTikTok" data-contact="tiktok" target="_blank" rel="noopener"><?php echo sanitize($pageSettings['contact_tiktok'] ?? '@sasida_shop'); ?></a>
            </div>
            <div>
              <strong>Address</strong>
              <span id="contactAddress" data-contact="address"><?php echo sanitize($pageSettings['contact_address'] ?? ''); ?></span>
            </div>
            <div class="grid-full">
              <strong>Business Hours</strong>
              <span id="contactHours" data-contact="hours"><?php echo sanitize($pageSettings['business_hours'] ?? ''); ?></span>
            </div>
          </div>

          <p style="margin-top:24px;">Subscribe to receive exclusive offers, new arrivals, and style inspiration.</p>
          <form class="newsletter-form" id="newsletterForm" novalidate>
            <input type="email" id="nlEmail" placeholder="your@email.com" required />
            <button type="submit" class="btn btn-primary">Subscribe</button>
          </form>
          <div class="newsletter-success" id="nlSuccess">Thank you for subscribing!</div>
        </div>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
