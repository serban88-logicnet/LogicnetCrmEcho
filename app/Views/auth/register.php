<div class="d-flex align-items-center mb-5">
    <a href="index.php?route=login" class="back-button me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="login-title mb-0"><?php echo __('register_title'); ?></h1>
</div>


<?php
// Display any registration errors or success messages
if (has_flash('error')) {
    echo '<div class="alert alert-danger" role="alert">' . get_flash('error') . '</div>';
}
if (has_flash('success')) {
    echo '<div class="alert alert-success" role="alert">' . get_flash('success') . '</div>';
}
?>

<form method="post" action="index.php?route=register">
    <!-- Email Input -->
    <div class="mb-3">
        <label for="email" class="form-label"><?php echo __('email_label'); ?></label>
        <input type="email" name="email" id="email" class="form-control" placeholder="nume@example.ro" required>
    </div>

    <!-- CUI Input -->
    <div class="mb-4">
        <label for="cui" class="form-label"><?php echo __('cui_label'); ?></label>
        <input type="text" name="cui" id="cui" class="form-control" placeholder="RO12345678" required>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100 mt-3"><?php echo __('agree_and_continue_button'); ?></button>
</form>
