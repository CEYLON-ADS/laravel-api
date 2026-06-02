@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Manage Users</h1>
            <p class="subtitle">Review user accounts and active status</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;">Create User</h3>
        <form method="POST" action="{{ route('admin.users.store') }}" style="display:grid;grid-template-columns:2fr 2fr 1fr auto auto;gap:.6rem;">
            @csrf
            <input name="mobile_number" placeholder="Mobile number..." required>
            <input name="name" placeholder="Name (optional)">
            <select name="role" required>
                <option value="user">User</option>
                <option value="ads_agent">Ads Agent</option>
            </select>
            <label style="display:flex; align-items:center; gap:.35rem; margin:0;">
                <input type="checkbox" name="is_active" value="1" checked style="width:auto;">
                Active
            </label>
            <button class="btn" type="submit">Create</button>
        </form>
    </section>

    <section class="panel" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:grid;grid-template-columns:2fr auto;gap:.6rem;">
            <input name="q" value="{{ request('q') }}" placeholder="Search by mobile or name...">
            <button class="btn" type="submit">Search</button>
        </form>
    </section>

    <section class="panel">
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>Mobile</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th>Ads Count</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->mobile_number }}</td>
                        <td>{{ $user->name ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" style="display:flex; gap:.4rem;">
                                @csrf
                                @method('PATCH')
                                <select name="role">
                                    <option value="user" @selected(($user->role ?? 'user') === 'user')>User</option>
                                    <option value="ads_agent" @selected($user->role === 'ads_agent')>Ads Agent</option>
                                </select>
                                <button class="btn muted" type="submit">Update</button>
                            </form>
                        </td>
                        <td><span class="pill">{{ $user->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                        <td>{{ $user->advertisements_count }}</td>
                        <td>{{ $user->last_login_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn muted" type="submit">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem;">
            {{ $users->links() }}
        </div>
    </section>
@endsection
