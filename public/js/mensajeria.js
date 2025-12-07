$(document).ready(function() {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let currentChatId = null;
    let otherUserName = '';
    let lastMessageId = 0;
    let refreshInterval = null;
    let otherUserPhoto = '';
    let selectedFiles = [];
    let fileUploadQueue = [];
    let isUploading = false;
    
    // ============================================
    // 1. INICIALIZACIÓN
    // ============================================
    console.log("🚀 Sistema de Mensajería con Archivos inicializado");
    loadChats();
    
    // ============================================
    // 2. MANEJO DE ARCHIVOS
    // ============================================
    
    // Click en botón adjuntar
    $('#attach-btn').click(function() {
        $('#file-input').click();
    });
    
    // Selección de archivos
    // ...existing code...
// Selección de archivos
    $('#file-input').on('change', function(e) {
        const files = Array.from(e.target.files || []);
        if (files.length === 0) return;

        // Solo permitimos 1 archivo: tomamos el primero y reemplazamos cualquier anterior
        const file = files[0];

        // Validar tamaño máximo (10MB)
        const MAX_SIZE = 10 * 1024 * 1024;
        if (file.size > MAX_SIZE) {
            alert(`El archivo "${file.name}" es demasiado grande (máximo 10MB)`);
            $(this).val('');
            return;
        }

        // Validar tipo de archivo (misma lista que antes)
        const allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip', 'application/x-rar-compressed',
            'audio/mpeg', 'video/mp4', 'video/avi', 'video/quicktime'
        ];
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(jpg|jpeg|png|gif|webp|pdf|doc|docx|xls|xlsx|txt|zip|rar|mp3|mp4|avi|mov)$/i)) {
            alert(`Tipo de archivo no permitido: "${file.name}"`);
            $(this).val('');
            return;
        }

        // Reemplazar cualquier archivo seleccionado previamente (solo 1 permitido)
        selectedFiles = [file];

        // Mostrar previsualización (el método que ya tienes se encargará de vaciar/mostrar)
        showFilePreview(file);

        // Limpiar input nativo y mostrar contenedor
        $(this).val('');
        $('#file-preview-container').show();

        // Actualizar estado del botón de enviar
        if (typeof updateSendButtonState === 'function') updateSendButtonState();
    });
// ...existing code...
    
    // Mostrar previsualización de archivo
    // ...existing code...
function showFilePreview(file) {
    const container = $('#file-preview-container');
    // Vaciar para asegurar que solo haya 1 preview a la vez
    container.empty();

    const fileId = 'file-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    const isImage = file.type.startsWith('image/');

    if (isImage) {
        const wrapper = $(`
            <div class="file-preview d-flex align-items-center" id="${fileId}">
                <div class="d-flex align-items-center">
                    <img alt="${file.name}" style="max-width:60px;max-height:60px;margin-right:10px;border-radius:4px;">
                    <div>
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <div class="remove-file" style="cursor:pointer;">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        `);

        const img = wrapper.find('img');
        const reader = new FileReader();
        reader.onload = function(e) { img.attr('src', e.target.result); };
        reader.readAsDataURL(file);

        wrapper.find('.remove-file').on('click', function() { window.removeSelectedFile(file.name); });
        container.append(wrapper).show();
    } else {
        const previewHtml = $(`
            <div class="file-preview d-flex align-items-center justify-content-between" id="${fileId}">
                <div class="d-flex align-items-center">
                    <div class="file-icon me-2"><i class="bi bi-file-earmark"></i></div>
                    <div class="file-info">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size text-muted">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <div class="remove-file" style="cursor:pointer;"><i class="bi bi-x-circle"></i></div>
            </div>
        `);
        previewHtml.find('.remove-file').on('click', function() { window.removeSelectedFile(file.name); });
        container.append(previewHtml).show();
    }
}
// ...existing code...
        
        // Función global para remover archivos
        window.removeSelectedFile = function(fileNameOrId) {
        // selectedFiles contiene objetos File (por nombre usamos file.name)
        selectedFiles = selectedFiles.filter(f => f.name !== fileNameOrId);

        // remover cualquier preview (vacía el contenedor)
        $('#file-preview-container').empty().hide();

        // actualizar estado del botón
        if (typeof updateSendButtonState === 'function') updateSendButtonState();
    };

    // función que habilita/deshabilita el botón de enviar
    function updateSendButtonState() {
        const hasChat = !!currentChatId;
        const text = ($('#message-input').val() || '').trim();
        const hasText = text.length > 0;
        const hasFile = selectedFiles.length > 0;
        const enable = hasChat && (hasText || hasFile);
        $('#send-btn').prop('disabled', !enable);
    }

    // enlazar input para actualizar estado en tiempo real
    $('#message-input').on('input', updateSendButtonState);

    // Modifica selectChat para llamar updateSendButtonState() (reemplaza la función selectChat existente)
    function selectChat(chatId, otherId) {
        currentChatId = chatId;
        lastMessageId = 0;

        $('#chat-title').text(otherUserName);
        $('#current-chat-id').val(chatId);
        $('#message-input').prop('disabled', false).focus();
        $('#chat-actions').removeClass('d-none');

        updateChatHeader();
        loadMessages(chatId);
        startMessageRefresh();

        // actualizar el estado del botón al seleccionar chat
        updateSendButtonState();
    }
    
    // Formatear tamaño de archivo
    function formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }
    
    // ============================================
    // 3. CARGAR Y MOSTRAR CHATS
    // ============================================
    
    function loadChats() {
        $.ajax({
            url: 'app/ajax/get_chats.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayChats(response.chats);
                } else {
                    showError('Error al cargar chats: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showError('Error de conexión al cargar chats');
            }
        });
    }
    
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
        
        // ...existing code...
    $('.chat-item').click(function() {
        $('.chat-item').removeClass('active-chat');
        $(this).addClass('active-chat');
        
        const chatId = $(this).data('chat-id');
        const otherId = $(this).data('other-id');
        otherUserName = $(this).data('other-name');
        otherUserPhoto = $(this).data('other-photo');
        const estado = $(this).data('estado'); // puede ser 1/0 o true/false
        
        selectChat(chatId, otherId, estado);
    });
