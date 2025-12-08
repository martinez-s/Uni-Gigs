<?php

include('conect.php'); 

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>'; 

$id_usuario_logueado = 2; // ID del usuario. Debería venir de una variable de sesión real.
$payment_method_errors = [];

// --- Mapeo de IDs de Campos (CRUCIAL para  la tabla 'valores') ---
// Estos IDs deben coincidir con los IDs que generaste en la tabla 'campos'.
$campos_ids = [
    // PAGO MÓVIL (id_metodo = 1)
    'pm_documento' => 1, 
    'pm_telefono' => 2,   
    'pm_banco' => 3,      
    // TRANSFERENCIA (id_metodo = 2)
    'tr_documento' => 4,  
    'tr_nro_cuenta' => 5, 
    'tr_banco' => 6,      
    // BINANCE (id_metodo = 3)
    'bi_correo' => 7,     
    // PAYPAL (id_metodo = 4)
    'pp_correo' => 8      
];

/**
 * Inserta un nuevo método de pago de usuario y sus valores de campos asociados, 
 * utilizando declaraciones preparadas. Es seguro contra inyección SQL.
 * Asume que el array $fields contiene todos los datos a insertar (previamente validados como completos).
 * * NOTA CRÍTICA: Se inserta la FK `id_usuario_metodo_pago` en la tabla `valores`.
 * @return bool|string Retorna true si tiene éxito, o un string con el mensaje de error.
 */
