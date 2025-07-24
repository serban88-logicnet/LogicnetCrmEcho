<!DOCTYPE html>
<html lang="en" class="<?php echo ($_GET['route'] ?? 'login') === 'login' ? 'login-page' : ''; ?>">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Logicnet CRM</title>
	<!-- Bootstrap 5 CSS from CDN -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?php
    // Conditionally load the login stylesheet only on the login page
    $route = $_GET['route'] ?? 'login';
    if ($route === 'login'):
    ?>
        <link rel="stylesheet" href="css/login-style.css">
    <?php endif; ?>


    <link rel="stylesheet" href="css/layout-editor.css">
</head>
<body class="<?php echo $route === 'login' ? 'login-page' : ''; ?>">

    <?php
    // --- CONDITIONAL NAVIGATION ---
    // The main navigation will NOT be displayed on the login route.
    if ($route !== 'login'):
    ?>
	<!-- Top Navigation -->
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="container">
			<a class="navbar-brand" href="#">LOGO</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto">
					<?php if (!empty($_SESSION['user'])): ?>
						<?php
						$company_id = $_SESSION['user']['company_id'];
						$entityModel = new \App\Models\EntityDefinitionModel(\App\Database\Database::getInstance()->getConnection());
						$entities = $entityModel->getAllEntities($company_id);
						?>

						<!-- Show all current entities -->
						<?php foreach ($entities as $e): ?>
							<li class="nav-item">
								<a class="nav-link" href="index.php?route=entity&type=<?= htmlspecialchars($e['slug']) ?>&action=list">
									<?= htmlspecialchars($e['name']) ?>
								</a>
							</li>
						<?php endforeach; ?>

						<!-- Link to entity manager -->
						<li class="nav-item">
							<a class="nav-link text-primary fw-bold" href="index.php?route=entitydef&action=list">
								🧩 <?= __('manage_entities_button') ?>
							</a>
						</li>

						<li class="nav-item">
							<a class="nav-link" href="index.php?route=logout"><?= __('logout_button'); ?></a>
						</li>
					<?php else: ?>
						<li class="nav-item">
							<a class="nav-link" href="index.php?route=login"><?= __('login_button'); ?></a>
						</li>
					<?php endif; ?>
				</ul>

			</div>
		</div>
	</nav>
    <?php endif; // End of conditional navigation ?>

	<?php
	// Flash messages for errors/success should be displayed on all pages
	if ($route !== 'login') { // Only show flash messages inside a container on non-login pages
        echo '<div class="container">';
    }

	$flash_types = ['success', 'error', 'warning', 'info'];
	$bootstrap_classes = [
	    'success' => 'alert-success',
	    'error' => 'alert-danger',
	    'warning' => 'alert-warning',
	    'info' => 'alert-info'
	];

	foreach ($flash_types as $type):
	    if (has_flash($type)):
	?>
	    <div class="alert <?php echo $bootstrap_classes[$type]; ?> alert-dismissible fade show m-3" role="alert">
	        <?php echo get_flash($type); ?>
	        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	    </div>
	<?php
	    endif;
	endforeach;

    if ($route !== 'login') {
        echo '</div>';
    }
	?>

    <?php if ($route !== 'login'): ?>
	<div class="container mt-4">
    <?php endif; ?>
