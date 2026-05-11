@extends('layouts.dashboardLayout')

@section('content')
<div class="admin user">
    <div class="tabular-wrapper">
        <h3 class="main--title">
            Guest List
        </h3>
        <div class="table-content">
            <table class="text-center">
                <thead class="text-center">
                    <tr>
                        <th>ID</th>
                        <th>Invitation Occasion</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($guest_list as $guest)
                    @foreach ($guest->guestEmail as $email)

                    <tr>
                        <td>{{ $guest->id }}</td>
                        <td>{{ $guest->invitation->occasion }}</td>
                        <td>{{ $email }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