function insert_pm_data($mysqli, $id_usuario, $metodo_id, $fields, $campos_ids) {
    
    // Si no hay datos, se asume que la validación externa ya determinó que no se debe insertar.
    if (empty($fields)) {
        return true; 
    }
    
    $mysqli->begin_transaction(); // Iniciar transacción para asegurar atomicidad

    try {
        // 1. Inserción SEGURA en usuario_metodos_pago
        $sql_ump = "INSERT INTO usuario_metodos_pago (id_usuario, id_metodo) VALUES (?, ?)";
        $stmt_ump = $mysqli->prepare($sql_ump);
        if ($stmt_ump === false) throw new Exception("Error preparando UMP: " . $mysqli->error);
        
        $stmt_ump->bind_param("ii", $id_usuario, $metodo_id);
        if (!$stmt_ump->execute()) throw new Exception("Error ejecutando UMP: " . $stmt_ump->error);
        $id_usuario_metodo_pago = $mysqli->insert_id; // Obtenemos la FK para la tabla 'valores'
        $stmt_ump->close();
        
        // 2. Inserción SEGURA en valores
        // CRÍTICO: Incluyendo id_usuario_metodo_pago en la consulta y en bind_param!
        $sql_valores = "INSERT INTO valores (valor, id_campo) VALUES (?, ?)";
        $stmt_val = $mysqli->prepare($sql_valores);
        if ($stmt_val === false) throw new Exception("Error preparando VALORES: " . $mysqli->error);
        
        foreach ($fields as $campo_key => $valor) {
            $id_campo = $campos_ids[$campo_key] ?? null;
            
            if ($id_campo === null) throw new Exception("ID de campo '$campo_key' no encontrado en el mapa.");
            
            $valor_to_store = (string)$valor;
            
            // Protección contra SQL Injection con bind_param (sii: string, int, int)
            $stmt_val->bind_param("si", $valor_to_store, $id_campo);
            if (!$stmt_val->execute()) throw new Exception("Error ejecutando VALORES para ID:$id_campo. Error: " . $stmt_val->error);
        }
        $stmt_val->close();

        $mysqli->commit(); // Confirmar la transacción
        return true;
        
    } catch (Exception $e) {
        $mysqli->rollback(); // Revertir en caso de error
        // Manejo de error si ya existe el método, pero no detiene otros
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            return "Ya existe un registro para este método (Duplicado).";
        }
        return "Error: " . $e->getMessage();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Extracción de variables de Métodos de Pago ---
    // PAGO MÓVIL (id_metodo = 1)
    $pm_documento = trim($_POST['documento_ident'] ?? '');
    $pm_telefono = trim($_POST['telefono'] ?? '');
    $pm_banco_id = (int)($_POST['banco_id'] ?? 0); 

    // TRANSFERENCIA (id_metodo = 2)
    $tr_documento = trim($_POST['documento_identidad'] ?? '');
    $tr_nro_cuenta = trim($_POST['nro_cuenta'] ?? '');
    $tr_banco_id = (int)($_POST['banco2_id'] ?? 0); 

    // BINANCE (id_metodo = 3)
    $bi_correo = trim($_POST['correo_binance'] ?? ''); 
        
    // PAYPAL (id_metodo = 4)
    $pp_correo = trim($_POST['correo_paypal'] ?? ''); 
    
    // ----------------------------------------------------------------------
    // VALIDACIÓN OBLIGATORIA: PAGO MÓVIL (id_metodo = 1)
    // ----------------------------------------------------------------------
    $is_pm_complete = !empty($pm_documento) && !empty($pm_telefono) && $pm_banco_id > 0;
    
    if (!$is_pm_complete) {
        $error_msg = "El método de pago **OBLIGATORIO** (Pago Móvil) debe ser llenado completamente para registrar los métodos de pago.";
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', '{$error_msg}', 'error');
            });
        </script>";
        
    } else {
        
        // --- INSERCIÓN Y VALIDACIÓN DE MÉTODOS DE PAGO ---
        
        // 1. PAGO MÓVIL (id_metodo = 1) - Ya validado como obligatorio
        $pm_fields = [
            'pm_documento' => $pm_documento,
            'pm_telefono' => $pm_telefono,
            'pm_banco' => $pm_banco_id,
        ];
        $result = insert_pm_data($mysqli, $id_usuario_logueado, 1, $pm_fields, $campos_ids);
        if ($result !== true) $payment_method_errors[] = "PAGO MÓVIL - {$result}";

        // 2. TRANSFERENCIA (id_metodo = 2) - OPCIONAL - Lógica 'ALL or NOTHING'
        $is_tr_complete = !empty($tr_documento) && !empty($tr_nro_cuenta) && $tr_banco_id > 0;
        // Verifica si *al menos uno* de los campos fue llenado (llenado parcial)
        $is_tr_partially_filled = !empty($tr_documento) || !empty($tr_nro_cuenta) || $tr_banco_id > 0;
        
        if ($is_tr_complete) {
            $tr_fields = [
                'tr_documento' => $tr_documento,
                'tr_nro_cuenta' => $tr_nro_cuenta,
                'tr_banco' => $tr_banco_id,
            ];
            $result = insert_pm_data($mysqli, $id_usuario_logueado, 2, $tr_fields, $campos_ids);
            if ($result !== true) $payment_method_errors[] = "TRANSFERENCIA - {$result}";
        } elseif ($is_tr_partially_filled) {
            // Si no está completo, pero al menos un campo tiene valor (llenó a medias)
            $payment_method_errors[] = "TRANSFERENCIA - Error: Debe llenar **todos** los campos (Documento, Nro. Cuenta y Banco) para registrar este método.";
        } // Si no está ni completo ni parcialmente lleno, se ignora (completamente vacío)
        
        // 3. BINANCE (id_metodo = 3) - OPCIONAL - Lógica 'ALL or NOTHING' (1 campo)
        $is_bi_complete = !empty($bi_correo);
        if ($is_bi_complete) {
            $bi_fields = [
                'bi_correo' => $bi_correo,
            ];
            $result = insert_pm_data($mysqli, $id_usuario_logueado, 3, $bi_fields, $campos_ids);
            if ($result !== true) $payment_method_errors[] = "BINANCE - {$result}";
        }
        
        // 4. PAYPAL (id_metodo = 4) - OPCIONAL - Lógica 'ALL or NOTHING' (1 campo)
        $is_pp_complete = !empty($pp_correo);
        if ($is_pp_complete) {
            $pp_fields = [
                'pp_correo' => $pp_correo,
            ];
            $result = insert_pm_data($mysqli, $id_usuario_logueado, 4, $pp_fields, $campos_ids);
            if ($result !== true) $payment_method_errors[] = "PAYPAL - {$result}";
        }
        
        // ----------------------------------------------------------------------
        // RESULTADOS FINALES
        // ----------------------------------------------------------------------

        $success_text = 'Método de Pago Móvil (Obligatorio) registrado correctamente.';
        
        if (!empty($payment_method_errors)) {
            // Éxito parcial (el obligatorio se registró, pero otros fallaron/se llenaron incompletos)
            $error_text = $success_text . ' Sin embargo, ocurrieron errores al intentar registrar otros métodos: \\n' . implode(" \\n", $payment_method_errors);
            
             echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Éxito con Advertencia',
                                text: '{$error_text}',
                                icon: 'warning',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.href = 'modal_pagos.php'; 
                            });
                        });
                    </script>";
        } else {
            // Éxito total
            $success_text .= ' Los métodos opcionales llenados también se registraron con éxito.';
            echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: '{$success_text}',
                                icon: 'success',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.href = 'modal_pagos.php'; 
                            });
                        });
                    </script>";
        }
    }
}
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
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConTabs">
  Abrir Modal con Pestañas
