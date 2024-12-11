<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContenidoPrincipal;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PublicacionesController extends Controller
{
    public function setAviso(Request $request) {
        try {
            // Validar la solicitud
            $validatedData = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
            ]);

            $url_imagen = null;
            if ($request->hasFile('imagen')) {
                // Subir imagen a Cloudinary
                $uploadResult = $cloudinary->uploadApi()->upload($request->file('imagen')->getRealPath(), [
                    'folder' => 'avisos'
                ]);
                $url_imagen = $uploadResult['secure_url'];
            }

            // Crear el aviso utilizando el modelo Eloquent
            ContenidoPrincipal::create([
                'tipo_contenido' => 'aviso',
                'titulo' => $validatedData['titulo'],
                'descripcion' => $validatedData['descripcion'],
                'url_imagen' => $url_imagen,
                'fecha' => $validatedData['fecha'],
            ]);

            return response()->json(['success' => 'Aviso creado correctamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Error de validación: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el aviso: ' . $e->getMessage()], 500);
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
                'url_pagina' => 'nullable|string|max:500',
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            // Crear el aviso utilizando el modelo Eloquent
            ContenidoPrincipal::create([
                'tipo_contenido' => 'tip',
                'titulo' => $validatedData['titulo'],
                'descripcion' => $validatedData['descripcion'],
                'url_pagina' => $request->input('url_pagina'),
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
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            // Crear el aviso utilizando el modelo Eloquent
            ContenidoPrincipal::create([
                'tipo_contenido' => 'pregunta',
                'pregunta' => $validatedData['pregunta'],
                'respuesta' => $validatedData['respuesta'],
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

}

