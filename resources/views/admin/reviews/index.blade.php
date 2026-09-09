@extends('layouts.admin')

@section('title', 'Reviews')

@section('content')
<div class="page-header page-header--row">
    <div><h1>Reviews</h1><p>Moderate customer feedback before it appears on the storefront.</p></div>
</div>
<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Product</th><th>Reviewer</th><th>Rating</th><th>Review</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse ($reviews as $review)
            <tr>
                <td>{{ $review->product->name }}</td>
                <td>{{ $review->name }}<small>{{ $review->user?->email ?: 'Guest review' }}</small></td>
                <td>{{ $review->rating }}/5</td>
                <td style="max-width: 360px;">{{ $review->comment }}</td>
                <td>{{ ucfirst($review->status) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="inline-form">
                        @csrf @method('PATCH')
                        <select name="status" aria-label="Review status">
                            @foreach (['pending', 'approved', 'rejected'] as $status)
                                <option value="{{ $status }}" @selected($review->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn--sm btn--outline" type="submit">Save</button>
                    </form>
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline-form" onsubmit="return confirm('Delete this review?')">
                        @csrf @method('DELETE')
                        <button class="btn btn--sm btn--danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty-cell">No reviews found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $reviews->links() }}
@endsection