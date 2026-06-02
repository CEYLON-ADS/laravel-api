@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Manage Districts</h1>
            <p class="subtitle">Create, edit, and delete district records</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;">Create District</h3>
        <form method="POST" action="{{ route('admin.districts.store') }}" style="display:grid;grid-template-columns:2fr 2fr auto;gap:.6rem;">
            @csrf
            <input name="district" placeholder="District name..." required>
            <select name="country_id" required>
                <option value="">Select country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Add District</button>
        </form>
    </section>

    <section class="panel" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.districts.index') }}" style="display:grid;grid-template-columns:2fr auto;gap:.6rem;">
            <input name="q" value="{{ request('q') }}" placeholder="Search districts...">
            <button class="btn" type="submit">Search</button>
        </form>
    </section>

    <section class="panel">
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>District</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($districts as $district)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.districts.update', $district) }}" style="display:flex;gap:.45rem;">
                                @csrf
                                @method('PATCH')
                                <input name="district" value="{{ $district->district }}" style="min-width:160px;">
                                <select name="country_id" style="min-width:160px;">
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" @selected($district->country_id === $country->id)>{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn muted" type="submit">Save</button>
                            </form>
                        </td>
                        <td>{{ $district->country?->country_name ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.districts.delete', $district) }}" onsubmit="return confirm('Delete this district?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn warn" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No districts found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem;">
            {{ $districts->links() }}
        </div>
    </section>
@endsection
