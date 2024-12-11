<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use App\Models\ContenidoPrincipal;


class PublicacionesController extends Controller
{
    public function setAvisos(Request $request) {
        try {
            // Validar la solicitud
            $validatedData = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'url_imagen' => 'nullable|string|max:500',
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            // Establecer una URL por defecto si no se proporciona
            $urlImagen = $request->input('url_imagen') ?: 'https://i.imgur.com/ShRoswn.png';

            // Crear el aviso utilizando el modelo Eloquent
            ContenidoPrincipal::create([
                'tipo_contenido' => 'aviso',
                'titulo' => $validatedData['titulo'],
                'descripcion' => $validatedData['descripcion'],
                'url_imagen' => $urlImagen,
                'fecha' => $validatedData['fecha'],
            ]);

            return response()->json(['success' => 'Aviso creado correctamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el Aviso: ' . $e->getMessage()], 500);
        }
    }

    public function getAviso()
    {
        try {
            $avisos = ContenidoPrincipal::where('tipo_contenido', 'aviso')->get();
            return response()->json($avisos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los avisos'], 500);
        }
    }

    public function getAvisoID($id)
    {
        try {
            $aviso = ContenidoPrincipal::where('tipo_contenido', 'aviso')->find($id);

            if (!$aviso) {
                return response()->json(['error' => 'Aviso no encontrado'], 404);
            }

            return response()->json($aviso);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el aviso'], 500);
        }
    }

    public function updateAviso(Request $request, $id)
    {
        try {
            $aviso = ContenidoPrincipal::where('tipo_contenido', 'aviso')->find($id);

            if (!$aviso) {
                return response()->json(['error' => 'Aviso no encontrado'], 404);
            }

            $aviso->titulo = $request->input('titulo');
            $aviso->descripcion = $request->input('descripcion');
            $aviso->url_imagen = $request->input('url_imagen');
            $aviso->save();

            return response()->json(['message' => 'Aviso actualizado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el aviso'], 500);
        }
    }

    public function deleteAviso($id)
    {
        try {
            $aviso = ContenidoPrincipal::where('tipo_contenido', 'aviso')->find($id);

            if (!$aviso) {
                return response()->json(['error' => 'Aviso no encontrado'], 404);
            }

            $aviso->delete();

            return response()->json(['message' => 'Aviso eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el aviso'], 500);
        }
    }

    // Función para  los tips
    public function setTip(Request $request) {
        try {
            // Validar la solicitud
            $validatedData = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'url_imagen' => 'nullable|string|max:500',
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            // Establecer una URL por defecto si no se proporciona
            $url_imagen = $request->input('url_imagen') ?: 'https://i.imgur.com/ShRoswn.png';

            // Crear el aviso utilizando el modelo Eloquent
            ContenidoPrincipal::create([
                'tipo_contenido' => 'tip',
                'titulo' => $validatedData['titulo'],
                'descripcion' => $validatedData['descripcion'],
                'url_imagen' => $url_imagen,
                'fecha' => $validatedData['fecha'],
            ]);

            return response()->json(['success' => 'Tip creado correctamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el Tip: ' . $e->getMessage()], 500);
        }
    }

    public function getTip()
    {
        try {
            $Tip = ContenidoPrincipal::where('tipo_contenido', 'tip')->get();
            return response()->json($Tip);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los tip'], 500);
        }
    }

    public function getTipID($id)
    {
        try {
            $Tip = ContenidoPrincipal::where('tipo_contenido', 'tip')->find($id);

            if (!$Tip) {
                return response()->json(['error' => 'Tip no encontrado'], 404);
            }

            return response()->json($Tip);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el tip'], 500);
        }
    }

    public function updateTip(Request $request, $id)
    {
        try {
            $Tip = ContenidoPrincipal::where('tipo_contenido', 'tip')->find($id);

            if (!$Tip) {
                return response()->json(['error' => 'Tip no encontrado'], 404);
            }

            $Tip->titulo = $request->input('titulo');
            $Tip->descripcion = $request->input('descripcion');
            $Tip->url_imagen = $request->input('url_imagen');
            $Tip->save();

            return response()->json(['message' => 'Tip actualizado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el tip'], 500);
        }
    }

    public function deleteTip($id)
    {
        try {
            $Tip = ContenidoPrincipal::where('tipo_contenido', 'tip')->find($id);

            if (!$Tip) {
                return response()->json(['error' => 'Tip no encontrado'], 404);
            }

            $Tip->delete();

            return response()->json(['message' => 'Tip eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el tip'], 500);
        }
    }

    // Función para  las preguntas
    public function setPregunta(Request $request) {
        try {
            // Validar la solicitud
            $validatedData = $request->validate([
                'pregunta' => 'required|string|max:255',
                'respuesta' => 'required|string',
                'url_imagen' => 'nullable|string|max:500',
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            $urlImagen = $request->input('url_imagen') ?: 'https://i.imgur.com/ShRoswn.png';

            // Crear el aviso utilizando el modelo Eloquent
            ContenidoPrincipal::create([
                'tipo_contenido' => 'pregunta',
                'pregunta' => $validatedData['pregunta'],
                'respuesta' => $validatedData['respuesta'],
                'url_imagen' => $urlImagen,
                'fecha' => $validatedData['fecha'],
            ]);

            return response()->json(['success' => 'Pregunta creado correctamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la pregunta: ' . $e->getMessage()], 500);
        }
    }

    public function getPreguntas()
    {
        try {
            $Pregunta = ContenidoPrincipal::where('tipo_contenido', 'pregunta')->get();
            return response()->json($Pregunta);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las preguntas'], 500);
        }
    }

    public function getPreguntaID($id)
    {
        try {
            $Pregunta = ContenidoPrincipal::where('tipo_contenido', 'pregunta')->find($id);

            if (!$Pregunta) {
                return response()->json(['error' => 'Pregunta no encontrada'], 404);
            }

            return response()->json($Pregunta);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la pregunta'], 500);
        }
    }

    public function updatePregunta(Request $request, $id)
    {
        try {
            $Pregunta = ContenidoPrincipal::where('tipo_contenido', 'pregunta')->find($id);

            if (!$Pregunta) {
                return response()->json(['error' => 'Pregunta no encontrado'], 404);
            }

            $Pregunta->pregunta = $request->input('pregunta');
            $Pregunta->respuesta = $request->input('respuesta');
            $Pregunta->url_imagen = $request->input('url_imagen');
            $Pregunta->save();

            return response()->json(['message' => 'Pregunta actualizado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el pregunta'], 500);
        }
    }

    public function deletePregunta($id)
    {
        try {
            $Pregunta = ContenidoPrincipal::where('tipo_contenido', 'pregunta')->find($id);

            if (!$Pregunta) {
                return response()->json(['error' => 'Pregunta no encontrado'], 404);
            }

            $Pregunta->delete();

            return response()->json(['message' => 'Pregunta eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el pregunta'], 500);
        }
    }

    public function verCarpetaDrive()
    {
        $folderId = '1pW4tnKrbJ6j3F0ERheG8_C-nU6RZHVxQ'; // ID de la carpeta que mencionaste

        try {
            // Configurar el cliente de Google
            $client = new Google_Client();
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            $client->setAuthConfig(storage_path('Drive.json'));
            $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);

            $service = new Google_Service_Drive($client);

            // Definir opciones para listar los archivos que están en la carpeta especificada
            $optParams = [
                'q' => "'$folderId' in parents",
                'fields' => 'files(id, name)',
                'pageSize' => 1000 // Ajusta este valor según sea necesario
            ];

            // Obtener la lista de archivos
            $results = $service->files->listFiles($optParams);

            if (count($results->getFiles()) == 0) {
                return response()->json(['message' => 'No se encontraron archivos en la carpeta especificada'], 200);
            }

            $archivos = [];
            foreach ($results->getFiles() as $file) {
                $archivos[] = ['id' => $file->getId(), 'name' => $file->getName()];
            }

            https://lh3.googleusercontent.com/d/$file=s500
            return response()->json(['files' => $archivos], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los archivos de la carpeta: ' . $e->getMessage()], 500);
        }
    }


    public function subirImagen(Request $request)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $folderId = '1pW4tnKrbJ6j3F0ERheG8_C-nU6RZHVxQ';

        try {
            // Configurar el cliente de Google con Guzzle
            $client = new Google_Client();
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            $client->setAuthConfig(storage_path('Drive.json'));
            $client->addScope(Google_Service_Drive::DRIVE_FILE);

            $service = new Google_Service_Drive($client);

            // Obtener el archivo cargado
            $file = $request->file('imagen');
            $filePath = $file->getPathname();
            $fileName = $file->getClientOriginalName();

            // Configurar el archivo para Google Drive
            $driveFile = new Google_Service_Drive_DriveFile();
            $driveFile->setName($fileName);
            $driveFile->setParents([$folderId]);

            // Subir el archivo
            $createdFile = $service->files->create($driveFile, [
                'data' => file_get_contents($filePath),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);
            $url = 'https://lh3.googleusercontent.com/d/' . $createdFile->getId() . '=s500';
            return response()->json(['url' => $url], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al subir la imagen: ' . $e->getMessage()], 500);
        }
    }
}

