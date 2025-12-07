function seleccionarCarrera(element) {
    // 1. Obtener el ID de la carrera del atributo data-carrera-id
    const carreraId = element.getAttribute('data-carrera-id');

    // 2. Establecer el valor en el input oculto del formulario
    document.getElementById('carreraIdInput').value = carreraId;

    // 3. Enviar el formulario POST
    document.getElementById('filtroCarreraForm').submit();
}

    // 1. Espera a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        const inputBusqueda = document.getElementById('inputBusqueda');

        // 2. Verifica si el input tiene texto (solo si el servidor no lo vació)
        // Aunque si el método es POST, usualmente se mantiene el valor.
        if (inputBusqueda.value.length > 0) {
            // 3. Limpia el campo de búsqueda inmediatamente
            inputBusqueda.value = '';
        }

        // Opcional: Si necesitas forzar el vaciado en todos los casos:
        // inputBusqueda.value = ''; 
    });

    function goToPage(pageNumber) {
        // Verifica que el número de página sea válido
        if (pageNumber < 1) return; 

        // Actualiza el campo oculto 'page' con el número de página deseado
        document.getElementById('page-input').value = pageNumber;
        
        // Envía el formulario de paginación
        document.getElementById('pagination-form').submit();
    }
