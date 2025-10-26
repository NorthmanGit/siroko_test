<?php
namespace App\Application\Query\Handler;

use App\Application\Model\ViewModelInterface;
use App\Application\QueryCommandInterface;

interface QueryHandlerInterface
{
    public function __invoke(QueryCommandInterface $query): ?ViewModelInterface;
}