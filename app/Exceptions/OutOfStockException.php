<?php

namespace App\Exceptions;

use RuntimeException;

class OutOfStockException extends RuntimeException
{
    protected $message = 'Some items are no longer available in the requested quantity.';
}
