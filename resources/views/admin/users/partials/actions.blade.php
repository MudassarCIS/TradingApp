<div class="btn-group" role="group">
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
        <i class="bi bi-pencil-square"></i> Edit
    </a>
    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i> Delete
        </button>
    </form>
</div>
