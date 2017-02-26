<?php

namespace Mikemix\ValueObjectId\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

abstract class AbstractUuidType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $fqcn = $this->getValueObjectClassName();

        if (is_scalar($value)) {
            $value = new $fqcn($value);
        }

        if (!is_a($value, $fqcn)) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $fqcn);
        }

        return pack('h*', str_replace('-', '', (string) $value));
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $values = unpack('h*', $value);
        $uuid = preg_replace(
            '/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/',
            '$1-$2-$3-$4-$5',
            array_pop($values)
        );

        $fqcn = $this->getValueObjectClassName();

        return new $fqcn($uuid);
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 16;
        $fieldDeclaration['fixed'] = 16;

        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }

    abstract protected function getValueObjectClassName(): string;
}
