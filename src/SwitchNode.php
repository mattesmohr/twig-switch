<?php

namespace mattesmohr\TwigSwitch;

class SwitchNode extends \Twig\Node\Node {

    public function __construct(\Twig\Node\Node $expression, \Twig\Node\Node $cases, ?\Twig\Node\Node $default, $line) {

        $nodes = ['expression' => $expression, 'cases' => $cases];

        if ($default !== null) {
            $nodes['default'] = $default;
        }

        parent::__construct($nodes, [], $line);
    }

    public function compile(\Twig\Compiler $compiler) {

        $compiler->addDebugInfo($this);

        $compiler
            ->write('switch (')
            ->subcompile($this->getNode('expression'))
            ->raw(") {\n")
            ->indent();

        for ($i = 0, $count = \count($this->getNode('cases')); $i < $count; $i += 2) {

            $compiler
                ->write('case ')
                ->subcompile($this->getNode('cases')->getNode((string) $i))
                ->raw(":\n");

            $compiler
                ->indent()
                ->subcompile($this->getNode('cases')->getNode((string) ($i + 1)))
                ->write("break;\n")
                ->outdent();
        }

        if ($this->hasNode('default')) {

            $compiler
                ->write("default:\n")
                ->indent()
                ->subcompile($this->getNode('default'))
                ->outdent();
        }

        $compiler
            ->outdent()
            ->write("}\n");
    }
}