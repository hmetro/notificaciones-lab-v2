<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Models\Reglas;
use App\Http\Resources\ReglaResourceCollection;
use App\Http\Resources\ReglaResource;
use App\Http\Requests\StoreReglaRequest;
use App\Http\Requests\UpdateReglaRequest;

class ReglasNegocioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            return new ReglaResourceCollection(Reglas::all());
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReglaRequest $request)
    {
        try {
            $newRegla = Reglas::create($request->all()+[
                "created_at" => now(),
                "updated_at" => now()
            ]);
    
            return response()->json([
                'success'   => true,
                'message'   => 'Regla creada exitosamente',
                'data'      => new ReglaResource($newRegla)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Reglas $regla)
    {
        try {
            return new ReglaResource($regla);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReglaRequest $request, Reglas $regla)
    {
        try {
            $regla->update($request->all()+[
                'updated_at' => now()
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Regla actualizada exitosamente',
                'data'      => new ReglaResource($regla)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reglas $regla)
    {
        try {
            $regla->delete();

            return response()->json([
                'success'   => true,
                'message'   => 'Regla eliminada exitosamente'
            ], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }
}
