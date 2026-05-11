@extends('layouts.dashboardLayout')

@section('content')
<div class="admin user">
    <div class="tabular-wrapper">
        <div class="d-flex justify-content-between">
            <h3 class="main--title">
                Templates
            </h3>
            <a href="{{ route('admin_template.create') }}">Add New Template</a>
        </div>

        <div class="table-content">
            <table class="text-center">
                <thead class="text-center">
                    <tr>
                        <th>ID</th>
                        <th>Event name</th>
                        
                        <th>Images</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($template as $item)
                    <tr>
                        <input type="hidden" name="delete_hidden_value" class="delete_hidden_value" value="{{$item->id}}">

                        <td>{{ $item->id }}</td>
                        <td>{{ $item->event_name }}</td>

                        <td>
                            @if ($item->image)

                            <img src="{{ asset('uploads/'. $item->image) }}" alt="Template Image" class="template-image" style="width:200px" />
                            @else
                            No Image
                            @endif
                        </td>
                        <td>
                            {{-- Edit Button --}}
                            <a href="{{ route('admin_template.edit', $item->id) }}" class="update"><i class="fa-solid fa-pen-to-square"></i></a>

                            {{-- Delete Button --}}

                                <button type="submit" class="delete deletebtn_template"><i class="fa-sharp fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
