<div class="login-logo">
    <i class="bi bi-box-seam-fill me-2" style="font-size: 1.5rem;"></i>
    CRM
</div>

<?php
if (has_flash('error')) {
    echo '<div class="alert alert-danger" role="alert">' . get_flash('error') . '</div>';
}
if (has_flash('success')) {
    echo '<div class="alert alert-success" role="alert">' . get_flash('success') . '</div>';
}
?>

<form method="post" action="index.php?route=login">
    <!-- Email Input -->
    <div class="mb-3">
        <label for="email" class="form-label"><?php echo __('email_label'); ?></label>
        <input type="email" name="email" id="email" class="form-control" placeholder="nume@example.ro" required>
    </div>

    <!-- Password Input -->
    <div class="mb-4">
        <label for="password" class="form-label"><?php echo __('password_label'); ?></label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" class="form-control" placeholder="<?php echo __('password_label'); ?>" required>
            <span class="password-toggle" onclick="togglePasswordVisibility()">
                <i id="password-toggle-icon" class="bi bi-eye-slash"></i>
            </span>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary mt-3"><?php echo __('continue_button'); ?></button>
    
    <!-- TWEAK: Forgot Password link moved here -->
    <div class="text-center mt-3">
        <a href="#" class="form-text text-decoration-none" style="color: #1D51FE; font-size: 0.9rem; font-weight: 500;"><?php echo __('forgot_password'); ?></a>
    </div>

    <!-- "OR" Separator -->
    <div class="divider"><?php echo __('or_separator'); ?></div>

    <!-- TWEAK: Social login buttons now use colored image logos -->
    <div class="d-grid gap-3">
        <a href="#" class="btn btn-social">
            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" width="20" height="20"/>
            <?php echo __('continue_with_google'); ?>
        </a>
        <a href="#" class="btn btn-social">
           <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" alt="Microsoft" width="20" height="20"/>
            <?php echo __('continue_with_microsoft'); ?>
        </a>
    </div>

    <!-- Registration Link -->
    <p class="text-center mt-4" style="font-size: 0.9rem;">
        <?php echo __('no_account_yet'); ?> 
        <a href="index.php?route=register" style="color: #1D51FE; font-weight: 600; text-decoration: none;"><?php echo __('register_now'); ?></a>
    </p>
</form>

<script>
// Script to toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('password-toggle-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}
</script>
