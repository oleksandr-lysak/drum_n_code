<?php

declare(strict_types=1);

namespace App;

use DomainException;

class IncompleteTasksException extends DomainException
{
    public array $incompleteTasks;
    public function __construct(string $message, array $incompleteTasks)
    {
        parent::__construct($message, 409);
        $this->incompleteTasks = $incompleteTasks;
    }
}
