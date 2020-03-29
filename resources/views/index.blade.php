@extends('includes.main')
@section('body')
    <div class="container">
        <div class="flex-row align-content-center justify-content-center mx-auto">
            <div class="m-1 text-center">
                <h1>Main Page</h1>
            </div>
            <div class="m-2 text-center">
                <hr>
                <a class="btn btn-primary" href="{{route('getToken')}}">Get Token</a>
                <a class="btn btn-primary" href="{{route('pageInfo')}}">Get Pages Information</a>
                <a class="btn btn-primary" href="{{route('postList')}}">Post List</a>
            </div>
            <div class="m-2 text-center">
                @if(!empty(session('type')))
                    <h5 class="alert text-center alert-{{session('type')}}">{{session('message')}}</h5>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('js')

@endsection
