<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        return view('account.addresses', [
            'addresses' => request()->user()->addresses()->latest()->get(),
            'editing' => request()->user()->addresses()->find(request()->integer('edit')),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $this->save($request, new Address);

        return back()->with('status', 'Address saved.');
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $this->save($request, $address);

        return back()->with('status', 'Address updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === request()->user()->id, 404);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    public function makeDefault(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === request()->user()->id, 404);
        DB::transaction(function () use ($address): void {
            $address->user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('status', 'Default address updated.');
    }

    private function save(AddressRequest $request, Address $address): void
    {
        DB::transaction(function () use ($request, $address): void {
            $data = $request->validated();
            $data['is_default'] = $request->boolean('is_default') || ! $request->user()->addresses()->exists();
            if ($data['is_default']) {
                $request->user()->addresses()->update(['is_default' => false]);
            }
            if (! $address->exists) {
                $address->user_id = $request->user()->id;
            }
            $address->fill($data)->save();
        });
    }
}
