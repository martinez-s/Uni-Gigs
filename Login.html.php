<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
    <title>LogIn</title>
</head>
<body>
  <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_login">
      INICIAR SESIÓN
    </button>

    <!-- Modal -->
    <div class="modal fade" id="modal_login" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="container conte_log">
              <form action="login.php" method="post">
                <h1>INICIA SESIÓN</h1>
                <hr>
                <?php
                  if(isset($_GET['error'])){
                  ?>
                  <p class="error">
                    <?php
                    echo $_GET['error']
                    ?>
                  </p>
                <?php
                  }
                ?>
                <label for="correo">CORREO</label>
                <br>
                <input type="text" id="correo" name="correo" class="inputs">
                <br>
                <label for="clave">CONTRASEÑA</label>
                <br>
                <input type="text" id="clave" name="clave" class="inputs">
                <a href="#"><p class="texto_log">¿OLVIDASTE LA CONTRASEÑA?</p></a>
                <button type="submit" class="btn_inicio" data-bs-dismiss="modal" >INICIAR</button>
                <a href=""><p>¿YA TIENES CUENTA? INICIA SESIÓN</p></a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script>
        <?php if(isset($_GET['error'])): ?>
      
            var errorModal = new bootstrap.Modal(document.getElementById('modal_login'), {
              keyboard: false // Hereda la configuración del HTML
            });
            errorModal.show();
          
        <?php endif; ?>
    </script>
</body>
</html> 