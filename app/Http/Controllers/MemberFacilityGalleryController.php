<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use App\Models\Facility;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Services\FacilityGalleryService;
use App\Support\ContentVisibility;
use App\Support\MemberPortalLayout;
use App\Support\SelectedFacility;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MemberFacilityGalleryController extends Controller
{
    use AuthorizesRequests;
    use ProvidesMemberPortalContext;

    public function __construct(
        protected FacilityGalleryService $galleries
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $this->authorize('viewAny', Gallery::class);

        $context = $this->memberPortalContext($user);
        $facility = $this->resolveGalleryFacility($user, $context['facility'] ?? null);

        abort_unless($facility, 404, 'Select a facility to view galleries.');

        $canManage = $user->can('create', Gallery::class);
        $search = trim((string) $request->query('q', ''));
        $yearFilter = $request->filled('year') ? (int) $request->query('year') : null;

        $albums = $this->galleries->listForFacility(
            $facility,
            'portal',
            includeInactive: $canManage,
            search: $search !== '' ? $search : null,
            year: $yearFilter
        );

        $availableYears = $this->galleries->availableYearsForFacility(
            $facility,
            'portal',
            includeInactive: $canManage
        );

        return view('dashboard.member.galleries.index', array_merge($context, [
            'facility' => $facility,
            'facilityName' => $facility->name,
            'albums' => $albums,
            'availableYears' => $availableYears,
            'filters' => [
                'q' => $search,
                'year' => $yearFilter,
            ],
            'canManage' => $canManage,
            'portalActive' => 'facility-galleries',
            'portalTitle' => 'Photo Galleries | Bio Pacific',
            'portalEyebrow' => 'Facility',
            'portalPageTitle' => 'Photo Galleries',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $this->authorize('create', Gallery::class);

        $context = $this->memberPortalContext($user);
        $facility = $this->resolveGalleryFacility($user, $context['facility'] ?? null);
        abort_unless($facility, 404, 'Select a facility to create a gallery.');

        return view('dashboard.member.galleries.create', array_merge($context, [
            'facility' => $facility,
            'facilityName' => $facility->name,
            'events' => $this->galleries->eventsForFacility($facility),
            'shareFacilities' => $this->galleries->facilitiesAvailableForSharing($facility),
            'visibilityOptions' => ContentVisibility::options(),
            'portalActive' => 'facility-galleries',
            'portalTitle' => 'Create Gallery | Bio Pacific',
            'portalEyebrow' => 'Facility',
            'portalPageTitle' => 'Create Gallery',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->authorize('create', Gallery::class);

        $facility = $this->resolveGalleryFacility(
            $user,
            $this->memberPortalContext($user)['facility'] ?? null
        );
        abort_unless($facility, 404, 'Select a facility to create a gallery.');

        $validated = $this->validateGallery($request, (int) $facility->id);

        $gallery = $this->galleries->createGallery($facility, $user, $validated);

        return redirect()
            ->route('member.galleries.show', $gallery)
            ->with('success', 'Gallery created. Upload photos and add captions.');
    }

    public function show(Request $request, Gallery $gallery)
    {
        $user = Auth::user();
        $this->authorize('view', $gallery);

        $context = $this->memberPortalContext($user);
        $this->assertGalleryFacilityAccess($user, $gallery, $context['facility'] ?? null);
        $facility = $gallery->facility ?: $this->resolveGalleryFacility($user, $context['facility'] ?? null);

        $gallery->load(['event', 'creator', 'facility', 'sharedFacilities', 'images' => fn ($q) => $q->orderBy('order')->orderBy('id')]);

        return view('dashboard.member.galleries.show', array_merge($context, [
            'facility' => $facility,
            'facilityName' => $facility?->name,
            'gallery' => $gallery,
            'canManage' => $user->can('update', $gallery),
            'isSharedReadOnly' => $facility && ! $gallery->isOwnedByFacility((int) $facility->id),
            'portalActive' => 'facility-galleries',
            'portalTitle' => $gallery->title.' | Bio Pacific',
            'portalEyebrow' => 'Facility',
            'portalPageTitle' => $gallery->title,
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function edit(Request $request, Gallery $gallery)
    {
        $user = Auth::user();
        $this->authorize('update', $gallery);

        $context = $this->memberPortalContext($user);
        $this->assertGalleryFacilityAccess($user, $gallery, $context['facility'] ?? null);
        $facility = $gallery->facility ?: $this->resolveGalleryFacility($user, $context['facility'] ?? null);
        abort_unless($facility, 404);

        $gallery->load('sharedFacilities');

        return view('dashboard.member.galleries.edit', array_merge($context, [
            'facility' => $facility,
            'facilityName' => $facility->name,
            'gallery' => $gallery,
            'events' => $this->galleries->eventsForFacility($facility),
            'shareFacilities' => $this->galleries->facilitiesAvailableForSharing($facility),
            'visibilityOptions' => ContentVisibility::options(),
            'portalActive' => 'facility-galleries',
            'portalTitle' => 'Edit Gallery | Bio Pacific',
            'portalEyebrow' => 'Facility',
            'portalPageTitle' => 'Edit '.$gallery->title,
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
        ]));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $user = Auth::user();
        $this->authorize('update', $gallery);

        $contextFacility = $this->memberPortalContext($user)['facility'] ?? null;
        $this->assertGalleryFacilityAccess($user, $gallery, $contextFacility);
        $facility = $gallery->facility ?: $this->resolveGalleryFacility($user, $contextFacility);
        abort_unless($facility, 404);

        $validated = $this->validateGallery($request, (int) $facility->id);
        $this->galleries->updateGallery($gallery, $validated);

        return redirect()
            ->route('member.galleries.show', $gallery)
            ->with('success', 'Gallery updated.');
    }

    public function destroy(Request $request, Gallery $gallery)
    {
        $user = Auth::user();
        $this->authorize('delete', $gallery);
        $this->assertGalleryFacilityAccess(
            $user,
            $gallery,
            $this->memberPortalContext($user)['facility'] ?? null
        );

        $this->galleries->deleteGallery($gallery);

        return redirect()
            ->route('member.galleries.index')
            ->with('success', 'Gallery deleted.');
    }

    public function storeImages(Request $request, Gallery $gallery)
    {
        $user = Auth::user();
        $this->authorize('manageImages', $gallery);

        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:5120',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:500',
        ]);

        $this->galleries->addImages(
            $gallery,
            $user,
            $request->file('images', []),
            $request->input('captions', [])
        );

        return redirect()
            ->route('member.galleries.show', $gallery)
            ->with('success', 'Photos uploaded.');
    }

    public function updateImage(Request $request, Gallery $gallery, GalleryImage $image)
    {
        $this->authorize('manageImages', $gallery);
        abort_unless((int) $image->gallery_id === (int) $gallery->id, 404);

        $validated = $request->validate([
            'caption' => 'nullable|string|max:500',
        ]);

        $this->galleries->updateImageCaption($image, $validated['caption'] ?? null);

        return redirect()
            ->route('member.galleries.show', $gallery)
            ->with('success', 'Caption saved.');
    }

    public function destroyImage(Request $request, Gallery $gallery, GalleryImage $image)
    {
        $this->authorize('manageImages', $gallery);
        abort_unless((int) $image->gallery_id === (int) $gallery->id, 404);

        $this->galleries->deleteImage($image);

        return redirect()
            ->route('member.galleries.show', $gallery)
            ->with('success', 'Photo removed.');
    }

    protected function validateGallery(Request $request, int $facilityId): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'year' => 'required|integer|min:1990|max:'.((int) now()->year + 1),
            'description' => 'nullable|string|max:5000',
            'event_id' => [
                'nullable',
                'integer',
                Rule::exists('events', 'id')->where(function ($q) use ($facilityId) {
                    $q->where(function ($inner) use ($facilityId) {
                        $inner->where('facility_id', $facilityId)
                            ->orWhere('scope', 'company');
                    });
                }),
            ],
            'visibility' => 'required|in:'.implode(',', array_keys(ContentVisibility::options())),
            'share_scope' => 'required|in:'.implode(',', array_keys(Gallery::shareScopeOptions())),
            'shared_facility_ids' => 'nullable|array',
            'shared_facility_ids.*' => [
                'integer',
                Rule::exists('facilities', 'id')->where(fn ($q) => $q->where('id', '!=', $facilityId)),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (($validated['share_scope'] ?? Gallery::SHARE_SCOPE_FACILITY) === Gallery::SHARE_SCOPE_SHARED
            && empty($validated['shared_facility_ids'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'shared_facility_ids' => 'Select at least one facility to share with, or choose this facility only.',
            ]);
        }

        if (($validated['share_scope'] ?? null) !== Gallery::SHARE_SCOPE_SHARED) {
            $validated['shared_facility_ids'] = [];
        }

        return $validated;
    }

    protected function resolveGalleryFacility($user, ?Facility $contextFacility = null): ?Facility
    {
        if ($contextFacility) {
            return $contextFacility;
        }

        if ($selected = SelectedFacility::model()) {
            return $selected;
        }

        if (MemberPortalLayout::userIsSystemAdmin($user) || SelectedFacility::userCanChooseFacility($user)) {
            return Facility::query()->orderBy('name')->first();
        }

        return null;
    }

    protected function assertGalleryFacilityAccess($user, Gallery $gallery, ?Facility $contextFacility = null): void
    {
        if (MemberPortalLayout::userIsSystemAdmin($user)) {
            return;
        }

        $facility = $this->resolveGalleryFacility($user, $contextFacility);
        abort_unless(
            $facility && $gallery->isSharedWithFacility((int) $facility->id),
            403
        );
    }
}
