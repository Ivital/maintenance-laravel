<?php

it('builds the Laravel view cache without a package view directory', function () {
    $this->artisan('view:cache')->assertSuccessful();
    $this->artisan('view:clear')->assertSuccessful();
});
