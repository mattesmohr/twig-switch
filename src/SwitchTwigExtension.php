<?php

class SwitchTwigExtension extends \Twig\Extension\AbstractExtension {

    public function getTokenParsers() {
        return [new SwitchTokenParser()];
    }

    public function getName() {
        return 'switch';
    }
}