<?php
declare (strict_types=1);
namespace MailPoetVendor\Doctrine\DBAL\Driver\PDO;
if (!defined('ABSPATH')) exit;
use MailPoetVendor\Doctrine\DBAL\Driver\Exception\UnknownParameterType;
use MailPoetVendor\Doctrine\DBAL\ParameterType;
use PDO;
final class ParameterTypeMap
{
 private const PARAM_TYPE_MAP = [ParameterType::NULL => PDO::PARAM_NULL, ParameterType::INTEGER => PDO::PARAM_INT, ParameterType::STRING => PDO::PARAM_STR, ParameterType::ASCII => PDO::PARAM_STR, ParameterType::BINARY => PDO::PARAM_LOB, ParameterType::LARGE_OBJECT => PDO::PARAM_LOB, ParameterType::BOOLEAN => PDO::PARAM_BOOL];
 public static function convertParamType(int $type) : int
 {
 if (!isset(self::PARAM_TYPE_MAP[$type])) {
 throw UnknownParameterType::new($type);
 }
 return self::PARAM_TYPE_MAP[$type];
 }
 private function __construct()
 {
 }
 private function __clone()
 {
 }
}
