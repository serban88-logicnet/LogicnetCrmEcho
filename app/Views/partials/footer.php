<?php
$route = $_GET['route'] ?? '';
if ($route !== 'login'):
?>
</div> <!-- End container -->
<?php endif; ?>
  <!-- Bootstrap 5 JS Bundle with Popper from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- jQuery (for ui.js) -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <!-- General UI scripts -->
  <script src="js/ui.js"></script>

    <?php
    $route = $_GET['route'] ?? '';
    $action = $_GET['action'] ?? '';
    ?>

    <?php if ($route === 'entity' && in_array($action, ['form', 'edit'])): ?>
        <!-- ✨ FIX: Load the new, centralized script for relationship forms -->
        <script src="js/entity-form.js"></script>
    <?php endif; ?>

</body>
</html>
