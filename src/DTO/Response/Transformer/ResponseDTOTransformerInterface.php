<?php

declare(strict_types=1);

namespace App\DTO\Response\Transformer;

interface ResponseDTOTransformerInterface
{
    public function transformFromObject($object);
    public function transformFromObjects(iterable $objects): iterable;
}