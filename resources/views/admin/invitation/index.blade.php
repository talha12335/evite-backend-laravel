@extends('layouts.dashboardLayout')

@section('content')
        <div class="admin user">
            <div class="tabular-wrapper">
                    <h3 class="main--title">
                        Users / Invitation
                    </h3>
                <div class="table-content">
                    <table class="text-center">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>User Email</th>
                                <th>Occasion</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Total no of Guest</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitation as $item)
                                <tr>
                                    <input type="hidden" name="delete_hidden_value" class="delete_hidden_value" value="{{$item->id}}">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->user->email }}</td>
                                    <td>{{ $item->occasion }}</td>
                                    <td>{{ $item->date }}</td>
                                    <td>{{ $item->time }}</td>

                                    <td>{{ $item->room }}</td>

                                    <td>
                                        <a href="{{route("admin_guest.show",$item->id)}}">View Guest List</a>
                                    </td>
                                    <td>
                                        <Button type="submit" class="delete deletebtn"><i class="fa-sharp fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
@endsection



