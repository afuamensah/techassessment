<?php
 namespace MailPoetVendor\Doctrine\DBAL; if (!defined('ABSPATH')) exit; use MailPoetVendor\Doctrine\Common\EventManager; use MailPoetVendor\Doctrine\DBAL\Driver\DrizzlePDOMySql; use MailPoetVendor\Doctrine\DBAL\Driver\IBMDB2; use MailPoetVendor\Doctrine\DBAL\Driver\Mysqli; use MailPoetVendor\Doctrine\DBAL\Driver\OCI8; use MailPoetVendor\Doctrine\DBAL\Driver\PDO; use MailPoetVendor\Doctrine\DBAL\Driver\PDO\Statement as PDODriverStatement; use MailPoetVendor\Doctrine\DBAL\Driver\SQLAnywhere; use MailPoetVendor\Doctrine\DBAL\Driver\SQLSrv; use MailPoetVendor\Doctrine\Deprecations\Deprecation; use function array_keys; use function array_merge; use function assert; use function class_implements; use function in_array; use function is_string; use function is_subclass_of; use function parse_str; use function parse_url; use function preg_replace; use function rawurldecode; use function str_replace; use function strpos; use function substr; final class DriverManager { private const DRIVER_MAP = ['pdo_mysql' => \MailPoetVendor\Doctrine\DBAL\Driver\PDO\MySQL\Driver::class, 'pdo_sqlite' => \MailPoetVendor\Doctrine\DBAL\Driver\PDO\SQLite\Driver::class, 'pdo_pgsql' => \MailPoetVendor\Doctrine\DBAL\Driver\PDO\PgSQL\Driver::class, 'pdo_oci' => \MailPoetVendor\Doctrine\DBAL\Driver\PDO\OCI\Driver::class, 'oci8' => \MailPoetVendor\Doctrine\DBAL\Driver\OCI8\Driver::class, 'ibm_db2' => \MailPoetVendor\Doctrine\DBAL\Driver\IBMDB2\Driver::class, 'pdo_sqlsrv' => \MailPoetVendor\Doctrine\DBAL\Driver\PDO\SQLSrv\Driver::class, 'mysqli' => \MailPoetVendor\Doctrine\DBAL\Driver\Mysqli\Driver::class, 'drizzle_pdo_mysql' => \MailPoetVendor\Doctrine\DBAL\Driver\DrizzlePDOMySql\Driver::class, 'sqlanywhere' => \MailPoetVendor\Doctrine\DBAL\Driver\SQLAnywhere\Driver::class, 'sqlsrv' => \MailPoetVendor\Doctrine\DBAL\Driver\SQLSrv\Driver::class]; private static $driverSchemeAliases = [ 'db2' => 'ibm_db2', 'mssql' => 'pdo_sqlsrv', 'mysql' => 'pdo_mysql', 'mysql2' => 'pdo_mysql', 'postgres' => 'pdo_pgsql', 'postgresql' => 'pdo_pgsql', 'pgsql' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite', 'sqlite3' => 'pdo_sqlite', ]; private function __construct() { } public static function getConnection(array $params, ?\MailPoetVendor\Doctrine\DBAL\Configuration $config = null, ?\MailPoetVendor\Doctrine\Common\EventManager $eventManager = null) : \MailPoetVendor\Doctrine\DBAL\Connection { if (!$config) { $config = new \MailPoetVendor\Doctrine\DBAL\Configuration(); } if (!$eventManager) { $eventManager = new \MailPoetVendor\Doctrine\Common\EventManager(); } $params = self::parseDatabaseUrl($params); if (isset($params['master'])) { $params['master'] = self::parseDatabaseUrl($params['master']); } if (isset($params['slaves'])) { foreach ($params['slaves'] as $key => $slaveParams) { $params['slaves'][$key] = self::parseDatabaseUrl($slaveParams); } } if (isset($params['primary'])) { $params['primary'] = self::parseDatabaseUrl($params['primary']); } if (isset($params['replica'])) { foreach ($params['replica'] as $key => $replicaParams) { $params['replica'][$key] = self::parseDatabaseUrl($replicaParams); } } if (isset($params['global'])) { $params['global'] = self::parseDatabaseUrl($params['global']); } if (isset($params['shards'])) { foreach ($params['shards'] as $key => $shardParams) { $params['shards'][$key] = self::parseDatabaseUrl($shardParams); } } if (isset($params['pdo']) && !$params['pdo'] instanceof \PDO) { throw \MailPoetVendor\Doctrine\DBAL\Exception::invalidPdoInstance(); } if (isset($params['pdo'])) { \MailPoetVendor\Doctrine\Deprecations\Deprecation::trigger('doctrine/dbal', 'https://github.com/doctrine/dbal/pull/3554', 'Passing a user provided PDO instance directly to Doctrine is deprecated.'); $params['pdo']->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); $params['pdo']->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [\MailPoetVendor\Doctrine\DBAL\Driver\PDO\Statement::class, []]); $params['driver'] = 'pdo_' . $params['pdo']->getAttribute(\PDO::ATTR_DRIVER_NAME); } $driver = self::createDriver($params); $wrapperClass = \MailPoetVendor\Doctrine\DBAL\Connection::class; if (isset($params['wrapperClass'])) { if (!\is_subclass_of($params['wrapperClass'], $wrapperClass)) { throw \MailPoetVendor\Doctrine\DBAL\Exception::invalidWrapperClass($params['wrapperClass']); } $wrapperClass = $params['wrapperClass']; } return new $wrapperClass($params, $driver, $config, $eventManager); } public static function getAvailableDrivers() : array { return \array_keys(self::DRIVER_MAP); } private static function createDriver(array $params) : \MailPoetVendor\Doctrine\DBAL\Driver { if (isset($params['driverClass'])) { $interfaces = \class_implements($params['driverClass'], \true); if ($interfaces === \false || !\in_array(\MailPoetVendor\Doctrine\DBAL\Driver::class, $interfaces)) { throw \MailPoetVendor\Doctrine\DBAL\Exception::invalidDriverClass($params['driverClass']); } return new $params['driverClass'](); } if (isset($params['driver'])) { if (!isset(self::DRIVER_MAP[$params['driver']])) { throw \MailPoetVendor\Doctrine\DBAL\Exception::unknownDriver($params['driver'], \array_keys(self::DRIVER_MAP)); } $class = self::DRIVER_MAP[$params['driver']]; return new $class(); } throw \MailPoetVendor\Doctrine\DBAL\Exception::driverRequired(); } private static function normalizeDatabaseUrlPath(string $urlPath) : string { return \substr($urlPath, 1); } private static function parseDatabaseUrl(array $params) : array { if (!isset($params['url'])) { return $params; } $url = \preg_replace('#^((?:pdo_)?sqlite3?):///#', '$1://localhost/', $params['url']); \assert(\is_string($url)); $url = \parse_url($url); if ($url === \false) { throw new \MailPoetVendor\Doctrine\DBAL\Exception('Malformed parameter "url".'); } foreach ($url as $param => $value) { if (!\is_string($value)) { continue; } $url[$param] = \rawurldecode($value); } unset($params['pdo']); $params = self::parseDatabaseUrlScheme($url['scheme'] ?? null, $params); if (isset($url['host'])) { $params['host'] = $url['host']; } if (isset($url['port'])) { $params['port'] = $url['port']; } if (isset($url['user'])) { $params['user'] = $url['user']; } if (isset($url['pass'])) { $params['password'] = $url['pass']; } $params = self::parseDatabaseUrlPath($url, $params); $params = self::parseDatabaseUrlQuery($url, $params); return $params; } private static function parseDatabaseUrlPath(array $url, array $params) : array { if (!isset($url['path'])) { return $params; } $url['path'] = self::normalizeDatabaseUrlPath($url['path']); if (!isset($params['driver'])) { return self::parseRegularDatabaseUrlPath($url, $params); } if (\strpos($params['driver'], 'sqlite') !== \false) { return self::parseSqliteDatabaseUrlPath($url, $params); } return self::parseRegularDatabaseUrlPath($url, $params); } private static function parseDatabaseUrlQuery(array $url, array $params) : array { if (!isset($url['query'])) { return $params; } $query = []; \parse_str($url['query'], $query); return \array_merge($params, $query); } private static function parseRegularDatabaseUrlPath(array $url, array $params) : array { $params['dbname'] = $url['path']; return $params; } private static function parseSqliteDatabaseUrlPath(array $url, array $params) : array { if ($url['path'] === ':memory:') { $params['memory'] = \true; return $params; } $params['path'] = $url['path']; return $params; } private static function parseDatabaseUrlScheme($scheme, array $params) : array { if ($scheme !== null) { unset($params['driverClass']); $driver = \str_replace('-', '_', $scheme); $params['driver'] = self::$driverSchemeAliases[$driver] ?? $driver; return $params; } if (!isset($params['driverClass']) && !isset($params['driver'])) { throw \MailPoetVendor\Doctrine\DBAL\Exception::driverRequired($params['url']); } return $params; } } 