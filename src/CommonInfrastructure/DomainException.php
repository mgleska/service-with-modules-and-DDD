<?php

declare(strict_types=1);

namespace App\CommonInfrastructure;

use App\CommonInfrastructure\Api\ApiProblemException;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Response;

use function get_class;
use function strrpos;
use function substr;

#[Exclude]
class DomainException extends ApiProblemException
{
    public function __construct(string $message)
    {
        $class = get_class($this);
        $pos = strrpos($class, '\\');
        if ($pos !== false) {
            $class = substr($class, $pos + 1);
        }

        parent::__construct(Response::HTTP_BAD_REQUEST, 'DomainException#' . $class, $message);
    }
}
