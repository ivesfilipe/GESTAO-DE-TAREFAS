<?php

test('the root route redirects to login for guests', function () {
    $this->get('/')->assertRedirect('/login');
});
