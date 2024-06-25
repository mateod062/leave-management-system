<?php

namespace App\Doctrine\Type;

use App\Entity\UserRole;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

class UserRoleType extends Type
{
    const NAME = 'user_role';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "VARCHAR(255)";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return !empty($value) ? UserRole::from($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof UserRole) {
            return $value->value;
        }

        try {
            return UserRole::from($value)->value;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid user role');
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}