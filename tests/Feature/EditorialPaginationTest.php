<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class EditorialPaginationTest extends TestCase
{
    public function test_out_of_range_page_numbers_are_not_found_instead_of_failing(): void
    {
        foreach (['/nieuws', '/kennis', '/podcast', '/agenda'] as $path) {
            $this->get("{$path}?page=99999999999999999999")->assertNotFound();
            $this->get("{$path}?page=1001")->assertNotFound();
            $this->get("{$path}?page=2")->assertOk();
        }
    }
}
