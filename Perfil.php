<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estructura de Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container pt-5">
    <div class="d-flex flex-column flex-md-row align-items-start gap-4">
        <div class="position-relative align-self-center-top">
            <div class="rounded-circle avatar-box"></div>
            <div class="position-absolute top-0 start-100 translate-middle rounded-circle badge-notification d-flex justify-content-center align-items-center">
                !
            </div>
        </div>
        <div class="flex-grow-1">
            <div class="row row-cols-5 g-2 mb-3">
                <div class="col-6 col-md-6 col-lg-4"><div class="label-box">NOMBRE</div></div>
                <div class="col-6 col-md-6 col-lg-4"><div class="label-box">APELLIDO</div></div>
                <div class="col-6 col-md-6 col-lg-4"><div class="label-box">RATING</div></div>
                <div class="col-6 col-md-6 col-lg-6"><div class="label-box">% COMPLETACION</div></div>
                <div class="col-12 col-md-12 col-lg-6"><div class="label-box">INGENIERIA DE SISTEMAS</div></div>
            </div>    
            <div class="row">
                <div class="col-12">
                    <div class="description-box">
                        DESCRIPCION
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>