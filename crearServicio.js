document.addEventListener('DOMContentLoaded', function() {
    const inputImagen = document.getElementById('input-archivos-servicio');
    const btnTrigger = document.getElementById('btn-trigger-image');
    const previewContainer = document.getElementById('preview-archivos');
    const mensajeVacio = document.getElementById('mensaje-vacio-imagen');
    const form = document.getElementById('formServicio');

    let imagenSeleccionada = null;

    btnTrigger.addEventListener('click', () => {
        if (!imagenSeleccionada) {
            inputImagen.click();
        }
    });

    inputImagen.addEventListener('change', function() {
        const file = this.files[0];
        
        if (!file) return;
        
        // Validar que sea una imagen
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Formato no válido', 'Solo se permiten imágenes JPG, JPEG, PNG o GIF.', 'warning');
            this.value = '';
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Archivo muy grande', 'La imagen no debe superar los 5MB.', 'warning');
            this.value = '';
            return;
        }
        
        imagenSeleccionada = file;
        mostrarMiniatura();
        this.value = '';
    });

    function mostrarMiniatura() {
        previewContainer.innerHTML = '';
        
        if (!imagenSeleccionada) {
            previewContainer.appendChild(mensajeVacio);
            btnTrigger.innerHTML = `<i class="bi bi-cloud-arrow-up-fill"></i> Seleccionar Imagen (Opcional)`;
            return;
        }

        btnTrigger.innerHTML = `<i class="bi bi-check-circle-fill"></i> Imagen Seleccionada`;
        
        const divPreview = document.createElement('div');
        divPreview.className = 'preview-item';
        
        const btnEliminar = document.createElement('span');
        btnEliminar.innerHTML = '&#10005;';
        btnEliminar.className = 'btn-eliminar-archivo';
        btnEliminar.onclick = () => {
            imagenSeleccionada = null;
            mostrarMiniatura();
        };
        
        const imagenBox = document.createElement('div');
        imagenBox.className = 'preview-box';
        
        const reader = new FileReader();
        reader.onload = (e) => { 
            imagenBox.style.backgroundImage = `url(${e.target.result})`;
            imagenBox.style.backgroundSize = 'cover';
            imagenBox.style.backgroundPosition = 'center';
        };
        reader.readAsDataURL(imagenSeleccionada);
        
        const spanNombre = document.createElement('span');
        spanNombre.className = 'file-name-span';
        spanNombre.textContent = imagenSeleccionada.name;

        divPreview.appendChild(btnEliminar);
        divPreview.appendChild(imagenBox);
        divPreview.appendChild(spanNombre);
        previewContainer.appendChild(divPreview);
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Agregar la imagen si existe (mismo nombre que en servicio.php)
        if (imagenSeleccionada) {
            formData.append('imagen-servicio', imagenSeleccionada);
        }

        Swal.fire({
            title: 'Publicando...',
            text: 'Subiendo información del servicio',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('servicio.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = data.redirect || 'index.php';
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Hubo un problema de conexión', 'error');
        });
    });
});