</button>
<form action="modal_pagos.php" method="POST" enctype="multipart/form-data">
<div class="modal fade" id="modalConTabs" tabindex="-1" aria-labelledby="modalConTabsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="miTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-uno-tab" data-bs-toggle="tab" data-bs-target="#tab-uno" type="button" role="tab" aria-controls="tab-uno" aria-selected="true">OBLIGATORIO</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-dos-tab" data-bs-toggle="tab" data-bs-target="#tab-dos" type="button" role="tab" aria-controls="tab-dos" aria-selected="false">OPCIONAL</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tres-tab" data-bs-toggle="tab" data-bs-target="#tab-tres" type="button" role="tab" aria-controls="tab-tres" aria-selected="false">OPCIONAL</button>
          </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-cuatro-tab" data-bs-toggle="tab" data-bs-target="#tab-cuatro" type="button" role="tab" aria-controls="tab-cuatro" aria-selected="false">OPCIONAL</button>
          </li>
        </ul>
        <div class="tab-content" id="miTabContent">
          <div class="tab-pane fade show active" id="tab-uno" role="tabpanel" aria-labelledby="tab-uno-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">PAGO MÓVIL</h2>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <label for="documento_ident" class="lb_modal">DOCUMENTO DE IDENTIFICACIÓN</label><br>
                        <input type="text" name="documento_ident" class="form-control inputs">
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <label for="telefono" class="lb_modal">TELÉFONO</label><br>
                        <input type="text" name="telefono" class="form-control inputs">
                    </div>
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
                  
                      <select id="banco_id" name="banco_id" style="display: none;"> 
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
                </div>
              </div>
          </div>
          <div class="tab-pane fade" id="tab-dos" role="tabpanel" aria-labelledby="tab-dos-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">TRANSFERENCIA BANCARIA</h2>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <label for="documento_identidad" class="lb_modal">DOCUMENTO DE IDENTIFICACIÓN</label><br>
                        <input type="text" name="documento_identidad" class="form-control inputs">
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <label for="nro_cuenta" class="lb_modal">NUMERO DE CUENTA</label><br>
                        <input type="text" name="nro_cuenta" class="form-control inputs">
                    </div>
                  <div class="col-lg-12">
                      <label for="banco2_visual_input" class="lb_modal">BANCO</label>
                      <br>
                  
                      <div class="custom-select-container">
                          <input 
                              type="text" 
                              id="banco2_visual_input"  class="form-control dropdown_front" 
                              placeholder="Seleccione o busque el banco..."
                              autocomplete="off"
                          >
                          <ul id="banco2_custom_list" class="list-group" style="display: none;">
                          </ul>
                      </div>
                  
                      <select id="banco2_id" name="banco2_id"  style="display: none;"> 
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
                </div>
              </div>            
          </div>
          <div class="tab-pane fade" id="tab-tres" role="tabpanel" aria-labelledby="tab-tres-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">BINANCE</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <label for="correo_binance" class="lb_modal">CORREO ASOCIADO</label><br>
                        <input type="text" name="correo_binance" class="form-control inputs">
                    </div>
                </div>
              </div>            
          </div>
          <div class="tab-pane fade" id="tab-cuatro" role="tabpanel" aria-labelledby="tab-cuatro-tab">
              <div class="container conte_pago">
                <h1 class="Titulo titu_modal">REGISTRA TU MÉTODO DE PAGO</h1>
                <h2 class="lb_subtitulo text-center">PAYPAL</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <label for="correo_paypal" class="lb_modal">CORREO ASOCIADO</label><br>
                        <input type="text" name="correo_paypal" class="form-control inputs">
                    </div>
                </div>
              </div>            
          </div>
          
        </div>
        
        
      </div>
      <div class="modal-footer justify-content-center btn-regis">
        <button type="submit" class="btn btn-secondary"">REGISTRAR</button>
      </div>
    </div>
  </div>
</div>
</form>


    <script src="login_regis.js"></script>
    <script src="dropdown.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>