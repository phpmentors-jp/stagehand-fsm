<?php
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/test')
    ;

return Symfony\CS\Config\Config::create()
    ->fixers(array('-remove_lines_between_uses', '-empty_return'))
    ->finder($finder)
    ;
