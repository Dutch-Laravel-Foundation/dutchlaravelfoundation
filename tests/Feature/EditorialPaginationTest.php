<?php

declare(strict_types=1);

test('out of range page numbers are not found instead of failing', function () {
    foreach (['/nieuws', '/kennis', '/podcast', '/agenda'] as $path) {
        $this->get("{$path}?page=99999999999999999999")->assertNotFound();
        $this->get("{$path}?page=1001")->assertNotFound();
        $this->get("{$path}?page=2")->assertOk();
    }
});
