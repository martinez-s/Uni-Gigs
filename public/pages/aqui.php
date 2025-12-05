<?php

session_start();

if (!isset($_SESSION['id_estudiante'])) {
    header("Location: Index.php");
    exit();
}

$success_message = $_SESSION['success'] ?? null; 

unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/styles/styles.css">
<title>Document</title>
</head>
<body>
    <h1>ingreso</h1>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        <?php if ($success_message): ?>
        
            const successMessage = "<?php echo htmlspecialchars($success_message); ?>";

            Swal.fire({
                icon: "success",
                title: "¡Exito!",
                text: successMessage, 
                showConfirmButton: true, 
            });

        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>