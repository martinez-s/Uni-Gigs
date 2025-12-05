
document.addEventListener('DOMContentLoaded', () => {
    const inputImagenPerfil = document.getElementById('input-imagen-perfil');
    const circuloImagenPerfil = document.querySelector('.circulo-imagen-perfil');
    const textoPlaceholder = circuloImagenPerfil.querySelector('.texto-placeholder'); 


    if (inputImagenPerfil && circuloImagenPerfil) {
        inputImagenPerfil.addEventListener('change', function() {

            if (this.files && this.files[0]) { 
                const reader = new FileReader(); 

                reader.onload = function(e) {
                    circuloImagenPerfil.style.backgroundImage = `url(${e.target.result})`;
                    circuloImagenPerfil.style.backgroundSize = 'cover'; 
                    circuloImagenPerfil.style.backgroundPosition = 'center'; 
                    circuloImagenPerfil.style.backgroundRepeat = 'no-repeat'; 

                    if (textoPlaceholder) {
                        textoPlaceholder.style.display = 'none';
                    }
                };


                reader.readAsDataURL(this.files[0]);
            } else {

                circuloImagenPerfil.style.backgroundImage = 'none';
                if (textoPlaceholder) {
                    textoPlaceholder.style.display = 'block';
                }
            }
        });
    }
});


