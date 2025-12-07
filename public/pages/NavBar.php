<style>
    .navbar-toggler:focus {
        outline: none !important;
        box-shadow: none !important;
        background-color: transparent !important; 
    }
</style>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        
        <a class="navbar-brand" href="principal.php">
            <img src="../img/Logo_Navbar.png" alt="Logo" width="170" height="48" class="d-inline-block align-text-center">
        </a>
        
        <button class="navbar-toggler" style="border:none;" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="material-symbols-outlined">menu</span> 
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            
            <form class="search w-100" role="search" method="POST" action="busqueda.php"> 
                <div class="search-overlay w-100">
                    <input class="form-control" 
                        type="search" 
                        placeholder="Busqueda" 
                        aria-label="Search"
                        name="q_post"
                        value="<?php echo isset($_POST['q_post']) ? htmlspecialchars($_POST['q_post']) : ''; ?>"
                    />
                    
                    <button class="buscar btn btn-outline-success rounded-circle" type="submit" aria-label="Buscar">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav mx-auto text-center mb-2 mb-lg-0 ms-3">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Carreras</a>
                    
                    <ul class="dropdown-menu">
                        
                        <li>
                            <form method="POST" action="principal.php" id="form-carrera-all" style="display:none;">
                                </form>
                            <a class="dropdown-item" href="#" onclick="document.getElementById('form-carrera-all').submit(); return false;">
                                <strong>Todas las Carreras</strong>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li> <?php
                    // ... (Tu código PHP de consulta y bucle a continuación)
                    $sql_carreras = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera ASC";
                    $resultado_carreras = $mysqli->query($sql_carreras);

                    if ($resultado_carreras && $resultado_carreras->num_rows > 0) {
                        while ($carrera = $resultado_carreras->fetch_assoc()) {
                            $nombreCarrera = htmlspecialchars($carrera['nombre_carrera']);
                            $id = htmlspecialchars($carrera['id_carrera']);
                    ?>
                                <li>
                                    <form method="POST" action="principal.php" id="form-carrera-<?php echo $id; ?>" style="display:none;">
                                        <input type="hidden" name="id_carrera_filtro" value="<?php echo $id; ?>">
                                    </form>
                                    <a class="dropdown-item" href="#" onclick="document.getElementById('form-carrera-<?php echo $id; ?>').submit(); return false;">
                                        <?php echo $nombreCarrera; ?>
                                    </a>
                                </li>
                    <?php
                        }
                        $resultado_carreras->free();
                    } else {
                    ?>
                        <li><a class="dropdown-item disabled" href="#">No hay carreras disponibles</a></li>
                    <?php
                    }
                    ?>
                    </ul>
                </li>
            </ul>

            <div class="icon-group d-flex align-items-center mx-auto">
                <a class="Icon fa-lg" href="Principal.html"><span class="material-symbols-outlined">notifications</span></a>
                <a class="Icon" href="../../mensajeria.php"><span class="material-symbols-outlined">mail</span></a>
                <a class="Icon"><span class="material-symbols-outlined">school</span></a>
                <a class="Icon"><span class="material-symbols-outlined">account_circle</span></a>
            </div>
            
        </div>
    </div>
</nav>

