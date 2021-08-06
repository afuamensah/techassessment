<?php
 namespace MailPoetVendor\Doctrine\DBAL\Driver; if (!defined('ABSPATH')) exit; use IteratorAggregate; class StatementIterator implements \IteratorAggregate { private $statement; public function __construct(\MailPoetVendor\Doctrine\DBAL\Driver\ResultStatement $statement) { $this->statement = $statement; } public function getIterator() { while (($result = $this->statement->fetch()) !== \false) { (yield $result); } } } 