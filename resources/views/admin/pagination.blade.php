@if($results->hasPages())
    <nav class="pager" aria-label="Results pages">
        @if($results->previousPageUrl())
            <a class="button ghost" href="{{ $results->previousPageUrl() }}">Previous</a>
        @else
            <span></span>
        @endif
        <span>Page {{ $results->currentPage() }} of {{ $results->lastPage() }}</span>
        @if($results->nextPageUrl())
            <a class="button ghost" href="{{ $results->nextPageUrl() }}">Next</a>
        @else
            <span></span>
        @endif
    </nav>
@endif
