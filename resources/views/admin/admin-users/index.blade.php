@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Admin Users</h1>
            <p class="subtitle">Create and manage admin accounts and roles</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;">Create Admin</h3>
        <form method="POST" action="{{ route('admin.admin-users.store') }}" style="display:grid; grid-template-columns:2fr 2fr 1fr auto; gap:.6rem;">
            @csrf
            <input name="username" placeholder="Username" required>
            <input name="password" type="password" placeholder="Password" required>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="ads_agent">Ads Agent</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <button class="btn" type="submit">Create</button>
        </form>
    </section>

    <section class="panel" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.admin-users.index') }}" style="display:grid;grid-template-columns:2fr auto;gap:.6rem;">
            <input name="q" value="{{ request('q') }}" placeholder="Search by username...">
            <button class="btn" type="submit">Search</button>
        </form>
    </section>

    <section class="panel">
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->username }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.admin-users.role', $admin) }}" style="display:flex; gap:.4rem;">
                                @csrf
                                @method('PATCH')
                                <select name="role">
                                    <option value="super_admin" @selected($admin->role === 'super_admin')>Super Admin</option>
                                    <option value="admin" @selected($admin->role === 'admin')>Admin</option>
                                    <option value="ads_agent" @selected($admin->role === 'ads_agent')>Ads Agent</option>
                                </select>
                                <button class="btn muted" type="submit">Update</button>
                            </form>
                        </td>
                        <td><span class="pill">{{ $admin->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                        <td>
                            <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
                                <form method="POST" action="{{ route('admin.admin-users.password', $admin) }}" style="display:flex; gap:.4rem;">
                                    @csrf
                                    @method('PATCH')
                                    <input name="password" type="password" placeholder="New password" style="min-width:160px;">
                                    <button class="btn muted" type="submit">Reset</button>
                                </form>
                                <form method="POST" action="{{ route('admin.admin-users.toggle-active', $admin) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn muted" type="submit">{{ $admin->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No admin users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem;">
            {{ $admins->links() }}
        </div>
    </section>
@endsection
