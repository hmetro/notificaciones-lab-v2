<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Reglas;
use Illuminate\Database\Seeder;

class ReglasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reglas = array([
            "nombre" => "ENVIAR SOLO CONSULTA EXTERNA",
            "ene" => 1,
            "tipo_validacion" => 1,
            "x_origen" => "Consulta Externa",
            "add_json" => "{\"direccion\": 1}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR METROLAB",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_motivo" => "METROLAB",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3420",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3420",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3422",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3422",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3426",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3426",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3418",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3418",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3414",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3414",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3402",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3402",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3429",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3429",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3438",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3438",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3441",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3441",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 1866",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "1866",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 8201",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "8201",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3774",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3774",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3775",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3775",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 1559",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "1559",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 1585",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "1585",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 1872",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "1872",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3775",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3775",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3776",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3776",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3777",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3777",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3778",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3778",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3779",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3779",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3780",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3780",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3781",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3781",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3784",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3784",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3785",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3785",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3787",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3787",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3790",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3790",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3801",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3801",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 3802",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "3802",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 10529",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "10529",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 37811",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "37811",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 34949",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "34949",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 16868",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "16868",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR PRUEBA 96692",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_idprueba" => "96692",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR UNIDAD BANCO DE SANGRE",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_especialidad" => "UNIDAD BANCO DE SANGRE",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ],
            [
            "nombre" => "NO ENVIAR CORREO METROLAB METROLAB",
            "ene" => 0,
            "tipo_validacion" => 1,
            "x_medico" => "METROLAB METROLAB",
            "add_json" => "{\"direccion\": 0}",
            "created_at" => now(),
            "updated_at" => now()
            ]);

        foreach ($reglas as $regla) {
            Reglas::create($regla);
        }
        // Reglas::create([
        //     'nombre' => 'NO ENVIAR METROLAB',
        //     'ene' => false,
        //     'tipo_validacion' => true,
        //     'x_motivo' => 'METROLAB',
        //     'add_json' => '{"direccion": 0}',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Reglas::create([
        //     'nombre' => 'ENVIAR DR VACAS SALAZAR JOSE JULIAN',
        //     'ene' => true,
        //     'tipo_validacion' => true,
        //     'x_medico' => 'VACAS SALAZAR JOSE JULIAN',
        //     'add_json' => '{"direccion": "germariova@gmail.com"}',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Reglas::create([
        //     'nombre' => 'NO ENVIAR URGENCIAS A PTE',
        //     'ene' => false,
        //     'tipo_validacion' => true,
        //     'x_origen' => 'Urgencias',
        //     'add_json' => '{"direccion": 0}',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Reglas::create([
        //     'nombre' => 'NO ENVIAR HOSPITALIZACION A PTE',
        //     'ene' => false,
        //     'tipo_validacion' => true,
        //     'x_origen' => 'Hospitalización',
        //     'add_json' => '{"direccion": 0}',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }
}
