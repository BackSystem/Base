<?php

$finder = PhpCsFixer\Finder::create()->in('./');

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@Symfony' => true,
])->setFinder($finder);