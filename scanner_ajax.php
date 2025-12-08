<?php

header('Content-Type: application/json');


$apiKey = "K83980801188957"; 
$upload_dir = "public/img/temp/"; 


$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['imagen_carnet'])) {
    

    if (!file_exists($upload_dir)) { mkdir($upload_dir, 0777, true); }

    $file_name = uniqid() . "_" . basename($_FILES['imagen_carnet']['name']);
    $uploaded_path = $upload_dir . $file_name;
    
    if(move_uploaded_file($_FILES['imagen_carnet']['tmp_name'], $uploaded_path)) {
        
        $ocr_image_path = $uploaded_path;
        if (extension_loaded('gd')) {
            $info = getimagesize($uploaded_path);
            $mime = $info['mime'] ?? '';
            $image = null;
            switch ($mime) {
                case 'image/jpeg': $image = imagecreatefromjpeg($uploaded_path); break;
                case 'image/png':  $image = imagecreatefrompng($uploaded_path); break;
            }
            if ($image) {
                imagefilter($image, IMG_FILTER_CONTRAST, -15); 
                imagefilter($image, IMG_FILTER_BRIGHTNESS, 5);
                imagejpeg($image, $uploaded_path, 90);
                imagedestroy($image);
            }
        }

        $fileData = new CURLFile($uploaded_path);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.ocr.space/parse/image");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'apikey' => $apiKey,
            'file' => $fileData,
            'language' => 'spa',
            'isOverlayRequired' => 'false',
            'detectOrientation' => 'true',
            'scale' => 'true',
            'OCREngine' => '2'
        ));

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result) {
            $json = json_decode($result, true);
            
            if (isset($json['ParsedResults'][0]['ParsedText'])) {
                $raw_text = $json['ParsedResults'][0]['ParsedText'];
                $lines = explode("\n", str_replace(["\r", "\t"], "", $raw_text));
                $clean_lines = array_values(array_filter($lines, function($v){ return strlen(trim($v)) > 1; }));


                $nombre = ""; $apellido = ""; $cedula = ""; 
                $carrera = ""; $universidad = "";

                $idx_estudiante = -1;

                foreach ($clean_lines as $i => $line) {
                    $line_upper = strtoupper(trim($line));

                    if (empty($universidad)) {
                        if (strpos($line_upper, 'UNIMAR') !== false || strpos($line_upper, 'UNIVERSIDAD DE MARGARITA') !== false) {
                            $universidad = "UNIVERSIDAD DE MARGARITA";
                        } elseif (strpos($line_upper, 'UNIVERSIDAD') !== false || strpos($line_upper, 'INSTITUTO') !== false) {
                            $universidad = trim($line);
                        }
                    }


                    if (empty($cedula) && preg_match('/(?:C\.?I\.?|V|E)?\s*[-:.]?\s*([VE])?\s*[-]?\s*(\d{6,9})\b/i', $line_upper, $matches)) {
                        if (strpos($line_upper, 'RIF') === false) {
                            $prefijo = !empty($matches[1]) ? $matches[1] : 'V';
                            $cedula = $prefijo . "-" . $matches[2];
                        }
                    }


                    if (strpos($line_upper, 'ESTUDIANTE') !== false || strpos($line_upper, 'TUDIANTE') !== false) {
                        $idx_estudiante = $i;
                    }
                }

                if ($idx_estudiante !== -1) {

                    if (isset($clean_lines[$idx_estudiante + 1])) {
                        $carrera = preg_replace('/[^A-ZÁÉÍÓÚÑ\s]/u', '', strtoupper($clean_lines[$idx_estudiante + 1]));
                        $carrera = str_replace("INGENIERIA", "INGENIERÍA", $carrera);
                    }

                    $nombres_raw = [];
                    for ($k = 1; $k <= 3; $k++) {
                        if (isset($clean_lines[$idx_estudiante - $k])) {
                            $txt = trim($clean_lines[$idx_estudiante - $k]);
                            if (!preg_match('/\d/', $txt) && strlen($txt) > 2) array_unshift($nombres_raw, strtoupper($txt));
                        }
                    }
                    if (count($nombres_raw) >= 2) { 
                        $nombre = $nombres_raw[0]; 
                        $apellido = implode(" ", array_slice($nombres_raw, 1)); 
                    } elseif (count($nombres_raw) == 1) {
                        $parts = explode(' ', $nombres_raw[0]);
                        if (count($parts) > 2) { 
                            $nombre = $parts[0] . " " . $parts[1]; 
                            $apellido = implode(" ", array_slice($parts, 2)); 
                        } else { 
                            $nombre = $nombres_raw[0]; 
                        }
                    }
                }

                $response['success'] = true;
                $response['data'] = [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'cedula' => $cedula,
                    'universidad' => $universidad,
                    'carrera' => $carrera
                ];
            } else {
                $response['message'] = 'No se detectó texto en la imagen.';
            }
        } else {
            $response['message'] = 'Error al conectar con OCR.space';
        }

        unlink($uploaded_path);
    } else {
        $response['message'] = 'Error al subir la imagen al servidor.';
    }
}

echo json_encode($response);
?>