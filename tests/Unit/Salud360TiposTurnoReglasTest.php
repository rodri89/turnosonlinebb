<?php

namespace Tests\Unit;

use App\Services\Salud360\TiposTurnoReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas de POST /api/salud360/tipos-turno y PUT /api/salud360/medicos/{id}/tipos-turno (sin base de datos).
 *
 * Los casos de la base (403, 404, transacción, idempotencia, filas inactivas, avisos) se prueban con curl
 * contra la copia local; ver md/API_SALUD360.md.
 */
class Salud360TiposTurnoReglasTest extends TestCase
{
    /** Ids activos del catálogo de prueba (el 33 existe pero está inactivo: no figura acá). */
    private $activos = [1, 2, 26, 27, 28];

    // ------------------------------------------------------------------
    // validarConfiguracion: forma del cuerpo
    // ------------------------------------------------------------------

    public function testCuerpoVacioEsDatosInvalidos()
    {
        $r = TiposTurnoReglas::validarConfiguracion([], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        // El mensaje orienta a quien llama: nombra el campo que falta.
        $this->assertStringContainsString('tipos', $r['mensaje']);
    }

    public function testCuerpoSinLaClaveEsDatosInvalidos()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['otra_cosa' => [['id' => 1]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos', $r['mensaje']);
    }

    /**
     * @dataProvider valoresQueNoSonLista
     */
    public function testTiposQueNoEsListaEsDatosInvalidos($valor)
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => $valor], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos', $r['mensaje']);
        $this->assertArrayNotHasKey('tipos', $r);
    }

    public function valoresQueNoSonLista()
    {
        return [
            'null' => [null],
            'texto' => ['1,2'],
            'numero' => [5],
            'booleano' => [true],
            // Un objeto JSON llega como arreglo asociativo: no es una lista.
            'objeto' => [['1' => ['id' => 1]]],
            'objeto con clave' => [['id' => 1]],
        ];
    }

