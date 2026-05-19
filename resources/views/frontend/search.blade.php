@extends('layouts.app')

@section('title', 'Tìm kiếm' . ($q ? ": {$q}" : '') . ' - ' . config('app.name'))

@push('styles')
<style>
    .post-card { border: 1px solid #e5e5e5; border-radius: 8px; overflow: hidden; }
    .post-card-img { width: 100%; height: 180px; object-fit: cover; }
    .post-card-placeholder { background: #ddd; height: 180px; }
    .post-card-body { padding: 1rem; }
    .grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
</style>
@endpush

@section('content')
    <h1 class="page-title">Tìm kiếm @if($q): "{{ $q }}"@endif</h1>

    <div class="grid grid-3">
        @forelse($posts as $post)
            @include('frontend.partials.post-card', ['post' => $post])
        @empty
            <p>Không tìm thấy bài viết phù hợp.</p>
        @endforelse
    </div>

    <div class="pagination">{{ $posts->links() }}</div>
@endsection
