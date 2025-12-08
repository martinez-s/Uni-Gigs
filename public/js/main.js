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

  function aplicarLimiteDeTexto() {
        // 1. Recortar títulos
        document.querySelectorAll('h5.card-title').forEach(element => {
            // Obtenemos el límite del atributo data-limite
            const limite = parseInt(element.dataset.limite);
            const textoCompleto = element.textContent.trim();

            if (limite && textoCompleto.length > limite) {
                // JS substring y length manejan UTF-8 correctamente
                element.textContent = textoCompleto.substring(0, limite) + '...';
            }
        });

        // 2. Recortar descripciones
        document.querySelectorAll('p.card-text').forEach(element => {
            // Obtenemos el límite del atributo data-limite
            const limite = parseInt(element.dataset.limite);
            const textoCompleto = element.textContent.trim();

            if (limite && textoCompleto.length > limite) {
                // JS substring y length manejan UTF-8 correctamente
                element.textContent = textoCompleto.substring(0, limite) + '...';
            }
        });
    }

    // Ejecutamos la función una vez que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', aplicarLimiteDeTexto);
