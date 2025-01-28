<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth;
use PhpParser\Node\Stmt\Return_;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="user",
 *     description="User related operations"
 * )
 * @OA\Info(
 *     version="1.0",
 *     title="Example API",
 *     description="Example info",
 *     @OA\Contact(name="Swagger API Team")
 * )
 * @OA\Server(
 *     url="https://example.localhost",
 *     description="API server"
 * )
 * @OA\Get(
 *     path="/api/users",
 *     @OA\Response(response="200", description="An example endpoint")
 * )
 */
class PeticioneController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }
    /**
     * @OA\Tag(name="index",description="Lista todas las peticiones")
     * @OA\Get(path="/api/peticiones",
     * @OA\Response(response="200",description="Todas las peticiones de la BBDD"))
     */
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
            $user = User::FindOrFail($id);
            $peticiones = $user->peticiones;
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
            if ($request->user()->cannot('update', $peticion)) {
                return response()->json(['Error' => 'No estás autorizado para actualizar la petición.', 403]);
            }
            if ($peticion) {
                $peticion->update($request->all());
            }
        } catch (Exception) {
            return response()->json(['Error' => 'Error actualizando la petición'], 500);
        }
        return response()->json(["Message" => 'Petición actualizada', 'Datos' => $peticion], 200);
    }

    public
    function store(Request $request)
    {
        $this->validate($request, [
            'titulo' => 'required|max:255',
            'descripcion' => 'required',
            'destinatario' => 'required',
            'categoria_id' => 'required',
            'foto' => 'required',
        ]);
        $input = $request->all();
        try {
            $category = Categoria::query()->findOrFail($input['categoria_id']);
            $user = auth()->user(); //asociarlo al usuario autenticado
            $peticion = new Peticione($input);
            $peticion->categoria()->associate($category);
            $peticion->user()->associate($user);
            $peticion->firmantes = 0;
            $peticion->estado = 'pendiente';
            $res = $peticion->save();
            if ($res) {
                $res_file = $this->fileUpload($request, $peticion->id);
                if ($res_file) {
                    $peticion->file = $res_file;
                    return response()->json(
                        ['message' => 'Petición creada', 'data' => $peticion, "file" => $res_file],
                    );
                }
            }
        } catch (Exception $e) {
            return response()->json(
                ['error' => 'Error creando la petición', 'data' => $e->getMessage()],
            );
        }
        return response()->json(
            ['message' => 'Petición creada', 'data' => $res],
        );
    }

    public
    function fileUpload(Request $req, $peticione_id = null)
    {
        $file = $req->file('foto');
        $fileModel = new File;
        $fileModel->peticione_id = $peticione_id;
        if ($req->file('foto')) {
            $filename = $fileName = time() . '_' . $file->getClientOriginalName();
            try {
                $file->move(public_path('images/peticiones/'), $filename);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()]);
            }
            $fileModel->name = $filename;
            $fileModel->file_path = $filename;
            $res = $fileModel->save();
            return $fileModel;
            if ($res) {
                return 0;
            } else {
                return 1;
            }
        }
        return 1;
    }

    function list(Request $request)
    {
        $peticiones = Peticione::jsonPaginate();
        return $peticiones;
    }

    public function firmar(Request $request, $id)
    {
        try {
            $peticion = Peticione::query()->findOrFail($id);
            $user = $request->user();
            if ($request->user()->cannot('firmar', $peticion)) {
                return response()->json(
                    ['message' => 'Ya has firmado esta petición'],
                    403
                );
            }
            $user_id = auth()->id();
            $peticion->firmas()->attach($user_id);
            $peticion->firmantes = $peticion->firmantes + 1;
            $peticion->save();
        } catch (\Exception $e) {
            return response()->json(['Error' => 'Ha ocurrido un error durante el firmado', "error_message" => $e->getMessage()]);
        }
        return response()->json(['Message' => 'Petición firmada', 'Data' => $peticion]);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $peticion = Peticione::findOrFail($id);
        if ($request->user()->cannot('cambiarEstado', $peticion)) {
            return response()->json(
                ['message' => 'No estás autorizado para realizar esta acción'],
                403
            );
        }
        try {
            $peticion->estado = "Aceptada";
        } catch (Exception) {
            return response()->json(['message' => 'Ha ocurrido un error buscando la petición.']);
        }
        return response()->json(['message' => 'Estado cambiado', 'data' => $peticion]);
    }

    public function delete($id)
    {
        try {
            $peticion = Peticione::query()->findOrFail($id);
            $peticion->file->delete();
            $peticion->delete();
        } catch (Exception) {
            return response()->json(['Error' => 'Error encontrando la petición'],405);
        }
        return response()->json(['Message' => 'Petición eliminada']);
    }
}
