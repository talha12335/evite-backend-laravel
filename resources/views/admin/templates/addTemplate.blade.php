@extends('layouts.dashboardLayout')
@section('content')
    <div class="tabular-wrapper">
        <div class="top-heading">
            <h3 class="main--title heading">
                Add New Template
            </h3>
            <p>Fill out the form below to add new Template</p>
            <hr>

        </div>
{{--        image_2--}}
        <div class="body">
            <form action="{{ route('admin_template.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-container add-new-product">
                    <div class="event_name">
                        <label for="event_name">Event Name:  <span class="required">*</span></label>
                        <input type="text" name="event_name" id="event_name" class="form-control">
                        @error('event_name')
                        <div class="alert required">{{ $message }}</div>
                        @enderror
                    </div>
{{--                    <div class="text1_color">--}}
{{--                        <label for="text1_color">Text Color 1:  <span class="required">*</span></label>--}}
{{--                        <input type="color" name="text1_color" id="text1_color" class="form-control">--}}
{{--                        @error('text1_color')--}}
{{--                            <div class="alert required">{{ $message }}</div>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
{{--                    <div class="text2_color">--}}
{{--                        <label for="text2_color">Text Color 2:  <span class="required">*</span></label>--}}
{{--                        <input type="color" name="text2_color" id="text2_color" class="form-control">--}}
{{--                        @error('text2_color')--}}
{{--                            <div class="alert required">{{ $message }}</div>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
{{--                    <div class="text3_color">--}}
{{--                        <label for="text3_color">Text Color 1:  <span class="required">*</span></label>--}}
{{--                        <input type="color" name="text3_color" id="text3_color" class="form-control">--}}
{{--                        @error('text3_color')--}}
{{--                            <div class="alert required">{{ $message }}</div>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
                    <div>
                        <label for="image">Image 1 <span class="required">*</span></label>
                        <input type="file" id="image" name="image" class="form-control">
                        @error('image')
                            <div class="alert required">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="image_2">Image 2 <span class="required">*</span></label>
                        <input type="file" id="image_2" name="image_2" class="form-control">
                        @error('image')
                        <div class="alert required">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="btn-container">
                        <input type="submit" value="Add Template" class="button">
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
