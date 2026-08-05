<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Mail;

it('forms use a local writable submission symlink', function () {
    $projectPath = dirname(__DIR__, 2);
    $submissionPath = $projectPath.'/storage/forms';
    $forms = glob($projectPath.'/resources/forms/*.yaml') ?: [];

    expect($forms)->not->toBeEmpty();
    expect(is_link($submissionPath))->toBeTrue();
    expect(readlink($submissionPath))->toBe('form-submissions');
    expect(realpath($submissionPath))->toBe($projectPath.'/storage/form-submissions');

    foreach ($forms as $form) {
        $directory = $submissionPath.'/'.pathinfo($form, PATHINFO_FILENAME);

        expect($directory)->toBeDirectory();
        expect($directory)->toBeWritableDirectory();
    }
});
it('sales funnel submission can be stored', function () {
    Mail::fake();
    config()->set('captcha.forms', []);

    $submissionPath = storage_path('forms/sales_funnel');
    $submissionsBefore = glob($submissionPath.'/*.yaml') ?: [];

    try {
        $response = $this
            ->from('/aanvraag')
            ->post('/!/forms/sales_funnel', [
                '_redirect' => '/aanvraag/bedankt',
                'product' => 'website',
                'product_label' => 'Website',
                'description' => 'Controle van lokale formulieropslag.',
                'budget' => '10000-25000',
                'budget_label' => '€ 10.000 – € 25.000',
                'company_type' => 'no_preference',
                'company_type_label' => 'Geen voorkeur',
                'name' => 'Formulier Opslagtest',
                'company_name' => 'Dutch Laravel Foundation',
                'email' => 'formulier-opslag@example.test',
                'last_completed_step' => '4',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/aanvraag/bedankt');

        $submissionsAfter = glob($submissionPath.'/*.yaml') ?: [];
        $createdSubmissions = array_values(array_diff($submissionsAfter, $submissionsBefore));

        expect($createdSubmissions)->toHaveCount(1);

        $contents = file_get_contents($createdSubmissions[0]);

        expect($contents)->toBeString();
        $this->assertStringContainsString('formulier-opslag@example.test', $contents);
    } finally {
        $submissionsAfter = glob($submissionPath.'/*.yaml') ?: [];

        foreach (array_diff($submissionsAfter, $submissionsBefore) as $submission) {
            unlink($submission);
        }
    }
});
