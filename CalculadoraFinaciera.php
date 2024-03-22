<?php
/*
Plugin Name: Calculadora Financiera
Description: Calculadora para generar tablas de amortización Cuota Nivelada y Cuota Sobre Saldos.
Version: 1.0
Author: Prolink GT
License: GPL v2 or later
*/

// Función para mostrar el formulario de la calculadora financiera
function mostrar_calculadora_financiera()
{
    // Definir las variables iniciales
    $ultima_cuota = 0; // Inicializamos $ultima_cuota con un valor predeterminado
    $monto_prestamo = isset($_POST["monto_prestamo"]) ? $_POST["monto_prestamo"] : 0;
    $plazo_meses = isset($_POST["plazo_meses"]) ? $_POST["plazo_meses"] : 0;
    $tipo_credito = isset($_POST["tipo_credito"]) ? $_POST["tipo_credito"] : '';
    $tipo_cuota = isset($_POST["tipo_cuota"]) ? $_POST["tipo_cuota"] : '';
    $interes = 0;
    $cuota = 0;
    $error_message = '';
    $tabla_pagos = array(); // Inicializamos la variable $tabla_pagos como un array vacío

    // Verificar si se ha enviado el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Calcular los intereses según el tipo de crédito (Lo ideal es que se pueda parametrizar)
        switch ($tipo_credito) {
            case 'vehiculo':
                $interes = 5; // Tasa de interés para crédito vehicular ( Tasa de ejemplo)
                break;
            case 'agricola':
                $interes = 7; // Tasa de interés para crédito agrícola (Tasas de ejemplo)
                break;
            case 'consumo':
                $interes = 10; // Tasa de interés para crédito de consumo (Tasas ejemplo)
                break;
            default:
                $interes = 0;
        }

        // Calcular la cuota según el tipo de cuota
        if ($tipo_cuota == 'nivelada') {
            $tasa_interes_decimal = $interes / 100 / 12;
            $num_cuotas = $plazo_meses;
            if ($tasa_interes_decimal != 0) {
                $cuota = ($monto_prestamo * $tasa_interes_decimal) / (1 - pow(1 + $tasa_interes_decimal, -$num_cuotas));
            } else {
                $cuota = $monto_prestamo / $plazo_meses;
            }
        } elseif ($tipo_cuota == 'saldos') {
            if ($plazo_meses != 0) {
                // Calcular la tasa de interés mensual
                $tasa_interes_decimal = $interes / 100 / 12;

                // Calcular el monto de cada cuota (capital + intereses)
                $cuota = $monto_prestamo / $plazo_meses;

                // Calcular los intereses de la primera cuota
                $intereses_primera_cuota = $monto_prestamo * $tasa_interes_decimal;

                // Calcular el monto de la primera cuota (capital + intereses)
                $primera_cuota = $cuota + $intereses_primera_cuota;

                // Calcular las cuotas y los intereses restantes
                for ($i = 1; $i <= $plazo_meses; $i++) {
                    // Calcular el saldo restante después de pagar la cuota
                    $saldo_restante = $monto_prestamo - ($cuota * $i);

                    // Calcular los intereses de la cuota actual utilizando el saldo restante
                    $intereses_cuota_actual = $saldo_restante * $tasa_interes_decimal;

                    // Calcular el monto de la cuota actual (capital + intereses)
                    $cuota_actual = $cuota + $intereses_cuota_actual;

                    // Almacenar los datos de la cuota actual en la tabla de pagos
                    $tabla_pagos[] = array(
                        'No. Cuota' => $i,
                        'Cuota Capital' => round($cuota, 2),
                        'Interés' => round($intereses_cuota_actual, 2),
                        'Cuota Total' => round($cuota_actual, 2),
                        'Saldo del Préstamo' => round($saldo_restante, 2)
                    );
                    // La última cuota es igual a la cuota calculada en el último ciclo
                    $ultima_cuota = $cuota_actual;
                }
            } else {
                $error_message = "El plazo no puede ser cero.";
            }
        }

        // Validar que la cuota esté calculada correctamente
        if ($cuota <= 0) {
            $error_message = "Error al calcular la cuota. Por favor, verifica los datos ingresados.";
        }

        // Mostrar la alerta si hay un error
        if (!empty($error_message)) {
            echo "<script>Swal.fire('Error', '$error_message', 'error');</script>";
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Calculadora Financiera</title>
        <!-- Agrega los enlaces a los archivos CSS de Bootstrap y Font Awesome -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/estilos.css">
    </head>

    <body>
        <div class="container mt-5">
            <div class="card crdbody">
                <div class="card-header">
                    <h2 class="card-title">Formulario de Calculadora</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>">
                                <div class="mb-3">
                                    <label for="monto_prestamo" class="form-label">Monto del crédito:</label>
                                    <input type="number" name="monto_prestamo" class="form-control" placeholder="Q.00" value="<?php echo $monto_prestamo; ?>" required>
                                </div>

                                <!--modo Manual & modo Slicer-->
                                <div class="mb-3">
                                    <label for="plazo_meses" class="form-label">Plazo en meses:</label>
                                    <input type="number" name="plazo_meses" id="plazo_meses_input" class="form-control" min="1" max="36" value="<?php echo $plazo_meses; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="plazo_meses_range" id="plazo_meses_range_label" class="form-label">Plazo en meses: <?php echo $plazo_meses; ?></label>
                                    <input type="range" name="plazo_meses_range" id="plazo_meses_range" class="form-range" min="1" max="36" value="<?php echo $plazo_meses; ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input class="form-check-input" type="checkbox" id="modo_manual_checkbox">
                                    <label class="form-check-label" for="modo_manual_checkbox">Modo Manual</label>
                                </div>
                                <!--final modo Manual & modo Slicer-->

                                <div class="mb-3">
                                    <label for="tipo_credito" class="form-label">Tipo de crédito:</label>
                                    <select name="tipo_credito" class="form-select" required>
                                        <option value="vehiculo" <?php if ($tipo_credito == 'vehiculo') echo 'selected'; ?>>Vehículo</option>
                                        <option value="agricola" <?php if ($tipo_credito == 'agricola') echo 'selected'; ?>>Agrícola</option>
                                        <option value="consumo" <?php if ($tipo_credito == 'consumo') echo 'selected'; ?>>Consumo</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tipo_cuota" class="form-label">Tipo de cuota:</label>
                                    <select name="tipo_cuota" class="form-select" required>
                                        <option value="nivelada" <?php if ($tipo_cuota == 'nivelada') echo 'selected'; ?>>Nivelada</option>
                                        <option value="saldos" <?php if ($tipo_cuota == 'saldos') echo 'selected'; ?>>Sobre Saldos</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Calcular</button>
                                <button type="button" class="btn btn-success" id="ver-tabla-pagos">Ver tabla de pagos</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $error_message == '') : ?>
                                <h2>Resumen de Pagos:</h2>
                                <?php if ($tipo_cuota == 'nivelada') : ?>
                                    <div class="alert alert-info" role="alert">La cuota nivelada será siempre la misma.</div>
                                    <p>Cuota Nivelada:</p>
                                    <p>a) Cuota a Pagar: Q<?php echo round($cuota, 2); ?></p>
                                    <p>b) Intereses Totales: Q<?php echo round(($cuota * $plazo_meses) - $monto_prestamo, 2); ?></p>
                                    <p>c) Monto Total del Crédito: Q<?php echo round($monto_prestamo, 2); ?></p>
                                    <p>d) Tasa del Crédito: <?php echo $interes; ?>%</p>
                                <?php elseif ($tipo_cuota == 'saldos') : ?>
                                    <p>Cuota Sobre Saldos:</p>
                                    <?php if ($plazo_meses != 0) : ?>
                                        <p>a) Primera Cuota (Más Intereses): Q<?php echo round($primera_cuota, 2); ?></p>
                                        <p>b) Última Cuota (Más Intereses): Q<?php echo round($ultima_cuota, 2); ?></p>
                                    <?php endif; ?>
                                    <p>c) Intereses Totales: Q<?php echo round(($interes / 100 / 12) * $plazo_meses * $monto_prestamo, 2); ?></p>
                                    <p>d) Tasa del Crédito: <?php echo $interes; ?>%</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal HTML -->
            <div id="modal-form" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Ingrese sus datos</h2>
                    <form id="modal-form-data" action="plan_pago.php" method="POST" class="form-inline">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" class="form-control mr-2" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" id="telefono" name="telefono" class="form-control mr-2" required>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo:</label>
                            <input type="email" id="correo" name="correo" class="form-control mr-2" required>
                        </div>
                        <div class="form-group">
                            <label for="dpi">DPI:</label>
                            <input type="text" id="dpi" name="dpi" class="form-control mr-2" required>
                        </div>
                        <button type="submit" class="btn btn-success crdbody">Enviar</button>
                    </form>
                </div>
            </div>
            <!-- Contenedor de la tabla de pagos y botones -->
            <div class="card crdbody" id="card-tabla" style="display: none;">
                <div class="card-header">
                    <h2 class="card-title">Tabla de Pagos</h2>
                </div>
                <div class="card-body">
                    <div id="tabla-container" style="display: none;">
                        <div class="container">
                            <div id="tabla-pagos">
                                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $error_message == '') : ?>
                                    <?php if (isset($tabla_pagos) && !empty($tabla_pagos)) : ?>
                                        <table class="table table-bordered custom-table crdbody">
                                            <tr>
                                                <th>No. Cuota</th>
                                                <th>Cuota Capital</th>
                                                <th>Interés</th>
                                                <th>Cuota Total</th>
                                                <th>Saldo del Préstamo</th>
                                            </tr>
                                            <?php foreach ($tabla_pagos as $index => $cuota) : ?>
                                                <tr>
                                                    <?php foreach ($cuota as $key => $valor) : ?>
                                                        <?php if (is_numeric($valor) && $key !== 'No. Cuota') : ?>
                                                            <td>Q<?php echo number_format($valor, 2); ?></td>
                                                        <?php else : ?>
                                                            <td><?php echo $valor; ?></td>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div id="botones-container" style="display: none;">
                                <button class="btn btn-success" id="descargar"><i class="fas fa-download"></i> Descargar</button>
                                <button class="btn btn-success" id="resumen"><i class="fas fa-info-circle"></i> Completa </button>
                                <button class="btn btn-success" id="ocultar"><i class="fas fa-eye-slash"></i> Ocultar</button>
                                <button class="btn btn-success" id="enviar-correo"><i class="fas fa-envelope"></i> Enviar por correo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="assets/funciones.js"></script>
            <!-- Enlace al archivo JavaScript de Bootstrap (opcional, solo si necesitas funcionalidades JS de Bootstrap) -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

            <script>
                document.getElementById('ver-tabla-pagos').addEventListener('click', function() {
                    document.getElementById('card-tabla').style.display = 'block';
                });
            </script>

            <script>
                // Obtener elementos del DOM
                const plazoMesesInput = document.getElementById('plazo_meses_input');
                const plazoMesesRange = document.getElementById('plazo_meses_range');
                const plazoMesesRangeLabel = document.getElementById('plazo_meses_range_label');

                // Evento para sincronizar el valor del campo de entrada con el slider al cambiar el valor del slider
                plazoMesesRange.addEventListener('input', function() {
                    // Actualizar el valor del campo de entrada con el valor del slider
                    plazoMesesInput.value = this.value;
                    // Actualizar el texto del label con el valor del slider
                    plazoMesesRangeLabel.textContent = 'Plazo en meses: ' + this.value;
                });
            </script>



            <script>
                var closeButton = document.getElementsByClassName("close")[0];

                // Agrega un evento de clic al botón de cierre para cerrar el modal
                closeButton.onclick = function() {
                    var modal = document.getElementById("modal-form");
                    modal.style.display = "none";
                }
                // Función para cambiar la visibilidad del control de rango y el campo de entrada de tipo número
                function toggleModoManual() {
                    var modoManualCheckbox = document.getElementById('modo_manual_checkbox');
                    var plazoMesesInput = document.getElementsByName('plazo_meses')[0];
                    var plazoMesesRange = document.getElementsByName('plazo_meses_range')[0];

                    // Si el checkbox de modo manual está seleccionado, ocultar el control de rango y mostrar el campo de entrada
                    if (modoManualCheckbox.checked) {
                        plazoMesesRange.style.display = 'none';
                        plazoMesesInput.style.display = 'block';
                    } else {
                        // Si no está seleccionado, mostrar el control de rango y ocultar el campo de entrada
                        plazoMesesRange.style.display = 'block';
                        plazoMesesInput.style.display = 'none';
                    }
                }

                // Event listener para el cambio en el estado del checkbox
                document.getElementById('modo_manual_checkbox').addEventListener('change', function() {
                    toggleModoManual();
                });

                // Llamar a la función al cargar la página para asegurar la visibilidad inicial
                toggleModoManual();
            </script>



            <script>
                // Función para mostrar la tabla de pagos y los botones
                function mostrarTablaPagos() {
                    var tablaContainer = document.getElementById('tabla-container');
                    var botonesContainer = document.getElementById('botones-container');
                    if (tablaContainer.style.display === 'none') {
                        tablaContainer.style.display = 'block';
                    }
                    if (botonesContainer.style.display === 'none') {
                        botonesContainer.style.display = 'flex'; // Mostrar los botones cuando se muestra la tabla
                    }
                }

                // Event listener para el botón "Ver tabla de pagos"
                document.getElementById('ver-tabla-pagos').addEventListener('click', function() {
                    mostrarTablaPagos();
                });

                // Event listener para el botón de descargar
                document.getElementById('descargar').addEventListener('click', function() {
                    // Mostrar el modal
                    var modal = document.getElementById('modal-form');
                    modal.style.display = 'block';

                    // Event listener para el formulario dentro del modal
                    document.getElementById('modal-form-data').addEventListener('submit', function(event) {
                        event.preventDefault(); // Evitar el envío del formulario

                        // Verificar si los campos del formulario están llenos
                        var nombre = document.getElementById('nombre').value;
                        var telefono = document.getElementById('telefono').value;
                        var correo = document.getElementById('correo').value;
                        var dpi = document.getElementById('dpi').value;

                        if (nombre !== '' && telefono !== '' && correo !== '' && dpi !== '') {
                            // Si todos los campos están llenos, descargar el archivo
                            descargarArchivo();
                            // Ocultar el modal
                            modal.style.display = 'none';
                        } else {
                            // Si algún campo está vacío, mostrar un mensaje de alerta
                            alert('Por favor, complete todos los campos antes de continuar.');
                        }
                    });
                });

                // Función para descargar el archivo
                function descargarArchivo() {
                    // Obtener los datos del formulario
                    var formData = new FormData();
                    formData.append('monto_prestamo', document.getElementsByName('monto_prestamo')[0].value);
                    formData.append('plazo_meses', document.getElementsByName('plazo_meses')[0].value);
                    formData.append('tipo_credito', document.getElementsByName('tipo_credito')[0].value);
                    formData.append('tipo_cuota', document.getElementsByName('tipo_cuota')[0].value);

                    // Crear una solicitud AJAX
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'plan_pago.php', true);
                    xhr.responseType = 'blob'; // Esperamos una respuesta binaria (PDF)
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            // Crear un objeto URL para el blob
                            var url = window.URL.createObjectURL(xhr.response);
                            // Crear un enlace y simular clic para descargar el PDF
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'TablaPagos.pdf';
                            document.body.appendChild(a);
                            a.click();
                            // Limpiar el objeto URL después de descargar
                            window.URL.revokeObjectURL(url);
                        }
                    };
                    xhr.send(formData);
                }
            </script>
    </body>

    </html>

<?php
}
mostrar_calculadora_financiera();
// Agregar la función como shortcode para que se pueda usar en las páginas
//add_shortcode('calculadora_financiera', 'mostrar_calculadora_financiera'); Codigo para embeberlo en Wordpress.
?>