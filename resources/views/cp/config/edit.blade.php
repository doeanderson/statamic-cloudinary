@extends('statamic::layout')

@section('title', __('Cloudinary Config'))

@section('content')
    <publish-form
        title='Cloudinary Config'
        action={{ $route }}
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
    ></publish-form>
@stop
