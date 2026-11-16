---
paths:
  - 'app/Listeners/**'
  - 'app/Events/**'
---

# Listeners & Events

## No EventServiceProvider — auto-discovery is by exact string, not by class

This app has no `EventServiceProvider`. Laravel auto-discovers listeners by scanning
`app/Listeners/*::handle()` for a type-hinted event parameter, then caches the map in
`bootstrap/cache/events.php` keyed by the **literal string** it found in your source
(the `use` import / type hint), not by the resolved class.

PHP class name resolution is case-insensitive, so a typo like
`use App\Events\CheckoutableCheckedin;` (lowercase `in`) still "works" at runtime — but
it produces a *different* array key than the real `App\Events\CheckoutableCheckedIn`,
because PHP array keys are case-sensitive strings. The listener silently registers under
a dead key and never fires for the real event, with no error anywhere.

- When adding or renaming a listener, match the event's `use` import and `handle()`
  type hint character-for-character against the event class's actual declared name.
- After touching anything in `app/Listeners/` or `app/Events/`, run
  `php artisan event:clear` (or `optimize:clear`) so a stale cache can't paper over a
  typo you just introduced or just fixed.
- If a listener "isn't firing" and there's no obvious reason, check
  `bootstrap/cache/events.php` for a duplicate/near-duplicate key before anything else.

## Dedup side effects across listeners on the same event with a property on the event

Several classes (`CheckoutableCheckedOut`, `CheckoutableCheckedIn`, ...) fan out to
multiple listeners (`*EmailNotification`, `*WebhookNotification`, `*LogCheckin`, ...).
None of these implement `ShouldQueue`, so Laravel dispatches the exact same event
*instance* to each of them in turn within one synchronous dispatch.

When more than one listener needs the result of a side-effecting action (e.g. creating
a `CheckoutAcceptance` row) for the same event, don't let each listener call the
side-effecting action independently — that duplicates the effect once per listener.
Instead, cache the result on a public property of the event itself, e.g.
`$event->checkoutAcceptance`, and have each listener check-then-set:

```php
if (! $event->checkoutAcceptance) {
    $event->checkoutAcceptance = CreateCheckoutAcceptanceAction::run(...);
}
return $event->checkoutAcceptance;
```

This only works because the listeners themselves are synchronous and share the literal
event object — `getCheckoutAcceptance()` runs entirely inside `handle()`, before any
`Notification`/`Mail` object is built or sent, so it's unaffected by queueing on those
downstream classes. Many `app/Notifications/*` already implement `ShouldQueue`, and
`app/Mail/*` Mailables are trending that way too — that's fine and doesn't touch this
caching at all, because by the time a `ShouldQueue` notification/mailable is handed off
to the queue, it already has the resolved `CheckoutAcceptance` baked into its
constructor; only *delivery* is deferred, not the acceptance lookup.

What WOULD break this: adding `ShouldQueue` to one of the *Listener* classes
themselves (e.g. `CheckoutableCheckedOutEmailNotification implements ShouldQueue`).
That's a different, much bigger change than queueing a Notification/Mailable — it would
give each listener its own independently-serialized copy of `$event`, dispatched to
possibly-concurrent workers, and this caching would silently stop deduping (or worse,
race). If that's ever proposed, move the idempotency into
`CreateCheckoutAcceptanceAction` itself (check-for-existing-pending-then-create inside
a transaction/lock) instead of relying on the event instance.
