<?php
/* ═══════════════════════════════════════════════
   SASIDA — register.php
   Secure customer registration page
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Server-side validation
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms of Service and Privacy Policy.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email address already exists.';
            } else {
                // Insert new user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
                $stmt->execute([$fullName, $email, $passwordHash]);
                
                // Automatically log in the user
                login_user($email, $password);
                
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== REGISTER FORM ====== -->
    <section class="auth-section section">
      <div class="container" style="max-width:480px;">
        <div class="auth-card glass">
          <div style="text-align:center;margin-bottom:32px;">
            <div class="logo-wrapper" id="centerLogoWrapper" style="width:80px;height:80px;margin:0 auto 16px;border:3px solid var(--gold);cursor:pointer;">
              <img src="sasida-logo.png" alt="SASIDA logo" class="logo-img" />
            </div>
            <h2 style="font-size:2rem;margin-top:8px;">Create Account</h2>
            <p style="color:var(--text-muted);font-size:0.9rem;">Join the SASIDA community</p>
          </div>

          <?php if (!empty($error)): ?>
            <div class="form-error-banner" style="background:#e05757;color:#fff;padding:12px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:0.9rem;text-align:center;">
              <?php echo sanitize($error); ?>
            </div>
          <?php endif; ?>

          <form id="registerForm" action="register.php" method="POST">
            <div class="form-group">
              <label for="regFullName">Full Name</label>
              <input type="text" id="regFullName" name="fullName" value="<?php echo sanitize($fullName); ?>" placeholder="John Doe" required autocomplete="name" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);" />
            </div>

            <div class="form-group" style="margin-top:16px;">
              <label for="regEmail">Email Address</label>
              <input type="email" id="regEmail" name="email" value="<?php echo sanitize($email); ?>" placeholder="you@example.com" required autocomplete="email" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);" />
            </div>

            <div class="form-group" style="position:relative;margin-top:16px;">
              <label for="regPassword">Password</label>
              <input type="password" id="regPassword" name="password" placeholder="••••••••" required autocomplete="new-password" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);" />
              <button type="button" id="toggleRegPassword" class="password-toggle" style="position:absolute;right:12px;top:38px;background:none;border:none;color:var(--text);cursor:pointer;font-size:1.2rem;">👁</button>
            </div>

            <div class="form-group" style="position:relative;margin-top:16px;">
              <label for="regConfirmPassword">Confirm Password</label>
              <input type="password" id="regConfirmPassword" name="confirmPassword" placeholder="••••••••" required autocomplete="new-password" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);" />
              <button type="button" id="toggleRegConfirmPassword" class="password-toggle" style="position:absolute;right:12px;top:38px;background:none;border:none;color:var(--text);cursor:pointer;font-size:1.2rem;">👁</button>
            </div>

            <div class="terms-label" style="margin-top:20px;display:flex;align-items:center;gap:8px;font-size:0.9rem;">
              <input type="checkbox" id="regTerms" name="terms" required style="cursor:pointer;" />
              <span>I agree to the <a href="#" style="color:var(--gold);text-decoration:none;">Terms of Service</a> and <a href="#" style="color:var(--gold);text-decoration:none;">Privacy Policy</a></span>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:24px;">Create Account</button>
          </form>

          <div class="divider" style="margin-top:24px;text-align:center;font-size:0.9rem;color:var(--text-muted);">
            <p>Already have an account? <a href="login.php" style="color:var(--gold);text-decoration:none;font-weight:500;">Sign In</a></p>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Interactive Password Toggle script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle register password
      var toggleReg = document.getElementById('toggleRegPassword');
      var regPass = document.getElementById('regPassword');
      if (toggleReg && regPass) {
        toggleReg.addEventListener('click', function() {
          if (regPass.type === 'password') {
            regPass.type = 'text';
            toggleReg.textContent = '🙈';
          } else {
            regPass.type = 'password';
            toggleReg.textContent = '👁';
          }
        });
      }

      // Toggle confirm password
      var toggleConf = document.getElementById('toggleRegConfirmPassword');
      var confPass = document.getElementById('regConfirmPassword');
      if (toggleConf && confPass) {
        toggleConf.addEventListener('click', function() {
          if (confPass.type === 'password') {
            confPass.type = 'text';
            toggleConf.textContent = '🙈';
          } else {
            confPass.type = 'password';
            toggleConf.textContent = '👁';
          }
        });
      }
    });
  </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
