document.addEventListener('DOMContentLoaded', function() {
 function loadCustomListLogic(selectOculto, listaCustom, inputVisual) {
        listaCustom.innerHTML = ''; 
        // Obtener todas las opciones excepto la que está vacía (Seleccione...)
        const options = selectOculto.querySelectorAll('option:not([value=""])');

        options.forEach(option => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item list-group-item-action';
            listItem.textContent = option.textContent;
            listItem.setAttribute('data-value', option.value);

            // Usa 'mousedown' para manejar la selección sin conflicto con el 'blur'
            listItem.addEventListener('mousedown', function(e) {
                e.preventDefault(); 
                inputVisual.value = this.textContent;
                selectOculto.value = this.getAttribute('data-value');
                listaCustom.style.display = 'none'; 
                inputVisual.blur(); 
                
                // Disparar el evento 'change' en el select oculto para que funcione la dependencia Carrera -> Materia
                selectOculto.dispatchEvent(new Event('change')); 
            });
            
            listaCustom.appendChild(listItem);
        });
    }

    // ----------------------------------------------------------------------
    // 🔑 Función Principal de Inicialización de Búsqueda (Reutilizable)
    // ----------------------------------------------------------------------

    function initCustomSelect(selectId, inputId, listId) {
        const selectOculto = document.getElementById(selectId);
        const inputVisual = document.getElementById(inputId);
        const listaCustom = document.getElementById(listId);

        // ✅ FIX: Asegura que el input visual esté habilitado al cargar, solucionando el problema de Carrera.
        inputVisual.disabled = false; 

        // Filtrado al escribir
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

        // Ocultar al perder el foco (con un pequeño retraso)
        inputVisual.addEventListener('blur', function() {
            setTimeout(() => {
                // Solo ocultar si el foco no va a un elemento dentro de la lista
                if (!listaCustom.contains(document.activeElement)) {
                    listaCustom.style.display = 'none';
                }
            }, 150); 
        });

        // Mostrar al ganar el foco
        inputVisual.addEventListener('focus', function() {
            listaCustom.style.display = 'block';
            const items = listaCustom.querySelectorAll('li');
            items.forEach(item => { item.style.display = ''; });
        });

        // Carga inicial de opciones
        loadCustomListLogic(selectOculto, listaCustom, inputVisual);
    }
    
    // ----------------------------------------------------------------------
    // 🚀 INICIALIZACIÓN DE COMPONENTES
    // ----------------------------------------------------------------------
    
    // 1. Inicialización de los tres selectores con búsqueda
    initCustomSelect('tipo_trabajo_id', 'tipo_trabajo_visual_input', 'tipo_trabajo_custom_list');
    initCustomSelect('carrera_id', 'carrera_visual_input', 'carrera_custom_list');
    
    // Materia se inicializa, pero su contenido será cargado por AJAX
    // Es importante inicializarla para que tenga la funcionalidad de keyup/blur/focus
    initCustomSelect('materia_id', 'materia_visual_input', 'materia_custom_list');

    // ----------------------------------------------------------------------
    // 🔗 LÓGICA DE DEPENDENCIA CARRERA -> MATERIA
    // ----------------------------------------------------------------------
    
    const carreraSelectOculto = document.getElementById('carrera_id');
    const materiaSelectOculto = document.getElementById('materia_id');
    const materiaInputVisual = document.getElementById('materia_visual_input');
    const materiaCustomList = document.getElementById('materia_custom_list');
    
    // Estado inicial: Deshabilitar la materia y dar un placeholder instructivo
    materiaInputVisual.disabled = true;
    materiaInputVisual.placeholder = "Seleccione una carrera primero...";


    // 🔑 Evento de cambio en Carrera (disparado por el 'mousedown' en loadCustomListLogic)
    carreraSelectOculto.addEventListener('change', function() {
        const idCarrera = this.value;
        
        // 1. Resetear y preparar el campo Materia
        materiaInputVisual.value = '';
        materiaSelectOculto.innerHTML = '<option value="" selected disabled>Seleccione la materia</option>'; // Vaciar select oculto
        materiaInputVisual.disabled = false; 
        materiaInputVisual.placeholder = "Cargando materias...";
        materiaCustomList.style.display = 'none'; // Asegurar que la lista esté cerrada

        // 2. Hacer la solicitud AJAX
        fetchMaterias(idCarrera);
    });
    
    // 🔑 Función para obtener las materias vía AJAX
    function fetchMaterias(idCarrera) {
        // Se asume que jQuery está disponible (incluido en tu HTML)
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
});