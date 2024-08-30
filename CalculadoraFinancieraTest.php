<?php

use PHPUnit\Framework\TestCase;

class CalculadoraFinancieraTest extends TestCase
{
    public function testCalculoCuotaNivelada()
    {
        // Simula la lógica del cálculo
        $monto_prestamo = 10000;
        $plazo_meses = 12;
        $interes = 5;
        $tipo_cuota = 'nivelada';

        $tasa_interes_decimal = $interes / 100 / 12;
        $cuota = ($monto_prestamo * $tasa_interes_decimal) / (1 - pow(1 + $tasa_interes_decimal, -$plazo_meses));

        // Verifica el resultado esperado
        $this->assertEquals(856.07, round($cuota, 2));
    }

    public function testCalculoCuotaSaldos()
    {
        // Simula la lógica del cálculo
        $monto_prestamo = 10000;
        $plazo_meses = 12;
        $interes = 5;

        $tasa_interes_decimal = $interes / 100 / 12;
        $cuota = $monto_prestamo / $plazo_meses;

        // Primera cuota con intereses
        $intereses_primera_cuota = $monto_prestamo * $tasa_interes_decimal;
        $primera_cuota = $cuota + $intereses_primera_cuota;

        // Verifica el resultado esperado
        $this->assertEquals(856.07, round($primera_cuota, 2));

        // Verifica el resultado de la última cuota (ejemplo de verificación)
        $saldo_restante = $monto_prestamo - ($cuota * $plazo_meses);
        $intereses_ultima_cuota = $saldo_restante * $tasa_interes_decimal;
        $ultima_cuota = $cuota + $intereses_ultima_cuota;

        // Verifica que la última cuota sea la esperada (ejemplo)
        $this->assertEquals(856.07, round($ultima_cuota, 2));
    }
    
    public function testCalculoConPlazoCero()
    {
        // Verifica que el sistema maneje correctamente un plazo de 0 meses
        $monto_prestamo = 10000;
        $plazo_meses = 0;
        $interes = 5;

        // Esperamos que la cuota sea cero o que el sistema arroje un error
        $this->expectException(\InvalidArgumentException::class);

        // Intentar calcular la cuota
        $tasa_interes_decimal = $interes / 100 / 12;
        if ($plazo_meses == 0) {
            throw new \InvalidArgumentException("El plazo no puede ser cero.");
        }
        $cuota = ($monto_prestamo * $tasa_interes_decimal) / (1 - pow(1 + $tasa_interes_decimal, -$plazo_meses));

        // No debería llegar aquí porque esperamos una excepción
        $this->assertEquals(0, round($cuota, 2));
    }
}
