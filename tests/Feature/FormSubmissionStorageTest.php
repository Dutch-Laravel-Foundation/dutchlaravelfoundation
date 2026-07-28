<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class FormSubmissionStorageTest extends TestCase
{
    public function testFormsUseALocalWritableSubmissionSymlink(): void
    {
        $projectPath = dirname(__DIR__, 2);
        $submissionPath = $projectPath . '/storage/forms';
        $forms = glob($projectPath . '/resources/forms/*.yaml') ?: [];

        $this->assertNotEmpty($forms);
        $this->assertTrue(is_link($submissionPath));
        $this->assertSame('form-submissions', readlink($submissionPath));
        $this->assertSame(
            $projectPath . '/storage/form-submissions',
            realpath($submissionPath),
        );

        foreach ($forms as $form) {
            $directory = $submissionPath . '/' . pathinfo($form, PATHINFO_FILENAME);

            $this->assertDirectoryExists($directory);
            $this->assertDirectoryIsWritable($directory);
        }
    }

    public function testSalesFunnelSubmissionCanBeStored(): void
    {
        Mail::fake();
        config()->set('captcha.forms', []);

        $submissionPath = storage_path('forms/sales_funnel');
        $submissionsBefore = glob($submissionPath . '/*.yaml') ?: [];

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

            $submissionsAfter = glob($submissionPath . '/*.yaml') ?: [];
            $createdSubmissions = array_values(array_diff($submissionsAfter, $submissionsBefore));

            $this->assertCount(1, $createdSubmissions);

            $contents = file_get_contents($createdSubmissions[0]);

            $this->assertIsString($contents);
            $this->assertStringContainsString('formulier-opslag@example.test', $contents);
        } finally {
            $submissionsAfter = glob($submissionPath . '/*.yaml') ?: [];

            foreach (array_diff($submissionsAfter, $submissionsBefore) as $submission) {
                unlink($submission);
            }
        }
    }
}
