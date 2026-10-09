<?php

test('the codebase does not use dump statements')
    ->expect(['dd', 'dump', 'ddd', 'print_r', 'dump', 'print', 'exit', 'die'])
    ->not->toBeUsed();
