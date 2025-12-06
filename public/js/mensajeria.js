$(document).ready(function() {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let currentChatId = null;
    let otherUserName = '';
    let lastMessageId = 0;
    let refreshInterval = null;
    let otherUserPhoto = '';
    
    console.log("🚀 Sistema de mensajería inicializado");
    
    // ============================================
    // 1. CARGAR LISTA DE CHATS AL INICIAR
    // ============================================
    loadChats();
    
    // ============================================
    // 2. FUNCIONES PRINCIPALES
    // ============================================
    
    // Cargar lista de chats (se ejecuta cada 30 segundos)
    function loadChats() {
        console.log("📋 Cargando lista de chats...");
        
        $.ajax({
            url: 'app/ajax/get_chats.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log(`✅ Chats cargados: ${response.chats.length}`);
                    displayChats(response.chats);
                } else {
                    console.error("❌ Error en get_chats.php:", response.message);
                    showError('Error al cargar chats');
                }
            },
            error: function(xhr, status, error) {
                console.error("❌ Error AJAX en loadChats:", error);
                showError('Error de conexión');
            }
        });
    }
    
    // Mostrar chats en sidebar
    function displayChats(chats) {
        const chatsList = $('#chats-list');
        chatsList.empty();
        
        if (chats.length === 0) {
            chatsList.html(`
                <div class="p-4 text-center text-light">
                    <i class="bi bi-chat-left-dots fs-1 mb-3"></i>
                    <p class="mb-2">No tienes conversaciones</p>
                    <small class="text-muted">Comienza un nuevo chat con alguien</small>
                </div>
            `);
            return;
        }
        
        chats.forEach(chat => {
            const chatElement = createChatElement(chat);
            chatsList.append(chatElement);
        });
        
        // Agregar eventos click
        $('.chat-item').click(function() {
            selectChat($(this));
        });
    }
    
    // Crear elemento HTML para un chat
    function createChatElement(chat) {
        const nombreCompleto = chat.nombre_otro_usuario + ' ' + chat.apellido_otro_usuario;
        
        return `
            <div class="chat-item p-3" 
                 data-chat-id="${chat.id_chat}"
                 data-other-id="${chat.id_otro_usuario}"
                 data-other-name="${nombreCompleto}"
                 data-other-photo="${chat.foto_otro_usuario}"
                 style="color: white; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div class="d-flex align-items-center">
                    <!-- Avatar -->
                    <div class="position-relative me-3">
                        ${chat.foto_otro_usuario ? 
                            `<img src="${chat.foto_otro_usuario}" 
                                 class="rounded-circle" 
                                 style="width: 50px; height: 50px; object-fit: cover;"
                                 alt="${chat.nombre_otro_usuario}"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>👤</text></svg>';">` :
                            `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                  style="width: 50px; height: 50px;">
                                <i class="bi bi-person fs-5 text-white"></i>
                            </div>`
                        }
                        ${chat.estado == 1 ? 
                            `<div class="status-online position-absolute bottom-0 end-0"></div>` : 
                            ''}
                    </div>
                    
                    <!-- Información -->
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.95rem;">
                                ${nombreCompleto}
                            </h6>
                            <small class="text-light" style="font-size: 0.75rem; opacity: 0.7;">
                                ${chat.ultima_fecha || ''}
                            </small>
                        </div>
                        <p class="mb-0 text-light text-truncate" style="font-size: 0.85rem; opacity: 0.8;">
                            ${chat.ultimo_mensaje}
                        </p>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Seleccionar un chat
    function selectChat(chatElement) {
        console.log("💬 Seleccionando chat...");
        
        // Remover active de todos
        $('.chat-item').removeClass('active-chat');
        // Agregar active al seleccionado
        chatElement.addClass('active-chat');
        
        // Obtener datos
        currentChatId = chatElement.data('chat-id');
        otherUserName = chatElement.data('other-name');
        otherUserPhoto = chatElement.data('other-photo');
        lastMessageId = 0;
        
        // Actualizar UI
        updateChatHeader();
        $('#current-chat-id').val(currentChatId);
        $('#message-input').prop('disabled', false).focus();
        $('#send-btn').prop('disabled', false);
        $('#chat-actions').removeClass('d-none');
        
        // Cargar mensajes
        loadMessages(currentChatId);
        
        // Iniciar actualización periódica
        startMessageRefresh();
    }
    
    // Actualizar cabecera del chat
    function updateChatHeader() {
        $('#chat-title').text(otherUserName);
        
        if (otherUserPhoto) {
            $('#chat-avatar').html(`
                <img src="${otherUserPhoto}" 
                     class="rounded-circle" 
                     style="width: 50px; height: 50px; object-fit: cover;"
                     alt="${otherUserName}"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\"bi bi-person fs-4 text-white\"></i>'">
            `);
        } else {
            $('#chat-avatar').html('<i class="bi bi-person fs-4 text-white"></i>');
        }
    }
    
    // Cargar mensajes de un chat
    function loadMessages(chatId) {
        console.log("📨 Cargando mensajes del chat:", chatId);
        
        $.ajax({
            url: 'app/ajax/get_messages.php',
            type: 'GET',
            data: { chat_id: chatId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log(`✅ ${response.messages.length} mensajes cargados`);
                    displayMessages(response.messages);
                    
                    // Guardar ID del último mensaje
                    if (response.messages.length > 0) {
                        lastMessageId = response.messages[response.messages.length - 1].id_mensaje;
                        console.log("📌 Último mensaje ID:", lastMessageId);
                    }
                } else {
                    console.error("❌ Error en get_messages.php:", response.message);
                    showError('Error al cargar mensajes');
                }
            },
            error: function(xhr, status, error) {
                console.error("❌ Error AJAX en loadMessages:", error);
                showError('Error de conexión');
            }
        });
    }
    
    // Mostrar mensajes en el contenedor
    function displayMessages(messages) {
        const container = $('#messages-container');
        container.empty();
        
        if (messages.length === 0) {
            container.html(`
                <div class="text-center text-muted mt-5">
                    <i class="bi bi-chat-left" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">No hay mensajes aún</p>
                    <small class="text-muted">Envía el primer mensaje para comenzar</small>
                </div>
            `);
            return;
        }
        
        // Agrupar mensajes por fecha
        let currentDate = '';
        messages.forEach(message => {
            const messageDate = message.fecha_completa.split(' ')[0];
            
            // Mostrar fecha si cambió
            if (messageDate !== currentDate) {
                currentDate = messageDate;
                container.append(`
                    <div class="text-center my-3">
                        <span class="badge bg-secondary" style="font-size: 0.7rem; font-weight: normal;">
                            ${messageDate}
                        </span>
                    </div>
                `);
            }
            
            // Agregar mensaje
            const messageElement = createMessageElement(message);
            container.append(messageElement);
        });
        
        // Hacer scroll al final
        setTimeout(scrollToBottom, 100);
    }
    
    // Crear elemento HTML para un mensaje
    function createMessageElement(message) {
        const isOwn = message.es_mio;
        const messageClass = isOwn ? 'text-end' : 'text-start';
        const bubbleClass = isOwn ? 'sent' : 'received';
        
        return `
            <div class="mb-2 ${messageClass}">
                ${!isOwn ? `
                    <small class="d-block mb-1 text-muted ms-1" style="font-size: 0.75rem;">
                        ${message.nombre_emisor} ${message.apellido_emisor}
                    </small>
                ` : ''}
                <div class="message-bubble ${bubbleClass} d-inline-block">
                    <div class="message-content">
                        ${message.contenido}
                    </div>
                    <div class="message-time text-end mt-1" style="font-size: 0.7rem; opacity: 0.7;">
                        ${message.hora}
                    </div>
                </div>
            </div>
        `;
    }
    
    // ============================================
    // 3. ACTUALIZACIÓN AUTOMÁTICA
    // ============================================
    
    // Iniciar actualización periódica
    function startMessageRefresh() {
        // Limpiar intervalo anterior
        if (refreshInterval) {
            clearInterval(refreshInterval);
            console.log("🔄 Intervalo anterior limpiado");
        }
        
        // Configurar nuevo intervalo (5 segundos)
        refreshInterval = setInterval(() => {
            if (currentChatId && lastMessageId > 0) {
                console.log("🔍 Verificando nuevos mensajes...");
                checkNewMessages();
            }
        }, 5000);
        
        console.log("⏱️ Intervalo de 5 segundos iniciado");
    }
    
    // Verificar nuevos mensajes
    function checkNewMessages() {
        $.ajax({
            url: 'app/ajax/check_new_messages.php',
            type: 'GET',
            data: { 
                chat_id: currentChatId,
                last_message_id: lastMessageId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.new_messages.length > 0) {
                    console.log(`🆕 ${response.new_messages.length} nuevos mensajes encontrados`);
                    appendNewMessages(response.new_messages);
                    lastMessageId = response.new_messages[response.new_messages.length - 1].id_mensaje;
                    
                    // Actualizar lista de chats
                    loadChats();
                }
            },
            error: function() {
                // Silenciar errores de polling
            }
        });
    }
    
    // Agregar nuevos mensajes al chat
    function appendNewMessages(newMessages) {
        const container = $('#messages-container');
        
        newMessages.forEach(message => {
            const messageElement = createMessageElement(message);
            container.append(messageElement);
        });
        
        // Hacer scroll si está cerca del final
        scrollToBottomIfNear();
    }
    
    // ============================================
    // 4. ENVÍO DE MENSAJES
    // ============================================
    
    // Enviar mensaje
    $('#message-form').submit(function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    function sendMessage() {
        const chatId = $('#current-chat-id').val();
        const message = $('#message-input').val().trim();
        
        if (!chatId || !message) {
            console.warn("⚠️ No se puede enviar: chat o mensaje vacío");
            return;
        }
        
        console.log("✉️ Enviando mensaje:", message.substring(0, 50) + "...");
        
        // Deshabilitar temporalmente
        $('#message-input').prop('disabled', true);
        $('#send-btn').prop('disabled', true);
        
        $.ajax({
            url: 'app/ajax/send_message.php',
            type: 'POST',
            data: {
                chat_id: chatId,
                message: message
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log("✅ Mensaje enviado, ID:", response.message_id);
                    
                    // Limpiar y habilitar
                    $('#message-input').val('').prop('disabled', false).focus();
                    $('#send-btn').prop('disabled', false);
                    
                    // Recargar mensajes
                    loadMessages(chatId);
                    
                    // Actualizar lista de chats
                    loadChats();
                } else {
                    console.error("❌ Error al enviar:", response.message);
                    alert('Error: ' + response.message);
                    $('#message-input').prop('disabled', false);
                    $('#send-btn').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error("❌ Error AJAX en sendMessage:", error);
                alert('Error de conexión');
                $('#message-input').prop('disabled', false);
                $('#send-btn').prop('disabled', false);
            }
        });
    }
    
    // Permitir enviar con Enter
    $('#message-input').keypress(function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // ============================================
    // 5. FUNCIONES UTILITARIAS
    // ============================================
    
    function scrollToBottom() {
        const container = $('#messages-container');
        container.scrollTop(container[0].scrollHeight);
    }
    
    function scrollToBottomIfNear() {
        const container = $('#messages-container');
        const distanceFromBottom = container[0].scrollHeight - container.scrollTop() - container.height();
        
        // Si está a menos de 150px del fondo, hacer scroll
        if (distanceFromBottom < 150) {
            scrollToBottom();
        }
    }
    
    function showError(message) {
        console.error(message);
        // Podrías agregar un toast o alert aquí
    }
    
    // ============================================
    // 6. ACTUALIZACIONES PERIÓDICAS
    // ============================================
    
    // Actualizar lista de chats cada 30 segundos
    setInterval(loadChats, 30000);
    console.log("⏱️ Intervalo de 30 segundos para chats iniciado");
    
    // Limpiar al cerrar página
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            console.log("🧹 Intervalos limpiados");
        }
    });
});