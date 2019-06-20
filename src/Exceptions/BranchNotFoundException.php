<?php

namespace Luur\Exceptions;

class BranchNotFoundException extends \Exception
{
    public function __construct(string $branch)
    {
        $this->message = "Array does not contain a valid branch `$branch`";
    }
}