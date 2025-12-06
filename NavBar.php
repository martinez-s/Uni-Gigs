<style>
    .navbar-toggler:focus {
        outline: none !important;
        box-shadow: none !important;
        background-color: transparent !important; 
    }
</style>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        
        <a class="navbar-brand" href="Principal.html">
            <img src="img/Logo_Navbar.png" alt="Logo" width="170" height="48" class="d-inline-block align-text-center">
        </a>
        
        <button class="navbar-toggler" style="border:none;" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="material-symbols-outlined">menu</span> 
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            
            <form class="search w-100" role="search"> 
                <div class="search-overlay w-100">
                    <input class="form-control" type="search" placeholder="Busqueda" aria-label="Search"/>
                    <button class="buscar btn btn-outline-success rounded-circle" type="submit" aria-label="Buscar">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav mx-auto text-center mb-2 mb-lg-0 ms-3"> 
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Carreras</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Ingenieria de Sistemas</a></li>
                        <li><a class="dropdown-item" href="#">Diseno Grafico</a></li>
                        <li><a class="dropdown-item" href="#">Idiomas modernos</a></li>
                    </ul>
                </li>
            </ul>

            <div class="icon-group d-flex align-items-center mx-auto">
                <a class="Icon fa-lg" href="Principal.html"><span class="material-symbols-outlined">notifications</span></a>
                <a class="Icon"><span class="material-symbols-outlined">mail</span></a>
                <a class="Icon"><span class="material-symbols-outlined">school</span></a>
                <a class="Icon"><span class="material-symbols-outlined">account_circle</span></a>
            </div>
            
        </div>
    </div>
</nav>

