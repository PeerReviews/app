<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/moodle/mod/studentqcm')
    ->name('*.php');

$config = new PhpCsFixer\Config();
$config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
]);

return $config->setFinder($finder);
