@extends('theme.main')

@section('pagecss')
    <style>
        {{ str_replace(array("'", "&#039;"), "", $page->styles ) }}
    </style>
@endsection
 
@section('content')

@php
    $page = \App\Models\Page::where('slug', $page->slug)->first();
@endphp

@if(isset($page) && $page->id == 2)
    {!! $page->contents !!}
@else
    <div class="container content-wrap">
        <div class="row">
            @if($parentPage)
                <div class="col-lg-3">
                    <span onclick="openNav()" class="d-lg-none mb-4 btn btn-primary btn-bg"><i class="icon-list-alt"></i></span>
                    
                    <div id="mySidenav">
                        <a href="javascript:void(0)" class="closebtn d-lg-none" onclick="closeNav()">&times;</a>
                        <div>
                            <h3 class="mb-4 loren-title-white text-uppercase fw-bold">Quicklinks</h3>
                        </div>
                        
                        <ul class="quicklinks m-0 pb-5">
                            @foreach($parentPage->sub_pages as $subPage)
                                <li @if($subPage->id == $page->id) class="current" @endif>
                                    <a href="{{ $subPage->get_url() }}">{{ $subPage->name }}</a>
                                    <ul>
                                        @foreach ($subPage->sub_pages as $subSubPage)
                                        <li @if ($subSubPage->id == $page->id) class="current" @endif>
                                            <a href="{{ $subSubPage->get_url() }}">{{ $subSubPage->name }}</a>
                                            @if ($subSubPage->has_sub_pages())
                                            <ul>
                                                @foreach ($subSubPage->sub_pages as $subSubSubPage)
                                                    <li @if ($subSubSubPage->id == $page->id) class="current" @endif>
                                                        <a href="{{ $subSubSubPage->get_url() }}">{{ $subSubSubPage->name }}></a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    {!! $page->contents !!}
                </div>
            @else
                <div class="col-lg-12">
                    {!! $page->contents !!}
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
