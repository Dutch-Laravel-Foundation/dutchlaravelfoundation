<?php

declare(strict_types=1);

namespace Tests\Feature\Content\Forms;

use App\Content\Forms\FormDefinitionDataMapper;
use App\Content\Forms\FormsRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatamicFormsRepositoryTest extends TestCase
{
    #[Test]
    public function all_react_form_definitions_match_the_live_graphql_schema(): void
    {
        $repository = $this->app->make(FormsRepository::class);

        foreach (['become_member', 'contact', 'sales_funnel'] as $handle) {
            $form = $repository->find($handle);

            $this->assertNotNull($form);
            $this->assertSame($handle, $form['handle']);
            $this->assertNotEmpty($form['fields']);
            $this->assertNotEmpty((new FormDefinitionDataMapper)->map($form)->rules);
        }
    }
}
