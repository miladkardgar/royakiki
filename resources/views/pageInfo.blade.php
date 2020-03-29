@extends('includes.main')
@section('body')
    <div class="container">
        <div class="flex-row flex-row align-content-center justify-content-center mx-auto">
            <div class="m-2 text-left">
                <h3>Facebook Page Info</h3>
                <hr>
            </div>
            <div class="m-2 text-left">
                <span>Page Name: </span><strong>{{$info['facebook']['name']}}</strong>
                <pre></pre>
                <span>Page ID: </span><strong>{{$info['facebook']['id']}}</strong>
            </div>
        </div>
        <hr>
        <div class="flex-row">
            <div class="m-2">
                <h3>Instagram Page Info</h3>
                <hr>
            </div>
            <div class="m-2">
                <div class="d-flex">
                    <div class="m-2">
                        <img src="{{$pageInfo['business_discovery']['profile_picture_url']}}"
                             class="img-fluid figure-img" width="100" alt="">
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <span>Name: </span>
                            <p><strong>{{$pageInfo['business_discovery']['name']}}</strong></p>
                        </div>
                        <div class="col-4">
                            <span>Page ID: </span>
                            <p><strong>{{$info['instagram']['id']}}</strong></p>
                        </div>
                        <div class="col-4">
                            <span>username: </span>
                            <p><strong>{{$pageInfo['business_discovery']['username']}}</strong></p>
                        </div>
                        <div class="col-6">
                            <span>site: </span>
                            <p><strong>{{$pageInfo['business_discovery']['website']}}</strong></p>
                        </div>
                        <div class="col-6">
                            <small>{{$pageInfo['business_discovery']['biography']}}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-2">
                <div class="d-flex justify-content-around">
                    <div class="m-3 p-4">
                        <span>posts</span>
                        <h4>{{$pageInfo['business_discovery']['media_count']}}</h4>
                    </div>
                    <div class="m-3 p-4">
                        <span>followers</span>
                        <h4>{{$pageInfo['business_discovery']['followers_count']}}</h4>
                    </div>
                    <div class="m-3 p-4">
                        <span>following</span>
                        <h4>{{$pageInfo['business_discovery']['follows_count']}}</h4>
                    </div>
                </div>
                <hr>
                <div class="row">
                    @foreach($pageInfo['business_discovery']['media']['data'] as $data)
                        <div class="col-3 form-group">
                            <div class="card">
                                <div class="card-img">
                                    <img src="{{$data['media_url']}}" width="100%" height="250px" alt=""></div>
                                <div class="card-body">
                                    @if(strlen($data['caption'])>=100)
                                        <small dir="rtl">{{substr($data['caption'],0,100)}}</small>
                                    @elseif($data['caption']<1)
                                        <small dir="rtl">No Caption</small>
                                    @else
                                        <small dir="rtl">{{$data['caption']}}</small>
                                    @endif
                                    <a class="btn btn-warning text-center" href="{{route('getComment',['id'=>$data['id']])}}">GetComment</a>
                                </div>
                                <div class="card-footer">
                                    <span class="float-left">like: <strong>{{$data['like_count']}}</strong></span>
                                    <span
                                        class="float-right">comment: <strong>{{$data['comments_count']}}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