// ...existing code...
    }
    
    // ...existing code...
    function createChatElement(chat) {
        const nombreCompleto = chat.nombre_otro_usuario + ' ' + chat.apellido_otro_usuario;
        
        return `
            <div class="chat-item p-3" 
                data-chat-id="${chat.id_chat}"
                data-other-id="${chat.id_otro_usuario}"
                data-other-name="${nombreCompleto}"
                data-other-photo="${chat.foto_otro_usuario}"
                data-estado="${chat.estado}"
                style="color: white; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        ${chat.foto_otro_usuario ? 
                            `<img src="${chat.foto_otro_usuario}" 
                                class="rounded-circle" 
                                style="width: 50px; height: 50px; object-fit: cover;"
                                alt="${chat.nombre_otro_usuario}">` :
                            `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                style="width: 50px; height: 50px;">
                                <i class="bi bi-person fs-5 text-white"></i>
                            </div>`
                        }
                        ${chat.estado == 1 ? 
                            `<div class="status-online position-absolute bottom-0 end-0"></div>` : 
                            `<div class="status-offline position-absolute bottom-0 end-0" title="Chat inactivo" style="width:10px;height:10px;border-radius:50%;background:#999;border:2px solid #1a252f"></div>`}
                    </div>
                    
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
// ...existing code...
    
    // ...existing code...
    function selectChat(chatId, otherId, estado) {
        currentChatId = chatId;
        lastMessageId = 0;
        
        $('#chat-title').text(otherUserName);
        $('#current-chat-id').val(chatId);

        // Si el chat está inactivo (estado == 0 / false) mantener input deshabilitado
        const isActive = (estado === 1 || estado === '1' || estado === true || estado === 'true');
        if (!isActive) {
            $('#message-input').prop('disabled', true).val('').attr('placeholder', 'Chat inactivo');
            $('#send-btn').prop('disabled', true);
            // opcional: ocultar acciones si existe contenedor
            $('#chat-actions').addClass('d-none');
        } else {
            $('#message-input').prop('disabled', false).attr('placeholder', 'Escribe un mensaje...').focus();
            $('#chat-actions').removeClass('d-none');
        }

        updateChatHeader();
        loadMessages(chatId);
        startMessageRefresh();

        // actualizar estado del botón al seleccionar chat
        if (typeof updateSendButtonState === 'function') updateSendButtonState();
    }
// ...existing code...
    
    function updateChatHeader() {
        if (otherUserPhoto) {
            $('#chat-avatar').html(`
                <img src="${otherUserPhoto}" 
                     class="rounded-circle" 
                     style="width: 50px; height: 50px; object-fit: cover;"
                     alt="${otherUserName}">
            `);
        } else {
            $('#chat-avatar').html('<i class="bi bi-person fs-4 text-white"></i>');
        }
    }
    
    // ============================================
    // 4. CARGAR Y MOSTRAR MENSAJES
    // ============================================
    
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
                    showError('Error al cargar mensajes');
                }
            },
            error: function() {
                showError('Error de conexión al cargar mensajes');
            }
        });
    }
    
    function displayMessages(messages) {
        const container = $('#messages-container');
        container.empty();
        
        if (messages.length === 0) {
            container.html(`
                <div class="text-center text-muted mt-5">
                    <i class="bi bi-chat-left" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">No hay mensajes aún</p>
                    <small class="text-muted">Envía el primer mensaje o archivo para comenzar</small>
                </div>
            `);
            return;
        }
        
        let currentDate = '';
        messages.forEach(message => {
            const messageDate = message.fecha_completa.split(' ')[0];
            
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
            
            const messageElement = createMessageElement(message);
            container.append(messageElement);
        });
        
        setTimeout(scrollToBottom, 100);
    }
    
    function createMessageElement(message) {
        const isOwn = message.es_mio;
        const messageClass = isOwn ? 'text-end' : 'text-start';
        const bubbleClass = isOwn ? 'sent' : 'received';
        
        let messageContent = '';
        
        switch (message.tipo_mensaje) {
            case 'texto':
                messageContent = `<p class="mb-1" style="white-space: pre-wrap;">${message.contenido}</p>`;
                break;
                
            case 'imagen':
                messageContent = `
                    <div class="mb-2">
                        <p class="mb-1" style="white-space: pre-wrap;">${message.contenido || ''}</p>
                        <img src="${message.url_archivo}" 
                             class="message-image"
                             alt="${message.nombre_archivo}"
                             onclick="openImageModal('${message.url_archivo}', '${message.nombre_archivo}')">
                        <div class="mt-1 small">${message.nombre_archivo}</div>
                    </div>
                `;
                break;
                
            case 'archivo':
                const fileIcon = getFileIcon(message.nombre_archivo);
                messageContent = `
                    <div class="mb-2">
                        <p class="mb-1" style="white-space: pre-wrap;">${message.contenido || ''}</p>
                        <div class="message-file">
                            <div class="d-flex align-items-center">
                                <div class="file-icon">
                                    <i class="bi ${fileIcon}"></i>
                                </div>
                                <div class="file-info">
                                    <div class="file-name">
                                        <a href="${message.url_archivo}" download="${message.nombre_archivo}" 
                                           class="text-decoration-none">
                                            ${message.nombre_archivo}
                                        </a>
                                    </div>
                                    <div class="file-size">${message.tamano_archivo || ''}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
        }
        
        return `
            <div class="mb-2 ${messageClass}">
                ${!isOwn ? `
                    <small class="d-block mb-1 text-muted ms-1" style="font-size: 0.75rem;">
                        ${message.nombre_emisor} ${message.apellido_emisor}
                    </small>
                ` : ''}
                <div class="message-bubble ${bubbleClass} d-inline-block">
                    ${messageContent}
                    <div class="message-time text-end mt-1" style="font-size: 0.7rem; opacity: 0.7;">
                        ${message.hora}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Función global para abrir imagen en modal
    window.openImageModal = function(imageUrl, fileName) {
        $('#modal-image').attr('src', imageUrl);
        $('#download-image').attr('href', imageUrl).attr('download', fileName);
        $('#imageModal').modal('show');
    };
    
    // Obtener ícono según tipo de archivo
    function getFileIcon(fileName) {
        const extension = fileName.split('.').pop().toLowerCase();
        
        switch (extension) {
            case 'pdf':
                return 'bi-file-earmark-pdf';
            case 'doc':
            case 'docx':
                return 'bi-file-earmark-word';
            case 'xls':
            case 'xlsx':
                return 'bi-file-earmark-excel';
            case 'zip':
            case 'rar':
                return 'bi-file-earmark-zip';
            case 'mp3':
                return 'bi-file-earmark-music';
            case 'mp4':
            case 'avi':
            case 'mov':
                return 'bi-file-earmark-play';
            default:
                return 'bi-file-earmark';
        }
    }
    
    // ============================================
    // 5. ENVÍO DE MENSAJES Y ARCHIVOS
    // ============================================
    
    // Enviar al hacer clic en el botón
    $('#send-btn').click(sendMessage);
    
    // Enviar con Enter
    $('#message-input').keypress(function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    async function sendMessage() {
        const chatId = currentChatId;
        const message = $('#message-input').val().trim();
        
        if (!chatId) {
            alert('Selecciona un chat primero');
            return;
        }
        
        if (selectedFiles.length === 0 && !message) {
            alert('Escribe un mensaje o adjunta un archivo');
            return;
        }
        
        // Deshabilitar botones durante el envío
        $('#send-btn').prop('disabled', true);
        $('#attach-btn').css('pointer-events', 'none');
        $('#message-input').prop('disabled', true);
        
        let fileData = null;
        
        // Subir archivos si hay
        if (selectedFiles.length > 0) {
            try {
                fileData = await uploadFiles(selectedFiles, chatId);
            } catch (error) {
                alert('Error al subir archivos: ' + error);
                resetForm();
                return;
            }
        }
        
        // Enviar mensaje
        $.ajax({
            url: 'app/ajax/send_message.php',
            type: 'POST',
            data: {
                chat_id: chatId,
                message: message,
                file_data: fileData ? JSON.stringify(fileData) : null
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log('✅ Mensaje enviado:', response);
                    
                    // Limpiar formulario
                    resetForm();
                    
                    // Recargar mensajes
                    loadMessages(chatId);
                    loadChats();
                } else {
                    alert('Error: ' + response.message);
                    resetForm();
                }
            },
            error: function(xhr, status, error) {
                alert('Error de conexión');
                resetForm();
            }
        });
    }
    
    // Subir archivos
    async function uploadFiles(files, chatId) {
        if (files.length === 0) return null;
        
        // Por simplicidad, subimos solo el primer archivo
        // Para múltiples archivos, necesitarías modificar la BD
        const file = files[0];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('chat_id', chatId);
        
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'app/ajax/upload_file.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        resolve(response);
                    } else {
                        reject(response.message);
                    }
                },
                error: function() {
                    reject('Error de conexión al subir archivo');
                }
            });
        });
    }
    
    // Resetear formulario después del envío
    function resetForm() {
        $('#message-input').val('').prop('disabled', false).focus();
        $('#send-btn').prop('disabled', true);
        $('#attach-btn').css('pointer-events', 'auto');
        $('#file-preview-container').empty().hide();
        selectedFiles = [];
    }
    
    // ============================================
    // 6. ACTUALIZACIÓN AUTOMÁTICA
    // ============================================
    
    function startMessageRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        refreshInterval = setInterval(() => {
            if (currentChatId && lastMessageId > 0) {
                checkNewMessages();
            }
        }, 5000);
    }
    
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
                    appendNewMessages(response.new_messages);
                    lastMessageId = response.new_messages[response.new_messages.length - 1].id_mensaje;
                    loadChats();
                }
            }
        });
    }
    
    function appendNewMessages(newMessages) {
        const container = $('#messages-container');
        
        newMessages.forEach(message => {
            const messageElement = createMessageElement(message);
            container.append(messageElement);
        });
        
        scrollToBottomIfNear();
    }
    
    // ============================================
    // 7. FUNCIONES UTILITARIAS
    // ============================================
    
    function scrollToBottom() {
        const container = $('#messages-container');
        container.scrollTop(container[0].scrollHeight);
    }
    
    function scrollToBottomIfNear() {
        const container = $('#messages-container');
        const distanceFromBottom = container[0].scrollHeight - container.scrollTop() - container.height();
        
        if (distanceFromBottom < 150) {
            scrollToBottom();
        }
    }
    
    function showError(message) {
        console.error(message);
        // Podrías mostrar un toast aquí
    }
    
    // ============================================
    // 8. ACTUALIZACIONES PERIÓDICAS
    // ============================================
    
    // Actualizar lista de chats cada 30 segundos
    setInterval(loadChats, 30000);
    
    // Limpiar al cerrar página
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });

    
});