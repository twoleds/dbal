<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Types;

use DateInterval;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\Deprecations\Deprecation;
use Throwable;

use function substr;

/**
 * Type that maps interval string to a PHP DateInterval Object.
 */
class DateIntervalType extends Type
{
    public const FORMAT = '%RP%YY%MM%DDT%HH%IM%SS';

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 255;

        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateInterval) {
            return $value->format(self::FORMAT);
        }

        throw InvalidType::new($value, static::class, ['null', 'DateInterval']);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateInterval
    {
        if ($value === null || $value instanceof DateInterval) {
            return $value;
        }

        $negative = false;

        if (isset($value[0]) && ($value[0] === '+' || $value[0] === '-')) {
            $negative = $value[0] === '-';
            $value    = substr($value, 1);
        }

        try {
            $interval = new DateInterval($value);

            if ($negative) {
                $interval->invert = 1;
            }

            return $interval;
        } catch (Throwable $exception) {
            throw InvalidFormat::new($value, static::class, self::FORMAT, $exception);
        }
    }

    /**
     * @deprecated
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/5509',
            '%s is deprecated.',
            __METHOD__
        );

        return true;
    }
}
