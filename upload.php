<?php
// === INICIO DE LA CONFIGURACIÓN Y PROCESAMIENTO PHP ===

// --- Configuración de Rutas y Parámetros ---
// IMPORTANTE: Verifica que esta ruta sea correcta para tu instalación de Tesseract.
$tesseract_exe = "C:\\Program Files\\Tesseract-OCR\\tesseract.exe";
$upload_dir = "images/";
$output_file = "out"; 
$output_filename = "out.txt";
// Parámetros optimizados
$parameters = "-l spa --oem 3 --psm 6"; 

// --- Inicialización de Variables de Extracción ---
$nombre_completo_raw = "NO ENCONTRADO";
$nombres = "NO ENCONTRADO";
$apellidos = "NO ENCONTRADO"; 
$ci = "NO ENCONTRADO";
$carrera = "NO ENCONTRADO";
$vencimiento = "NO ENCONTRADO";
$uploaded_path = '';
$ocr_image_path = '';

// --- Lógica Principal: Procesamiento de la Imagen Subida ---
if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK){
    
    $file_name = $_FILES['image']['name'];
    $file_tmp = $_FILES['image']['tmp_name'];
    $uploaded_path = $upload_dir . $file_name;
    $preprocessed_path = $upload_dir . "clean_" . $file_name; 

    // Mover archivo original
    move_uploaded_file($file_tmp, $uploaded_path);
    
    // --- 1. PREPROCESAMIENTO (Requiere Imagick) ---
    $ocr_image_path = $uploaded_path; 
    
    if (class_exists('Imagick')) {
        try {
            $imagick = new Imagick($uploaded_path);
            $imagick->modulateImage(100, 0, 100); // Desaturar
            $imagick->thresholdImage(32768); // Binarización (B/N)
            $imagick->writeImage($preprocessed_path); 
            $ocr_image_path = $preprocessed_path;
        } catch (ImagickException $e) {
             // Si falla Imagick, se usa la imagen original
        }
    }
    
    // --- 2. EJECUTAR TESSERACT ---
    // IMPORTANTE: Verifica la ruta ABSOLUTA de tu proyecto
    $image_path_full = "C:\\laragon\\www\\Uni-gigs\\" . $ocr_image_path; 
    
    $command = '"' . $tesseract_exe . '" "' . $image_path_full . '" ' . $output_file . ' ' . $parameters;
    shell_exec($command);
    
    // --- 3. PARSING DEL TEXTO (Extracción y Separación) ---
    if (file_exists($output_filename)) {
        $raw_text = file_get_contents($output_filename);
        $raw_text_upper = strtoupper($raw_text);
        
        // NORMALIZACIÓN: Reemplaza cualquier salto de línea, tabulador o espacio múltiple con un solo espacio.
        $cleaned_text = preg_replace('/\s+/', ' ', trim($raw_text_upper)); 
        
        $lines = explode("\n", $raw_text_upper);
        $carrera_index = -1;

        // A. Extracción de Cédula, Vencimiento y Carrera (Línea por Línea)
        foreach ($lines as $index => $line) {
            $line = trim($line);
            
            // 🛑 CORRECCIÓN CÉDULA: Ahora incluye 'U' y 'J' como posibilidades para la letra inicial
            if (preg_match('/(C\.I\.\s*:\s*)?([VUEJG])\s*-?\s*(\d{6,8})/', $line, $matches)) {
                $ci = $matches[2] . '-' . $matches[3]; // [2] es la letra, [3] es el número
            }
            // Buscar Vencimiento (flexible: Vto. o Vio.)
            else if (preg_match('/(V(TO|IO)\.?:?|VENCIMIENTO)\s*(\d{1,2}\/\d{1,2}\/\d{4})/', $line, $matches)) {
                $vencimiento = $matches[3]; 
            }
            // Identificar la línea de "Estudiante"
            else if (stripos($line, 'ESTUDIANTE') !== false) {
                $carrera_index = $index + 1;
            }
        }
        
        // B. Extracción y Separación de Nombres (Usando el texto limpio global)
        
        // Buscar Nombre Completo en el texto limpio global.
        if (preg_match('/([A-Z\s]{10,})/', $cleaned_text, $name_matches)) {
            $nombre_completo_raw = trim($name_matches[0]);
        }
        
        // --- LÓGICA DE SEPARACIÓN (2 Nombres + Resto Apellidos) ---
        if ($nombre_completo_raw != "NO ENCONTRADO") {
            
            $parts = explode(' ', $nombre_completo_raw);
            $parts = array_filter($parts); 
            $count = count($parts);

            if ($count >= 2) {
                // REGLA: Las dos primeras palabras son Nombres
                $nombres = $parts[0]; 
                if (isset($parts[1])) {
                    $nombres .= ' ' . $parts[1]; 
                }
                
                // El resto (a partir del índice 2) son Apellidos
                if ($count > 2) {
                    $apellidos = implode(' ', array_slice($parts, 2)); 
                } else {
                    $apellidos = '';
                }
                
            } else {
                // 1 palabra o error
                $nombres = $nombre_completo_raw;
                $apellidos = '';
            }
        }
        
        // Obtener la Carrera
        if ($carrera_index != -1 && isset($lines[$carrera_index])) {
            $carrera = trim($lines[$carrera_index]);
        }
    }
} else {
    // Manejar errores de subida si es necesario
}

// === FIN DEL CÓDIGO PHP DE PROCESAMIENTO ===
?>

<!DOCTYPE html>
<html>
<head>
    <title>OCR de Cédula y Formulario</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        input[type="text"] { padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        #results { border: 1px solid #007bff; padding: 20px; margin-top: 30px; border-radius: 5px; background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container">
    <center>
        <h3>OCR y Extracción de Datos para Formulario</h3>
        
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="image" required style="padding: 10px;"/>
            <input type="submit" value="Subir y Extraer Datos" style="padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;"/>
        </form>
    </center>

    <?php
    // --- 4. MOSTRAR RESULTADOS EN EL FORMULARIO ---
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        echo '<div id="results">';
        echo '<h3>✅ Datos Extraídos Exitosamente</h3>';
        
        // Mostrar la imagen subida
        if ($uploaded_path) {
            echo '<img src="'.$uploaded_path.'" alt="Imagen Subida" style="max-width:300px; height:auto; margin-bottom: 20px; border: 1px solid #ddd;">';
        }
        
        // Formulario de salida precargado
        echo '<form method="POST" action="save_data.php">'; 
        
        // Campos de Nombres y Apellidos
        echo '<label>Nombres:</label><input type="text" name="first_name" value="' . htmlspecialchars($nombres) . '" style="width:100%;"><br>';
        echo '<label>Apellidos:</label><input type="text" name="last_name" value="' . htmlspecialchars($apellidos) . '" style="width:100%;"><br>';
        
        // Resto de campos
        echo '<label>Cédula (C.I.):</label><input type="text" name="ci_number" value="' . htmlspecialchars($ci) . '" style="width:100%;"><br>';
        echo '<label>Carrera:</label><input type="text" name="major" value="' . htmlspecialchars($carrera) . '" style="width:100%;"><br>';
        echo '<label>Vencimiento:</label><input type="text" name="expiration_date" value="' . htmlspecialchars($vencimiento) . '" style="width:100%;"><br>';
        
        echo '<input type="submit" value="Guardar Datos" style="margin-top: 20px; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">';
        echo '</form>';
        echo '</div>';
    }
    ?>
</div>

</body>
</html>