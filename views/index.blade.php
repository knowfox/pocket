@extends('knowfox::layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li><a href="{{ route('concept.toplevel') }}">Concepts</a></li>
                <li class="active">Pocket Bookmarks</li>
            </ol>

            @include('knowfox::partials.messages')

            <h1>Today's bookmarks</h1>

            <p>Token: {{ $pocket->access_token }}</p>

        </section>

        <div class="body">

            @forelse ($list as $item)
                <h2 style="clear:both">{{ $item->title }}</h2>
                @if (!empty($item->config->image))
                    <img style="width: 100px; float:right" src="{{ $item->config->image }}">
                @endif
                <p>{{ $item->summary }} - <a href="{{ $item->source_url }}">URL</a> /
                    <a href="{{ env('APP_URL') }}/{{ $item->id }}">more&nbsp;&hellip;</a>
                </p>
            @empty
                <p>(None today)</p>
            @endforelse

        </div>
    </main>

@endsection
