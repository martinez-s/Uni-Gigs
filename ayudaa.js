document.addEventListener('DOMContentLoaded', function() {
    
    const carreraSSelectOculto = document.getElementById('carreraS_id');
    const carreraSInputVisual = document.getElementById('carreraS_visual_input');
    const carreraSCustomList = document.getElementById('carreraS_custom_list');
    

    const materiaSelectFiltro = document.getElementById('id_materia_filtro'); 


    if (!carreraSSelectOculto || !materiaSelectFiltro) return;

    function loadCustomListLogic(selectOculto, listaCustom, inputVisual) {
        listaCustom.innerHTML = ''; 

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
                
                selectOculto.dispatchEvent(new Event('change')); 
            });
            
            listaCustom.appendChild(listItem);
        });
    }

    function initCustomSelect(selectId, inputId, listId) {
        const selectOculto = document.getElementById(selectId);
        const inputVisual = document.getElementById(inputId);
        const listaCustom = document.getElementById(listId);

        if (!inputVisual || !selectOculto || !listaCustom) return; 

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

    function fetchMaterias(idCarrera, selectElement) {

        selectElement.innerHTML = '<option value="0" selected>Todas las Materias</option>';
        selectElement.disabled = true; 
        
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
                
            },
            error: function() {
                selectElement.disabled = false;
                console.error("Error al cargar las materias.");
            }
        });
    }

    initCustomSelect('carreraS_id', 'carreraS_visual_input', 'carreraS_custom_list');

    const initialIdCarrera = carreraSSelectOculto.value;

    if (initialIdCarrera && initialIdCarrera !== "0" && initialIdCarrera !== "") {
        fetchMaterias(initialIdCarrera, materiaSelectFiltro);
    } else {
        materiaSelectFiltro.innerHTML = '<option value="0" selected>Todas las Materias</option>';
        materiaSelectFiltro.disabled = true;
    }

    carreraSSelectOculto.addEventListener('change', function() {
        const idCarrera = this.value;
        

        if (idCarrera === "0" || idCarrera === "") {
            materiaSelectFiltro.innerHTML = '<option value="0" selected>Todas las Materias</option>';
            materiaSelectFiltro.disabled = true;
        } else {
            materiaSelectFiltro.disabled = false;
            fetchMaterias(idCarrera, materiaSelectFiltro);
        }
    });

});