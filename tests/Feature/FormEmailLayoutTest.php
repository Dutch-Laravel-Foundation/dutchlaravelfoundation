<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\Email;
use Tests\TestCase;

final class FormEmailLayoutTest extends TestCase
{
    public function testEveryFormEmailUsesTheBrandedLaravelMarkdownLayout(): void
    {
        $transport = Mail::mailer()->getSymfonyTransport();
        $transport->flush();

        $renderedEmails = 0;

        foreach ($this->formSubmissions() as $formHandle => $submissionData) {
            $form = Form::find($formHandle);

            $this->assertNotNull($form);

            $submission = $form->makeSubmission()->data($submissionData);
            $emailConfigs = $form->email();
            $emailConfigs = isset($emailConfigs['to']) ? [$emailConfigs] : $emailConfigs;

            foreach ($emailConfigs as $index => $emailConfig) {
                $notification = "{$formHandle} email {$index}";

                $this->assertTrue(
                    $emailConfig['markdown'] ?? false,
                    "{$notification} must use Laravel Markdown.",
                );
                $this->assertArrayNotHasKey('text', $emailConfig);
                $this->assertIsString($emailConfig['html'] ?? null);

                Mail::send(new Email($submission, $emailConfig, Site::default()));

                $message = $transport->messages()->last()->getOriginalMessage();
                $html = $message->getHtmlBody();
                $text = $message->getTextBody();

                $this->assertIsString($html);
                $this->assertIsString($text);
                $this->assertStringContainsString('class="wrapper"', $html);
                $this->assertStringContainsString('class="inner-body"', $html);
                $this->assertStringContainsString('background-color: #ff2d20', $html);
                $this->assertStringContainsString('/assets/redesign/logo-email.png', $html);
                $this->assertNotSame('', trim($text));

                $renderedEmails++;
            }
        }

        $this->assertSame(6, $renderedEmails);
        $this->assertCount(6, $transport->messages());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function formSubmissions(): array
    {
        return [
            'become_member' => [
                'name' => 'Robin de Vries',
                'email' => 'robin@example.test',
                'company_name' => 'Voorbeeld Laravel Bureau',
                'phone' => '+31 20 123 45 67',
                'remarks' => "We willen met ons team aansluiten | en kennis delen.\nBel ons gerust.",
                'agree' => true,
            ],
            'contact' => [
                'name' => 'Sam Jansen',
                'company_name' => 'Voorbeeldorganisatie',
                'email' => 'sam@example.test',
                'remarks' => "Kunnen jullie ons helpen met een Laravel-vraag?\nAlvast bedankt.",
                'agree' => true,
            ],
            'newsletter' => [
                'email' => 'nieuwsbrief@example.test',
            ],
            'sales_funnel' => [
                'product' => 'application',
                'product_label' => 'Ontwikkelen van een applicatie/portal',
                'description' => 'We zoeken een ervaren Laravel-partner.',
                'budget' => '10000-25000',
                'budget_label' => '€ 10.000 – € 25.000',
                'company_type' => 'agency',
                'company_type_label' => 'Bureau / Agency',
                'name' => 'Noa Bakker',
                'company_name' => 'Voorbeeldbedrijf',
                'email' => 'noa@example.test',
                'last_completed_step' => '4',
            ],
        ];
    }
}
