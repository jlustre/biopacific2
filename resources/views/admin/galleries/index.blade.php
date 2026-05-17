@extends('layouts.dashboard', ['title' => 'Photo Galleries'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Photo Galleries</h1>
        <p class="text-gray-600 mt-2">
            @if($facility)
            Manage images displayed on {{ $facility->name }}'s public website.
            @else
            Select a facility to upload and organize gallery photos.
            @endif
        </p>
    </div>
    @if($facility)
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.facilities.galleries.create', ['facility' => $facility->id]) }}"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-sm transition">
            <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Image
        </a>
        @if($images->isNotEmpty())
        <form action="{{ route('admin.gallery.clear', $facility->id) }}" method="POST"
            onsubmit="return confirm('Delete ALL gallery images for {{ $facility->name }}? This cannot be undone.');">
            @csrf
            <button type="submit"
                class="inline-flex items-center bg-white border border-red-300 text-red-600 hover:bg-red-50 px-5 py-2.5 rounded-lg font-semibold transition">
                <i class="fas fa-trash-alt mr-2"></i> Clear Gallery
            </button>
        </form>
        @endif
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-center">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    @if(!$facility)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($facilities as $f)
        <a href="{{ route('admin.facilities.galleries.index', ['facility' => $f->id]) }}"
            class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-teal-300 transition p-5 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center flex-shrink-0 group-hover:bg-teal-200 transition">
                <i class="fas fa-images text-teal-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-gray-900 group-hover:text-teal-700 transition">{{ $f->name }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $f->city ?? 'N/A' }}, {{ $f->state ?? 'N/A' }}</p>
                <p class="text-xs text-teal-600 font-medium mt-2">Manage gallery →</p>
            </div>
        </a>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-gray-200 shadow-sm py-12 text-center text-gray-500">
            No facilities available.
        </div>
        @endforelse
    </div>
    @else
    @if(!empty($canFilterFacilities))
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Switch facility</label>
                <select onchange="if(this.value) window.location='{{ url('/admin/facilities') }}/'+this.value+'/galleries'"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Choose another facility…</option>
                    @foreach($facilities as $f)
                    <option value="{{ $f->id }}" {{ $f->id === $facility->id ? 'selected' : '' }}>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('admin.galleries.index') }}"
                class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                All facilities
            </a>
        </div>
    </div>
    @elseif(isset($scopedFacility) && $scopedFacility)
    <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
        Managing gallery for <strong>{{ $scopedFacility->name }}</strong> only.
    </div>
    @endif

    @if($stats)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center">
                <i class="fas fa-images text-slate-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total images</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-eye text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active on site</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-star text-amber-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Featured</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['featured'] }}</p>
            </div>
        </div>
    </div>
    @endif

    @if($images->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <i class="fas fa-camera text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No photos yet</h3>
        <p class="text-gray-500 max-w-md mx-auto mb-6">Upload images to showcase {{ $facility->name }} on the facility website gallery.</p>
        <a href="{{ route('admin.facilities.galleries.create', ['facility' => $facility->id]) }}"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold">
            <i class="fas fa-cloud-upload-alt mr-2"></i> Upload First Image
        </a>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($images as $image)
        <div class="group bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="relative aspect-square bg-gray-100">
                <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $image->title }}"
                    class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                    <form action="{{ route('admin.gallery.move', ['image' => $image->id, 'direction' => 'up']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-full bg-white/90 text-gray-700 hover:bg-white" title="Move up">
                            <i class="fas fa-arrow-up text-xs"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.gallery.move', ['image' => $image->id, 'direction' => 'down']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-full bg-white/90 text-gray-700 hover:bg-white" title="Move down">
                            <i class="fas fa-arrow-down text-xs"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.gallery.delete', $image) }}" method="POST"
                        onsubmit="return confirm('Delete this image?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-full bg-red-500 text-white hover:bg-red-600" title="Delete">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
                @if(!$image->is_active)
                <span class="absolute top-2 left-2 px-2 py-0.5 text-xs font-semibold bg-gray-800/80 text-white rounded">Hidden</span>
                @endif
                @if($image->is_featured ?? false)
                <span class="absolute top-2 right-2 px-2 py-0.5 text-xs font-semibold bg-amber-500 text-white rounded">Featured</span>
                @endif
            </div>
            <div class="p-3">
                <p class="text-sm font-medium text-gray-900 truncate" title="{{ $image->title }}">{{ $image->title }}</p>
                @if($image->caption)
                <p class="text-xs text-gray-500 truncate mt-0.5" title="{{ $image->caption }}">{{ $image->caption }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">#{{ $image->order ?? '—' }} · {{ $image->created_at->format('M j, Y') }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif
</div>
@endsection
