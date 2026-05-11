@extends('layouts.dashboardLayout')

@section('content')
        <div class="admin user">
            <div class="tabular-wrapper">
                <div class="d-flex justify-content-between">
                    <h3 class="main--title">
                        Recent Invitation
                    </h3>
                    <div>
                        <a href="{{route('admin_invitation.index')}}" class="view_all_link">View all</a>
                    </div>
                </div>


                <div class="table-content">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Email</th>
                                <th>Occasion</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($new_invitation as $item)
                                <tr>
                                    <input type="hidden" name="delete_hidden_value" class="delete_hidden_value" value="{{$item->id}}">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->user->email }}</td>
                                    <td>{{ $item->occasion }}</td>
                                    <td>{{ $item->date }}</td>
                                    <td>{{ $item->time }}</td>
                                    <td>{{ $item->room }}</td>

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



