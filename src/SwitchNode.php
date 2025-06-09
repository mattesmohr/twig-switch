<?php

class SwitchNode extends \Twig\Node\Node {

    public function __construct($name, \Twig\Node\Node $tests, ?\Twig\Node\Node $default, $line) {
        parent::__construct(['tests' => $tests, 'default' => $default], ['name' => $name], $line);
    }

    public function compile(\Twig\Compiler $compiler) {

        $compiler->addDebugInfo($this);

        $compiler
            ->write('switch ($context[\''.$this->getAttribute('name').'\']')
            ->raw(") {\n")
            ->indent();

        for ($i = 0, $count = \count($this->getNode('tests')); $i < $count; $i += 2) {

            $compiler
                ->write('case ')
                ->subcompile($this->getNode('tests')->getNode((string) $i))
                ->raw(":\n");

            $compiler
                ->indent()
                ->subcompile($this->getNode('tests')->getNode((string) ($i + 1)))
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