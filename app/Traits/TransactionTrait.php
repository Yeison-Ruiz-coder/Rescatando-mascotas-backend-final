<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait TransactionTrait
{
    protected function runInTransaction(callable $callback, $errorMessage = 'Error en la operación')
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($errorMessage . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
