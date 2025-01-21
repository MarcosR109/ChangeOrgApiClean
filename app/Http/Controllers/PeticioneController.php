<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use Exception;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Return_;

class PeticioneController extends Controller
{
    
    public function __construct()
    {
    $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    public function index(Request $request)
    {
        try {
            $peticiones = Peticione::all();
        } catch (Exception) {
            return response()->json(['Error' => 'Error buscando las peticiones']);
        }
        return response()->json(['Message' => 'Peticiones encontradas:', 'Data' => $peticiones]);
    }

    public function listMine($id)
    {
        try {
            $peticiones = Peticione::findOrFail($id);
        } catch (Exception) {
            return response()->json(['Error' => 'Error buscando usuario'], 404);
        }
        return response()->json(['Message' => 'Peticiones encontradas en función listMine:', 'Data' => $peticiones]);
    }
    public function listarFirmadas()
    {
        try {
            $peticiones = Peticione::where("firmantes", '>', "0")->get();
        } catch (Exception) {
            return response()->json(['Error' => 'Error buscando peticiones'], 404);
        }
        if ($peticiones->count() < 0) {
            return response()->json(['Error' => 'Error buscando peticiones'], 404);
        }
        return response()->json(['Message' => 'Peticiones encontradas en función listarFirmadas:', 'Data' => $peticiones]);
    }
    public function show($id)
    {
        try {
            $peticion = Peticione::query()->findOrFail($id);
        } catch (Exception) {
            return response()->json(['Message' => 'Ha ocurrido un error']);
        }
        return response()->json(['Message' => 'Petición encontrada:', 'Data' => $peticion]);
    }

    public function update(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->update($request->all());
        } catch (Exception) {
            return response()->json(['Error' => 'Error actualizando la petición']);
        }
        return response()->json(["Message" => 'Petición actualizada', 'Datos' => $peticion]);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'titulo' => 'required|max:255',
                'descripcion' => 'required|max:255',
                'destinatario' => 'required|max:255',
                'categoria_id' => 'required|max:255',
            ]);
        } catch (Exception) {
            return response()->json(['Error' => 'Error en la validación de los campos']);
        }
        try {
            $input = $request->all();
            $category = Categoria::query()->findOrFail($input['categoria_id']);
            $user = 1;
            $peticion = new Peticione($input);
            $peticion->user()->associate($user);
            $peticion->categoria()->associate($category);
            $peticion->firmantes = 0;
            $peticion->estado = 'pendiente';
            $peticion->save();
        } catch (Exception) {
            return response()->json(['Error' => 'Error guardando la petición']);
        }
        return response()->json(['Message' => 'Petición creada:', 'Data' => $peticion]);
    }

    public function firmar($id)
    {
        try {
            $peticion = Peticione::query()->findOrFail($id);
            //$user = 1; //??
            $user_id = auth()->id();
            $peticion->firmas()->attach($user_id);
            $peticion->firmantes = $peticion->firmantes + 1;
            $peticion->save();
        } catch (Exception) {
            return response()->json(['Error' => 'Ha ocurrido un error durante el firmado']);
        }
        return response()->json(['Message' => 'Petición firmada', 'Data' => $peticion]);
    }

    public function cambiarEstado($id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->estado = 'Aceptada';
            $peticion->save();
        } catch (Exception) {
            return response()->json(['Error' => 'Petición no encontrada']);
        }
        return response()->json(['Message' => 'Estado cambiado', 'Data' => $peticion]);
    }
    public function delete($id)
    {
        try {
            $peticion = Peticione::query()->findOrFail($id);
            $peticion->delete();
        } catch (Exception) {
            return response()->json(['Error' => 'Error encontrando la petición']);
        }
        return response()->json(['Message' => 'Petición eliminada']);
    }
}
