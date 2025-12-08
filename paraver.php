<?php

include('conect.php'); 




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
  <title>Document</title>
</head>
<body>
                  <div class="col-lg-12">
                      <label for="banco_visual_input" class="lb_modal">BANCO</label>
                      <br>
                  
                      <div class="custom-select-container">
                          <input 
                              type="text" 
                              id="banco_visual_input"  class="form-control dropdown_front" 
                              placeholder="Seleccione o busque el banco..."
                              autocomplete="off"
                          >
                          <ul id="banco_custom_list" class="list-group" style="display: none;">
                          </ul>
                      </div>
                  
                      <select id="banco_id" name="banco_id" required style="display: none;"> 
                          <option value="" selected disabled>Seleccione EL BANCO</option> 
                          <?php
                          // Definición de la consulta SQL
                          $sql = "SELECT id, Concat(codigo, ' ', nombre) as Banco FROM bancos ORDER BY nombre";

                              $result = $mysqli->query($sql);
                          
                              if ($result && $result->num_rows > 0) {
                                  // Si hay resultados, genera las opciones
                                  while($row = $result->fetch_assoc()) {
                                      echo '<option value="' . $row["id"] . '" data-nombre="' . htmlspecialchars($row["Banco"]) . '">' . htmlspecialchars($row["Banco"]) . '</option>';
                                  }
                              } else {
                                  // Mensaje si no hay datos o la consulta falló
                                  echo '<option value="" class="text-dropdown">(No hay bancos disponibles)</option>';
                              }
                          ?>
                      </select>
                    </div> 
    <script src="login_regis.js"></script>
    <script src="dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>