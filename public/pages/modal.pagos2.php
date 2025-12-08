<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$path_to_connect = __DIR__ . '/../../conect.php';
    
if (file_exists($path_to_connect)) {
    require_once $path_to_connect;
    $mysqli = isset($mysqli) ? $mysqli : (isset($conn) ? $conn : null);
}



if (!$mysqli || !($mysqli instanceof mysqli)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        error_log('Error: conexión DB no encontrada en modal_pagos.php durante POST.');
        header('Location: perfil.php?error=db_connect');
        exit;
    }
}

$id_usuario_logueado = $_SESSION['id_usuario'] ?? 0;


$campos_ids = [
    'pm_documento' => 1, 'pm_telefono' => 2, 'pm_banco' => 3, 
    'tr_documento' => 4, 'tr_nro_cuenta' => 5, 'tr_banco' => 6, 
    'bi_correo' => 7, 'pp_correo' => 8 
];



/**
 * 
 * 
 * @return bool|string
 */
function insert_pm_data($mysqli, $id_usuario, $metodo_id, $fields, $campos_ids) {
    
    if (empty($fields)) return true;
    
    $mysqli->begin_transaction(); 
    
    try {

        $sql_ump = "INSERT INTO usuario_metodos_pago (id_usuario, id_metodo) VALUES (?, ?)";
        $stmt_ump = $mysqli->prepare($sql_ump);
        if ($stmt_ump === false) throw new Exception("Error preparando UMP: " . $mysqli->error);
        
        $stmt_ump->bind_param("ii", $id_usuario, $metodo_id);
        
        
        if (!$stmt_ump->execute()) {

             if ($mysqli->errno == 1062) { 
                  throw new Exception("Ya existe un registro para este método.");
             }
             throw new Exception("Error ejecutando UMP: " . $stmt_ump->error);
        }
        $id_usuario_metodo_pago = $mysqli->insert_id; 
        $stmt_ump->close();
        

        $sql_valores = "INSERT INTO valores (valor, id_campo) VALUES (?, ?)";
        $stmt_val = $mysqli->prepare($sql_valores);
        if ($stmt_val === false) throw new Exception("Error preparando VALORES: " . $mysqli->error);
        
        foreach ($fields as $campo_key => $valor) {
            $id_campo = $campos_ids[$campo_key] ?? null;
            if ($id_campo === null) throw new Exception("ID de campo '$campo_key' no encontrado.");
            $valor_to_store = (string)$valor;
            
            $stmt_val->bind_param("si", $valor_to_store, $id_campo);
            if (!$stmt_val->execute()) throw new Exception("Error ejecutando VALORES para ID:$id_campo. Error: " . $stmt_val->error);
        }
        $stmt_val->close();

        $mysqli->commit(); 
        return true;
        
    } catch (Exception $e) {
        $mysqli->rollback(); 
        return "Error: " . $e->getMessage();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if ($id_usuario_logueado == 0) {
        header("Location: ../../login.php");
        exit();
    }


    $pm_documento = trim($_POST['documento_ident'] ?? '');
    $pm_telefono = trim($_POST['telefono'] ?? '');
    $pm_banco_id = (int)($_POST['banco_id'] ?? 0); 
    $is_pm_complete = !empty($pm_documento) && !empty($pm_telefono) && $pm_banco_id > 0;
    
    $payment_method_errors = [];


    if (!$is_pm_complete) {
        $error_msg = urlencode("El método de pago OBLIGATORIO (Pago Móvil) debe ser llenado completamente.");

        header("Location: perfil.php?show_modal=true&pm_status=error&msg={$error_msg}");
        exit();
    } else {
        

        $pm_fields = ['pm_documento' => $pm_documento, 'pm_telefono' => $pm_telefono, 'pm_banco' => $pm_banco_id];
        $result = insert_pm_data($mysqli, $id_usuario_logueado, 1, $pm_fields, $campos_ids);
        if ($result !== true) {
            $payment_method_errors[] = "PAGO MÓVIL - {$result}";
        }


        $tr_documento = trim($_POST['documento_identidad'] ?? '');
        $tr_nro_cuenta = trim($_POST['nro_cuenta'] ?? '');
        $tr_banco_id = (int)($_POST['banco2_id'] ?? 0); 
        $is_tr_complete = !empty($tr_documento) && !empty($tr_nro_cuenta) && $tr_banco_id > 0;
        $is_tr_partially_filled = !empty($tr_documento) || !empty($tr_nro_cuenta) || $tr_banco_id > 0;
        
        if ($is_tr_complete) {
            $tr_fields = ['tr_documento' => $tr_documento, 'tr_nro_cuenta' => $tr_nro_cuenta, 'tr_banco' => $tr_banco_id];
            $result = insert_pm_data($mysqli, $id_usuario_logueado, 2, $tr_fields, $campos_ids);
            if ($result !== true) $payment_method_errors[] = "TRANSFERENCIA - {$result}";
        } elseif ($is_tr_partially_filled) {
            $payment_method_errors[] = "TRANSFERENCIA - Error: Debe llenar **todos** los campos.";
        }
        

        $correo_binance = trim($_POST['correo_binance'] ?? '');
        if (!empty($correo_binance)) {
            $bi_fields = ['bi_correo' => $correo_binance];
            $result = insert_pm_data($mysqli, $id_usuario_logueado, 3, $bi_fields, $campos_ids);
            if ($result !== true) $payment_method_errors[] = "BINANCE - {$result}";
        }
        

        $correo_paypal = trim($_POST['correo_paypal'] ?? '');
        if (!empty($correo_paypal)) {
            $pp_fields = ['pp_correo' => $correo_paypal];
            $result = insert_pm_data($mysqli, $id_usuario_logueado, 4, $pp_fields, $campos_ids);
            if ($result !== true) $payment_method_errors[] = "PAYPAL - {$result}";
        }
        

        $redirect_url = 'perfil.php'; 

        if (!empty($payment_method_errors)) {

            $error_message_combined = "Móvil registrado. Errores al registrar opcionales: " . implode(" | ", $payment_method_errors);
            $error_msg = urlencode($error_message_combined);


            header("Location: {$redirect_url}?pm_status=warning&msg={$error_msg}");
            exit();
        } else {

            header("Location: {$redirect_url}?pm_status=success");
            exit();
        }
    }
}
?>