document.addEventListener('DOMContentLoaded', () => {
    const inputImagencarnet = document.getElementById('input-imagen-carnet');
    const ImagenCarnet = document.querySelector('.imagen_carnet');
    const textoPlaceholder = ImagenCarnet.querySelector('.texto-placeholder'); 


    if (inputImagencarnet && ImagenCarnet) {
        inputImagencarnet.addEventListener('change', function() {

            if (this.files && this.files[0]) { 
                const reader = new FileReader(); 

                reader.onload = function(e) {
                    ImagenCarnet.style.backgroundImage = `url(${e.target.result})`;
                    ImagenCarnet.style.backgroundSize = 'cover'; 
                    ImagenCarnet.style.backgroundPosition = 'center'; 
                    ImagenCarnet.style.backgroundRepeat = 'no-repeat'; 

                    if (textoPlaceholder) {
                        textoPlaceholder.style.display = 'none';
                    }
                };


                reader.readAsDataURL(this.files[0]);
            } else {

                ImagenCarnet.style.backgroundImage = 'none';
                if (textoPlaceholder) {
                    textoPlaceholder.style.display = 'block';
                }
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const inputArchivos = document.getElementById('input-archivos-request');
    const previewContainer = document.getElementById('preview-archivos');
    const mensajeVacio = document.getElementById('mensaje-vacio');
    const labelBoton = document.querySelector('label[for="input-archivos-request"]');
    const MAX_FILES = 3;

    // Array para almacenar y gestionar los archivos seleccionados
    let archivosAlmacenados = [];

    // 1. Escuchador del evento de cambio del input
    inputArchivos.addEventListener('change', function() {
        // Obtener los archivos recién seleccionados
        const nuevosArchivos = Array.from(this.files);
        
        // 2. Comprobar el límite máximo
        if (archivosAlmacenados.length + nuevosArchivos.length > MAX_FILES) {
            alert(`Solo puedes subir un total de ${MAX_FILES} archivos. Ya tienes ${archivosAlmacenados.length} seleccionados.`);
            this.value = ''; 
            return;
        }

        // 3. Agregar los nuevos archivos al array de almacenamiento
        archivosAlmacenados.push(...nuevosArchivos);
        
        // 4. Actualizar la visualización y el input real
        actualizarTodo();
        
        // 5. Limpiar el valor del input para que el evento 'change' se active de nuevo
        this.value = '';
    });

    // Función principal para renderizar miniaturas y actualizar el input
    function actualizarTodo() {
        mostrarMiniaturas(archivosAlmacenados);
        actualizarInputReal();
    }

    // Función para actualizar el input oculto con la lista de archivos gestionada
    function actualizarInputReal() {
        const dataTransfer = new DataTransfer();
        archivosAlmacenados.forEach(file => {
            dataTransfer.items.add(file);
        });
        // Asignamos el FileList real al input para que se envíe con el formulario
        inputArchivos.files = dataTransfer.files;

        // Actualizar el texto del botón
        labelBoton.textContent = archivosAlmacenados.length >= MAX_FILES 
            ? 'Límite Alcanzado (3)' 
            : `Seleccionar Archivos (${archivosAlmacenados.length}/${MAX_FILES})`;
    }


    // --- Funciones de Previsualización (Miniaturas y Eliminación) ---

    function mostrarMiniaturas(files) {
        previewContainer.innerHTML = ''; // Limpiar previsualizaciones
        
        if (files.length === 0) {
            previewContainer.appendChild(mensajeVacio);
            return;
        }

        files.forEach((file, index) => {
            const divPreview = crearElementoMiniatura(file, index);
            previewContainer.appendChild(divPreview);
        });
    }

    function crearElementoMiniatura(file, index) {
        const divPreview = document.createElement('div');
        divPreview.style.textAlign = 'center';
        divPreview.style.maxWidth = '90px'; 
        divPreview.style.position = 'relative'; // Para posicionar el botón de eliminar
        
        // ------------------ Botón de Eliminar ------------------
        const btnEliminar = document.createElement('span');
        btnEliminar.textContent = '✖';
        btnEliminar.className = 'btn-eliminar-archivo'; // Clase para estilos CSS si es necesario
        btnEliminar.style.position = 'absolute';
        btnEliminar.style.top = '-5px';
        btnEliminar.style.right = '-5px';
        btnEliminar.style.cursor = 'pointer';
        btnEliminar.style.backgroundColor = 'gray';
        btnEliminar.style.color = 'white';
        btnEliminar.style.borderRadius = '50%';
        btnEliminar.style.width = '20px';
        btnEliminar.style.height = '20px';
        btnEliminar.style.lineHeight = '20px';
        btnEliminar.style.fontSize = '12px';
        
        // Manejador de evento para eliminar el archivo
        btnEliminar.onclick = () => {
            eliminarArchivo(index);
        };
        divPreview.appendChild(btnEliminar);
        // --------------------------------------------------------

        // ... Lógica de la miniatura (misma que antes) ...
        const mediaBox = document.createElement('div');
        mediaBox.style.width = '75px';
        mediaBox.style.height = '75px';
        mediaBox.style.backgroundColor = '#eee';
        mediaBox.style.borderRadius = '5px';
        mediaBox.style.border = '1px solid #ccc';
        mediaBox.style.marginBottom = '5px';

        const spanNombre = document.createElement('span');
        const nombreCorto = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
        spanNombre.textContent = nombreCorto;
        spanNombre.title = file.name; 
        spanNombre.style.fontSize = '0.7em';
        spanNombre.style.display = 'block';

        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '5px';
            
            const reader = new FileReader();
            reader.onload = (e) => { img.src = e.target.result; };
            reader.readAsDataURL(file);
            mediaBox.appendChild(img);
        } else {
            const icono = document.createElement('div');
            icono.textContent = obtenerIcono(file.name);
            icono.style.fontSize = '30px';
            icono.style.lineHeight = '75px';
            icono.style.textAlign = 'center';
            mediaBox.appendChild(icono);
        }
        
        divPreview.appendChild(mediaBox);
        divPreview.appendChild(spanNombre);
        
        return divPreview;
    }

    // Nueva función para eliminar un archivo del array
    function eliminarArchivo(index) {
        // Eliminar 1 elemento a partir del índice dado
        archivosAlmacenados.splice(index, 1);
        // Actualizar la vista y el input real
        actualizarTodo();
    }

    function obtenerIcono(fileName) {
        const extension = fileName.split('.').pop().toLowerCase();
        switch(extension) {
            case 'pdf': return '📄'; 
            case 'doc':
            case 'docx': return '📝'; 
            case 'zip':
            case 'rar': return '📦'; 
            case 'txt': return '🗒️';
            default: return ' '; 
        }
    }
});