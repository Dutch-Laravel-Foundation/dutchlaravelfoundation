<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\Forms\FormDefinitionDataMapper;
use App\Content\Forms\FormsRepository;
use App\Content\PublicPages\PublicPageDataMapper;
use App\Content\PublicPages\PublicPageRepository;
use App\Content\SiteShell\SiteShellDataMapper;
use App\Content\SiteShell\SiteShellRepository;
use App\Data\Forms\AcquisitionPageData;
use App\Data\Forms\FormDefinitionData;
use App\Data\Forms\FormSubmissionStateData;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Inertia\Inertia;
use Inertia\Response;

final readonly class AcquisitionPageController
{
    /** @var array<string, array{0: string, 1: string|null}> */
    private const array PAGES = [
        '/contact' => ['Forms/Contact', 'contact'],
        '/lid-worden' => ['Forms/BecomeMember', 'become_member'],
        '/aanvraag' => ['Forms/SalesFunnel', 'sales_funnel'],
        '/aanvraag/bedankt' => ['Forms/Thanks', null],
    ];

    public function __invoke(
        Request $request,
        PublicPageRepository $pages,
        PublicPageDataMapper $pageMapper,
        FormsRepository $forms,
        FormDefinitionDataMapper $formMapper,
        SiteShellRepository $siteShell,
        SiteShellDataMapper $siteShellMapper,
    ): Response {
        $uri = '/'.ltrim($request->path(), '/');
        $configuration = self::PAGES[$uri] ?? null;

        if ($configuration === null) {
            abort(404);
        }

        [$component, $formHandle] = $configuration;
        $page = $pageMapper->map($pages->findByUri($uri));

        if ($page === null) {
            abort(404);
        }

        $form = $this->form($formHandle, $forms, $formMapper);

        return Inertia::render($component, [
            'acquisition' => new AcquisitionPageData(
                page: $page,
                form: $form,
                submission: $this->submission($request, $form),
            ),
            'site' => $siteShellMapper->map($siteShell->fetch(), $request->getRequestUri()),
        ]);
    }

    private function form(
        ?string $handle,
        FormsRepository $forms,
        FormDefinitionDataMapper $mapper,
    ): ?FormDefinitionData {
        if ($handle === null) {
            return null;
        }

        $form = $forms->find($handle);

        if ($form === null) {
            abort(404);
        }

        return $mapper->map($form);
    }

    private function submission(Request $request, ?FormDefinitionData $form): FormSubmissionStateData
    {
        if ($form === null) {
            return new FormSubmissionStateData(false, [], []);
        }

        $errorBag = $request->session()->get('errors');
        $errors = $errorBag instanceof ViewErrorBag
            ? $errorBag->getBag("form.{$form->handle}")->getMessages()
            : [];

        $firstErrors = [];

        foreach ($errors as $field => $messages) {
            if (is_string($field) && is_array($messages) && is_string($messages[0] ?? null)) {
                $firstErrors[$field] = $messages[0];
            }
        }

        $oldInput = $request->session()->get('_old_input', []);
        $old = array_filter(
            is_array($oldInput) ? $oldInput : [],
            static fn (mixed $value, string $key): bool => ! str_starts_with($key, '_')
                && $key !== $form->honeypot,
            ARRAY_FILTER_USE_BOTH,
        );

        return new FormSubmissionStateData(
            success: (bool) $request->session()->get("form.{$form->handle}.success"),
            errors: $firstErrors,
            old: $old,
        );
    }
}
