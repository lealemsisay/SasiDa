<?php
/* ═══════════════════════════════════════════════
   SASIDA — index.php
   Landing page
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';
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

    <!-- ====== ABOUT ====== -->
    <section class="about section" id="about">
      <div class="container about-grid">
        <div class="about-visual reveal">
          <div class="about-img-main"></div>
          <div class="about-img-accent"></div>
          <div class="about-badge">
            <span class="about-badge-num" id="aboutRating">4.9</span>
            <span class="about-badge-txt">★ Rating</span>
          </div>
        </div>
        <div class="about-content reveal">
          <span class="section-tag" id="aboutTag">Our Story</span>
          <h2 class="section-title" id="aboutTitle">Elevating Everyday<br /><em>Lifestyle</em></h2>
          <p id="aboutDesc1">Founded in 2025, SASIDA was born from a passion for curating the finest products across beauty, fashion, and wellness. We believe that everyone deserves access to premium quality at fair prices.</p>
          <p id="aboutDesc2">Our team travels the world to bring you the best – from artisan perfumes to sustainable fashion. Every product is handpicked to ensure it meets our high standards.</p>
          <a href="#contact" class="btn btn-primary">Get In Touch</a>
        </div>
      </div>
    </section>

    <!-- ====== CONTACT / NEWSLETTER ====== -->
    <section class="newsletter section" id="contact">
      <div class="container newsletter-wrap glass">
        <div class="newsletter-content">
          <span class="section-tag">Get in Touch</span>
          <h2>Contact Us</h2>

          <div class="contact-info-grid">
            <div>
              <strong>Phone</strong>
              <span id="contactPhone" data-contact="phone">+251 911 234 567</span>
            </div>
            <div>
              <strong>Email</strong>
              <span id="contactEmail" data-contact="email">info@sasida.com</span>
            </div>
            <div>
              <strong>WhatsApp</strong>
              <span id="contactWhatsApp" data-contact="whatsapp">+251 911 234 567</span>
            </div>
            <div>
              <strong>Instagram</strong>
              <a href="#" id="contactInstagram" data-contact="instagram">@sasida_shop</a>
            </div>
            <div>
              <strong>TikTok</strong>
              <a href="#" id="contactTikTok" data-contact="tiktok">@sasida_shop</a>
            </div>
            <div>
              <strong>Address</strong>
              <span id="contactAddress" data-contact="address">Addis Ababa, Ethiopia</span>
            </div>
            <div class="grid-full">
              <strong>Business Hours</strong>
              <span id="contactHours" data-contact="hours">Mon–Sat 9am – 9pm</span>
            </div>
          </div>

          <p>Subscribe to receive exclusive offers, new arrivals, and style inspiration.</p>
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
