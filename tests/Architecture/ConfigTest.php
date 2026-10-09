<?php

test('the codebase does not reference env variables outside of config files')
    ->expect('env')
    ->not->toBeUsed();
