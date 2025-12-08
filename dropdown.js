document.addEventListener('DOMContentLoaded', function() {
    
    // Función reutilizable para llenar la lista visual <ul> a partir del <select> oculto
    function loadCustomListLogic(selectOculto, listaCustom, inputVisual) {
        listaCustom.innerHTML = ''; 
        // Obtener todas las opciones que tienen valor (excluyendo el placeholder inicial)
        const options = selectOculto.querySelectorAll('option:not([value=""])');

        options.forEach(option => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item list-group-item-action';
            listItem.textContent = option.textContent;
            listItem.setAttribute('data-value', option.value);

            listItem.addEventListener('mousedown', function(e) {
                e.preventDefault(); 
                inputVisual.value = this.textContent;
                selectOculto.value = this.getAttribute('data-value');
                listaCustom.style.display = 'none'; 
                inputVisual.blur(); 
                
                // Dispara el evento 'change' para que el selector de carrera active la carga de materias
                selectOculto.dispatchEvent(new Event('change')); 
            });
            
            listaCustom.appendChild(listItem);
        });
    }

    // Función para configurar el comportamiento del select personalizado (búsqueda, focus, blur)
    function initCustomSelect(selectId, inputId, listId) {
        const selectOculto = document.getElementById(selectId);
        const inputVisual = document.getElementById(inputId);
        const listaCustom = document.getElementById(listId);

        if (!inputVisual || !selectOculto || !listaCustom) return; 

        inputVisual.disabled = false; 

        // Manejar la búsqueda al escribir
        inputVisual.addEventListener('keyup', function() {
            const filter = this.value.toUpperCase();
            const items = listaCustom.querySelectorAll('li');
            listaCustom.style.display = 'block'; 

            items.forEach(item => {
                const text = item.textContent || item.innerText;
                if (text.toUpperCase().indexOf(filter) > -1) {
                    item.style.display = ''; 
                } else {
                    item.style.display = 'none'; 
                }
            });
        });

        // Ocultar al perder el foco
        inputVisual.addEventListener('blur', function() {
            setTimeout(() => {
                if (!listaCustom.contains(document.activeElement)) {
                    listaCustom.style.display = 'none';
                }
            }, 150); 
        });

        // Mostrar lista al ganar el foco
        inputVisual.addEventListener('focus', function() {
            listaCustom.style.display = 'block';
            const items = listaCustom.querySelectorAll('li');
            items.forEach(item => { item.style.display = ''; });
        });

        // Carga inicial de la lista visual desde el select oculto
        loadCustomListLogic(selectOculto, listaCustom, inputVisual);
    }
    
    // Función AJAX para cargar materias dinámicamente
    function fetchMaterias(idCarrera) {
        const materiaSelectOculto = document.getElementById('materia_id');
        const materiaInputVisual = document.getElementById('materia_visual_input');
        const materiaCustomList = document.getElementById('materia_custom_list');

        $.ajax({
            url: 'fetch_materias.php', 
            type: 'GET',
            data: { id_carrera: idCarrera },
            dataType: 'json',
            success: function(response) {
                // Limpiar el select oculto antes de llenarlo
                materiaSelectOculto.innerHTML = '<option value="" selected disabled>Seleccione la materia</option>';

                if (response.success && response.data.length > 0) {
                    
                    response.data.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.id_materia;
                        option.textContent = materia.nombre;
                        option.setAttribute('data-nombre', materia.nombre);
                        materiaSelectOculto.appendChild(option);
                    });
                    
                    // Recargar la lista visual usando la lógica reutilizable
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "Seleccione o busque una materia...";
                    
                } else {
                    // Si no hay datos, refrescar con lista vacía
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "No hay materias disponibles para esta carrera";
                }
            },
            error: function() {
                materiaInputVisual.placeholder = "Error al cargar las materias.";
                loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
            }
        });
    }

    // ------------------------------------------------------------------
    // INICIALIZACIÓN Y LÓGICA DE INTERDEPENDENCIA
    // ------------------------------------------------------------------

    // Inicialización de Tipo de Trabajo y Carrera
    initCustomSelect('tipo_trabajo_id', 'tipo_trabajo_visual_input', 'tipo_trabajo_custom_list');
    initCustomSelect('carrera_id', 'carrera_visual_input', 'carrera_custom_list');

    // Inicialización de Materia (solo para configurar eventos de input)
    initCustomSelect('materia_id', 'materia_visual_input', 'materia_custom_list');
    initCustomSelect('carreraS_id', 'carreraS_visual_input', 'carreraS_custom_list');


    const carreraSelectOculto = document.getElementById('carrera_id');
    const materiaSelectOculto = document.getElementById('materia_id');
    const materiaInputVisual = document.getElementById('materia_visual_input');
    const materiaCustomList = document.getElementById('materia_custom_list');


    // **1. Lógica para la CARGA INICIAL (Página de Edición)**
    const initialIdCarrera = carreraSelectOculto.value;

    if (initialIdCarrera && initialIdCarrera !== "") {
        // Habilitamos el campo de materia y disparamos la carga dinámica
        materiaInputVisual.disabled = false; 
        materiaInputVisual.placeholder = "Cargando materias...";
        
        // Llamada AJAX para cargar las materias correspondientes a la carrera precargada.
        fetchMaterias(initialIdCarrera);
        
    } else {
        // Si no hay carrera seleccionada, se mantiene deshabilitado.
        materiaInputVisual.disabled = true;
        materiaInputVisual.placeholder = "Seleccione una carrera primero...";
    }

    // **2. Lógica para el evento CHANGE (cuando el usuario cambia la carrera)**
    carreraSelectOculto.addEventListener('change', function() {
        const idCarrera = this.value;
        
        // Limpiamos el input y el select oculto de Materia para una nueva selección
        materiaInputVisual.value = '';
        materiaSelectOculto.value = ''; 
        materiaSelectOculto.innerHTML = '<option value="" selected disabled>Seleccione la materia</option>'; 

        materiaInputVisual.disabled = false; 
        materiaInputVisual.placeholder = "Cargando materias...";
        materiaCustomList.style.display = 'none'; 

        fetchMaterias(idCarrera);
    });

});

    function fetchMaterias(idCarrera) {

        $.ajax({
            url: 'fetch_materias.php', 
            type: 'GET',
            data: { id_carrera: idCarrera },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    
                    // Llenar el select oculto de Materia
                    response.data.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.id_materia;
                        option.textContent = materia.nombre;
                        option.setAttribute('data-nombre', materia.nombre);
                        materiaSelectOculto.appendChild(option);
                    });
                    
                    // Recargar la lista visual usando la lógica reutilizable
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "Seleccione o busque una materia...";
                    
                } else {
                    // Si no hay datos, refrescar con lista vacía
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "No hay materias disponibles para esta carrera";
                }
            },
            error: function() {
                materiaInputVisual.placeholder = "Error al cargar las materias.";
            }
        });
    }



