document.addEventListener('DOMContentLoaded', function() {
        const inputArchivos = document.getElementById('input-archivos-request');
        const btnTrigger = document.getElementById('btn-trigger-file');
        const previewContainer = document.getElementById('preview-archivos');
        const mensajeVacio = document.getElementById('mensaje-vacio');
        const form = document.getElementById('formRequest');
        const MAX_FILES = 3;

        let archivosAlmacenados = [];

        btnTrigger.addEventListener('click', () => inputArchivos.click());

        inputArchivos.addEventListener('change', function() {
            const nuevosArchivos = Array.from(this.files);
            
            if (archivosAlmacenados.length + nuevosArchivos.length > MAX_FILES) {
                Swal.fire('Límite excedido', `Solo puedes subir un total de ${MAX_FILES} archivos.`, 'warning');
                this.value = '';
                return;
            }

            archivosAlmacenados.push(...nuevosArchivos);
            mostrarMiniaturas();
            this.value = '';
        });

        function mostrarMiniaturas() {
            previewContainer.innerHTML = '';
            
            if (archivosAlmacenados.length === 0) {
                previewContainer.appendChild(mensajeVacio);
                btnTrigger.innerHTML = `<i class="bi bi-cloud-arrow-up-fill"></i> Seleccionar Archivos (Máx ${MAX_FILES})`;
                return;
            }

            btnTrigger.innerHTML = archivosAlmacenados.length >= MAX_FILES 
                ? `<i class="bi bi-check-circle-fill"></i> Límite Alcanzado (${archivosAlmacenados.length})` 
                : `<i class="bi bi-cloud-arrow-up-fill"></i> Seleccionar Archivos (${archivosAlmacenados.length}/${MAX_FILES})`;

            archivosAlmacenados.forEach((file, index) => {
                const divPreview = document.createElement('div');
                divPreview.className = 'preview-item';
                
                const btnEliminar = document.createElement('span');
                btnEliminar.innerHTML = '&#10005;';
                btnEliminar.className = 'btn-eliminar-archivo';
                btnEliminar.onclick = () => {
                    archivosAlmacenados.splice(index, 1);
                    mostrarMiniaturas();
                };
                
                const mediaBox = document.createElement('div');
                mediaBox.className = 'preview-box';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => { mediaBox.style.backgroundImage = `url(${e.target.result})`; };
                    reader.readAsDataURL(file);
                } else {
                    mediaBox.textContent = obtenerIcono(file.name);
                }
                
                const spanNombre = document.createElement('span');
                spanNombre.className = 'file-name-span';
                spanNombre.textContent = file.name;

                divPreview.appendChild(btnEliminar);
                divPreview.appendChild(mediaBox);
                divPreview.appendChild(spanNombre);
                previewContainer.appendChild(divPreview);
            });
        }

        function obtenerIcono(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            if (['pdf'].includes(ext)) return '📄';
            if (['doc', 'docx'].includes(ext)) return '📝';
            return '📎';
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.delete('input-archivos-request'); 

            archivosAlmacenados.forEach(file => {
                formData.append('archivos-request[]', file);
            });

            Swal.fire({
                title: 'Publicando...',
                text: 'Subiendo archivos e información',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'public/pages/principal.php';
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