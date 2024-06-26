<?php
declare (strict_types=1);
namespace MailPoetVendor\Doctrine\ORM\Mapping;
if (!defined('ABSPATH')) exit;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use MailPoetVendor\Doctrine\DBAL\Types\Type;
use MailPoetVendor\Doctrine\DBAL\Types\Types;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionProperty;
use function array_merge;
use function assert;
use function enum_exists;
use const PHP_VERSION_ID;
final class DefaultTypedFieldMapper implements TypedFieldMapper
{
 private $typedFieldMappings;
 private const DEFAULT_TYPED_FIELD_MAPPINGS = [DateInterval::class => Types::DATEINTERVAL, DateTime::class => Types::DATETIME_MUTABLE, DateTimeImmutable::class => Types::DATETIME_IMMUTABLE, 'array' => Types::JSON, 'bool' => Types::BOOLEAN, 'float' => Types::FLOAT, 'int' => Types::INTEGER, 'string' => Types::STRING];
 public function __construct(array $typedFieldMappings = [])
 {
 $this->typedFieldMappings = array_merge(self::DEFAULT_TYPED_FIELD_MAPPINGS, $typedFieldMappings);
 }
 public function validateAndComplete(array $mapping, ReflectionProperty $field) : array
 {
 $type = $field->getType();
 if (!isset($mapping['type']) && $type instanceof ReflectionNamedType) {
 if (PHP_VERSION_ID >= 80100 && !$type->isBuiltin() && enum_exists($type->getName())) {
 $mapping['enumType'] = $type->getName();
 $reflection = new ReflectionEnum($type->getName());
 $type = $reflection->getBackingType();
 assert($type instanceof ReflectionNamedType);
 }
 if (isset($this->typedFieldMappings[$type->getName()])) {
 $mapping['type'] = $this->typedFieldMappings[$type->getName()];
 }
 }
 return $mapping;
 }
}
