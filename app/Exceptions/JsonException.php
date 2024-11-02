<?php

namespace App\Exceptions;

use Exception;

class JsonException extends Exception
{
    public function render($request)
    {
        return response()->json(['status' => false, 'message' => $this->getMessage()], $this->getCode());
    }
}
