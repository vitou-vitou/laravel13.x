<x-app-layout>
    <div class="p-6">
        <a href="{{ route('customers.create') }}" class="text-blue-600">+ New Customer</a>
        <table class="mt-4 w-full">
            <thead><tr><th>Name</th><th>Email</th><th></th></tr></thead>
            <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td>{{ $c->email }}</td>
                    <td>
                        <a href="{{ route('customers.edit', $c) }}">Edit</a>
                        <form method="POST" action="{{ route('customers.destroy', $c) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</x-app-layout>
