<?php

test('index page can be rendered', function () {
    $response = $this
        ->get('/');

    $response->assertOk()
        ->assertViewIs('index');
});
