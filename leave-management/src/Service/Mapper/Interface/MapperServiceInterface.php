<?php

namespace App\Service\Mapper\Interface;

interface MapperServiceInterface
{
    /**
     * Map an entity class into its corresponding DTO class
     *
     * @param object $entity
     * @param string|null $dtoClass
     * @return object
     */
    public function mapToDTO(object $entity, string $dtoClass = null): object;

    /**
     * Map a DTO class to its corresponding entity class
     *
     * @param object $dto
     * @param string|null $entityClass
     * @return object
     */
    public function mapToEntity(object $dto, string $entityClass = null): object;
}