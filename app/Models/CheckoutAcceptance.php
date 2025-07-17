<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class CheckoutAcceptance extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $casts = [
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'alert_on_response_id' => 'integer',
    ];

    /**
     * Get the mail recipient from the config
     *
     * @return mixed|string|null
     */
    public function routeNotificationForMail()
    {
        // At this point the endpoint is the same for everything.
        //  In the future this may want to be adapted for individual notifications.
        $recipients_string = explode(',', Setting::getSettings()->alert_email);
        $recipients = array_map('trim', $recipients_string);

        return array_filter($recipients);
    }

    /**
     * The resource that was is out
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function checkoutable()
    {
        return $this->morphTo();
    }

    /**
     * The user that the checkoutable was checked out to
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Is this checkout acceptance pending?
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->accepted_at == null && $this->declined_at == null;
    }

    /**
     * Was the checkoutable checked out to this user?
     *
     * @param  User $user
     * @return bool
     */
    public function isCheckedOutTo(User $user)
    {
        return $this->assignedTo?->is($user);
    }

    /**
     * Add a record to the checkout_acceptance table ONLY.
     * Do not add stuff here that doesn't have a corresponding column in the
     * checkout_acceptances table or you'll get an error.
     *
     * @param string $signature_filename
     */
    public function accept($signature_filename, $eula = null, $filename = null, $note = null)
    {
        $this->accepted_at = now();
        $this->signature_filename = $signature_filename;
        $this->stored_eula = $eula;
        $this->stored_eula_file = $filename;
        $this->note = $note;
        $this->save();

        /**
         * Update state for the checked out item
         */
        $this->checkoutable->acceptedCheckout($this->assignedTo, $signature_filename, $filename);
    }

    /**
     * Decline the checkout acceptance
     *
     * @param string $signature_filename
     */
    public function decline($signature_filename, $note = null)
    {
        $this->declined_at = now();
        $this->note = $note;
        $this->signature_filename = $signature_filename;
        $this->save();

        /**
         * Update state for the checked out item
         */
        $this->checkoutable->declinedCheckout($this->assignedTo, $signature_filename);
    }

    /**
     * Filter checkout acceptences by the user
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  User                                 $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('assigned_to_id', $user->id);
    }

    /**
     * Filter to only get pending acceptances
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->whereNull('accepted_at')->whereNull('declined_at');
    }
}
