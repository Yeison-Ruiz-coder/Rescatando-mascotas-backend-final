<?php

namespace App\Traits;

trait ApiResponses
{
    protected function successResponse($data, $message = 'Operación exitosa', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $errors = null, $code = 422)
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors) $response['errors'] = $errors;
        return response()->json($response, $code);
    }

    protected function notFoundResponse($message = 'Recurso no encontrado')
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }

    protected function paginatedResponse($paginator, $message = 'Datos obtenidos exitosamente')
    {
        return $this->successResponse($paginator, $message);
    }
}
