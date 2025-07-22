<?php
$route = $_GET['route'] ?? '';
if ($route !== 'login'):
?>
</div> <!-- End container -->
<?php endif; ?>
  <!-- Bootstrap 5 JS Bundle with Popper from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  
  <!-- Your JS -->
  <script src="js/ui.js"></script>

    <?php
    $route = $_GET['route'] ?? '';
    $type = $_GET['type'] ?? '';
    $action = $_GET['action'] ?? '';
    ?>

    <?php if ($route === 'entity' && in_array($action, ['form', 'edit'])): ?>
        <script src="js/dynamic-relations.js"></script>
    <?php endif; ?>

</body>
</html>
