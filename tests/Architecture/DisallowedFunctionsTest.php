<?php

// sleep and usleep should use the Sleep facade instead
test('the codebase does not use sleep or usleep. use the Sleep facade instead')
    ->expect(['usleep', 'sleep'])
    ->not->toBeUsed();
