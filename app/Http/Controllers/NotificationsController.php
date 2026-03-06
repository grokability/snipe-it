<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CheckoutRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    private function isAdminUser($user): bool
    {
        return $user && (
            Gate::allows('admin') ||
            Gate::allows('superadmin') ||
            (method_exists($user, 'isSuperUser') && $user->isSuperUser())
        );
    }

    private function findNotificationForUserOrAdmin($id): DatabaseNotification
    {
        $user = auth()->user();
        $isAdmin = $this->isAdminUser($user);

        if ($isAdmin) {
            return DatabaseNotification::query()->where('id', $id)->firstOrFail();
        }

        return $user->notifications()->where('id', $id)->firstOrFail();
    }

    public function index()
	{
		$user = auth()->user();
		$isAdmin = $this->isAdminUser($user);

		// Admin βλέπει ΟΛΕΣ μόνο αν ζητηθεί ρητά ?all=1
		$showAll = $isAdmin && request()->boolean('all');

		$notifications = $showAll
			? DatabaseNotification::query()->latest()->get()
			: $user->notifications()->latest()->get();

		// 1) Sort DESC ώστε το unique() να κρατήσει το πιο πρόσφατο
		$notifications = $notifications->sortByDesc('created_at')->values();

		// 2) DEDUPE
		$notifications = $notifications->unique(function ($n) {
			$type = $n->data['type'] ?? 'other';

			// acceptance_required: άστο όπως είναι (είναι “per notification”)
			if ($type === 'acceptance_required') {
				return $n->id;
			}

			$itemId   = $n->data['item_id']   ?? null;   // asset_request
			$assetId  = $n->data['asset_id']  ?? null;   // returns/other
			$returnId = $n->data['return_id'] ?? null;   // returns

			$keyId = $itemId ?: ($assetId ?: ($returnId ?: 'none'));

			// ✅ ΠΡΟΣΟΧΗ: ΔΕΝ βάζουμε event εδώ, για να μη βγάζει διπλά
			return $type . '|' . $keyId;
		})->values();

		$pending = [];
		$completed = [];

		foreach ($notifications as $n) {
			$type = $n->data['type'] ?? null;

			if ($type === 'acceptance_required') {
				if (is_null($n->read_at)) $pending[] = $n; else $completed[] = $n;
				continue;
			}

			if ($type === 'asset_request') {
				$itemId = $n->data['item_id'] ?? null;

				$stillPending = false;

				if ($itemId) {
					// 1) Αν το asset είναι ήδη σε χρήστη (checked-out), ΔΕΝ είναι pending για αποθήκη
					$asset = Asset::find($itemId);
					if ($asset && !empty($asset->assigned_to)) {
						$stillPending = false;
					} else {
						// 2) Κοίτα το πιο πρόσφατο request για αυτό το asset
						$req = CheckoutRequest::where('requestable_type', Asset::class)
							->where('requestable_id', $itemId)
							->whereNull('canceled_at')
							->latest()
							->first();

						if ($req) {
							// Default: pending
							$stillPending = true;

							// Αν υπάρχουν columns και έχουν γεμίσει -> completed
							if (Schema::hasColumn('checkout_requests', 'fulfilled_at') && !is_null($req->fulfilled_at)) {
								$stillPending = false;
							}
							if (Schema::hasColumn('checkout_requests', 'checked_out_at') && !is_null($req->checked_out_at)) {
								$stillPending = false;
							}
							if (Schema::hasColumn('checkout_requests', 'status') && ($req->status !== 'pending')) {
								$stillPending = false;
							}
						}
					}
				}

				if ($stillPending) $pending[] = $n;
				else $completed[] = $n;

				continue;
			}


			if ($type === 'asset_request_canceled') {
				if (is_null($n->read_at)) $pending[] = $n; else $completed[] = $n;
				continue;
			}

			if (in_array($type, ['return_requested','return_in_transit','return_received'], true)) {
				if (is_null($n->read_at)) $pending[] = $n; else $completed[] = $n;
				continue;
			}

			if (is_null($n->read_at)) $pending[] = $n; else $completed[] = $n;
		}

		return view('notifications.index', [
			'pending'   => collect($pending),
			'completed' => collect($completed),
			'isAdmin'   => $isAdmin,
			'showAll'   => $showAll,
		]);
	}



    public function open($id)
    {
        $notification = $this->findNotificationForUserOrAdmin($id);

        // ✅ Read ΜΟΝΟ όταν ανοίξει
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;

        // allow only internal paths like "/returns"
        if (is_string($url) && Str::startsWith($url, '/')) {
            return redirect($url);
        }

        $type = $notification->data['type'] ?? null;

        if ($type === 'acceptance_required') return redirect('/account/accept');
        if ($type === 'asset_request') return redirect('/hardware/requested');
        if ($type === 'asset_request_canceled') return redirect('/hardware/requested');
        if (in_array($type, ['return_requested', 'return_in_transit', 'return_received'], true)) return redirect('/returns');

        return redirect('/notifications');
    }

    public function markRead($id)
    {
        $notification = $this->findNotificationForUserOrAdmin($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // ✅ Αν είναι ajax -> JSON, αλλιώς redirect back (για forms)
        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$this->isAdminUser($user)) abort(403); // ✅ delete μόνο admin

        $notification = DatabaseNotification::query()->where('id', $id)->firstOrFail();
        $notification->delete();

        return redirect()->back();
    }

    public function dropdown()
    {
        $user = auth()->user();

        $unread = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($n) {
                $data = $n->data ?? [];
                return [
                    'id'      => $n->id,
                    'title'   => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? '',
                    'url'     => $data['url'] ?? route('notifications.open', $n->id),
                ];
            });

        return response()->json([
            'count'  => $user->unreadNotifications()->count(),
            'unread' => $unread,
        ]);
    }

    public function unreadCount()
    {
        $user = auth()->user();

        return response()->json([
            'count' => $user ? $user->unreadNotifications()->count() : 0,
        ]);
    }
}

