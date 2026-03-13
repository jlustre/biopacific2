{{-- Success Message (Livewire or Session) --}}
@if(!empty($successMessage))
<div class="p-4 bg-green-100 text-green-800 rounded-lg flex justify-between items-center">
    <span><i class="fas fa-check-circle mr-2"></i>{{ $successMessage }}</span>
    <button type="button" wire:click="closeSucess" class="text-green-800 hover:text-green-900 cursor-pointer">
        <i class="fas fa-times"></i>
    </button>
</div>
@elseif(session('success'))
<div class="p-4 bg-green-100 text-green-800 rounded-lg flex justify-between items-center">
    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
    <button type="button" class="text-green-800 hover:text-green-900 cursor-pointer"
        onclick="this.parentElement.style.display='none';">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

{{-- Error Message (Livewire or Session) --}}
@if(!empty($errorMessage))
<div class="p-4 bg-red-100 text-red-800 rounded-lg flex justify-between items-center">
    <span><i class="fas fa-exclamation-circle mr-2"></i>{{ $errorMessage }}</span>
    <button type="button" wire:click="$set('errorMessage', '')" class="text-red-800 hover:text-red-900 cursor-pointer">
        <i class="fas fa-times"></i>
    </button>
</div>
@elseif(session('error'))
<div class="p-4 bg-red-100 text-red-800 rounded-lg flex justify-between items-center">
    <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
    <button type="button" class="text-red-800 hover:text-red-900 cursor-pointer"
        onclick="this.parentElement.style.display='none';">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
<div class="p-4 bg-red-100 text-red-800 rounded-lg border-2 border-red-500 mb-6">
    <p class="font-bold text-lg mb-3"><i class="fas fa-exclamation-triangle mr-2"></i>Please fix these errors:
    </p>
    <ul class="list-disc list-inside space-y-2">
        @foreach($errors->all() as $error)
        <li class="font-semibold">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif