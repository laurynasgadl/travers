<?php

namespace Luur\Exceptions;

class BranchNotFoundException extends \Exception
{
    public function __construct($branch)
    {
        parent::__construct("Array does not contain a valid branch `$branch`");
    }
}