<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Models\Facility;
use App\Models\PortalHelpRequest;
use App\Services\PortalHelpRequestService;
use App\Support\SelectedFacility;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MemberPortalHelpController extends Controller
{
    use ProvidesMemberPortalContext;

    public function index(Request $request): View
    {
        $user = $request->user();
        $requests = PortalHelpRequest::query()
            ->with('facility:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.member.help.index', array_merge($this->memberPortalContext($user), [
            'helpRequests' => $requests,
            'portalActive' => 'help',
            'portalTitle' => 'Help Center | Bio Pacific',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => 'My help requests',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function hrForm(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.member.help.hr', array_merge($this->memberPortalContext($user), $this->formContext($user, PortalHelpRequest::TYPE_HR)));
    }

    public function supportForm(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.member.help.support', array_merge($this->memberPortalContext($user), $this->formContext($user, PortalHelpRequest::TYPE_SUPPORT)));
    }

    public function manuals(Request $request): View
    {
        $user = $request->user();
        $manuals = $this->manualCatalog();

        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $filtered = $manuals
            ->when($category !== '', fn (Collection $rows) => $rows->where('category', $category)->values())
            ->when($search !== '', function (Collection $rows) use ($search) {
                $needle = mb_strtolower($search);

                return $rows
                    ->map(function (array $manual) use ($needle) {
                        $match = $this->manualSearchMatch($manual, $needle);
                        if ($match === null) {
                            return null;
                        }

                        return array_merge($manual, [
                            'match_in_content' => $match['in_content'],
                            'match_snippet' => $match['snippet'],
                        ]);
                    })
                    ->filter()
                    ->values();
            });

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('dashboard.member.help.manuals', array_merge($this->memberPortalContext($user), [
            'manuals' => $paginator,
            'manualCategories' => $manuals->pluck('category')->unique()->sort()->values(),
            'manualFilters' => [
                'q' => $search,
                'category' => $category,
                'per_page' => $perPage,
            ],
            'manualTotalCount' => $manuals->count(),
            'portalActive' => 'help-manuals',
            'portalTitle' => 'Manuals and Docs | Bio Pacific',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => 'Manuals and Docs',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function userManual(Request $request)
    {
        return $this->portalDocumentPdf($request, 'HR_PORTAL_USER_MANUAL.md');
    }

    public function portalDocumentPdf(Request $request, string $document)
    {
        $meta = $this->portalDocumentMeta($document);
        abort_unless($meta !== null, 404);

        $path = base_path('docs/'.$meta['path']);
        abort_unless(is_file($path), 404);

        $search = trim((string) $request->query('q', ''));

        $markdown = $this->rewritePortalDocumentLinks((string) file_get_contents($path));
        $content = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $content = $this->addHeadingAnchors($content);

        if ($search !== '') {
            $content = $this->highlightFirstSearchMatch($content, $search);
        }

        $pdf = Pdf::loadView('dashboard.member.help.user-manual-pdf', [
            'content' => $content,
            'documentTitle' => $meta['title'],
            'updatedAt' => date('F j, Y', (int) filemtime($path)),
            'searchQuery' => $search,
        ])
            ->setPaper('letter');

        $filename = Str::replaceEnd('.md', '.pdf', basename($meta['path']));

        return $pdf->stream($filename);
    }

    /**
     * @return Collection<int, array{key: string, path: string, title: string, description: string, category: string, icon: string}>
     */
    protected function manualCatalog(): Collection
    {
        return collect(config('portal-help.manuals', []))
            ->filter(fn ($manual) => is_array($manual) && filled($manual['key'] ?? null) && filled($manual['path'] ?? null))
            ->map(fn (array $manual) => [
                'key' => (string) $manual['key'],
                'path' => (string) $manual['path'],
                'title' => (string) ($manual['title'] ?? $manual['key']),
                'description' => (string) ($manual['description'] ?? ''),
                'category' => (string) ($manual['category'] ?? 'Reference'),
                'icon' => (string) ($manual['icon'] ?? 'fa-book'),
            ])
            ->values();
    }

    /**
     * @param  array{key: string, path: string, title: string, description: string, category: string, icon: string}  $manual
     * @return array{in_content: bool, snippet: ?string}|null
     */
    protected function manualSearchMatch(array $manual, string $needle): ?array
    {
        if ($needle === '') {
            return ['in_content' => false, 'snippet' => null];
        }

        $meta = mb_strtolower(implode(' ', [
            $manual['title'] ?? '',
            $manual['description'] ?? '',
            $manual['category'] ?? '',
        ]));
        $body = $this->manualSearchableBody($manual);
        $haystack = trim($meta.' '.$body);

        if ($haystack === '' || ! $this->textMatchesSearchNeedle($haystack, $needle)) {
            return null;
        }

        $inContent = $body !== '' && $this->textMatchesSearchNeedle($body, $needle);
        $metaMatched = $this->textMatchesSearchNeedle($meta, $needle);

        return [
            'in_content' => $inContent && ! $metaMatched,
            'snippet' => ($inContent && ! $metaMatched)
                ? $this->manualContentSnippet($body, $needle)
                : null,
        ];
    }

    protected function manualSearchableBody(array $manual): string
    {
        $relative = str_replace('\\', '/', (string) ($manual['path'] ?? ''));
        if ($relative === '' || str_contains($relative, '..')) {
            return '';
        }

        $path = base_path('docs/'.$relative);
        if (! is_file($path) || ! is_readable($path)) {
            return '';
        }

        $markdown = (string) file_get_contents($path);
        // Keep searchable text close to what readers see in the PDF.
        $plain = html_entity_decode(strip_tags(Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return mb_strtolower(preg_replace('/\s+/u', ' ', $plain) ?? $plain);
    }

    protected function textMatchesSearchNeedle(string $haystack, string $needle): bool
    {
        if (str_contains($haystack, $needle)) {
            return true;
        }

        $tokens = preg_split('/\s+/u', $needle, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($tokens) <= 1) {
            return false;
        }

        foreach ($tokens as $token) {
            if (! str_contains($haystack, $token)) {
                return false;
            }
        }

        return true;
    }

    protected function manualContentSnippet(string $body, string $needle): ?string
    {
        $tokens = preg_split('/\s+/u', $needle, -1, PREG_SPLIT_NO_EMPTY) ?: [$needle];
        $anchor = $tokens[0] ?? $needle;
        $position = mb_stripos($body, $anchor);
        if ($position === false) {
            return null;
        }

        $start = max(0, $position - 60);
        $snippet = trim(mb_substr($body, $start, 160));
        if ($start > 0) {
            $snippet = '…'.$snippet;
        }
        if (($start + 160) < mb_strlen($body)) {
            $snippet .= '…';
        }

        return $snippet;
    }

    /**
     * Wrap the first searchable occurrence so the PDF can open at that location
     * (#search=… in Chromium, #search-match named destination fallback).
     */
    protected function highlightFirstSearchMatch(string $html, string $query): string
    {
        $needle = $this->resolveHighlightNeedle($html, $query);
        if ($needle === null) {
            return $html;
        }

        $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$html];
        $highlighted = false;

        foreach ($parts as $index => $part) {
            if ($highlighted || $part === '' || str_starts_with($part, '<')) {
                continue;
            }

            $position = mb_stripos($part, $needle);
            if ($position === false) {
                continue;
            }

            $length = mb_strlen($needle);
            $match = mb_substr($part, $position, $length);
            $parts[$index] = mb_substr($part, 0, $position)
                .'<a id="search-match" name="search-match"></a>'
                .'<mark class="search-hit">'.$match.'</mark>'
                .mb_substr($part, $position + $length);
            $highlighted = true;
        }

        return implode('', $parts);
    }

    protected function resolveHighlightNeedle(string $html, string $query): ?string
    {
        $plain = mb_strtolower(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $query = trim($query);
        if ($query === '' || $plain === '') {
            return null;
        }

        if (mb_stripos($plain, mb_strtolower($query)) !== false) {
            return $query;
        }

        $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) < 2) {
                continue;
            }
            if (mb_stripos($plain, mb_strtolower($token)) !== false) {
                return $token;
            }
        }

        return null;
    }

    /**
     * @return array{key: string, path: string, title: string, description: string, category: string, icon: string}|null
     */
    protected function portalDocumentMeta(string $document): ?array
    {
        return $this->manualCatalog()->firstWhere('key', $document);
    }

    private function rewritePortalDocumentLinks(string $markdown): string
    {
        return (string) preg_replace_callback(
            '/(?<=\]\()((?:\.\.\/)*(?:workflows\/)?[A-Za-z0-9_-]+\.md)(#[^)]+)?(?=\))/',
            function (array $matches): string {
                $key = $this->resolvePortalDocumentKey($matches[1]);
                if ($key === null) {
                    return $matches[0];
                }

                return route('member.help.document', ['document' => $key])
                    .($matches[2] ?? '');
            },
            $markdown
        );
    }

    private function resolvePortalDocumentKey(string $relativePath): ?string
    {
        $basename = basename(str_replace('\\', '/', $relativePath));

        $match = $this->manualCatalog()->first(function (array $manual) use ($basename, $relativePath) {
            return $manual['key'] === $basename
                || basename($manual['path']) === $basename
                || $manual['path'] === ltrim(str_replace('\\', '/', $relativePath), './');
        });

        return $match['key'] ?? null;
    }

    private function addHeadingAnchors(string $html): string
    {
        return (string) preg_replace_callback(
            '/<(h[1-6])>(.*?)<\/\1>/si',
            function (array $matches): string {
                $heading = html_entity_decode(strip_tags($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $slug = mb_strtolower($heading, 'UTF-8');
                $slug = (string) preg_replace('/[^\p{L}\p{N}\s-]/u', '', $slug);
                $slug = (string) preg_replace('/\s/u', '-', $slug);

                return sprintf(
                    '<%1$s id="%2$s">%3$s</%1$s>',
                    $matches[1],
                    e($slug),
                    $matches[2]
                );
            },
            $html
        );
    }

    public function storeHr(Request $request, PortalHelpRequestService $service): RedirectResponse
    {
        return $this->storeRequest($request, $service, PortalHelpRequest::TYPE_HR);
    }

    public function storeSupport(Request $request, PortalHelpRequestService $service): RedirectResponse
    {
        return $this->storeRequest($request, $service, PortalHelpRequest::TYPE_SUPPORT);
    }

    public function show(Request $request, PortalHelpRequest $helpRequest): View
    {
        $user = $request->user();
        $this->authorizeHelpRequest($user, $helpRequest);
        $helpRequest->load('facility:id,name');

        return view('dashboard.member.help.show', array_merge($this->memberPortalContext($user), [
            'helpRequest' => $helpRequest,
            'portalActive' => 'help',
            'portalTitle' => $helpRequest->subject . ' | Help Center',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => $helpRequest->typeLabel(),
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function confirmation(Request $request, PortalHelpRequest $helpRequest): View
    {
        $user = $request->user();
        $this->authorizeHelpRequest($user, $helpRequest);
        $helpRequest->load('facility:id,name');

        return view('dashboard.member.help.confirmation', array_merge($this->memberPortalContext($user), [
            'helpRequest' => $helpRequest,
            'portalActive' => 'help',
            'portalTitle' => 'Request submitted | Help Center',
            'portalEyebrow' => 'Help & Support',
            'portalPageTitle' => 'Request submitted',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    protected function storeRequest(Request $request, PortalHelpRequestService $service, string $type): RedirectResponse
    {
        $user = $request->user();
        $canPickFacility = $this->facilitiesForUser($user)->count() > 1;
        $rules = $type === PortalHelpRequest::TYPE_HR
            ? $service->hrValidationRules($canPickFacility)
            : $service->supportValidationRules($canPickFacility);

        $validated = $request->validate($rules);

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        $facilityId = $canPickFacility
            ? (int) $validated['facility_id']
            : ($this->defaultFacilityIdForUser($user) ?? (int) ($validated['facility_id'] ?? 0) ?: null);

        $helpRequest = $service->createRequest([
            'user_id' => $user->id,
            'facility_id' => $facilityId,
            'type' => $type,
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'normal',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'employee_num' => $employee?->employee_num,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'preferred_contact' => $validated['preferred_contact'],
            'best_time_to_reach' => $validated['best_time_to_reach'] ?? null,
            'steps_to_reproduce' => $validated['steps_to_reproduce'] ?? null,
            'no_phi_confirmed' => true,
        ], $type === PortalHelpRequest::TYPE_SUPPORT ? $service->attachmentFilesFromRequest($request) : []);

        return redirect()->route('member.help.confirmation', $helpRequest);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formContext($user, string $type): array
    {
        $isHr = $type === PortalHelpRequest::TYPE_HR;
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        return [
            'formType' => $type,
            'categories' => config($isHr ? 'portal-help.hr_categories' : 'portal-help.support_categories', []),
            'preferredContactOptions' => config('portal-help.preferred_contact_options', []),
            'bestTimeOptions' => config('portal-help.best_time_options', []),
            'facilities' => $this->facilitiesForUser($user),
            'defaultFacilityId' => $this->defaultFacilityIdForUser($user),
            'prefillName' => $user->name,
            'prefillEmail' => $user->email,
            'prefillPhone' => $employee?->displayPhoneNumber(),
            'prefillEmployeeNum' => $employee?->employee_num,
            'portalActive' => $isHr ? 'help-hr' : 'help-support',
            'portalTitle' => ($isHr ? 'Contact HR' : 'Technical Support') . ' | Bio Pacific',
            'portalEyebrow' => 'Need Help',
            'portalPageTitle' => $isHr ? 'Contact HR' : 'Technical Support',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'userGuides' => $isHr ? [] : config('portal-help.user_guides', []),
        ];
    }

    protected function authorizeHelpRequest($user, PortalHelpRequest $helpRequest): void
    {
        if (! app(\App\Services\PortalHelpRecipientService::class)->userCanAccessHelpRequest($user, $helpRequest)) {
            throw new NotFoundHttpException();
        }
    }

    protected function facilitiesForUser($user)
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return Facility::query()->orderBy('name')->get(['id', 'name']);
        }

        $facilityId = $this->defaultFacilityIdForUser($user);

        return $facilityId
            ? Facility::query()->whereKey($facilityId)->get(['id', 'name'])
            : collect();
    }

    protected function defaultFacilityIdForUser($user): ?int
    {
        if ($facilityId = SelectedFacility::id()) {
            return $facilityId;
        }

        if ($user->facility_id) {
            return (int) $user->facility_id;
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;

        $assignmentFacilityId = $employee?->currentAssignment?->facility_id;

        return $assignmentFacilityId ? (int) $assignmentFacilityId : null;
    }
}
