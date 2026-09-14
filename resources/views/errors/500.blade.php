@include('errors.layout', [
    'code' => '500',
    'title' => 'Something went wrong',
    'heading' => 'The shop hit a snag',
    'message' => 'Please try again in a moment. If you were placing an order, check My orders before retrying payment.',
])
