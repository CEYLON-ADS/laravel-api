@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Manage Ads</h1>
            <p class="subtitle">Review, approve, reject, and activate listings</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.ads.index') }}" style="display:grid;grid-template-columns:2fr 1fr auto;gap:.6rem;">
            <input name="q" value="{{ request('q') }}" placeholder="Search title, description or phone...">
            <select name="status">
                <option value="">All statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
            <button class="btn" type="submit">Filter</button>
        </form>
    </section>

    <section class="panel">
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Active</th>
                    <th>Pinned</th>
                    <th>Views</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($ads as $ad)
                    <tr>
                        <td>{{ $ad->title }}</td>
                        <td>{{ $ad->category?->name ?? '-' }}</td>
                        <td>{{ $ad->city?->name ?? '-' }}</td>
                        <td><span class="pill">{{ strtoupper($ad->status) }}</span></td>
                        <td>{{ $ad->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $ad->is_pinned ? 'Yes' : 'No' }}</td>
                        <td>{{ $ad->views_count }}</td>
                        <td>{{ $ad->contact_phone }}</td>
                        <td>
                            <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                <form method="POST" action="{{ route('admin.ads.approve', $ad) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn" type="submit">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ads.reject', $ad) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn warn" type="submit">Reject</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ads.toggle-active', $ad) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn muted" type="submit">{{ $ad->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ads.toggle-pinned', $ad) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn" type="submit">{{ $ad->is_pinned ? 'Unpin' : 'Pin' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No ads found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem;">
            {{ $ads->links() }}
        </div>
    </section>
@endsection
