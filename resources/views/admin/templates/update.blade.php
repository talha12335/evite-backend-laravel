@extends('layouts.dashboardLayout')
@section('content')
    <div class="tabular-wrapper">
        <div class="top-heading">
            <h3 class="main--title heading">
                Edit Template
            </h3>
            <p>Edit the template below</p>
            <hr>
        </div>
        <div class="body">
            <form action="{{ route('admin_template.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') <!-- Use PUT method for updating the resource -->
                <div class="form-container add-new-product">
                    <div class="event_name">
                        <label for="event_name">Event Name  <span class="required">*</span></label>
                        <input type="text" name="event_name" id="event_name" class="form-control" value="{{ $template->event_name }}">
                        @error('event_name')
                        <div class="alert required">{{ $message }}</div>
                        @enderror
                    </div>
{{--                    <div class="text1_color">--}}
{{--                        <label for="text1_color">Text Color 1:  <span class="required">*</span></label>--}}
{{--                        <input type="color" name="text1_color" id="text1_color" class="form-control" value="{{ $template->text1_color }}">--}}
{{--                        @error('text1_color')--}}
{{--                            <div class="alert required">{{ $message }}</div>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
{{--                    <div class="text2_color">--}}
{{--                        <label for="text2_color">Text Color 2:  <span class="required">*</span></label>--}}
{{--                        <input type="color" name="text2_color" id="text2_color" class="form-control" value="{{ $template->text2_color }}">--}}
{{--                        @error('text2_color')--}}
{{--                            <div class="alert required">{{ $message }}</div>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
{{--                    <div class="text3_color">--}}
{{--                        <label for="text3_color">Text Color 3:  <span class="required">*</span></label>--}}
{{--                        <input type="color" name="text3_color" id="text3_color" class="form-control" value="{{ $template->text3_color }}">--}}
{{--                        @error('text3_color')--}}
{{--                            <div class="alert required">{{ $message }}</div>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
                    <div>
                        <label for="image">Image <span class="required">*</span></label>
                        <input type="file" id="image" name="image" class="form-control">
                        @error('image')
                            <div class="alert required">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="image_2">Image <span class="required">*</span></label>
                        <input type="file" id="image_2" name="image_2" class="form-control">
                        @error('image')
                        <div class="alert required">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="btn-container">
                        <input type="submit" value="Update Template" class="button">
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
