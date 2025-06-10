<?php

namespace mattesmohr\TwigSwitch;

class SwitchTokenParser extends \Twig\TokenParser\AbstractTokenParser {

    public function parse(\Twig\Token $token) {

        $stream = $this->parser->getStream();

        // The variable name, which is used for the context
        $name = $stream->expect(\Twig\Token::NAME_TYPE)->getValue();

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        // Parse till you hit one of the symbols
        $this->parser->subparse([$this, 'decideIfFork']);

        $cases = [];
        $default = [];

        $continue = true;

        while ($continue) {
            switch ($stream->next()->getValue()) {
                case 'case':

                    // Parse the expression of the case
                    $cases[] = $this->parser->getExpressionParser()->parseExpression();

                    $stream->expect(\Twig\Token::BLOCK_END_TYPE);

                    // Parse the body of the case
                    $cases[] = $this->parser->subparse([$this, 'decideIfFork']);

                    break;

                case 'default':

                    $stream->expect(\Twig\Token::BLOCK_END_TYPE);

                    /// Parse the body of the default
                    $default[] = $this->parser->subparse([$this, 'decideIfEnd']);;

                    break;

                case 'endswitch':

                    $continue = false;

                    break;

                default:
                    throw new \Twig\Error\SyntaxError(\sprintf('Unexpected end of template.'));
            }
        }

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new SwitchNode($name, new \Twig\Node\Node($cases), new \Twig\Node\Node($default), $token->getLine());
    }

    public function decideIfFork(\Twig\Token $token) {
        return $token->test(['case', 'default', 'endswitch']);
    }

    public function decideIfEnd(\Twig\Token $token) {
        return $token->test(['endswitch']);
    }

    public function getTag() {
        return 'switch';
    }
}