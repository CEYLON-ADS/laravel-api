@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Manage Cities</h1>
            <p class="subtitle">Create and edit city records</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;">Create City</h3>
        <form method="POST" action="{{ route('admin.cities.store') }}" style="display:grid;grid-template-columns:2fr 2fr auto;gap:.6rem;">
            @csrf
            <input name="name" placeholder="City name..." required>
            <select name="district_id" required>
                <option value="">Select district</option>
                @foreach ($districts as $district)
                    <option value="{{ $district->id }}">{{ $district->district }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Add City</button>
        </form>
    </section>

    <section class="panel" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.cities.index') }}" style="display:grid;grid-template-columns:2fr auto;gap:.6rem;">
            <input name="q" value="{{ request('q') }}" placeholder="Search cities...">
            <button class="btn" type="submit">Search</button>
        </form>
    </section>

    <section class="panel">
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>District</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($cities as $city)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.cities.update', $city) }}" style="display:flex;gap:.45rem;">
                                @csrf
                                @method('PATCH')
                                <input name="name" value="{{ $city->name }}" style="min-width:160px;">
                                <select name="district_id" style="min-width:160px;">
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}" @selected($city->district_id === $district->id)>{{ $district->district }}</option>
                                    @endforeach
                                </select>
                                <button class="btn muted" type="submit">Save</button>
                            </form>
                        </td>
                        <td>{{ $city->district?->district ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.cities.delete', $city) }}" onsubmit="return confirm('Delete this city?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn warn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No cities found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem;">
            {{ $cities->links() }}
        </div>
    </section>
@endsection
