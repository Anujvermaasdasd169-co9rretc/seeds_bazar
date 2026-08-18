@extends('layouts.admin')

@section('title', 'Contacts')

@section('content')
<div class="page-header page-header--row">
    <div>
        <h1>Contact Submissions</h1>
        <p>Read-only list from the Contact Us form.</p>
    </div>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Address</th>
                <th>Query</th>
                <th>Date</th>
                <th style="width: 70px;">Delete</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($messages as $msg)
                <tr>
                    <td><strong>{{ $msg->name }}</strong></td>
                    <td>{{ $msg->mobile }}</td>
                    <td>{{ $msg->email }}</td>
                    <td style="max-width: 260px;">
                        <span class="text-muted">{{ $msg->address }}</span>
                    </td>
                    <td style="max-width: 420px;">
                        <span class="text-muted">{{ $msg->query ?: '—' }}</span>
                    </td>
                    <td style="white-space:nowrap;">{{ $msg->created_at->format('Y-m-d') }}</td>
                    <td>
                        <form method="POST"
                              action="{{ route('admin.contacts.destroy', $msg) }}"
                              class="inline-form"
                              onsubmit="return confirm('Delete this record?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn icon-btn--danger" aria-label="Delete">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M6 6l1 16h10l1-16" />
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-cell">No contact submissions yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 1rem;">
    {{ $messages->links() }}
</div>
@endsection

