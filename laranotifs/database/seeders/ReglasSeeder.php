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
        Reglas::create([
            'nombre' => 'ENVIAR SOLO CONSULTA EXTERNA',
            'ene' => true,
            'tipo_validacion' => true,
            'x_servicio' => null,
            'x_origen' => 'Consulta Externa',
            'x_motivo' => null,
            'x_especialidad' => null,
            'x_medico' => null,
            'x_idprueba' => null,
            'add_json' => '{"direccion": 1}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Reglas::create([
            'nombre' => 'NO ENVIAR METROLAB',
            'ene' => false,
            'tipo_validacion' => true,
            'x_servicio' => null,
            'x_origen' => null,
            'x_motivo' => 'METROLAB',
            'x_especialidad' => null,
            'x_medico' => null,
            'x_idprueba' => null,
            'add_json' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Reglas::create([
            'nombre' => 'ENVIAR DR VACAS SALAZAR JOSE JULIAN',
            'ene' => true,
            'tipo_validacion' => true,
            'x_servicio' => null,
            'x_origen' => null,
            'x_motivo' => null,
            'x_especialidad' => null,
            'x_medico' => 'VACAS SALAZAR JOSE JULIAN',
            'x_idprueba' => null,
            'add_json' => '{"direccion": "germariova@gmail.com"}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Reglas::create([
            'nombre' => 'NO ENVIAR URGENCIAS A PTE',
            'ene' => false,
            'tipo_validacion' => true,
            'x_servicio' => null,
            'x_origen' => 'Urgencias',
            'x_motivo' => null,
            'x_especialidad' => null,
            'x_medico' => null,
            'x_idprueba' => null,
            'add_json' => '{"direccion": 0}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Reglas::create([
            'nombre' => 'NO ENVIAR HOSPITALIZACION A PTE',
            'ene' => false,
            'tipo_validacion' => true,
            'x_servicio' => null,
            'x_origen' => 'Hospitalización',
            'x_motivo' => null,
            'x_especialidad' => null,
            'x_medico' => null,
            'x_idprueba' => null,
            'add_json' => '{"direccion": 0}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
