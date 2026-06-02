@extends('admin.layout')

@section('content')
    <div class="topbar">
        <div>
            <h1 class="title">Manage Categories</h1>
            <p class="subtitle">Create, edit, activate, and delete categories</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;">Create Category</h3>
        <form method="POST" action="{{ route('admin.categories.store') }}" style="display:grid;grid-template-columns:2fr auto;gap:.6rem;">
            @csrf
            <input name="name" placeholder="Category name..." required>
            <button class="btn" type="submit">Add Category</button>
        </form>
    </section>

    <section class="panel" style="margin-bottom:1rem;">
        <form method="GET" action="{{ route('admin.categories.index') }}" style="display:grid;grid-template-columns:2fr auto;gap:.6rem;">
            <input name="q" value="{{ request('q') }}" placeholder="Search categories...">
            <button class="btn" type="submit">Search</button>
        </form>
    </section>

    <section class="panel">
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Ads Count</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" style="display:flex;gap:.45rem;">
                                @csrf
                                @method('PATCH')
                                <input name="name" value="{{ $category->name }}" style="min-width:160px;">
                                <button class="btn muted" type="submit">Save</button>
                            </form>
                        </td>
                        <td>{{ $category->slug }}</td>
                        <td><span class="pill">{{ $category->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                        <td>{{ $category->advertisements_count }}</td>
                        <td>
                            <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                <form method="POST" action="{{ route('admin.categories.toggle-active', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn muted" type="submit">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.delete', $category) }}" onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn warn" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No categories found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem;">
            {{ $categories->links() }}
        </div>
    </section>
@endsection
