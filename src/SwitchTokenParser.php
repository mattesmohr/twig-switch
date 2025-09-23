<?php

namespace mattesmohr\TwigSwitch;

class SwitchTokenParser extends \Twig\TokenParser\AbstractTokenParser {

    public function parse(\Twig\Token $token) {

        $stream = $this->parser->getStream();

        $expression = [];

        // Capture the entire switch expression
        $expression[] = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        // Parse till you hit one of the symbols
        $this->parser->subparse([$this, 'decideCaseBranch']);

        $cases = [];

        $default = null;

        $continue = true;

        while ($continue) {
            switch ($stream->next()->getValue()) {
                case 'case':

                    // Parse the expression of the case
                    $cases[] = $this->parser->getExpressionParser()->parseExpression();

                    $stream->expect(\Twig\Token::BLOCK_END_TYPE);

                    // Parse the body of the case
                    $cases[] = $this->parser->subparse([$this, 'decideCaseBranch']);

                    break;

                case 'default':

                    $stream->expect(\Twig\Token::BLOCK_END_TYPE);

                    /// Parse the body of the default
                    $default = $this->parser->subparse([$this, 'decideSwitchEnd']);;

                    break;

                case 'endswitch':

                    $continue = false;

                    break;

                default:
                    throw new \Twig\Error\SyntaxError(\sprintf('Unexpected end of template.'));
            }
        }

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new SwitchNode(new \Twig\Node\Node($expression), new \Twig\Node\Node($cases), $default, $token->getLine());
    }

    public function decideCaseBranch(\Twig\Token $token) {
        return $token->test(['case', 'default', 'endswitch']);
    }

    public function decideSwitchEnd(\Twig\Token $token) {
        return $token->test(['endswitch']);
    }

    public function getTag() {
        return 'switch';
    }
}