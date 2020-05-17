@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1>Hallo Pocket!</h1>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>Token: {{ $pocket->access_token }}</p>

                    <h1>Today's bookmarks</h1>

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
            </div>
        </div>
    </div>
</div>
@endsection