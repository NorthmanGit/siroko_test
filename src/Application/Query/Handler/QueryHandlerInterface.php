<?php
namespace App\Application\Query\Handler;

use App\Application\Model\ViewModelInterface;

interface QueryHandlerInterface
{
    public function __invoke(object $query): ViewModelInterface;
}