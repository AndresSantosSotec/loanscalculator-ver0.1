<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_calculadora");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Variables para filtros
$tipo_credito = isset($_GET['tipo_credito']) ? $_GET['tipo_credito'] : '';
$tipo_cuota = isset($_GET['tipo_cuota']) ? $_GET['tipo_cuota'] : '';
$fecha_solicitud = isset($_GET['fecha_solicitud']) ? $_GET['fecha_solicitud'] : '';
$nombre_cliente = isset($_GET['nombre_cliente']) ? $_GET['nombre_cliente'] : '';

// Construir la consulta con los filtros aplicados
$query = "SELECT * FROM creditos WHERE 1=1";

if ($tipo_credito != '') {
    $query .= " AND tipo_credito = '$tipo_credito'";
}

if ($tipo_cuota != '') {
    $query .= " AND tipo_cuota = '$tipo_cuota'";
}

if ($fecha_solicitud != '') {
    $query .= " AND fecha_solicitud = '$fecha_solicitud'";
}

if ($nombre_cliente != '') {
    $query .= " AND nombre_cliente LIKE '%$nombre_cliente%'";
}

$result = $conexion->query($query);

// Inicializar variables para métricas
$total_creditos = 0;
$total_monto_prestado = 0;
$tipos_credito = [];
$tipos_cuota = [];

// Recorrer los datos para calcular las métricas
while ($row = $result->fetch_assoc()) {
    $total_creditos++;
    $total_monto_prestado += $row['monto_prestamo'];

    // Contar créditos por tipo
    if (!isset($tipos_credito[$row['tipo_credito']])) {
        $tipos_credito[$row['tipo_credito']] = 0;
    }
    $tipos_credito[$row['tipo_credito']]++;

    // Contar créditos por tipo de cuota
    if (!isset($tipos_cuota[$row['tipo_cuota']])) {
        $tipos_cuota[$row['tipo_cuota']] = 0;
    }
    $tipos_cuota[$row['tipo_cuota']]++;
}

// Cerrar la conexión
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas de Créditos</title>
    <!-- Agregar enlaces a Bootstrap y Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .card {
            margin-bottom: 20px;
        }

        .card-body {
            text-align: center;
        }

        .display-4 {
            font-size: 2.5rem;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="dashboard-header">Métricas de Créditos</h1>
            <a href="CalculadoraFinaciera.php" class="btn btn-info">Regresar a la Calculadora</a>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Filtros</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="Metricas.php">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tipo_credito" class="form-label">Tipo de Crédito</label>
                            <select id="tipo_credito" name="tipo_credito" class="form-select">
                                <option value="">Todos</option>
                                <option value="vehiculo" <?php if($tipo_credito == 'vehiculo') echo 'selected'; ?>>Vehículo</option>
                                <option value="agricola" <?php if($tipo_credito == 'agricola') echo 'selected'; ?>>Agrícola</option>
                                <option value="consumo" <?php if($tipo_credito == 'consumo') echo 'selected'; ?>>Consumo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo_cuota" class="form-label">Tipo de Cuota</label>
                            <select id="tipo_cuota" name="tipo_cuota" class="form-select">
                                <option value="">Todas</option>
                                <option value="nivelada" <?php if($tipo_cuota == 'nivelada') echo 'selected'; ?>>Nivelada</option>
                                <option value="saldos" <?php if($tipo_cuota == 'saldos') echo 'selected'; ?>>Sobre Saldos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_solicitud" class="form-label">Fecha de Solicitud</label>
                            <input type="date" id="fecha_solicitud" name="fecha_solicitud" class="form-control" value="<?php echo $fecha_solicitud; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="nombre_cliente" class="form-label">Buscar por Cliente</label>
                            <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" placeholder="Nombre del Cliente" value="<?php echo $nombre_cliente; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Aplicar Filtros</button>
                </form>
            </div>
        </div>

        <!-- Métricas -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3>Total de Créditos</h3>
                        <p class="display-4"><?php echo $total_creditos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3>Monto Total Prestado</h3>
                        <p class="display-4">Q<?php echo number_format($total_monto_prestado, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3>Distribución por Tipo de Crédito</h3>
                        <canvas id="tipoCreditoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3>Distribución por Tipo de Cuota</h3>
                        <canvas id="tipoCuotaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para gráficos con Chart.js -->
    <script>
        var tipoCreditoCtx = document.getElementById('tipoCreditoChart').getContext('2d');
        var tipoCreditoChart = new Chart(tipoCreditoCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($tipos_credito)); ?>,
                datasets: [{
                    label: 'Créditos por Tipo',
                    data: <?php echo json_encode(array_values($tipos_credito)); ?>,
                    backgroundColor: ['#007bff', '#28a745', '#dc3545'],
                }]
            }
        });

        var tipoCuotaCtx = document.getElementById('tipoCuotaChart').getContext('2d');
        var tipoCuotaChart = new Chart(tipoCuotaCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($tipos_cuota)); ?>,
                datasets: [{
                    label: 'Créditos por Tipo de Cuota',
                    data: <?php echo json_encode(array_values($tipos_cuota)); ?>,
                    backgroundColor: '#ffc107',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
