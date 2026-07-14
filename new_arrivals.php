<?php
/* ═══════════════════════════════════════════════
   SASIDA — new_arrivals.php
   Products marked as New Arrival
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';
include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== BREADCRUMB ====== -->
    <div class="breadcrumb" style="padding:100px 0 10px;background:var(--bg-alt);">
      <div class="container">
        <p style="font-size:0.85rem;color:var(--text-muted);margin:0;">
          <a href="index.php" style="color:var(--gold);text-decoration:none;">Home</a> /
          <span style="color:var(--text);">New Arrivals</span>
        </p>
      </div>
    </div>

    <!-- ====== PAGE HEADER ====== -->
    <section class="page-header" style="padding:20px 0 40px;background:var(--bg-alt);">
      <div class="container">
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:3rem;margin-bottom:8px;">New Arrivals</h1>
        <p style="color:var(--text-muted);">Fresh styles just landed — discover the latest additions to our collection.</p>
      </div>
    </section>

    <!-- ====== NEW ARRIVALS GRID ====== -->
    <section class="products section" style="padding-top:0;background:var(--bg);">
      <div class="container">
        <div class="shop-results" style="font-size:0.85rem;color:var(--text-muted);margin-bottom:24px;">
          <span id="newArrivalsCount">0</span> new arrivals
        </div>
        <div class="products-grid" id="newArrivalsGrid">
          <!-- Rendered by new_arrivals.js -->
        </div>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
