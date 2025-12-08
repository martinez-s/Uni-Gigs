document.addEventListener('DOMContentLoaded', function() {
    const inputImagen = document.getElementById('input-archivos-servicio');
    const btnTrigger = document.getElementById('btn-trigger-image');
    const previewContainer = document.getElementById('preview-archivos');
    const mensajeVacio = document.getElementById('mensaje-vacio-imagen');
    const form = document.getElementById('formServicio');
    const btnPublicar = document.getElementById('btn-publicar-servicio');
    let imagenSeleccionada = null;


    btnTrigger.addEventListener('click', () => {
        if (!imagenSeleccionada) {
            inputImagen.click();
        }
    });

    inputImagen.addEventListener('change', function() {
        const file = this.files[0];
        
        if (!file) return;
        

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Formato no válido', 'Solo se permiten imágenes JPG, JPEG, PNG o GIF.', 'warning');
            this.value = '';
            return;
        }
        

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


    function submitServiceForm() {

        
        const formData = new FormData(form);
        

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
    }


    btnPublicar.addEventListener('click', function(e) {
        

        if (!form.checkValidity() || document.getElementById('carrera_id').value === '' || document.getElementById('materia_id').value === '') {
            form.reportValidity();
            if (document.getElementById('carrera_id').value === '' || document.getElementById('materia_id').value === '') {
                Swal.fire('Advertencia', 'Por favor, seleccione una carrera y una materia válidas.', 'warning');
            }
            return;
        }

        btnPublicar.disabled = true;
        btnPublicar.textContent = 'Verificando...';


        fetch('check_metodos.php')
            .then(response => response.json())
            .then(data => {
                btnPublicar.disabled = false;
                btnPublicar.textContent = 'CREAR';

                if (data.success && data.has_payment_method) {

                    Swal.fire({
                        title: 'Confirmar Publicación',
                        text: "¿Estás seguro de que quieres publicar este servicio?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, Publicar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitServiceForm(); 
                        }
                    });

                } else {

                    Swal.fire({
                        title: 'Método de Pago Requerido',
                        text: "Debes registrar el método de Pago Móvil (obligatorio) antes de publicar tu servicio.",
                        icon: 'warning',
                        confirmButtonText: 'Ir a Registrar Pago'
                    }).then(() => {

                        const modalElement = document.getElementById('modalConTabs');
                        if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modal = new bootstrap.Modal(modalElement, {
                                backdrop: 'static', 
                                keyboard: false 
                            });
                            modal.show();
                        }
                    });
                }
            })
            .catch(error => {
                btnPublicar.disabled = false;
                btnPublicar.textContent = 'CREAR';
                console.error('Error checking payments:', error);
                Swal.fire('Error de Conexión', 'Ocurrió un error al verificar los pagos.', 'error');
            });
    });


});