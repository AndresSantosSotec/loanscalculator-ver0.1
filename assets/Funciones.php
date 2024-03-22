<?php
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
