<?php

declare(strict_types=1);
use Symfony\Component\Yaml\Yaml;

it('authors collection is cms only', function () {
    $collection = knowledgeParseYaml(base_path('content/collections/authors.yaml'));

    expect($collection['title'] ?? null)->toBe('Auteurs');
    $this->assertArrayNotHasKey('route', $collection);
    $this->assertArrayNotHasKey('template', $collection);
});
it('knowledge blueprint uses reusable authors', function () {
    $authorBlueprint = knowledgeParseYaml(base_path('resources/blueprints/collections/authors/author.yaml'));
    $knowledgeBlueprint = knowledgeParseYaml(base_path('resources/blueprints/collections/knowledge/knowledge.yaml'));
    $authorFields = knowledgeFieldsByHandle($authorBlueprint);
    $knowledgeFields = knowledgeFieldsByHandle($knowledgeBlueprint);

    foreach (['title', 'job_title', 'photo', 'description', 'linkedin_url', 'website_url'] as $handle) {
        expect($authorFields)->toHaveKey($handle);
    }

    expect($knowledgeFields['authors']['type'] ?? null)->toBe('entries');
    expect($knowledgeFields['authors']['collections'] ?? null)->toBe(['authors']);
    expect($knowledgeFields['authors']['reorderable'] ?? false)->toBeTrue();
    $this->assertArrayNotHasKey('max_items', $knowledgeFields['authors']);

    foreach (['author_name', 'author_role', 'author_bio', 'author_image', 'author_link'] as $legacyHandle) {
        $this->assertArrayNotHasKey($legacyHandle, $knowledgeFields);
    }
});
it('bob kosse is migrated to the authors collection', function () {
    $author = knowledgeParseFrontMatter(base_path('content/collections/authors/bob-kosse.md'));
    $article = knowledgeParseFrontMatter(base_path('content/collections/knowledge/2026-07-01-2200.het-belang-van-toegankelijke-websites.md'));

    expect($author['id'] ?? null)->toBe('fe71f8d9-3bfe-4f29-af8b-c1726e2b4849');
    expect($author['blueprint'] ?? null)->toBe('author');
    expect($author['title'] ?? null)->toBe('Bob Kosse');
    expect($author['job_title'] ?? null)->toBe('Onafhankelijk Laravel-ontwikkelaar');
    expect($author['photo'] ?? null)->toBe('introductie-afbeelding-bob-kosse.png');
    expect($author['website_url'] ?? null)->toBe('https://www.ikverstajeniet.nl');
    expect($article['authors'] ?? null)->toBe(['fe71f8d9-3bfe-4f29-af8b-c1726e2b4849']);

    foreach (['author_name', 'author_role', 'author_bio', 'author_image', 'author_link'] as $legacyHandle) {
        $this->assertArrayNotHasKey($legacyHandle, $article);
    }
});
it('knowledge article renders its assigned author', function () {
    $response = $this->get('/kennis/het-belang-van-toegankelijke-websites');

    $response->assertOk();
    $response->assertSee('data-knowledge-authors', false);
    $response->assertSee('Over de auteur', false);
    $response->assertSee('Bob Kosse', false);
    $response->assertSee('Onafhankelijk Laravel-ontwikkelaar', false);
    $response->assertSee('Website van Bob Kosse', false);
    $response->assertSee('href="https://www.ikverstajeniet.nl"', false);
    $response->assertSee('LinkedIn van Bob Kosse', false);
    $response->assertSee('href="https://www.linkedin.com/in/bobkosse/"', false);
});
it('knowledge article without authors renders no author ui', function () {
    $response = $this->get('/kennis/ai-gedreven-zoekfunctionaliteit-dankzij-vragenai');

    $response->assertOk();
    $response->assertDontSee('data-knowledge-authors', false);
    $response->assertDontSee('editorial-article__author-summary', false);
});
it('inline knowledge credits are migrated to reusable authors', function () {
    $authors = [
        'bob-van-biezen.md' => [
            'id' => 'e4be4882-49b4-4f7a-b9d6-eee27d701246',
            'title' => 'Bob van Biezen',
            'linkedin_url' => 'https://www.linkedin.com/in/bobvanbiezen/',
            'website_url' => 'https://webwhales.nl/',
            'photo' => 'bob-van-biezen.jpeg',
        ],
        'dennis-koster.md' => [
            'id' => 'ae0015ae-5d98-4b16-b952-3845bab456b4',
            'title' => 'Dennis Koster',
            'linkedin_url' => 'https://www.linkedin.com/in/dennis-koster-688b7b48/',
            'website_url' => 'https://endeavour.nl',
        ],
        'nick-retel.md' => [
            'id' => 'dee61b68-cabe-4246-b927-2cdcddacb8ba',
            'title' => 'Nick Retel',
            'linkedin_url' => 'https://www.linkedin.com/in/nckrtl/',
            'website_url' => 'https://nckrtl.com',
            'photo' => 'nick-retel.jpg',
        ],
        'timo-feenstra.md' => [
            'id' => '642a1cce-4a7f-4c72-a43e-b074f9ebfc7b',
            'title' => 'Timo Feenstra',
            'linkedin_url' => 'https://www.linkedin.com/in/timofeenstra/',
            'website_url' => 'https://www.shockmedia.nl/',
        ],
        'sven-mollinga.md' => [
            'id' => '2ffdfef5-6ff8-4638-8485-3c366fd05a12',
            'title' => 'Sven Mollinga',
            'linkedin_url' => 'https://www.linkedin.com/in/svenmollinga/',
            'website_url' => 'https://www.shockmedia.nl/',
            'photo' => 'sven-mollinga.jpeg',
        ],
        'justin-aan-de-stegge.md' => [
            'id' => '32edbb81-2c4f-4c9c-829a-c3604113a2c4',
            'title' => 'Justin aan de Stegge',
            'linkedin_url' => 'https://www.linkedin.com/in/justin-aan-de-stegge/',
            'website_url' => 'https://www.shockmedia.nl/',
            'photo' => 'justin-aan-de-stegge.png',
        ],
        'reinier-sierag.md' => [
            'id' => 'f1aada63-5de9-4833-a13d-3aafdc7992cb',
            'title' => 'Reinier Sierag',
            'linkedin_url' => 'https://www.linkedin.com/in/sierag/',
            'website_url' => 'https://kobaltdigital.nl/',
            'photo' => 'reinier-sierag.jpg',
        ],
        'mattias-geniar.md' => [
            'id' => '8208e028-25f9-4896-ade9-5b85d09c62e5',
            'title' => 'Mattias Geniar',
            'linkedin_url' => 'https://www.linkedin.com/in/mattiasgeniar/',
            'website_url' => 'https://ohdear.app',
            'photo' => 'mattias-geniar.jpg',
        ],
        'jason-mccreary.md' => [
            'id' => 'bb8a4316-87d8-4bfa-aa79-8e30514a9cb7',
            'title' => 'Jason McCreary',
            'linkedin_url' => 'https://www.linkedin.com/in/jasonmccreary/',
            'website_url' => 'https://laravelshift.com',
            'photo' => 'jason-mccreary.jpg',
        ],
    ];

    foreach ($authors as $filename => $expected) {
        $author = knowledgeParseFrontMatter(base_path("content/collections/authors/{$filename}"));

        expect($author['blueprint'] ?? null)->toBe('author');

        foreach ($expected as $field => $value) {
            expect($author[$field] ?? null)->toBe($value, "Unexpected {$field} for {$filename}");
        }
    }

    $articles = [
        '2024-09-02.laravel-meer-dan-een-framework.md' => ['e4be4882-49b4-4f7a-b9d6-eee27d701246'],
        '2024-10-01.graphql-met-laravel-en-lighthouse.md' => ['ae0015ae-5d98-4b16-b952-3845bab456b4'],
        '2025-04-07.consistente-opmaak-van-je-php-code-met-laravel-pint.md' => ['dee61b68-cabe-4246-b927-2cdcddacb8ba'],
        '2025-08-04.cybersecurity-voor-webapps-bescherm-je-applicatie-tegen-aanvallen.md' => ['642a1cce-4a7f-4c72-a43e-b074f9ebfc7b'],
        '2025-10-06.observability-in-laravel.md' => ['2ffdfef5-6ff8-4638-8485-3c366fd05a12'],
        '2025-12-15.hostingmigraties-planning-communicatie-en-organisatie.md' => [
            '32edbb81-2c4f-4c9c-829a-c3604113a2c4',
            'f1aada63-5de9-4833-a13d-3aafdc7992cb',
        ],
        '2026-01-03.razendsnelle-php-tooling-met-mago.md' => ['ae0015ae-5d98-4b16-b952-3845bab456b4'],
        '2026-02-11.common-ground-en-wat-dit-betekent-voor-laravel-developers.md' => ['32edbb81-2c4f-4c9c-829a-c3604113a2c4'],
        '2026-02-25-2300.sql-performance-bewaken-met-automatische-detectie-en-regressietests-in-laravel.md' => ['8208e028-25f9-4896-ade9-5b85d09c62e5'],
        '2026-03-16-2300.automate-your-laravel-upgrades-with-shift.md' => ['bb8a4316-87d8-4bfa-aa79-8e30514a9cb7'],
        '2026-04-08-2200.viteplus-als-enige-frontend-tool-in-je-laravel-project.md' => ['dee61b68-cabe-4246-b927-2cdcddacb8ba'],
        '2026-05-25-2200.ai-workloads-hosten-in-productie.md' => ['2ffdfef5-6ff8-4638-8485-3c366fd05a12'],
        '2026-06-11-2200.under-the-hood-of-shift.md' => ['bb8a4316-87d8-4bfa-aa79-8e30514a9cb7'],
    ];

    foreach ($articles as $filename => $authorIds) {
        $path = base_path("content/collections/knowledge/{$filename}");
        $article = knowledgeParseFrontMatter($path);

        expect($article['authors'] ?? null)->toBe($authorIds, "Unexpected authors for {$filename}");
        $this->assertStringNotContainsStringIgnoringCase('over de auteur', bodyAfterFrontMatter($path));
    }
});
it('knowledge article renders multiple assigned authors', function () {
    $response = $this->get('/kennis/hostingmigraties-planning-communicatie-en-organisatie');

    $response->assertOk();
    $response->assertSee('Over de auteurs', false);
    $response->assertSee('editorial-author__list--multiple', false);
    $response->assertSeeInOrder(['Justin aan de Stegge', 'Reinier Sierag'], false);
});
it('knowledge author links use accessible icons', function () {
    $response = $this->get('/kennis/viteplus-als-enige-frontend-tool-in-je-laravel-project');

    $response->assertOk();
    $response->assertSee('aria-label="LinkedIn van Nick Retel"', false);
    $response->assertSee('aria-label="Website van Nick Retel"', false);
    $response->assertSee('editorial-author__icon', false);
    $response->assertSee('src="/assets/redesign/socials/linkedin.svg"', false);
    $response->assertDontSee('dlf-btn-face--red">LinkedIn van Nick Retel', false);
    $response->assertDontSee('dlf-btn-face--red">Website van Nick Retel', false);
});
it('knowledge author portraits are borderless', function () {
    $stylesheet = file_get_contents(base_path('resources/css/redesign-editorial.css'));

    expect($stylesheet)->toBeString();
    expect($stylesheet)->toMatch('/\.editorial-author__portrait\s*\{[^}]*border:\s*0;/s');
    expect($stylesheet)->toMatch('/\.editorial-author__link\s*\{[^}]*border-radius:\s*4px;/s');
});
it('published knowledge articles do not use break tags for spacing', function () {
    $paths = glob(base_path('content/collections/knowledge/*.md'));
    expect($paths)->toBeArray();
    expect($paths)->not->toBeEmpty();

    foreach ($paths as $path) {
        $body = bodyAfterFrontMatter($path);

        $this->assertDoesNotMatchRegularExpression('/<br\s*\/?>/i', $body, "Break tag found in {$path}");
    }
});
it('hosting migration lists render markers without break tags', function () {
    $response = $this->get('/kennis/hostingmigraties-planning-communicatie-en-organisatie');
    $stylesheet = file_get_contents(base_path('resources/css/redesign-editorial.css'));

    $response->assertOk();
    preg_match(
        '/<article\b[^>]*class="[^"]*\beditorial-article__prose\b[^"]*"[^>]*>(.*?)<\/article>/s',
        $response->getContent(),
        $article,
    );

    expect($article)->toHaveCount(2);
    $this->assertDoesNotMatchRegularExpression(
        '/<li\b[^>]*>(?:(?!<\/li>).)*<br\s*\/?>/s',
        $article[1],
    );
    expect($stylesheet)->toBeString();
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose ol:not\(\.dlf-block \*\)\s*\{[^}]*list-style:\s*decimal;/s');
});
it('knowledge template uses the reusable authors relationship', function () {
    $template = file_get_contents(base_path('resources/views/templates/knowledge/show.antlers.html'));
    $partial = file_get_contents(base_path('resources/views/partials/editorial/_knowledge-authors.antlers.html'));

    expect($template)->toBeString();
    expect($partial)->toBeString();
    $this->assertStringContainsString('{{ authors }}', $template);
    $this->assertStringContainsString('partial:editorial/knowledge-authors', $template);
    $this->assertStringContainsString('{{ authors }}', $partial);
    $this->assertStringContainsString('linkedin_url', $partial);
    $this->assertStringContainsString('website_url', $partial);
    $this->assertStringNotContainsString('author_name', $template);
});
/** @return array<string, mixed> */
function knowledgeParseYaml(string $path): array
{
    expect($path)->toBeFile();

    return Yaml::parseFile($path);
}
/** @return array<string, mixed> */
function knowledgeParseFrontMatter(string $path): array
{
    expect($path)->toBeFile();

    $contents = file_get_contents($path);
    expect($contents)->toBeString();
    expect(preg_match('/^---\R(.*?)\R---/s', $contents, $matches))->toBe(1);

    return Yaml::parse($matches[1]);
}
function bodyAfterFrontMatter(string $path): string
{
    $contents = file_get_contents($path);
    expect($contents)->toBeString();
    expect(preg_match('/^---\R.*?\R---\R(.*)$/s', $contents, $matches))->toBe(1);

    return $matches[1];
}
/**
 * @param  array<string, mixed>  $node
 * @return array<string, array<string, mixed>>
 */
function knowledgeFieldsByHandle(array $node): array
{
    $fields = [];

    foreach ($node as $key => $value) {
        if ($key === 'handle' && is_string($value) && isset($node['field']) && is_array($node['field'])) {
            $fields[$value] = $node['field'];
        }

        if (is_array($value)) {
            $fields = array_merge($fields, knowledgeFieldsByHandle($value));
        }
    }

    return $fields;
}
