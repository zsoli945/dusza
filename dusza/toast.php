<?php
function displayToast($message) {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_show'] = true;
}
?>
<?php if (isset($_SESSION['toast_show']) && $_SESSION['toast_show'] == true): ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .toast-container {
      position: fixed;
      top: 10px;
      right: 10px;
      z-index: 9999;
    }
    .toast-body {
      position: relative;
    }
    .progress-bar {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 4px;
      width: 0;
      background-color: #00ff2a;
      transition: width 5s linear;
    }
  </style>
</head>
<body>
  <div class="toast-container">
    <div class="toast" data-autohide="false" id="myToast">
      <div class="toast-header">
        <strong class="mr-auto text-primary">Értesítés</strong>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
      </div>
      <div class="toast-body">
        <?= $_SESSION['toast_message'] ?>
        <div class="progress-bar" id="progressBar"></div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      if ($('#myToast').length) {
        $('#myToast').toast('show');
        $('#progressBar').css('width', '100%');
        setTimeout(function() {
          $('#myToast').toast('hide');
        }, 5000);
      }
    });
  </script>
</body>
</html>
<?php
  unset($_SESSION['toast_message']);
  unset($_SESSION['toast_show']);
?>
<?php endif; ?>
