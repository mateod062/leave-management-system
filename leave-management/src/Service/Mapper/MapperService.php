<?php

namespace App\Service\Mapper;

use App\Service\Mapper\Interface\MapperServiceInterface;
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
     */
    public function mapToDTO(object $entity, string $dtoClass = null): object
    {
        if ($dtoClass === null) {
            $className = (new ReflectionClass($entity))->getShortName();
            $dtoClass = 'App\\DTO\\' . $className . 'DTO';
        }
        
        $dto = new $dtoClass();
        $entityReflection = new ReflectionClass($entity);
        $dtoReflection = new ReflectionClass($dtoClass);

        foreach ($entityReflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if ($dtoReflection->hasProperty($propertyName)) {
                $value = $this->propertyAccessor->getValue($entity, $propertyName);

                if ($value instanceof UnitEnum) {
                    $value = $value->name;
                }

                $this->propertyAccessor->setValue($dto, $propertyName, $value);
            } elseif (str_contains($propertyName, 'Id')) {
                $entityProperty = str_replace('Id', '', $propertyName);
                if ($entityReflection->hasProperty($entityProperty)) {
                    $associatedEntity = $this->propertyAccessor->getValue($entity, $entityProperty);
                    if ($associatedEntity) {
                        $associatedEntityId = $this->propertyAccessor->getValue($associatedEntity, 'id');
                        $this->propertyAccessor->setValue($dto, $propertyName, $associatedEntityId);
                    }
                }
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
                if ($entityReflection->hasProperty($propertyName)) {
                    $value = $this->propertyAccessor->getValue($dto, $propertyName);

                    $entityPropertyReflection = $entityReflection->getProperty($propertyName);
                    $entityPropertyType = $entityPropertyReflection->getType();

                    if ($entityPropertyType && $entityPropertyType->isBuiltin() === false && enum_exists($entityPropertyType->getName())) {
                        $enumClass = $entityPropertyType->getName();
                        $value = $enumClass::from($value);
                    }

                    $this->propertyAccessor->setValue($entity, $propertyName, $value);
                } elseif (str_contains($propertyName, 'Id')) {
                    $entityProperty = str_replace('Id', '', $propertyName);
                    if ($entityReflection->hasProperty($entityProperty)) {
                        $associatedEntityClass = $entityReflection->getProperty($entityProperty)->getType()->getName();
                        $associatedEntity = $this->entityManager->find($associatedEntityClass, $this->propertyAccessor->getValue($dto, $propertyName));
                        $this->propertyAccessor->setValue($entity, $entityProperty, $associatedEntity);
                    }
                }
            } catch (NoSuchPropertyException $e) {
                // Skip properties that don't exist
            }
        }

        return $entity;
    }
}