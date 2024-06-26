<?php

namespace App\Service\Mapper;

use App\Entity\Comment;
use App\Entity\User;
use App\Service\Mapper\Interface\MapperServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use UnitEnum;

class MapperService implements MapperServiceInterface
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly EntityManagerInterface $entityManager
    ) {}


    /**
     * @throws ReflectionException
     * @throws ORMException
     */
    public function mapToDTO(object $entity, string $dtoClass = null): object
    {
        // Regular DTO mapping
        if ($dtoClass === null) {
            $className = (new ReflectionClass($entity))->getShortName();
            $dtoClass = 'App\\DTO\\' . $className . 'DTO';
            if (!class_exists($dtoClass)) {
                $dtoClass = 'App\\DTO\\' . $className . 'ResponseDTO';
            }
        }
        
        $dto = new $dtoClass();
        $entityReflection = new ReflectionClass($entity);
        $dtoReflection = new ReflectionClass($dtoClass);

        foreach ($entityReflection->getProperties() as $property) {
            $propertyName = $property->getName();

            $propertyValue = $this->getPropertyValueSafely($entity, $propertyName);

            // If the property is an entity collection, map it to an array of DTOs
            if ($dtoReflection->hasProperty($propertyName) && $propertyValue instanceof Collection) {
                $value = $this->mapToDTOArray($propertyValue, $propertyName);
                $this->propertyAccessor->setValue($dto, $propertyName, $value);
            }
            // Regular property mapping
            elseif ($dtoReflection->hasProperty($propertyName)) {
                $value = $this->propertyAccessor->getValue($entity, $propertyName);

                if ($value instanceof UnitEnum) {
                    $value = $value->name;
                }

                $this->propertyAccessor->setValue($dto, $propertyName, $value);
            }
            // If the property is an entity, map it to an id
            elseif ($dtoReflection->hasProperty($propertyName . 'Id')) {
                if ($propertyValue) {
                    $associatedEntityId = $this->propertyAccessor->getValue($propertyValue, 'id');
                    $this->propertyAccessor->setValue($dto, $propertyName . 'Id', $associatedEntityId);
                }
            }
            // If the property is an entity collection, map it to an array of ids
            elseif ($dtoReflection->hasProperty($propertyName . 'Ids')) {
                $value = $this->getIdsFromCollectionOrArray($propertyValue);
                $this->propertyAccessor->setValue($dto, $propertyName . 'Ids', $value);
            }
        }

        return $dto;
    }

    /**
     * @throws OptimisticLockException
     * @throws ReflectionException
     * @throws ORMException
     */
    public function mapToEntity(object $dto, string $entityClass = null): object
    {
        // Regular DTO mapping
        if ($entityClass === null) {
            $className = str_replace('DTO', '', (new ReflectionClass($dto))->getShortName());
            $entityClass = 'App\\Entity\\' . $className;
        }

        $entity = new $entityClass();
        $dtoReflection = new ReflectionClass($dto);
        $entityReflection = new ReflectionClass($entityClass);

        foreach ($dtoReflection->getProperties() as $property) {
            $propertyName = $property->getName();

            try {
                // If the property is an array of ids, map it to a collection of entities
                if (is_array($this->propertyAccessor->getValue($dto, $propertyName)) && str_ends_with($propertyName, 'Ids')) {
                    $entityProperty = $entityReflection->getProperty(str_replace('Ids', '', $propertyName));
                    $associatedEntities = $this->getEntitiesFromIds($entityProperty->getDocComment(), $this->propertyAccessor->getValue($dto, $propertyName));
                    $this->propertyAccessor->setValue($entity, str_replace('Ids', '', $propertyName), $associatedEntities);
                }
                // Regular property mapping
                elseif ($entityReflection->hasProperty($propertyName)) {
                    $value = $this->propertyAccessor->getValue($dto, $propertyName);

                    $entityPropertyReflection = $entityReflection->getProperty($propertyName);
                    $entityPropertyType = $entityPropertyReflection->getType();

                    if ($entityPropertyType && $entityPropertyType->isBuiltin() === false && enum_exists($entityPropertyType->getName())) {
                        $enumClass = $entityPropertyType->getName();
                        $value = $enumClass::tryFrom($value);
                    }

                    $this->propertyAccessor->setValue($entity, $propertyName, $value);
                }
                // If the property is an object id, map it to an entity
                elseif (str_ends_with($propertyName, 'Id')) {
                    $entityProperty = str_replace('Id', '', $propertyName);
                    if ($entityReflection->hasProperty($entityProperty)) {
                        $associatedEntityClass = $entityReflection->getProperty($entityProperty)->getType()->getName();
                        $associatedEntityId = $this->propertyAccessor->getValue($dto, $propertyName);
                        if ($associatedEntityId === null) {
                            $this->propertyAccessor->setValue($entity, $entityProperty, null);
                        }
                        else {
                            $associatedEntity = $this->entityManager->find($associatedEntityClass, $associatedEntityId);
                            $this->propertyAccessor->setValue($entity, $entityProperty, $associatedEntity);
                        }
                    }
                }
            } catch (NoSuchPropertyException $e) {
                // Skip properties that don't exist
            }
        }

        return $entity;
    }

    /**
     * Safely get the value of a property, checking if it is initialized.
     * @throws ReflectionException
     * @throws ORMException
     */
    private function getPropertyValueSafely(object $entity, string $propertyName)
    {
        $property = (new ReflectionClass($entity))->getProperty($propertyName);

        if (!$property->isInitialized($entity)) {
            $this->entityManager->refresh($entity);
        }

        return $property->getValue($entity);
    }

    /**
     * Get entity objects from repository based on ids.
     * @param string|null $docBlock
     * @param array $ids
     * @return array
     */
    private function getEntitiesFromIds(?string $docBlock, array $ids): array
    {
        $entityClass = $this->getEntityClassFromDocBlock($docBlock);
        $repository = $this->entityManager->getRepository($entityClass);
        return $repository->findBy(['id' => $ids]);
    }

    /**
     * Extract ids from a collection or array of entities.
     * @param Collection|array $collectionOrArray
     * @return array
     */
    private function getIdsFromCollectionOrArray(Collection|array $collectionOrArray): array
    {
        $ids = [];

        foreach ($collectionOrArray as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }


    /**
     * Get entity class from docblock type hint.
     * @param string|null $docComment
     * @return string|null
     */
    private function getEntityClassFromDocBlock(?string $docComment): ?string
    {
        if ($docComment === null) {
            return null;
        }

        preg_match('/@var\s+Collection<\s*[^,]+,\s*([^>]+)>/', $docComment, $matches);
        if (!isset($matches[1])) {
            return null;
        }

        $typeHint = "App\\Entity\\$matches[1]";
        if (class_exists($typeHint)) {
            return $typeHint;
        }

        return null;
    }

    /**
     * Map a collection of entities to an array of DTOs.
     * @param Collection $collection
     * @return array
     * @throws ReflectionException
     */
    private function mapToDTOArray(Collection $collection, string $propertyName): array
    {
        $dtos = [];

        foreach ($collection as $entity) {
            $dtoClass = 'App\\DTO\\' . (new ReflectionClass($entity))->getShortName() . 'DTO';
            if (!class_exists($dtoClass)) {
                $dtoClass = 'App\\DTO\\' . (new ReflectionClass($entity))->getShortName() . 'ResponseDTO';
            }

            $dtos[] = $this->mapToDTO($entity, $dtoClass);
        }

        return $dtos;
    }
}