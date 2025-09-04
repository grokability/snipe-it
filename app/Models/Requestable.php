<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

// $asset->requests
// $asset->isRequestedBy($user)
// $asset->whereRequestedBy($user)
trait Requestable
{
    public function requests()
    {
        return $this->morphMany(CheckoutRequest::class, 'requestable');
    }

    public function isRequestedBy(User $user)
    {
        return $this->requests->where('canceled_at', null)->where('user_id', $user->id)->first();
    }

    public function scopeRequestedBy($query, User $user)
    {
        return $query->whereHas(
            'requests', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        );
    }

    public function request($qty = 1)
    {
        $this->requests()->save(
            new CheckoutRequest(['user_id' => auth()->id(), 'qty' => $qty])
        );
    }

    public function deleteRequest()
    {
        $this->requests()->where('user_id', auth()->id())->delete();
    }

    public function cancelRequest($user_id = null)
    {
        if (!$user_id) {
            $user_id = auth()->id();
        }

        $this->requests()->where('user_id', $user_id)->update(['canceled_at' => \Carbon\Carbon::now()]);
    }
}
