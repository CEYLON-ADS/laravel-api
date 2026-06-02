@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Dashboard</h1>
            <p class="subtitle">Admin overview and moderation summary</p>
        </div>
    </div>

    <section style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:.75rem; margin-bottom:1rem;">
        <article class="panel"><div class="subtitle">Total Ads</div><h3 style="margin:.2rem 0 0;">{{ $stats['total_ads'] }}</h3></article>
        <article class="panel"><div class="subtitle">Pending</div><h3 style="margin:.2rem 0 0;">{{ $stats['pending_ads'] }}</h3></article>
        <article class="panel"><div class="subtitle">Approved</div><h3 style="margin:.2rem 0 0;">{{ $stats['approved_ads'] }}</h3></article>
        <article class="panel"><div class="subtitle">Rejected</div><h3 style="margin:.2rem 0 0;">{{ $stats['rejected_ads'] }}</h3></article>
        <article class="panel"><div class="subtitle">Active</div><h3 style="margin:.2rem 0 0;">{{ $stats['active_ads'] }}</h3></article>
        <article class="panel"><div class="subtitle">Users</div><h3 style="margin:.2rem 0 0;">{{ $stats['total_users'] }}</h3></article>
        <article class="panel"><div class="subtitle">Categories</div><h3 style="margin:.2rem 0 0;">{{ $stats['total_categories'] }}</h3></article>
    </section>

    <section class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:.6rem;">
            <h2 style="margin:0;">Latest Ads</h2>
            <a class="btn" href="{{ route('admin.ads.index') }}">Manage Ads</a>
        </div>
        <div style="overflow:auto;margin-top:.8rem;">
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Active</th>
                    <th>Views</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($latestAds as $ad)
                    <tr>
                        <td>{{ $ad->title }}</td>
                        <td>{{ $ad->category?->name ?? '-' }}</td>
                        <td>{{ $ad->city?->name ?? '-' }}</td>
                        <td><span class="pill">{{ strtoupper($ad->status) }}</span></td>
                        <td>{{ $ad->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $ad->views_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No advertisements available.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
