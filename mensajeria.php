<?php
// mensajeria.php - EN RAÍZ del proyecto
session_start();
require_once __DIR__ . '/conect.php';  // ✅ Ahora está en la misma carpeta

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajería - UniGigs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <link rel="stylesheet" href="public/styles/mensajeria.css">
    <link rel ="stylesheet" href="public/pages/stylesNav.css">
    
    
    <style>
        /* Estilos para mensajería */
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            height: 100vh;
            overflow-y: auto;
        }
        
        .chat-item {
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 3px solid transparent;
        }
        
        .chat-item:hover {
            background-color: rgba(52, 73, 94, 0.7) !important;
            border-left: 3px solid #3498db;
        }
        
        .chat-item.active-chat {
            background-color: #34495e !important;
            border-left: 3px solid #007bff;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            margin-bottom: 8px;
            position: relative;
            word-wrap: break-word;
            animation: fadeIn 0.3s ease-out;
        }
        
        .message-bubble.sent {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.2);
        }
        
        .message-bubble.received {
            background: white;
            color: #333;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        #messages-container {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            height: calc(100vh - 140px);
            overflow-y: auto;
        }
        
        .status-online {
            width: 10px;
            height: 10px;
            background: #2ecc71;
            border-radius: 50%;
            border: 2px solid #2c3e50;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Scrollbar personalizado */
        #messages-container::-webkit-scrollbar {
            width: 6px;
        }
        
        #messages-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        #messages-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        #messages-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <style>
    .navbar-toggler:focus {
        outline: none !important;
        box-shadow: none !important;
        background-color: transparent !important; 
    }
    </style>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <!-- ruta del logo desde la raíz hacia public/img -->
            <a class="navbar-brand" href="public/pages/principal.php">
                <img src="public/img/Logo_Navbar.png" alt="Logo" width="170" height="48" class="d-inline-block align-text-center">
            </a>
            
            <button class="navbar-toggler" style="border:none;" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="material-symbols-outlined">menu</span> 
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <form class="search w-100" role="search"> 
                    <div class="search-overlay w-100">
                        <input class="form-control" type="search" placeholder="Busqueda" aria-label="Search"/>
                        <button class="buscar btn btn-outline-success rounded-circle" type="submit" aria-label="Buscar">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav mx-auto text-center mb-2 mb-lg-0 ms-3"> 
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Carreras</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Ingenieria de Sistemas</a></li>
                            <li><a class="dropdown-item" href="#">Diseno Grafico</a></li>
                            <li><a class="dropdown-item" href="#">Idiomas modernos</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="icon-group d-flex align-items-center mx-auto">
                    <a class="Icon fa-lg" href="public/pages/principal.php"><span class="material-symbols-outlined">notifications</span></a>
                    <!-- link al propio mensajeria (estás en la raíz) -->
                    <a class="Icon" href="mensajeria.php"><span class="material-symbols-outlined">mail</span></a>
                    <a class="Icon" href="public/pages/Perfil.php"><span class="material-symbols-outlined">school</span></a>
                    <a class="Icon" href="public/pages/Perfil.php"><span class="material-symbols-outlined">account_circle</span></a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <!-- ============================================
                 SIDEBAR - LISTA DE CHATS 
                 Se actualiza cada 30 segundos
            ============================================ -->
            <div class="col-md-4 col-lg-3 p-0">
                <div class="sidebar">
                    <div class="p-3 border-bottom" style="background: #34495e;">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="bi bi-chat-left-text fs-4 text-white"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white">Mensajes</h4>
                                <p class="mb-0 text-light small">Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CONTENEDOR DE CHATS - Se llena por AJAX -->
                    <div id="chats-list" class="pt-2">
                        <div class="text-center mt-4">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Cargando chats...</span>
                            </div>
                            <p class="text-light mt-2 small">Cargando conversaciones...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ============================================
                 ÁREA PRINCIPAL DEL CHAT 
                 Se actualiza cada 5 segundos cuando hay chat activo
            ============================================ -->
            <div class="col-md-8 col-lg-9 p-0">
                <div class="d-flex flex-column" style="height: 100vh;">
                    <!-- Cabecera del chat -->
                    <div class="p-3 border-bottom bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div id="chat-avatar" class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-person fs-4 text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h4 id="chat-title" class="mb-0">Selecciona un chat</h4>
                                <small id="chat-status" class="text-muted">Elige una conversación para comenzar</small>
                            </div>
                            <div id="chat-actions" class="d-none">
                                <button class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CONTENEDOR DE MENSAJES -->
                    <div id="messages-container" class="flex-grow-1 p-3 overflow-auto">
                        <div class="text-center text-muted mt-5">
                            <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3">Selecciona un chat para ver los mensajes</p>
                            <small class="text-muted">Los mensajes se actualizan automáticamente cada 5 segundos</small>
                        </div>
                    </div>
                    
                    <!-- FORMULARIO PARA ENVIAR MENSAJES -->
                    <div class="p-3 border-top bg-white shadow-sm">
                        <form id="message-form" class="d-flex">
                            <input type="hidden" id="current-chat-id">
                            <div class="flex-grow-1 me-2">
                                <input type="text" 
                                       id="message-input" 
                                       class="form-control" 
                                       placeholder="Escribe un mensaje..." 
                                       disabled
                                       style="border-radius: 20px; padding: 10px 20px;">
                            </div>
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    id="send-btn" 
                                    disabled
                                    style="border-radius: 20px; padding: 10px 25px;">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================
         SCRIPTS 
    ============================================ -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variable global con el ID del usuario logueado
        const userId = <?php echo $id_usuario; ?>;
        console.log("🔧 Sistema de Mensajería iniciado");
        console.log("👤 Usuario ID:", userId);
    </script>
    
    <!-- Script principal de mensajería -->
    <script src="public/js/mensajeria.js"></script>
</body>
</html>