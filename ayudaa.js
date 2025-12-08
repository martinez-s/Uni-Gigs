document.addEventListener('DOMContentLoaded', function() {
    
    // --- REFERENCIAS A ELEMENTOS DEL FILTRO ---
    
    // Selectores de Carrera (Custom Dropdown)
    const carreraSSelectOculto = document.getElementById('carreraS_id');
    const carreraSInputVisual = document.getElementById('carreraS_visual_input');
    const carreraSCustomList = document.getElementById('carreraS_custom_list');
    
    // Selector de Materia (Standard/Select2 Dropdown) - ESTE ES EL CORREGIDO
    const materiaSelectFiltro = document.getElementById('id_materia_filtro'); 

    // Si los elementos críticos no existen, terminamos.
    if (!carreraSSelectOculto || !materiaSelectFiltro) return;


    // --- FUNCIONES REUTILIZABLES ---

    // Función para llenar la lista visual <ul> a partir del <select> oculto (USADA SOLO POR CARRERA)
    function loadCustomListLogic(selectOculto, listaCustom, inputVisual) {
        listaCustom.innerHTML = ''; 
        // Solo obtener las opciones que tienen un valor definido
        const options = selectOculto.querySelectorAll('option:not([value=""]):not([value="0"])');

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
                
                // DISPARA el evento 'change' en el select oculto de Carrera
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

        // Carga inicial de la lista visual desde el select oculto
        loadCustomListLogic(selectOculto, listaCustom, inputVisual);
        
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
    }
    
    // Función AJAX para cargar materias dinámicamente, apuntando a id_materia_filtro
    function fetchMaterias(idCarrera, selectElement) {
        // Limpiar el select de Materia y añadir la opción por defecto
        selectElement.innerHTML = '<option value="0" selected>Todas las Materias</option>';
        selectElement.disabled = true; // Deshabilitar temporalmente
        
        // Llamada AJAX (Requiere jQuery)
        $.ajax({
            url: 'fetch_materias.php', 
            type: 'GET',
            data: { id_carrera: idCarrera },
            dataType: 'json',
            success: function(response) {
                selectElement.disabled = false; // Habilitar
                
                if (response.success && response.data.length > 0) {
                    response.data.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.id_materia;
                        option.textContent = materia.nombre;
                        selectElement.appendChild(option);
                    });
                }
                
                // Si usas Select2 en id_materia_filtro, descomenta la línea de abajo para refrescarlo visualmente:
                // $('#id_materia_filtro').trigger('change.select2'); 
            },
            error: function() {
                selectElement.disabled = false;
                console.error("Error al cargar las materias.");
            }
        });
    }

    // ------------------------------------------------------------------
    // INICIALIZACIÓN Y LÓGICA DE INTERDEPENDENCIA (Carrera -> Materia)
    // ------------------------------------------------------------------

    // Inicialización del selector de Carrera (Filtro)
    initCustomSelect('carreraS_id', 'carreraS_visual_input', 'carreraS_custom_list');

    // **1. Lógica para la CARGA INICIAL (Si hay una carrera precargada)**
    const initialIdCarrera = carreraSSelectOculto.value;

    if (initialIdCarrera && initialIdCarrera !== "0" && initialIdCarrera !== "") {
        fetchMaterias(initialIdCarrera, materiaSelectFiltro);
    } else {
         // Si es "Todas las Carreras" o no hay selección, inicializar Materia con solo "Todas" y deshabilitar
         materiaSelectFiltro.innerHTML = '<option value="0" selected>Todas las Materias</option>';
         materiaSelectFiltro.disabled = true;
    }

    // **2. Lógica para el evento CHANGE (cuando el usuario cambia la carrera)**
    carreraSSelectOculto.addEventListener('change', function() {
        const idCarrera = this.value;
        
        // Limpiar el input visual de la Carrera, ya que la lógica custom hace esto
        // Aquí solo nos enfocamos en el select de Materia.

        if (idCarrera === "0" || idCarrera === "") {
            // Si selecciona "Todas las Carreras" (valor 0), limpiar Materia
            materiaSelectFiltro.innerHTML = '<option value="0" selected>Todas las Materias</option>';
            materiaSelectFiltro.disabled = true;
        } else {
            // Si selecciona una carrera válida, cargar materias
            materiaSelectFiltro.disabled = false;
            fetchMaterias(idCarrera, materiaSelectFiltro);
        }
    });

});