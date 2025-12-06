$(document).ready(function() {
    let currentChatId = null;
    let refreshInterval = null;
    let lastMessageId = 0;
    let otherUserName = '';
    
    // Cargar chats al inicio
    loadChats();
    
    // Función para cargar la lista de chats
    function loadChats() {
        $.ajax({
            url: 'app/ajax/get_chats.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayChats(response.chats);
                } else {
                    $('#chats-list').html('<div class="p-3 text-center text-light"><p>Error al cargar chats</p></div>');
                }
            },
            error: function() {
                $('#chats-list').html('<div class="p-3 text-center text-light"><p>Error de conexión</p></div>');
            }
        });
    }
    
    // Función para mostrar los chats en el sidebar
    function displayChats(chats) {
        const chatsList = $('#chats-list');
        chatsList.empty();
        
        if (chats.length === 0) {
            chatsList.html('<div class="p-3 text-center text-light"><p class="text-muted">No tienes chats activos</p></div>');
            return;
        }
        
        chats.forEach(chat => {
            const lastMessage = chat.ultimo_mensaje ? 
                (chat.ultimo_mensaje.length > 30 ? 
                    chat.ultimo_mensaje.substring(0, 30) + '...' : 
                    chat.ultimo_mensaje) : 
                'Sin mensajes aún';
            
            const initials = chat.nombre_otro_usuario.charAt(0) + chat.apellido_otro_usuario.charAt(0);
            
            const chatElement = `
                <div class="chat-item p-3 border-bottom border-secondary" 
                     data-chat-id="${chat.id_chat}"
                     data-other-id="${chat.id_otro_usuario}"
                     data-other-name="${chat.nombre_otro_usuario} ${chat.apellido_otro_usuario}"
                     style="cursor: pointer; color: white;">
                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            ${chat.foto_otro_usuario ? 
                                `<img src="${chat.foto_otro_usuario}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">` :
                                `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                      style="width: 50px; height: 50px;">
                                    <span class="text-white fw-bold">${initials}</span>
                                </div>`
                            }
                            ${chat.estado == 1 ? 
                                `<span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle" 
                                      style="width: 12px; height: 12px;"></span>` : 
                                ''}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-0 fw-bold">${chat.nombre_otro_usuario} ${chat.apellido_otro_usuario}</h6>
                                <small class="text-light">${chat.ultima_fecha || ''}</small>
                            </div>
                            <p class="mb-0 text-light" style="opacity: 0.8; font-size: 0.9rem;">${lastMessage}</p>
                        </div>
                    </div>
                </div>
            `;
            chatsList.append(chatElement);
        });
        
        // Agregar eventos a los chats
        $('.chat-item').click(function() {
            $('.chat-item').removeClass('active-chat');
            $(this).addClass('active-chat');
            
            const chatId = $(this).data('chat-id');
            const otherId = $(this).data('other-id');
            otherUserName = $(this).data('other-name');
            
            selectChat(chatId, otherId);
        });
        
        // Seleccionar el primer chat si hay alguno
        if (chats.length > 0 && !currentChatId) {
            $('.chat-item:first').click();
        }
    }
    
    // Función para seleccionar un chat
    function selectChat(chatId, otherId) {
        currentChatId = chatId;
        lastMessageId = 0;
        
        // Actualizar UI
        $('#chat-title').text(otherUserName);
        $('#current-chat-id').val(chatId);
        $('#message-input').prop('disabled', false).focus();
        $('#send-btn').prop('disabled', false);
        
        // Cargar mensajes del chat seleccionado
        loadMessages(chatId);
        
        // Iniciar actualización periódica
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        refreshInterval = setInterval(() => {
            checkNewMessages(chatId);
        }, 5000); // Actualizar cada 5 segundos
    }
    
    // Función para cargar mensajes
    function loadMessages(chatId) {
        $.ajax({
            url: 'app/ajax/get_messages.php',
            type: 'GET',
            data: { chat_id: chatId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayMessages(response.messages);
                    if (response.messages.length > 0) {
                        lastMessageId = response.messages[response.messages.length - 1].id_mensaje;
                    }
                } else {
                    $('#messages-container').html('<div class="text-center text-muted mt-5"><p>Error al cargar mensajes</p></div>');
                }
            },
            error: function() {
                $('#messages-container').html('<div class="text-center text-muted mt-5"><p>Error de conexión</p></div>');
            }
        });
    }
    
    // Función para verificar nuevos mensajes
    function checkNewMessages(chatId) {
        if (!lastMessageId) return;
        
        $.ajax({
            url: 'app/ajax/check_new_messages.php',
            type: 'GET',
            data: { 
                chat_id: chatId,
                last_message_id: lastMessageId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.new_messages.length > 0) {
                    appendNewMessages(response.new_messages);
                    lastMessageId = response.new_messages[response.new_messages.length - 1].id_mensaje;
                    
                    // Actualizar lista de chats si hay nuevos mensajes
                    loadChats();
                }
            }
        });
    }
    
    // Función para mostrar mensajes
    function displayMessages(messages) {
        const container = $('#messages-container');
        container.empty();
        
        if (messages.length === 0) {
            container.html('<div class="text-center text-muted mt-5"><p>No hay mensajes aún. ¡Envía el primero!</p></div>');
            return;
        }
        
        messages.forEach(message => {
            const messageElement = createMessageElement(message);
            container.append(messageElement);
        });
        
        // Scroll al final
        scrollToBottom();
    }
    
    // Función para agregar nuevos mensajes
    function appendNewMessages(newMessages) {
        const container = $('#messages-container');
        
        newMessages.forEach(message => {
            const messageElement = createMessageElement(message);
            container.append(messageElement);
        });
        
        // Scroll al final si ya está cerca del fondo
        scrollToBottomIfNear();
    }
    
    // Función para crear elemento de mensaje
    function createMessageElement(message) {
        const isOwn = message.es_mio;
        const messageClass = isOwn ? 'text-end' : 'text-start';
        const bubbleClass = isOwn ? 'sent' : 'received';
        
        let messageContent = '';
        if (message.tipo_mensaje === 'texto') {
            messageContent = `<p class="mb-1">${message.contenido}</p>`;
        } else if (message.url_archivo) {
            messageContent = `
                <div class="alert alert-info p-2 mb-2">
                    <i class="bi bi-file-earmark"></i> Archivo adjunto
                    <a href="${message.url_archivo}" target="_blank" class="d-block">${message.nombre_archivo || 'Descargar'}</a>
                </div>
            `;
        }
        
        return `
            <div class="mb-3 ${messageClass}">
                ${!isOwn ? `
                    <small class="d-block mb-1 text-muted">
                        ${message.nombre_emisor} ${message.apellido_emisor}
                    </small>
                ` : ''}
                <div class="message-bubble ${bubbleClass}">
                    ${messageContent}
                    <small class="opacity-75 d-block text-end" style="font-size: 0.75rem;">${message.hora}</small>
                </div>
            </div>
        `;
    }
    
    // Función para scroll al final
    function scrollToBottom() {
        const container = $('#messages-container');
        container.scrollTop(container[0].scrollHeight);
    }
    
    // Función para scroll si está cerca del fondo
    function scrollToBottomIfNear() {
        const container = $('#messages-container');
        const threshold = 100; // px desde el fondo
        const distanceFromBottom = container[0].scrollHeight - container.scrollTop() - container.height();
        
        if (distanceFromBottom <= threshold) {
            scrollToBottom();
        }
    }
    
    // Enviar mensaje
    $('#message-form').submit(function(e) {
        e.preventDefault();
        
        const chatId = $('#current-chat-id').val();
        const message = $('#message-input').val().trim();
        
        if (!chatId || !message) return;
        
        // Deshabilitar temporalmente el input
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
                    $('#message-input').val('').prop('disabled', false).focus();
                    $('#send-btn').prop('disabled', false);
                    
                    // Recargar mensajes y chats
                    loadMessages(chatId);
                    loadChats();
                } else {
                    alert('Error al enviar mensaje: ' + response.message);
                    $('#message-input').prop('disabled', false);
                    $('#send-btn').prop('disabled', false);
                }
            },
            error: function() {
                alert('Error de conexión al enviar mensaje');
                $('#message-input').prop('disabled', false);
                $('#send-btn').prop('disabled', false);
            }
        });
    });
    
    // Permitir enviar con Enter
    $('#message-input').keypress(function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            $('#message-form').submit();
        }
    });
    
    // Actualizar lista de chats cada 30 segundos
    setInterval(loadChats, 30000);
    
    // Manejar recarga de página
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
});