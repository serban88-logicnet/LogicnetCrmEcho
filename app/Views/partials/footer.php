<?php
$route = $_GET['route'] ?? '';
if ($route !== 'login' && $route !== 'register'): // Also check for register or other auth pages
?>
        </div> <!-- End main content area from header.php -->
    </main>
</div>
<?php endif; ?>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (required by some of your scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- General UI scripts for the whole site -->
    <script src="/js/ui.js"></script>

    <?php
    // Get route and action for conditional script loading
    $action = $_GET['action'] ?? '';
    ?>

    <?php if ($route === 'entity' && in_array($action, ['form', 'edit'])): ?>
        <!-- Scripts for the standard entity form -->
        <script src="/js/entity-form.js"></script>
    <?php endif; ?>
    
    <?php if ($route === 'entity-layout' && $action === 'editor'): ?>
        <!-- ✅ FIX: Load scripts ONLY for the layout editor page, in the correct order. -->

        <!-- 1. Sortable.js library -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        
        <!-- 2. Your custom layout editor logic (loads AFTER GridStack is ready) -->
        <script src="/js/layout-editor.js"></script>
    <?php endif; ?>

</body>
</html>
