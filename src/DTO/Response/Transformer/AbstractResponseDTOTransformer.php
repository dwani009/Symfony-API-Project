<?php

declare(strict_types=1);

namespace App\DTO\Response\Transformer;

abstract class AbstractResponseDTOTransformer implements ResponseDTOTransformerInterface
{
    /**
     * @param iterable $objects
     * @return iterable
     */
    public function transformFromObjects(iterable $objects): iterable
    {
        $dto = [];

        foreach ($objects as $object) {
            $dto[] = $this->transformFromObject($object);
        }

        return $dto;
    }
}