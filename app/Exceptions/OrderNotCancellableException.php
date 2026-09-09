<?php

namespace App\Exceptions;

use RuntimeException;

class OrderNotCancellableException extends RuntimeException
{
    protected $message = 'This order can no longer be cancelled.';
}
