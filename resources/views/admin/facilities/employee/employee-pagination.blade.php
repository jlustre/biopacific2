<nav class="flex items-center" aria-label="Pagination">
    <ul class="inline-flex items-center -space-x-px">
        {{-- Previous Page Link --}}
        @if ($employees->onFirstPage())
        <li>
            <span
                class="px-3 py-2 ml-0 leading-tight text-gray-400 bg-white border border-gray-300 rounded-l-lg cursor-not-allowed">&lt;</span>
        </li>
        @else
        <li>
            <a href="{{ $employees->previousPageUrl() }}"
                class="px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">&lt;</a>
        </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
        @if ($page == $employees->currentPage())
        <li>
            <span class="px-3 py-2 leading-tight text-white bg-blue-600 border border-gray-300">{{ $page
                }}</span>
        </li>
        @else
        <li>
            <a href="{{ $url }}"
                class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">{{
                $page }}</a>
        </li>
        @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($employees->hasMorePages())
        <li>
            <a href="{{ $employees->nextPageUrl() }}"
                class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">&gt;</a>
        </li>
        @else
        <li>
            <span
                class="px-3 py-2 leading-tight text-gray-400 bg-white border border-gray-300 rounded-r-lg cursor-not-allowed">&gt;</span>
        </li>
        @endif
    </ul>
</nav>