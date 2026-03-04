<div x-show="showActionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    style="display: none;">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4" x-text="actionModalTitle"></h3>
        <p class="text-gray-600 mb-6" x-text="actionMessage"></p>

        <div class="flex gap-3">
            <form id="action-form" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="job_id" x-bind:value="actionModalJobId">
            </form>

            <button type="button" @click="confirmAction()"
                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                Confirm
            </button>
            <button type="button" @click="showActionModal = false"
                class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 rounded-lg transition cursor-pointer">
                Cancel
            </button>
        </div>
    </div>
</div>