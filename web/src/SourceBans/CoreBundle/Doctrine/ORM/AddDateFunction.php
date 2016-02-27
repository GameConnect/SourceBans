<?php

namespace SourceBans\CoreBundle\Doctrine\ORM;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * AddDateFunction ::=
 *     "ADDDATE" "(" ArithmeticPrimary ", INTERVAL" ArithmeticPrimary Identifier ")"
 */
class AddDateFunction extends FunctionNode
{
    /**
     * @var FunctionNode
     */
    public $firstDateExpression;

    /**
     * @var FunctionNode
     */
    public $intervalExpression;

    /**
     * @var string
     */
    public $unit;

    /**
     * @inheritdoc
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->firstDateExpression = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);

        $this->intervalExpression = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_IDENTIFIER);

        /* @var $lexer Lexer */
        $lexer = $parser->getLexer();
        $this->unit = $lexer->token['value'];

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @inheritdoc
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'ADDDATE(%s, INTERVAL %s %s)',
            $this->firstDateExpression->dispatch($sqlWalker),
            $this->intervalExpression->dispatch($sqlWalker),
            $this->unit
        );
    }
}
