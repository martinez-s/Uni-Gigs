
document.addEventListener('DOMContentLoaded', () => {
    const inputImagenPerfil = document.getElementById('input-imagen-perfil');
    const circuloImagenPerfil = document.querySelector('.circulo-imagen-perfil');
    const textoPlaceholder = circuloImagenPerfil.querySelector('.texto-placeholder'); 


    if (inputImagenPerfil && circuloImagenPerfil) {
        inputImagenPerfil.addEventListener('change', function() {

            if (this.files && this.files[0]) { 
                const reader = new FileReader(); 

                reader.onload = function(e) {
                    circuloImagenPerfil.style.backgroundImage = `url(${e.target.result})`;
                    circuloImagenPerfil.style.backgroundSize = 'cover'; 
                    circuloImagenPerfil.style.backgroundPosition = 'center'; 
                    circuloImagenPerfil.style.backgroundRepeat = 'no-repeat'; 

                    if (textoPlaceholder) {
                        textoPlaceholder.style.display = 'none';
                    }
                };


                reader.readAsDataURL(this.files[0]);
            } else {

                circuloImagenPerfil.style.backgroundImage = 'none';
                if (textoPlaceholder) {
                    textoPlaceholder.style.display = 'block';
                }
            }
        });
    }
});


document.addEventListener('DOMContentLoaded', () => {
    const inputImagencarnet = document.getElementById('input-imagen-carnet');
    const ImagenCarnet = document.querySelector('.imagen_carnet');
    const textoPlaceholder = ImagenCarnet.querySelector('.texto-placeholder'); 


    if (inputImagencarnet && ImagenCarnet) {
        inputImagencarnet.addEventListener('change', function() {

            if (this.files && this.files[0]) { 
                const reader = new FileReader(); 

                reader.onload = function(e) {
                    ImagenCarnet.style.backgroundImage = `url(${e.target.result})`;
                    ImagenCarnet.style.backgroundSize = 'cover'; 
                    ImagenCarnet.style.backgroundPosition = 'center'; 
                    ImagenCarnet.style.backgroundRepeat = 'no-repeat'; 

                    if (textoPlaceholder) {
                        textoPlaceholder.style.display = 'none';
                    }
                };


                reader.readAsDataURL(this.files[0]);
            } else {

                ImagenCarnet.style.backgroundImage = 'none';
                if (textoPlaceholder) {
                    textoPlaceholder.style.display = 'block';
                }
            }
        });
    }
});