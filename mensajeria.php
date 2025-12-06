<?php
session_start();
require_once __DIR__ . '/app/includes/conect.php'; // Cambiar la ruta según sea necesario

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
    <link rel="stylesheet" href="public/styles/mensajeria.css">
    <title>Mensajería - UniGigs</title>
    <style>
        .chat-item:hover {
            background-color: #34495e !important;
        }
        .chat-item.active-chat {
            background-color: #34495e !important;
            border-left: 4px solid #007bff;
        }
        #messages-container {
            background-color: #f8f9fa;
            min-height: 400px;
            max-height: calc(100vh - 200px);
        }
        .message-bubble {
            max-width: 70%;
            border-radius: 18px;
            padding: 10px 15px;
            margin-bottom: 10px;
            position: relative;
            word-wrap: break-word;
        }
        .message-bubble.sent {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        .message-bubble.received {
            background-color: #e9ecef;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }
        .chat-input {
            background-color: white;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/app/includes/Navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar para chats -->
            <div class="col-md-4 col-lg-3 p-0">
                <div class="sidebar">
                    <div class="sidebar-header p-3 border-bottom" style="background-color: #34495e;">
                        <h4 class="mb-0 text-white">Chats</h4>
                    </div>
                    <div id="chats-list" class="nav-links">
                        <!-- Los chats se cargarán aquí por AJAX -->
                        <div class="text-center mt-3">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Cargando chats...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Área principal del chat -->
            <div class="col-md-8 col-lg-9 p-0">
                <div class="chat-container d-flex flex-column" style="height: 100vh;">
                    <!-- Cabecera del chat -->
                    <div class="chat-header p-3 border-bottom bg-white">
                        <h4 id="chat-title" class="mb-0">Selecciona un chat</h4>
                        <small id="chat-status" class="text-muted"></small>
                    </div>
                    
                    <!-- Área de mensajes -->
                    <div id="messages-container" class="flex-grow-1 p-3 overflow-auto">
                        <div class="text-center text-muted mt-5">
                            <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                            <p class="mt-3">Selecciona un chat para comenzar a conversar</p>
                        </div>
                    </div>
                    
                    <!-- Input para enviar mensajes -->
                    <div class="chat-input p-3 border-top bg-white">
                        <form id="message-form" class="d-flex">
                            <input type="hidden" id="current-chat-id">
                            <div class="flex-grow-1 me-2">
                                <input type="text" 
                                       id="message-input" 
                                       class="form-control" 
                                       placeholder="Escribe un mensaje..." 
                                       disabled>
                            </div>
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    id="send-btn" 
                                    disabled>
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userId = <?php echo $id_usuario; ?>;
    </script>
    <script src="public/js/mensajeria.js"></script>
</body>
</html>