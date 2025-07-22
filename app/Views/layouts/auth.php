<?php
// This is the master layout file for authentication pages (login, register, etc.)
?>
<!DOCTYPE html>
<html lang="en" class="login-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'CRM'; ?></title>
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login-style.css">
</head>
<body class="login-page">

    <div class="login-container">
        <div class="login-image-panel" style="background-image: url('<?php echo $backgroundImageUrl; ?>');">
        </div>

        <div class="login-form-panel">
            <!-- TWEAK: This new wrapper helps position the footer -->
            <div class="login-form-wrapper">
                <div class="login-form-content">
                    <!-- The specific form (login or register) is included here -->
                    <?php require_once $formView; ?>
                </div>

                <!-- Shared footer for auth pages -->
                <div class="auth-footer">
                    <a href="#"><?php echo __('help'); ?></a>
                    <a href="#"><?php echo __('terms_and_conditions'); ?></a>
                    <a href="#"><?php echo __('privacy_and_cookies'); ?></a>
                </div>
                 <p class="auth-footer-notice">
                    <?php echo __('private_browser_notice'); ?>
                    <a href="#"><?php echo __('read_more'); ?></a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
