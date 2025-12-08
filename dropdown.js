document.addEventListener('DOMContentLoaded', function() {
    
    function loadCustomListLogic(selectOculto, listaCustom, inputVisual) {
        listaCustom.innerHTML = ''; 
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
                
                selectOculto.dispatchEvent(new Event('change')); 
            });
            
            listaCustom.appendChild(listItem);
        });
    }

    function initCustomSelect(selectId, inputId, listId) {
        const selectOculto = document.getElementById(selectId);
        const inputVisual = document.getElementById(inputId);
        const listaCustom = document.getElementById(listId);

        if (!inputVisual || !selectOculto || !listaCustom) {
            console.error(`Error: No se encontraron los elementos para el ID: ${selectId}. Verifique el HTML.`);
            return; 
        } 

        inputVisual.disabled = false; 
        loadCustomListLogic(selectOculto, listaCustom, inputVisual);
        
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

        inputVisual.addEventListener('blur', function() {
            setTimeout(() => {
                if (!listaCustom.contains(document.activeElement)) {
                    listaCustom.style.display = 'none';
                }
            }, 150); 
        });

        inputVisual.addEventListener('focus', function() {
            listaCustom.style.display = 'block';
            const items = listaCustom.querySelectorAll('li');
            items.forEach(item => { item.style.display = ''; });
        });

        
    }

    function fetchMaterias(idCarrera) {
        const materiaSelectOculto = document.getElementById('materia_id');
        const materiaInputVisual = document.getElementById('materia_visual_input');
        const materiaCustomList = document.getElementById('materia_custom_list');

        materiaSelectOculto.innerHTML = '<option value="" selected disabled>Seleccione la materia</option>';

        $.ajax({
            url: 'fetch_materias.php', 
            type: 'GET',
            data: { id_carrera: idCarrera },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    response.data.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.id_materia;
                        option.textContent = materia.nombre;
                        option.setAttribute('data-nombre', materia.nombre);
                        materiaSelectOculto.appendChild(option);
                    });
                    
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "Seleccione o busque una materia...";
                    materiaInputVisual.disabled = false; 
                } else {
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "No hay materias disponibles para esta carrera";
                    materiaInputVisual.disabled = true; 
                }
            },
            error: function() {
                materiaInputVisual.placeholder = "Error al cargar las materias.";
                materiaInputVisual.disabled = true;
                loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
            }
        });
    }

    initCustomSelect('tipo_trabajo_id', 'tipo_trabajo_visual_input', 'tipo_trabajo_custom_list');

    initCustomSelect('carrera_id', 'carrera_visual_input', 'carrera_custom_list');
    
    initCustomSelect('banco_id', 'banco_visual_input', 'banco_custom_list');

    initCustomSelect('materia_id', 'materia_visual_input', 'materia_custom_list');
    initCustomSelect('carreraS_id', 'carreraS_visual_input', 'carreraS_custom_list');

    initCustomSelect('banco2_id', 'banco2_visual_input', 'banco2_custom_list');

    

    const carreraSelectOculto = document.getElementById('carrera_id');
    const materiaSelectOculto = document.getElementById('materia_id');
    const materiaInputVisual = document.getElementById('materia_visual_input');
    const materiaCustomList = document.getElementById('materia_custom_list');


    const initialIdCarrera = carreraSelectOculto ? carreraSelectOculto.value : null;

    if (initialIdCarrera && initialIdCarrera !== "") {
        if (materiaInputVisual) {
            materiaInputVisual.disabled = false; 
            materiaInputVisual.placeholder = "Cargando materias...";
        }
        fetchMaterias(initialIdCarrera);
    } else if (materiaInputVisual) {

        materiaInputVisual.disabled = true;
        materiaInputVisual.placeholder = "Seleccione una carrera primero...";
    }

    if (carreraSelectOculto) {
        carreraSelectOculto.addEventListener('change', function() {
            const idCarrera = this.value;
            
            if (materiaInputVisual) {
                materiaInputVisual.value = '';
                materiaSelectOculto.value = ''; 
                materiaSelectOculto.innerHTML = '<option value="" selected disabled>Seleccione la materia</option>'; 

                materiaInputVisual.disabled = true; 
                materiaInputVisual.placeholder = "Cargando materias...";
                materiaCustomList.style.display = 'none'; 
            }

            fetchMaterias(idCarrera);
        });
    }
});

    function fetchMaterias(idCarrera) {

        $.ajax({
            url: 'fetch_materias.php', 
            type: 'GET',
            data: { id_carrera: idCarrera },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    
                    response.data.forEach(materia => {
                        const option = document.createElement('option');
                        option.value = materia.id_materia;
                        option.textContent = materia.nombre;
                        option.setAttribute('data-nombre', materia.nombre);
                        materiaSelectOculto.appendChild(option);
                    });
                    
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "Seleccione o busque una materia...";
                    
                } else {
                    loadCustomListLogic(materiaSelectOculto, materiaCustomList, materiaInputVisual);
                    materiaInputVisual.placeholder = "No hay materias disponibles para esta carrera";
                }
            },
            error: function() {
                materiaInputVisual.placeholder = "Error al cargar las materias.";
            }
        });
    }



