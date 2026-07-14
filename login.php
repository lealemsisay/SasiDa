<?php
/* ═══════════════════════════════════════════════
   SASIDA — login.php
   Secure customer sign in page with full validations
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Server-side validation
    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $db_user = $stmt->fetch();
            
            if (!$db_user) {
                $error = 'Account not found. Please register first.';
            } else {
                // Verify password hash
                if (password_verify($password, $db_user['password_hash'])) {
                    // Populate Session variables securely
                    $_SESSION['user_id'] = $db_user['id'];
                    $_SESSION['user_name'] = $db_user['full_name'];
                    $_SESSION['user_email'] = $db_user['email'];
                    $_SESSION['user_role'] = $db_user['role'];
                    
                    // Generate CSRF token if not set
                    if (!isset($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }

                    // Redirect based on role
                    if ($db_user['role'] === 'admin') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: " . get_safe_return_url('dashboard.php'));
                    }
                    exit;
                } else {
                    $error = 'Incorrect password. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database connection error. Please try again later.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== LOGIN FORM ====== -->
    <section class="auth-section section">
      <div class="container" style="max-width:480px;">
        <div class="auth-card glass">
          <div style="text-align:center;margin-bottom:32px;">
            <!-- Store Logo -->
            <div class="logo-wrapper" id="centerLogoWrapper" style="width:80px;height:80px;margin:0 auto 16px;border:3px solid var(--gold);cursor:pointer;">
              <img src="sasida-logo.png" alt="SASIDA logo" class="logo-img" />
            </div>
            <!-- Welcome Back title -->
            <h2 style="font-size:2rem;margin-top:8px;">Welcome Back</h2>
            <p style="color:var(--text-muted);font-size:0.9rem;">Sign in to your account</p>
          </div>

          <?php if (!empty($error)): ?>
            <div class="form-error-banner" style="background:#e05757;color:#fff;padding:12px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:0.9rem;text-align:center;">
              <?php echo sanitize($error); ?>
            </div>
          <?php endif; ?>

          <form id="loginForm" action="login.php<?php echo isset($_GET['return']) ? '?return=' . urlencode($_GET['return']) : ''; ?>" method="POST" novalidate>
            <!-- Email Address input -->
            <div class="form-group">
              <label for="loginEmail">Email Address</label>
              <input type="email" id="loginEmail" name="email" value="<?php echo sanitize($email); ?>" placeholder="you@example.com" required autocomplete="email" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);" />
            </div>

            <!-- Password input -->
            <div class="form-group" style="position:relative;margin-top:16px;">
              <label for="loginPassword">Password</label>
              <input type="password" id="loginPassword" name="password" placeholder="••••••••" required autocomplete="current-password" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);" />
              <button type="button" id="toggleLoginPassword" class="password-toggle" style="position:absolute;right:12px;top:38px;background:none;border:none;color:var(--text);cursor:pointer;font-size:1.2rem;">👁</button>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="auth-options" style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;font-size:0.85rem;color:var(--text-muted);">
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                <input type="checkbox" id="rememberMe" name="rememberMe" style="cursor:pointer;" /> Remember me
              </label>
              <!-- Forgot Password link -->
              <a href="#" style="color:var(--gold);text-decoration:none;">Forgot Password?</a>
            </div>

            <!-- Login button -->
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:24px;">Sign In</button>
          </form>

          <!-- Register link -->
          <div class="divider" style="margin-top:24px;text-align:center;font-size:0.9rem;color:var(--text-muted);">
            <p>Don't have an account? <a href="register.php" style="color:var(--gold);text-decoration:none;font-weight:500;">Sign Up</a></p>
            <p style="margin-top:12px;font-size:0.8rem;color:var(--text-muted);">Demo customer: customer@sasida.com / customer123</p>
            <p style="margin-top:4px;font-size:0.8rem;color:var(--text-muted);">Demo admin: admin@sasida.com / admin123</p>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Password visibility + client-side validation -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var toggleBtn = document.getElementById('toggleLoginPassword');
      var passInput = document.getElementById('loginPassword');
      if (toggleBtn && passInput) {
        toggleBtn.addEventListener('click', function() {
          if (passInput.type === 'password') {
            passInput.type = 'text';
            toggleBtn.textContent = '🙈';
          } else {
            passInput.type = 'password';
            toggleBtn.textContent = '👁';
          }
        });
      }

      var form = document.getElementById('loginForm');
      var emailInput = document.getElementById('loginEmail');
      if (form && emailInput && passInput) {
        form.addEventListener('submit', function(e) {
          var email = emailInput.value.trim();
          var password = passInput.value;
          var existingBanner = form.querySelector('.client-validation-banner');
          if (existingBanner) existingBanner.remove();

          if (!email || !password) {
            e.preventDefault();
            showClientError(form, 'Both email and password are required.');
            return;
          }
          if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            e.preventDefault();
            showClientError(form, 'Please enter a valid email address.');
          }
        });
      }

      function showClientError(formEl, message) {
        var banner = document.createElement('div');
        banner.className = 'form-error-banner client-validation-banner';
        banner.style.cssText = 'background:#e05757;color:#fff;padding:12px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:0.9rem;text-align:center;';
        banner.textContent = message;
        formEl.insertBefore(banner, formEl.firstChild);
      }
    });
  </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
