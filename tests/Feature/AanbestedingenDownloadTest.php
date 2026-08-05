<?php

declare(strict_types=1);

it('page offers the laravel tender package as a download', function () {
    $response = $this->get('/aanbestedingen');

    $response->assertOk();
    $response->assertSee('Download het Laravel Aanbestedingspakket');
    $response->assertSee('Download PDF');
    $response->assertSee(
        'href="/assets/uploads/assets/laravel-aanbestedingspakket.pdf" download',
        false,
    );
    expect(public_path('assets/uploads/assets/laravel-aanbestedingspakket.pdf'))->toBeFile();
});
