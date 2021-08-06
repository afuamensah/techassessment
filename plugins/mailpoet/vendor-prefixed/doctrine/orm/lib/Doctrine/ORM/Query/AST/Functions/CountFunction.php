<?php
 namespace MailPoetVendor\Doctrine\ORM\Query\AST\Functions; if (!defined('ABSPATH')) exit; use MailPoetVendor\Doctrine\DBAL\Types\Type; use MailPoetVendor\Doctrine\ORM\Query\AST\AggregateExpression; use MailPoetVendor\Doctrine\ORM\Query\AST\TypedExpression; use MailPoetVendor\Doctrine\ORM\Query\Parser; use MailPoetVendor\Doctrine\ORM\Query\SqlWalker; final class CountFunction extends \MailPoetVendor\Doctrine\ORM\Query\AST\Functions\FunctionNode implements \MailPoetVendor\Doctrine\ORM\Query\AST\TypedExpression { private $aggregateExpression; public function getSql(\MailPoetVendor\Doctrine\ORM\Query\SqlWalker $sqlWalker) : string { return $this->aggregateExpression->dispatch($sqlWalker); } public function parse(\MailPoetVendor\Doctrine\ORM\Query\Parser $parser) : void { $this->aggregateExpression = $parser->AggregateExpression(); } public function getReturnType() : \MailPoetVendor\Doctrine\DBAL\Types\Type { return \MailPoetVendor\Doctrine\DBAL\Types\Type::getType(\MailPoetVendor\Doctrine\DBAL\Types\Type::INTEGER); } } 