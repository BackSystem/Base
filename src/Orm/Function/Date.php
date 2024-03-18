<?php

namespace BackSystem\Base\Orm\Function;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class Date extends FunctionNode
{
    public mixed $date;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'CONVERT(date, '.$sqlWalker->walkArithmeticPrimary($this->date).')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
