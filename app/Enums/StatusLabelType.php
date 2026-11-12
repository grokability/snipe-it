<?php

namespace App\Enums;

use App\Models\Statuslabel;

/**
 * Status label semantic type.
 */
enum StatusLabelType: string
{
    case Deployable = 'deployable';
    case Pending = 'pending';
    case Archived = 'archived';
    case Undeployable = 'undeployable';

    /**
     * Snipe-IT stores the semantic as three boolean columns. This
     * returns the row-shape the DB expects so callers can merge it
     * into the fill() input without reconstructing the mapping.
     *
     * @return array{deployable: int, pending: int, archived: int}
     */
    public function toDbColumns(): array
    {
        return match ($this) {
            self::Deployable => ['deployable' => 1, 'pending' => 0, 'archived' => 0],
            self::Pending => ['deployable' => 0, 'pending' => 1, 'archived' => 0],
            self::Archived => ['deployable' => 0, 'pending' => 0, 'archived' => 1],
            self::Undeployable => ['deployable' => 0, 'pending' => 0, 'archived' => 0],
        };
    }

    /**
     * Reverse of toDbColumns(). Reads the three boolean columns off a
     * Statuslabel row and returns the semantic.
     */
    public static function fromModel(Statuslabel $label): self
    {
        if ($label->pending == '1' && $label->archived == '0' && $label->deployable == '0') {
            return self::Pending;
        }

        if ($label->pending == '0' && $label->archived == '1' && $label->deployable == '0') {
            return self::Archived;
        }

        if ($label->pending == '0' && $label->archived == '0' && $label->deployable == '0') {
            return self::Undeployable;
        }

        return self::Deployable;
    }

    /**
     * Translated label for UI dropdowns.
     */
    public function label(): string
    {
        return match ($this) {
            self::Deployable => trans('admin/hardware/general.deployable'),
            self::Pending => trans('admin/hardware/general.pending'),
            self::Archived => trans('admin/hardware/general.archived'),
            self::Undeployable => trans('admin/hardware/general.undeployable'),
        };
    }

    /**
     * All case values as a flat array of strings. Useful for building
     * a Validator "in:" rule or an HTML select.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
