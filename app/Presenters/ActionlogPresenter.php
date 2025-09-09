<?php

namespace App\Presenters;

/**
 * Class CompanyPresenter
 */
class ActionlogPresenter extends Presenter
{
    public function adminuser()
    {
        if ($user = $this->model->user) {
            if (empty($user->deleted_at)) {
                return $user->present()->nameUrl();
            }
            // The user was deleted
            return '<del>'.$user->getFullNameAttribute().'</del> (deleted)';
        }

        return '';
    }

    public function item()
    {
        if ($this->action_type == 'uploaded') {
            return (string) link_to_route('show/userfile', $this->model->filename, [$this->model->item->id, $this->model->id]);
        }
        if ($item = $this->model->item) {
            if (empty($item->deleted_at)) {
                return $this->model->item->present()->nameUrl();
            }
            // The item was deleted
            return '<del>'.$item->name.'</del> (deleted)';
        }

        return '';
    }

    public function icon()
    {

        // User related icons
        if ($this->itemType() == 'user') {

            if ($this->action_type == '2fa reset') {
                return 'fa-solid fa-mobile-screen';
            }

            if ($this->action_type == 'create') {
                return 'fa-solid fa-user-plus';
            }

            if ($this->action_type == 'merged') {
                return 'fa-solid fa-people-arrows';
            }

            if ($this->action_type == 'delete') {
                return 'fa-solid fa-user-minus';
            }

            if ($this->action_type == 'delete') {
                return 'fa-solid fa-user-minus';
            }

            if ($this->action_type == 'upload deleted') {
                return 'fa-solid fa-trash';
            }

            if ($this->action_type == 'update') {
                return 'fa-solid fa-user-pen';
            }

             return 'fa-solid fa-user';
        }

        // Everything else
        if ($this->action_type == 'create') {
            return 'fa-solid fa-plus';
        }

        if (($this->action_type == 'delete') || ($this->action_type == 'upload deleted')) {
            return 'fa-solid fa-trash';
        }

        if ($this->action_type == 'update') {
            return 'fa-solid fa-pen';
        }

        if ($this->action_type == 'restore') {
            return 'fa-solid fa-trash-arrow-up';
        }

        if ($this->action_type == 'upload') {
            return 'fas fa-paperclip';
        }

        if ($this->action_type == 'checkout') {
            return 'fa-solid fa-rotate-left';
        }

        if ($this->action_type == 'checkin from') {
            return 'fa-solid fa-rotate-right';
        }

        if ($this->action_type == 'note_added') {
            return 'fas fa-sticky-note';
        }

        if ($this->action_type == 'audit') {
            return 'fas fa-clipboard-check';
        }

        return 'fa-solid fa-rotate-right';

    }

    public function actionType()
    {
        return mb_strtolower(trans('general.'.str_replace(' ', '_', $this->action_type)));
    }

    public function target()
    {
        $target = null;
        // Target is messy.
        // On an upload, the target is the item we are uploading to, stored as the "item" in the log.
        if ($this->action_type == 'uploaded') {
            $target = $this->model->item;
        } elseif (($this->action_type == 'accepted') || ($this->action_type == 'declined')) {
            // If we are logging an accept/reject, the target is not stored directly,
            // so we access it through who the item is assigned to.
            // FIXME: On a reject it's not assigned to anyone.
            $target = $this->model->item->assignedTo;
        } elseif ($this->action_type == 'requested') {
            if ($this->model->user) {
                $target = $this->model->user;
            }
        } elseif ($this->model->target) {
            // Otherwise, we'll just take the target of the log.
            $target = $this->model->target;
        }

        if ($target) {
            if (empty($target->deleted_at)) {
                return $target->present()->nameUrl();
            }

            return '<del>'.$target->display_name.'</del>';
        }

        return '';
    }
}
