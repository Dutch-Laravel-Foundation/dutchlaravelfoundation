<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Mail;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\Email;

it('sales funnel email uses the laravel markdown layout and dlf logo', function () {
    $form = Form::find('sales_funnel');

    expect($form)->not->toBeNull();

    $emailConfig = $form->email()[0];

    expect($emailConfig['markdown'] ?? false)->toBeTrue();
    $this->assertArrayNotHasKey('text', $emailConfig);

    $submission = $form->makeSubmission()->data([
        'product' => 'application',
        'product_label' => 'Ontwikkelen van een applicatie/portal',
        'description' => "Een heldere omschrijving met een | teken\nen een nieuwe regel.",
        'budget' => '0-10000',
        'budget_label' => '€ 0 – € 10.000',
        'company_type' => 'agency',
        'company_type_label' => 'Bureau / Agency',
        'name' => 'Taylor',
        'company_name' => 'Dutch Laravel Foundation',
        'email' => 'formulier@example.test',
        'last_completed_step' => '4',
    ]);

    $mailable = new Email($submission, $emailConfig, Site::default());

    Mail::send($mailable);

    $message = Mail::mailer()->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    $html = $message->getHtmlBody();
    $text = $message->getTextBody();

    expect($html)->toBeString();
    expect($text)->toBeString();
    $this->assertStringContainsString('class="wrapper"', $html);
    $this->assertStringContainsString('class="inner-body"', $html);
    $this->assertStringContainsString('/assets/redesign/logo-email.png', $html);
    $this->assertStringContainsString('alt="Dutch Laravel Foundation"', $html);
    $this->assertStringContainsString('Aanvraag via Dutch Laravel Foundation', $html);
    $this->assertStringContainsString('Ontwikkelen van een applicatie/portal', $html);
    $this->assertStringContainsString('formulier@example.test', $html);
    $this->assertStringContainsString(
        'Dutch Laravel Foundation. Alle rechten voorbehouden.',
        $html,
    );
    $this->assertStringNotContainsString('{{ name }}', $html);
    $this->assertStringContainsString('Aanvraag via Dutch Laravel Foundation', $text);
    $this->assertStringContainsString('formulier@example.test', $text);
});