    /**
     * @dataProvider elementosQueNoSonObjeto
     */
    public function testElementoQueNoEsObjetoEsDatosInvalidos($elemento)
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [$elemento]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos[0]', $r['mensaje']);
    }

    public function elementosQueNoSonObjeto()
    {
        return [
            'id suelto' => [1],
            'texto' => ['1'],
            'null' => [null],
            'booleano' => [false],
            // Una lista JSON (`[1]`) tampoco es un objeto.
            'lista' => [[1]],
        ];
    }

    public function testListaVaciaEsValidaYDeshabilitaTodo()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => []], $this->activos);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame([], $r['tipos']);
    }

    // ------------------------------------------------------------------
    // validarConfiguracion: id
    // ------------------------------------------------------------------

    /**
     * @dataProvider idsInvalidos
     */
    public function testIdInvalidoEsDatosInvalidos($id)
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => $id]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos[0].id', $r['mensaje']);
    }

    public function idsInvalidos()
    {
        return [
            'cero' => [0],
            'negativo' => [-3],
            // JSON estricto: el id es un entero, no un texto ni un decimal.
            'texto' => ['1'],
            'decimal' => [1.5],
            'booleano' => [true],
            'null' => [null],
            'lista' => [[1]],
        ];
    }

    public function testFaltaElIdEsDatosInvalidos()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['primer_control' => 1]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos[0].id', $r['mensaje']);
    }

    public function testIdInexistenteEnElCatalogoNombraElId()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1], ['id' => 999]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('999', $r['mensaje']);
    }

    public function testIdInactivoEnElCatalogoNombraElId()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 33]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('33', $r['mensaje']);
    }

    public function testIdRepetidoEsDatosInvalidoYNombraElId()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 26], ['id' => 1], ['id' => 26]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('26', $r['mensaje']);
        $this->assertStringContainsString('repetido', $r['mensaje']);
    }

    // ------------------------------------------------------------------
    // validarConfiguracion: primer_control
    // ------------------------------------------------------------------

    /**
     * @dataProvider primerControlValidos
     */
    public function testPrimerControlValido($valor)
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1, 'primer_control' => $valor]]], $this->activos);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame($valor, $r['tipos'][0]['primer_control']);
    }

    public function primerControlValidos()
    {
        return [
            'siempre (null)' => [null],
            'solo primera consulta (1)' => [1],
            'solo seguimiento (0)' => [0],
        ];
    }

    public function testPrimerControlAusenteEsSiempre()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1]]], $this->activos);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertNull($r['tipos'][0]['primer_control']);
    }

    /**
     * @dataProvider primerControlInvalidos
     */
    public function testPrimerControlInvalidoEsDatosInvalidos($valor)
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1, 'primer_control' => $valor]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos[0].primer_control', $r['mensaje']);
    }

    public function primerControlInvalidos()
    {
        return [
            'texto "1"' => ['1'],
            'texto "0"' => ['0'],
            'texto vacío' => [''],
            'verdadero' => [true],
            'falso' => [false],
            'dos' => [2],
            'menos uno' => [-1],
            'decimal' => [1.0],
            'lista' => [[1]],
        ];
    }

    // ------------------------------------------------------------------
    // validarConfiguracion: opciones
    // ------------------------------------------------------------------

    public function testOpcionesAusentesQuedanNulasYNoSeTocan()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1]]], $this->activos);
        $this->assertNull($r['tipos'][0]['opciones']);
    }

    public function testOpcionesVaciasSonValidasYDejanSinOpciones()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1, 'opciones' => []]]], $this->activos);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame([], $r['tipos'][0]['opciones']);
    }

    /**
     * @dataProvider opcionesQueNoSonListaDeEnteros
     */
    public function testOpcionesQueNoSonListaDeEnterosEsDatosInvalidos($valor)
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 1, 'opciones' => $valor]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('tipos[0].opciones', $r['mensaje']);
    }

    public function opcionesQueNoSonListaDeEnteros()
    {
        return [
            'null' => [null],
            'texto' => ['26,27'],
            'numero' => [26],
            'objeto' => [['a' => 26]],
            'elemento texto' => [['26']],
            'elemento decimal' => [[26.5]],
            'elemento booleano' => [[true]],
            'elemento cero' => [[0]],
            'elemento negativo' => [[-1]],
            'elemento lista' => [[[26]]],
        ];
    }

    public function testOpcionIgualAlPadreEsDatosInvalidos()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 26, 'opciones' => [27, 26]]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('26', $r['mensaje']);
        $this->assertStringContainsString('opciones', $r['mensaje']);
    }

    public function testOpcionInexistenteNombraElId()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 26, 'opciones' => [27, 777]]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('777', $r['mensaje']);
    }

    public function testOpcionInactivaNombraElId()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 26, 'opciones' => [33]]]], $this->activos);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('33', $r['mensaje']);
    }

    public function testOpcionesRepetidasSeJuntan()
    {
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 26, 'opciones' => [27, 28, 27]]]], $this->activos);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame([27, 28], $r['tipos'][0]['opciones']);
    }

    public function testOpcionPuedeSerOtroTipoNoHabilitado()
    {
        // Las opciones son tipos del catálogo; no hace falta que el médico los tenga habilitados.
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 26, 'opciones' => [2]]]], $this->activos);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame([2], $r['tipos'][0]['opciones']);
    }

    // ------------------------------------------------------------------
    // validarConfiguracion: caso completo
    // ------------------------------------------------------------------

    public function testConfiguracionCompletaSeNormalizaEnElOrdenEnviado()
    {
        $r = TiposTurnoReglas::validarConfiguracion([
            'tipos' => [
                ['id' => 28, 'primer_control' => 1, 'opciones' => [26, 27]],
                ['id' => 1, 'primer_control' => null],
                ['id' => 2, 'primer_control' => 0, 'opciones' => []],
            ],
        ], $this->activos);

        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame([
            ['id' => 28, 'primer_control' => 1, 'opciones' => [26, 27]],
            ['id' => 1, 'primer_control' => null, 'opciones' => null],
            ['id' => 2, 'primer_control' => 0, 'opciones' => []],
        ], $r['tipos']);
    }

    public function testElPrimerErrorDeFormaGanaSobreLosDelCatalogo()
    {
        // El elemento 0 apunta a un id que no existe, pero el elemento 1 tiene mal la forma: primero la forma.
        $r = TiposTurnoReglas::validarConfiguracion(['tipos' => [['id' => 999], ['id' => 1, 'primer_control' => 2]]], $this->activos);
        $this->assertStringContainsString('tipos[1].primer_control', $r['mensaje']);
    }

    // ------------------------------------------------------------------
    // validarNombre
    // ------------------------------------------------------------------

    private function catalogo()
    {
        return [
            ['id' => 1, 'nombre' => 'Consulta', 'activo' => 1],
            ['id' => 33, 'nombre' => 'Tipo de prueba temporal', 'activo' => 0],
        ];
    }

    public function testNombreValidoSeDevuelveSinEspaciosExtremos()
    {
        $r = TiposTurnoReglas::validarNombre("  Control anual \n", $this->catalogo());
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame('Control anual', $r['nombre']);
    }

    /**
     * @dataProvider nombresVacios
     */
    public function testNombreVacioEsDatosInvalidos($nombre)
    {
        $r = TiposTurnoReglas::validarNombre($nombre, $this->catalogo());
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('nombre', $r['mensaje']);
    }

    public function nombresVacios()
    {
        return [
            'vacío' => [''],
            'espacios' => ['    '],
            'tabulación y salto' => ["\t \n"],
            'espacio duro' => ["\u{00A0}\u{00A0}"],
        ];
    }

    /**
     * @dataProvider nombresQueNoSonTexto
     */
    public function testNombreQueNoEsTextoEsDatosInvalidos($nombre)
    {
        $r = TiposTurnoReglas::validarNombre($nombre, $this->catalogo());
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('nombre', $r['mensaje']);
    }

    public function nombresQueNoSonTexto()
    {
        return [
            'null' => [null],
            'número' => [5],
            'booleano' => [true],
            'lista' => [['Consulta']],
        ];
    }

    public function testNombreDeSesentaCaracteresEsValido()
    {
        $nombre = str_repeat('á', 60);
        $r = TiposTurnoReglas::validarNombre($nombre, $this->catalogo());
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame($nombre, $r['nombre']);
    }

    public function testNombreMuyLargoEsDatosInvalidos()
    {
        $r = TiposTurnoReglas::validarNombre(str_repeat('á', 61), $this->catalogo());
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('60', $r['mensaje']);
    }

    public function testLosEspaciosExtremosNoCuentanParaElLargo()
    {
        $r = TiposTurnoReglas::validarNombre('  ' . str_repeat('a', 60) . '  ', $this->catalogo());
        $this->assertArrayNotHasKey('error', $r);
    }

    public function testNombreDuplicadoSinMayusculasNiEspaciosEsNombreRepetidoConLaFichaDelExistente()
    {
        $r = TiposTurnoReglas::validarNombre('  CONSULTA ', $this->catalogo());
        $this->assertSame('nombre_repetido', $r['error']);
        $this->assertSame(['id' => 1, 'nombre' => 'Consulta', 'activo' => 1], $r['existente']);
        $this->assertNotEmpty($r['mensaje']);
    }

    public function testNombreDuplicadoConAcentosYMayusculasUnicode()
    {
        $catalogo = [['id' => 7, 'nombre' => 'Ecografías', 'activo' => 1]];
        $r = TiposTurnoReglas::validarNombre('ECOGRAFÍAS', $catalogo);
        $this->assertSame('nombre_repetido', $r['error']);
        $this->assertSame(7, $r['existente']['id']);
    }

    public function testNombreDuplicadoDeUnTipoInactivoTambienEsRepetido()
    {
        $r = TiposTurnoReglas::validarNombre('tipo de prueba temporal', $this->catalogo());
        $this->assertSame('nombre_repetido', $r['error']);
        $this->assertSame(['id' => 33, 'nombre' => 'Tipo de prueba temporal', 'activo' => 0], $r['existente']);
    }

    public function testNombreParecidoPeroDistintoNoEsRepetido()
    {
        $r = TiposTurnoReglas::validarNombre('Consulta 2', $this->catalogo());
        $this->assertArrayNotHasKey('error', $r);
    }

    public function testElCatalogoVacioNuncaRepite()
    {
        $r = TiposTurnoReglas::validarNombre('Consulta', []);
        $this->assertSame('Consulta', $r['nombre']);
    }
}
