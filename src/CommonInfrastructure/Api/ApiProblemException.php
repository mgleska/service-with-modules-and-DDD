<?php

declare(strict_types=1);

namespace App\CommonInfrastructure\Api;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Exclude]
class ApiProblemException extends HttpException
{
    private string $type;
    private string $title;

    public function __construct(int $httpStatus, string $type, string $title)
    {
        $this->type = $type;
        $this->title = $title;

        parent::__construct(
            $httpStatus,
            $title
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
