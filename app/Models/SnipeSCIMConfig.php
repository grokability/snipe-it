<?php

namespace App\Models;

use ArieTimmerman\Laravel\SCIMServer\Attribute\Attribute;
use ArieTimmerman\Laravel\SCIMServer\Attribute\Collection;
use ArieTimmerman\Laravel\SCIMServer\Attribute\Complex;
use ArieTimmerman\Laravel\SCIMServer\Attribute\Constant;
use ArieTimmerman\Laravel\SCIMServer\Attribute\Eloquent;
use ArieTimmerman\Laravel\SCIMServer\Attribute\JSONCollection;
use ArieTimmerman\Laravel\SCIMServer\Attribute\Meta;
use ArieTimmerman\Laravel\SCIMServer\Attribute\MutableCollection;
use ArieTimmerman\Laravel\SCIMServer\Attribute\Schema as AttributeSchema;
use ArieTimmerman\Laravel\SCIMServer\Exceptions\SCIMException;
use ArieTimmerman\Laravel\SCIMServer\Parser\Parser;
use ArieTimmerman\Laravel\SCIMServer\Parser\Path;
use ArieTimmerman\Laravel\SCIMServer\SCIM\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tmilos\ScimFilterParser\Error\FilterException;

function a($name = null): Attribute
{
    return new Attribute($name);
}

function complex($name = null): Complex
{
    return new Complex($name);
}

function eloquent($name, $attribute = null): Attribute
{
    return new Eloquent($name, $attribute);
}

// Extends Complex to handle schema-qualified attribute keys in PATCH add/replace operations.
// Azure Entra ID sends PATCH without a "path" field, putting the full URN as the value dict key
// e.g. {"op":"add","value":{"urn:...grokability...:location":"Head Office"}}.
// The upstream library's add() only searches the default (core) schema, silently dropping grokability attrs.
class SnipeRootComplex extends Complex
{
    private function findInSchema(string $schemaUrn, string $attrName): ?object
    {
        $schemaNode = $this->getSubNode($schemaUrn);

        return ($schemaNode instanceof AttributeSchema) ? $schemaNode->getSubNode($attrName) : null;
    }

    public function add($value, Model &$object)
    {
        $match = false;
        $this->dirty = true;

        if ($this->mutability == 'readOnly') {
            return;
        }

        foreach ($value as $key => $v) {
            if (is_numeric($key)) {
                throw new SCIMException('Invalid key: '.$key.' for complex object '.$this->getFullKey());
            }

            // Malformed keys like `[type eq "work"]` (a value-path filter
            // with no leading attribute name) make the Tmilos parser throw
            // FilterException before we can inspect the parsed shape.
            // Same class of misconfigured-client problem as the null-guard
            // below, one layer further back. Turn it into a 400 so the
            // caller sees "your key is malformed" instead of a 500 that
            // looks like our fault.
            try {
                $path = Parser::parse($key);
            } catch (FilterException $e) {
                throw new SCIMException('SnipeRootComplex::add: Malformed SCIM key: '.$key.' ('.$e->getMessage().')', 400);
            }

            if ($path->isNotEmpty()) {
                // Path::isNotEmpty() returns true when EITHER the
                // attribute-path OR the value-path is populated. A body
                // key that's only a value-path filter expression (e.g.
                // `emails[type eq "work"]`) makes it past this check
                // but has getAttributePath() === null, which the
                // library's shiftAttributePathAttributes() will null-
                // deref on. Route-level replace/add here only
                // dispatches by simple attribute name — a filter-key at
                // this layer is a misconfigured SCIM client that we
                // can't honor, and this will return a 400 so the user knows
                // the misconfiguration is on their end, versus the previous 500,
                // which made it look like it was on our end.
                if ($path->getAttributePath() === null) {
                    throw new SCIMException('SnipeRootComplex::add: Cannot route SCIM key with no attribute path: '.$key, 400);
                }

                $attributeNames = $path->getAttributePathAttributes();
                $schema = $path->getAttributePath()?->path?->schema;
                $path = $path->shiftAttributePathAttributes();

                $subNode = ($schema !== null) ? $this->findInSchema($schema, $attributeNames[0]) : null;
                if ($subNode === null) {
                    $subNode = $this->getSubNode($attributeNames[0]);
                }

                $match = true;

                $newValue = $v;
                if ($path->isNotEmpty()) {
                    $newValue = [implode('.', $path->getAttributePathAttributes()) => $v];
                }

                if ($subNode !== null) {
                    $subNode->add($newValue, $object);
                }
            }
        }

        if (! $match && $this->parent == null) {
            foreach ($this->subAttributes as $attribute) {
                if ($attribute instanceof AttributeSchema) {
                    $attribute->add($value, $object);
                }
            }
        }
    }

    public function replace($value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        $this->dirty = true;

        if ($this->mutability == 'readOnly') {
            return;
        }

        $subNode = null;

        foreach ($value as $key => $v) {
            if (is_numeric($key)) {
                throw new SCIMException('Invalid key: '.$key.' for complex object '.$this->getFullKey());
            }

            $subNode = null;
            $path = null;
            $key = trim($key);

            if (strpos($key, ':') !== false) {
                try {
                    $parsed = Parser::parse($key);
                } catch (FilterException $e) {
                    throw new SCIMException('SnipeRootComplex::replace: Malformed SCIM key: '.$key.' ('.$e->getMessage().')', 400);
                }
                $schemaUrn = $parsed->getAttributePath()?->path?->schema;
                $attrName = $parsed->getAttributePathAttributes()[0] ?? null;
                if ($schemaUrn !== null && $attrName !== null) {
                    $subNode = $this->findInSchema($schemaUrn, $attrName);
                }
                if ($subNode === null) {
                    $subNode = $this->getSubNode($key);
                }
            } else {
                // See the matching try/catch in add(). Malformed keys
                // starting with `[` throw FilterException from the parser
                // itself, before any of our null-guards fire.
                try {
                    $path = Parser::parse($key);
                } catch (FilterException $e) {
                    throw new SCIMException('SnipeRootComplex::replace: Malformed SCIM key: '.$key.' ('.$e->getMessage().')', 400);
                }
                if ($path->isNotEmpty()) {
                    // See the matching guard in add() — isNotEmpty() lets
                    // a value-path-only key through (e.g. emails[type eq
                    // "work"]) but shiftAttributePathAttributes() null-
                    // derefs on getAttributePath() when it's not present.
                    // Trace: null-deref on Path.php:79 seen in the
                    // wild for misconfigured SCIM clients sending
                    // filter-key bodies to PUT /Users/{id}.
                    if ($path->getAttributePath() === null) {
                        throw new SCIMException('SnipeRootComplex::replace: Cannot route SCIM key with no attribute path: '.$key, 400);
                    }

                    $attributeNames = $path->getAttributePathAttributes();
                    $path = $path->shiftAttributePathAttributes();
                    $subNode = $this->getSubNode($attributeNames[0] ?? $path->getAttributePath()?->path?->schema);
                }
            }

            if ($subNode !== null) {
                $newValue = $v;
                if ($path !== null && $path->isNotEmpty()) {
                    $newValue = [implode('.', $path->getAttributePathAttributes()) => $v];
                }

                // Vendor Complex::replace declares $subNode inside its
                // foreach then references it outside the loop. Handing
                // it an empty array (e.g. PATCH body `"emails": []`)
                // makes the foreach a no-op and PHP 8 throws
                // "Undefined variable $subNode" at the outer reference.
                // Skip empty-array recursions here so the whole request
                // does not 500 on a client that sent a well-formed
                // "clear this multi-valued attribute" body. Non-empty
                // scalars and populated arrays fall through to the
                // library normally. "Clear" semantics for multi-valued
                // attributes are still reachable via the SCIM PATCH
                // remove operation.
                if (is_array($newValue) && $newValue === []) {
                    continue;
                }

                $subNode->replace($newValue, $object, $path);
            }
        }

        if ($subNode == null && $this->parent == null) {
            foreach ($this->subAttributes as $attribute) {
                if ($attribute instanceof AttributeSchema) {
                    $attribute->replace($value, $object, $path);
                }
            }
        }

        if ($removeIfNotSet) {
            foreach ($this->subAttributes as $attribute) {
                if (! $attribute->isDirty()) {
                    $attribute->remove(null, $object);
                }
            }
        }
    }

    // Complex::patch's value-path branch (path like
    // `employeeNumber[value eq "123"]`) calls getSubNode($attr) on root,
    // falls back to getSchemaNode() (always the FIRST schema, core) when
    // that misses, and throws "Unknown path" because employeeNumber
    // lives on the enterprise schema. Entra ID sends exactly this shape
    // when it de-provisions a user whose employeeNumber also changed:
    // one PATCH with ops for `active=false` and `employeeNumber[value eq
    // "..."]`. See #19347 and #19819.
    //
    // Before deferring to the vendor, check whether getSubNode() misses
    // the attribute on root.
    // Dropping the leftover `[value eq "X"]` filter is deliberate:
    // Entra attaches it as "the one whose current value is X" context,
    // but for a single-valued attribute like employeeNumber there's nothing to filter
    // against and the Eloquent attribute's patch throws when it sees
    // any non-empty path anyway.
    //
    // When getSubNode DOES resolve the attribute (ie, the attribute is
    // on the first/core schema), defer to the vendor.
    public function patch($operation, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        if ($path !== null && $path->isNotEmpty() && $this->parent === null) {
            $valuePathAttributes = $path->getValuePathAttributes();
            if (! empty($valuePathAttributes) && $this->getSubNode($valuePathAttributes[0]) === null) {
                $attrName = $valuePathAttributes[0];
                $attribute = null;

                foreach ($this->subAttributes as $sub) {
                    if ($sub instanceof AttributeSchema) {
                        $candidate = $sub->getSubNode($attrName);
                        if ($candidate !== null) {
                            $attribute = $candidate;
                            break;
                        }
                    }
                }

                if ($attribute !== null) {
                    switch ($operation) {
                        case 'add':
                            $attribute->add($value, $object);

                            return;
                        case 'replace':
                            $attribute->replace($value, $object);

                            return;
                        case 'remove':
                            // Eloquent::patch treats remove as "set to
                            // null". No public remove() on the Eloquent
                            // attribute, so emulate it via replace(null)
                            // which hits the same underlying write.
                            $attribute->replace(null, $object);

                            return;
                    }
                }
            }
        }

        parent::patch($operation, $value, $object, $path, $removeIfNotSet);
    }

    // #19347: Complex::applyComparison ignores the schema URN on the
    // filter path and always falls back to the FIRST schema node (core).
    // A filter like:
    //   ?filter=urn:...enterprise:2.0:User:employeeNumber eq "1234567"
    // parses with schema=<enterprise URN> and attributeNames=['employeeNumber'],
    // but the library calls getSubNode('employeeNumber') on root, misses,
    // then dispatches to getSchemaNode() (always core) which reports
    // "Unknown path" since employeeNumber lives on the enterprise schema.
    // Same shape of bug as the URN-blind add()/replace() routing above.
    // If the path carries a schema URN and we have a schema node for it,
    // dispatch the whole path there so its own applyComparison finds the
    // attribute. Otherwise defer to the library.
    public function applyComparison(Builder &$query, Path $path, $parentAttribute = null)
    {
        $schemaUrn = $path->getAttributePath()?->path?->schema;

        if ($schemaUrn !== null) {
            $schemaNode = $this->getSubNode($schemaUrn);
            if ($schemaNode instanceof AttributeSchema) {
                $schemaNode->applyComparison($query, $path, $parentAttribute);

                return;
            }
        }

        parent::applyComparison($query, $path, $parentAttribute);
    }
}

// Azure Entra ID sends op=replace with path=members and only the single user being provisioned,
// not the full member list. Using sync() would wipe all other members on every user update.
// Override replace() to use syncWithoutDetaching() so it behaves like add(); op=remove with a
// filter path still handles explicit removals correctly.
class SnipeMutableCollection extends MutableCollection
{
    // Read the membership as ids instead of models. The parent's
    // Collection::doRead() touches $object->members, hydrating a full User
    // model plus its Pivot for every member of the group — and
    // objectToSCIMArray() runs three times over a single PATCH. On a large
    // group that is enough to exhaust the PHP memory limit before the
    // request can respond.
    //
    // The output has to stay identical to what the parent builds from the
    // value / $ref / display sub-attributes below, `display => null`
    // included: `display` maps to users.name, which is neither a column nor
    // an accessor on User, so it has always read as null.
    // PatchGroupMembersTest::test_patch_response_still_lists_every_member
    // pins that.
    //
    // pluck() keeps the soft-delete scope the parent's join carries; the
    // $ref prefix is built once to avoid a route() call per member.
    protected function doRead(&$object, $attributes = [])
    {
        $ref_prefix = route('scim.resources', ['resourceType' => 'Users']).'/';

        return $object->{$this->attribute}()
            ->pluck('users.id')
            ->map(fn ($id) => ['value' => (string) $id, '$ref' => $ref_prefix.$id, 'display' => null])
            ->all();
    }

    public function replace($value, Model &$object, ?Path $path = null)
    {
        $this->add($value, $object);
    }

    // POST /scim/v2/Groups with an initial `members` array arrives here with
    // an unsaved parent: the SCIM library runs attribute mappers before it
    // calls save() on the new resource. Letting parent::add() fire on an
    // unsaved model makes Eloquent's pivot INSERT bind NULL for group_id
    // (since $parent->getKey() is null) and blow up with a MySQL 23000 integrity
    // violation. Persist first so the pivot row has a real parent id, then
    // stash the object into the request so the displayName uniqueness closure
    // (which re-runs after mapping) can recognize its own row instead of
    // treating it as an existing name collision.
    //
    // Missing-value guard: the per-member `required` rule that used to live on
    // the SCIM config was dropped because it caused ValidationRuleParser to
    // allocate O(N) rule stacks on the flattened payload, which OOMed on
    // large group syncs (see the docblock above the members mapping in
    // SnipeSCIMConfig::getGroupConfig). The check now happens here in a
    // single walk so clients get a clean 400 pointing at the bad indices
    // instead of the parent library's misleading 500 with an empty
    // "One or more members are unknown: " message from findMany() eating
    // the nulls.
    public function add($value, Model &$object)
    {
        $missing = [];
        foreach ((array) $value as $index => $entry) {
            if (! is_array($entry)
                || ! array_key_exists('value', $entry)
                || $entry['value'] === null
                || $entry['value'] === ''
            ) {
                $missing[] = $index;
            }
        }
        if ($missing !== []) {
            throw new SCIMException(
                'Every members entry must include a "value" field. Missing at indices: '.implode(',', $missing),
                400
            );
        }

        if (! $object->exists) {
            $object->save();
            request()->attributes->set('scim_in_flight_resource', $object);
        }

        // Snapshot ONLY the user ids referenced in the SCIM payload,
        // not the whole membership, so a PATCH against a group with
        // 50k members doesn't load 50k rows just to log a single-add.
        // See PatchGroupMembersTest for the shape guard.
        $requestedUserIds = $this->userIdsFromMemberPayload($value);
        $previouslyAttachedIds = $this->pivotFilterToAttached($object, $requestedUserIds);

        //   1) Skip its trailing $object->load($this->attribute) call.
        //      That load fires a `select users.* from users inner join
        //      users_groups` that hydrates the entire membership into
        //      User models on every PATCH, enough to exhaust PHP memory
        //      on a group with tens of thousands of members. The
        //      response's `members` list is rebuilt by our doRead()
        //      override (id-only pluck), so the load is redundant here.
        //   2) Attach only the id delta we already know is new
        //      (requestedUserIds minus previouslyAttachedIds) instead
        //      of syncWithoutDetaching(). Laravel's syncWithoutDetaching
        //      begins by SELECT-ing every pivot row for group_id to
        //      figure out what already exists, defeating the point of
        //      the bounded pre-check.
        $submittedValues = collect($value)->pluck('value')->all();
        $existingObjects = $object
            ->{$this->attribute}()
            ->getRelated()
            ->findMany($submittedValues)
            ->map(fn ($o) => $o->getKey());

        if (($diff = collect($submittedValues)->diff($existingObjects))->count() > 0) {
            throw new SCIMException(
                sprintf('One or more %s are unknown: %s', $this->attribute, implode(',', $diff->all())),
                500
            );
        }

        $toAttach = array_values(array_diff($requestedUserIds, $previouslyAttachedIds));
        if ($toAttach !== []) {
            $object->{$this->attribute}()->attach($toAttach);
        }

        $this->logGroupMembershipChangesForIds(
            $object,
            attachedIds: array_values(array_diff($requestedUserIds, $previouslyAttachedIds)),
            detachedIds: [],
        );
    }

    public function remove($value, Model &$object, ?Path $path = null)
    {
        // Two shapes: filter-path form ("members[value eq 5]") targets
        // one user id, list-value form targets an explicit list. Both
        // reduce to a small requestedUserIds set that we pre-check
        // against the pivot so we log only real detachments.
        $comparison = $path?->getValuePathFilter()?->getComparisonExpression();
        if ($comparison !== null) {
            $requestedUserIds = [(int) $comparison->compareValue];
        } else {
            $requestedUserIds = $this->userIdsFromMemberPayload($value);
        }
        $previouslyAttachedIds = $this->pivotFilterToAttached($object, $requestedUserIds);

        // Inline vendor MutableCollection::remove() minus its trailing
        // $object->load($this->attribute) call. Same reason as add():
        // the full-membership hydration exhausts memory on large
        // groups and doRead() rebuilds the response list itself.
        if ($comparison !== null) {
            $attributes = $comparison->attributePath->attributeNames ?? [];
            $operator = $comparison->operator;

            if ($value !== null) {
                throw new SCIMException('Remove operation with filter requires a null value parameter', 400);
            }
            if (count($attributes) !== 1) {
                throw new SCIMException(sprintf('Filter must specify exactly one attribute, found %d attributes', count($attributes)), 400);
            }
            if ($operator !== 'eq') {
                throw new SCIMException(sprintf('Unsupported filter operator "%s" - only "eq" is supported', $operator), 400);
            }
            if ($attributes[0] !== 'value') {
                throw new SCIMException(sprintf('Cannot filter on "%s" - only filtering on "value" attribute is supported', $attributes[0]), 400);
            }

            $object->{$this->attribute}()->detach([$comparison->compareValue]);
        } else {
            $object->{$this->attribute}()->detach(collect($value)->pluck('value')->all());
        }

        $this->logGroupMembershipChangesForIds(
            $object,
            attachedIds: [],
            detachedIds: $previouslyAttachedIds,
        );
    }

    /**
     * Flatten the SCIM `members` payload down to a plain list of
     * integer user ids. Skips entries missing a `value` field so the
     * validation error in add() stays the one thrown by the guard
     * above, not a silent misclassification here.
     *
     * @return array<int, int>
     */
    private function userIdsFromMemberPayload($value): array
    {
        $ids = [];
        foreach ((array) $value as $entry) {
            if (is_array($entry) && isset($entry['value'])) {
                $ids[] = (int) $entry['value'];
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Return the subset of $requestedUserIds that are currently on
     * the pivot for this group. Cheap: one indexed lookup on
     * (group_id, user_id in (…)) instead of a full-membership scan.
     *
     * Returns [] when the parent relation is not user-shaped
     * (defensive guard for future MutableCollection usage against
     * non-Group parents).
     *
     * @param  array<int, int>  $requestedUserIds
     * @return array<int, int>
     */
    private function pivotFilterToAttached(Model $object, array $requestedUserIds): array
    {
        if (! $object instanceof Group || $requestedUserIds === []) {
            return [];
        }

        return $object->users()
            ->whereIn('users.id', $requestedUserIds)
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Write per-user Actionlogs for the attach / detach delta the
     * caller computed. Delegates to User::logGroupAttached() /
     * logGroupDetached() so the log_meta shape matches
     * User::syncGroupsWithLogging(), letting the history UI render
     * SCIM-driven changes with the same formatter as Web / API edits.
     *
     * @param  array<int, int>  $attachedIds
     * @param  array<int, int>  $detachedIds
     */
    private function logGroupMembershipChangesForIds(Model $object, array $attachedIds, array $detachedIds): void
    {
        if (! $object instanceof Group) {
            return;
        }

        foreach ($attachedIds as $userId) {
            $user = User::find($userId);
            if ($user !== null) {
                $user->logGroupAttached((int) $object->id);
            }
        }
        foreach ($detachedIds as $userId) {
            $user = User::find($userId);
            if ($user !== null) {
                $user->logGroupDetached((int) $object->id);
            }
        }
    }
}

class MappedTable extends Attribute
{
    public function __construct(
        private string $scim_attribute_name,
        private string $relationship_name,
        private string $relationship_class,
        private string $relationship_id_field,
        private string $relationship_field
    ) {
        parent::__construct($this->scim_attribute_name);
    }

    protected function doRead(&$object, $attributes = [])
    {
        return $object->{$this->relationship_name}?->{$this->relationship_field};
    }

    public function add($value, Model &$object)
    {
        $value = $this->coerceScalar($value);
        $object->{$this->relationship_id_field} = $value ? $this->relationship_class::firstOrCreate([$this->relationship_field => $value])->id : null;
    }

    public function replace($value, Model &$object, $path = null, $removeIfNotSet = false)
    {
        $value = $this->coerceScalar($value);
        $object->{$this->relationship_id_field} = $value ? $this->relationship_class::firstOrCreate([$this->relationship_field => $value])->id : null;
    }

    public function patch($operation, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        $value = $this->coerceScalar($value);
        $object->{$this->relationship_id_field} = $value ? $this->relationship_class::firstOrCreate([$this->relationship_field => $value])->id : null;
    }

    // SCIM clients may send scalar-mapped attributes like `department`
    // and `location` as complex objects — {"value": "Engineering"} or
    // {"displayName": "Engineering"} — and SnipeRootComplex::replace()
    // additionally wraps sub-attribute values ({"remaining.path" => v})
    // when descending. MappedTable is a leaf that maps ONE relationship
    // field, so anything arriving as an array must be unwrapped before
    // firstOrCreate() gets it — otherwise Grammar::parameterize()
    // throws when the WHERE binding receives an array. Prefer the SCIM
    // conventional keys "value" and "displayName", then fall back to
    // any scalar leaf; return null if no usable value exists so the
    // caller nulls the relationship (matching the empty-string branch).
    private function coerceScalar($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach (['value', 'displayName'] as $key) {
            if (isset($value[$key]) && is_scalar($value[$key])) {
                return $value[$key];
            }
        }

        foreach ($value as $v) {
            if (is_scalar($v) && $v !== '') {
                return $v;
            }
        }

        return null;
    }
}

// Company is stored only in the company_user pivot, not company_id. Read from the pivot
// and sync it on write. For new users (not yet saved) defer the sync via a saved() callback.
class SCIMCompanyAttribute extends Attribute
{
    protected function doRead(&$object, $attributes = [])
    {
        return $object->companies->first()?->name;
    }

    private function applyCompany(?int $companyId, Model &$object): void
    {
        $ids = $companyId ? [$companyId] : [];

        if ($object->exists) {
            $object->companies()->sync($ids);
            if (method_exists($object, 'syncLegacyCompanyIdMirror')) {
                $object->syncLegacyCompanyIdMirror();
            }
        } else {
            $object->saved(function () use ($object, $ids) {
                $object->companies()->sync($ids);
                if (method_exists($object, 'syncLegacyCompanyIdMirror')) {
                    $object->syncLegacyCompanyIdMirror();
                }
            });
        }
    }

    public function add($value, Model &$object)
    {
        $this->applyCompany($value ? Company::firstOrCreate(['name' => $value])->id : null, $object);
    }

    public function replace($value, Model &$object, $path = null, $removeIfNotSet = false)
    {
        $this->applyCompany($value ? Company::firstOrCreate(['name' => $value])->id : null, $object);
    }

    public function patch($operation, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        $this->applyCompany($value ? Company::firstOrCreate(['name' => $value])->id : null, $object);
    }
}

class SCIMMultiCompanyArray extends Attribute
{
    protected function doRead(&$object, $attributes = [])
    {
        return $object->companies()->pluck('name')->toArray();
    }

    private function applyCompanies(array $company_names, Model &$object): void
    {
        $names = [];
        foreach ($company_names as $company) {
            if (is_array($company) && isset($company['value'])) {
                // this is how Entra ID does it
                $names[] = $company['value'];
            } elseif (is_string($company)) {
                // This seems to be how Okta does it?
                $names[] = $company;
            } else {
                throw new SCIMException("Unknown 'companies' value: '".print_r($company, true)."' of type: ".get_debug_type($company), 400);
            }
        }

        $ids = [];
        foreach ($names as $company_name) {
            $ids[] = Company::firstOrCreate(['name' => $company_name])->id;
        }
        if ($object->exists) {
            $object->companies()->sync($ids);
        } else {
            $object->saved(fn () => $object->companies()->sync($ids));
        }
    }

    public function add($value, Model &$object)
    {
        \Log::debug('MC ADD VALUE IS: '.print_r($value, true));
        $this->applyCompanies($value, $object);
    }

    public function replace($value, Model &$object, $path = null, $removeIfNotSet = false)
    {
        \Log::debug('MC REPLACE VALUE IS: '.print_r($value, true));
        $this->applyCompanies($value, $object);
    }

    public function patch($operation, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        \Log::debug('MC PATCH VALUE IS: '.print_r($value, true));
        $this->applyCompanies($value, $object);
    }
}

class EloquentWithRemove extends Eloquent
{
    public function remove($value, Model &$object, ?Path $path = null)
    {
        $object->{$this->attribute} = null;
    }
}

class UpdatableComplex extends Complex
{
    public function doWrite($operation, $subop, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        throw new \Exception("doWrite is not implemented yet for Operation: $operation ".($subop ? "($subop)" : '').'on attribute '.$this->getFullKey());
    }

    public function add($value, Model &$object)
    {
        $this->doWrite('add', null, $value, $object);
    }

    public function replace($value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        $this->doWrite('replace', null, $value, $object, $path, $removeIfNotSet);
    }

    public function patch($operation, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        $this->doWrite('patch', $operation, $value, $object, $path, $removeIfNotSet);
    }

    public function remove($value, Model &$object, ?Path $path = null)
    {
        $this->doWrite('remove', null, null, $object, $path);
    }
}

class SnipeSCIMConfig
{
    public function __construct() {}

    public function getConfigForResource($name)
    {
        $result = $this->getConfig();

        return @$result[$name];
    }

    public function getGroupClass()
    {
        // Route SCIM group operations through SCIMGroup so that the
        // upstream library's create-path firstOrNew([]) call resolves
        // to a fresh Group instance rather than the first existing row.
        // See SCIMGroup and #19493 for the underlying library bug.
        return SCIMGroup::class;
    }

    const ENTERPRISE = 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User';

    const GROKABILITY = 'urn:ietf:params:scim:schemas:extension:grokability:2.0:User';

    public function getUserConfig()
    {
        return [

            // Set to 'null' to make use of auth.providers.users.model (App\User::class)
            'class' => SCIMUser::class,
            'singular' => 'User',

            // Eager load the relations.
            'withRelations' => ['companies', 'manager'],
            'description' => 'User Account',

            'map' => (new SnipeRootComplex)->withSubAttributes(
                new class('schemas', ['urn:ietf:params:scim:schemas:core:2.0:User', self::ENTERPRISE, self::GROKABILITY]) extends Constant
                {
                    public function replace($value, &$object, $path = null)
                    {
                        // do nothing
                        $this->dirty = true;
                    }
                },
                (new class('id', null) extends Constant // TODO - this 'id' is in the same namespace for objects OR groups?
                {
                    protected function doRead(&$object, $attributes = [])
                    {
                        return (string) $object->id;
                    }

                    public function remove($value, &$object, $path = null)
                    {
                        // do nothing
                    }
                }
                ),
                new Meta('Users'),
                (new AttributeSchema(Schema::SCHEMA_USER, true))->withSubAttributes(
                    eloquent('userName', 'username')->ensure('required'),
                    (new class('active', 'activated') extends Eloquent
                    {
                        protected function doRead(&$object, $attributes = [])
                        {
                            return (bool) $object->activated; // need this extension to force boolean-ness
                        }
                    }),
                    complex('name')->withSubAttributes(
                        eloquent('givenName', 'first_name')->ensure('required'),
                        eloquent('familyName', 'last_name'),
                    ), //     ->ensure('required'),  It *is* a bit weird, but I would've thought 'name' is required since 'givenName' is required? But apparently not?
                    eloquent('displayName', 'display_name'), // yes, this is *not* under 'name' - that's the spec
                    // eloquent('password')->ensure('nullable')->setReturned('never'),
                    eloquent('externalId', 'scim_externalid'),

                    // Email chonk
                    (new class('emails') extends UpdatableComplex
                    {
                        protected function doRead(&$object, $attributes = [])
                        {
                            if (! $object->email) {
                                return null;
                            }

                            // RFC 7643 §4.1.2: multi-valued attributes MUST be
                            // JSON arrays even when they hold a single element.
                            // Return an array-of-objects so `emails` serializes
                            // as `[{...}]`. Previously returned a bare
                            // associative array and Rollbar surfaced downstream
                            // clients constructing malformed filter keys against
                            // the scalar shape.
                            return [[
                                'value' => $object->email,
                                'type' => 'work',
                                'primary' => true,
                            ]];
                        }

                        public function doWrite($operation, $subop, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
                        {
                            if ($value) {
                                try {
                                    if (is_string($value)) {
                                        $object->email = $value; // Weird MS-SCIM stuff :/
                                    } else {
                                        $object->email = $value[0]['value'];
                                    }
                                } catch (\Throwable $e) {
                                    \Log::debug($e);
                                    throw new SCIMException("Unknown email object:  '".print_r($value, true)."'", 422);
                                }
                            } else {
                                $object->email = null;
                            }
                        }
                    })->withSubAttributes(
                        eloquent('value', 'email')->ensure('email', 'nullable'), // Weird, this 'needs' nullable to work?
                        new Constant('type', 'work'),
                        (new Constant('primary', true))->ensure('boolean')
                    )->ensure('array')
                        ->setMultiValued(true),

                    // phone chonk
                    (new class('phoneNumbers') extends UpdatableComplex
                    {
                        protected function doRead(&$object, $attributes = [])
                        {
                            $phones = [];
                            if ($object->phone) {
                                $phones[] = [
                                    'value' => $object->phone,
                                    'type' => 'work',
                                ];

                            }
                            if ($object->mobile) {
                                $phones[] = [
                                    'value' => $object->mobile,
                                    'type' => 'mobile',
                                ];
                            }

                            return $phones;
                        }

                        public function doWrite($operation, $subop, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
                        {
                            \Log::debug("Phones 'value' is: ".print_r($value, true));
                            try {
                                if ($operation == 'patch') {
                                    if ($path->getValuePathFilter() != null) {
                                        if ((string) $path == 'phoneNumbers[type eq "mobile"].value') {
                                            $object->mobile = $value; // I don't know why the value is the raw value, but it is?

                                            return;
                                        }
                                        if ((string) $path == 'phoneNumbers[type eq "work"].value') {
                                            $object->phone = $value; // similar, don't know why, but it is

                                            return;
                                        }
                                    }
                                    parent::patch($subop, $value, $object, $path, $removeIfNotSet);

                                    return;
                                }
                                foreach ($value as $phone) {
                                    switch ($phone['type']) {
                                        case 'work':
                                            $object->phone = $phone['value'];
                                            break;

                                        case 'mobile':
                                            $object->mobile = $phone['value'];
                                            break;

                                        default:
                                            throw new SCIMException("Unknown phone type '".@$phone['type']."'", 400);
                                    }
                                }
                            } catch (\Throwable $e) {
                                \Log::debug($e);
                                throw new SCIMException("Unknown phone object(s) '".print_r($value, true)."'", 422);
                            }
                        }
                    })->withSubAttributes( // TODO: I suspect these 'sub-attributes' aren't being checked at all
                        (new Constant('value', 'email'))->ensure('string'), // TODO - this is WRONG, but it works somehow? Probably because it's ignored
                        new Constant('type', 'other'), // TODO uh, *also* wrong? but, again, seems to be ignored
                    )->ensure('array')
                        ->setMultiValued(true),

                    // addresses chonk
                    (new class('addresses') extends UpdatableComplex
                    {
                        public static $addressmap = [
                            'streetAddress' => 'address',
                            'locality' => 'city',
                            'region' => 'state',
                            'postalCode' => 'zip',
                            'country' => 'country',
                        ];

                        protected function doRead(&$object, $attributes = [])
                        {
                            $address = [];
                            foreach (self::$addressmap as $scim_field => $db_field) {
                                if ($object->{$db_field}) {
                                    $address[$scim_field] = $object->{$db_field};
                                }
                            }
                            if (count($address) > 0) {
                                $address['type'] = 'work';
                                $address['primary'] = true;
                            }
                            if ($address) {
                                return [(object) $address]; // cast-to-object forces "squiggly-brackets" in JSON
                            } else {
                                return null; // this should remove the addresses block entirely
                            }
                        }

                        public function doWrite($operation, $subop, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
                        {
                            // $path is nullable per the signature. Treat a null path the same as a filter-less path:
                            // fall through to the bulk-replace branch below. Without this guard the next line
                            // crashes with "Call to a member function getValuePathFilter() on null" on PUT-shaped
                            // requests from clients that don't send a path (Okta's create-user flow is one).
                            if ($path !== null && $path->getValuePathFilter() !== null) {
                                \Log::debug("path for update $path");
                                // get the part of the $path that we actually care about - something like:
                                // addresses[type eq "work"]
                                $matches = null;
                                if (! preg_match('/^.+\[type eq "([a-zA-Z]+)"](?:\.([a-zA-Z]+))?$/', (string) $path, $matches)) {
                                    throw new SCIMException("Unknown path type '$path'", 422);
                                }
                                $type = $matches[1];
                                if ($type != 'work') {
                                    throw new SCIMException("Unknown object type '$type'", 422);
                                }
                                $attribute = array_key_exists(2, $matches) ? $matches[2] : null;
                                if (array_key_exists($attribute, self::$addressmap)) {
                                    $object->{self::$addressmap[$attribute]} = $value;

                                    return;
                                }

                                throw new SCIMException("Could not handle path for update $path", 422);
                            } else {
                                // Okta hits this one for creating a user - it does a full PUT for their ID
                                \Log::debug("GetValuePAthFilter is null for path: $path");
                                \Log::debug('GetValuePathFilter is now null and trying to set value of: '.print_r($value, true));
                                // the Addresses object is a 'list' (array with numeric indices) by definition...
                                if (is_array($value) && array_is_list($value)) {
                                    foreach ($value as $address) {
                                        // we just need to check if this is a 'work' address, we don't really care about "primary => true"
                                        if (@$address['type'] == 'work') {
                                            foreach ($address as $key => $v) {
                                                if (array_key_exists($key, self::$addressmap)) {
                                                    \Log::debug('Addresses: Setting '.self::$addressmap[$key]." to '$v'");
                                                    $object->{self::$addressmap[$key]} = $v;
                                                }
                                            }
                                        } else {
                                            // should we throw if you give us a 'home' address? I don't know.
                                            // what if you gave us _both_ ?
                                        }
                                    }
                                } else {
                                    \Log::debug('Unknown Address Object: '.print_r($value, true));
                                    throw new SCIMException('Unknown Address object of type: '.gettype($value), 422);
                                }
                            }
                        }
                    })->withSubAttributes(
                        eloquent('streetAddress', 'address'),
                        eloquent('locality', 'city'),
                        eloquent('region', 'state'),
                        eloquent('postalCode', 'zip'),
                        eloquent('country', 'country'),
                        new Constant('type', 'other'),
                        (new Constant('primary', true))->ensure('boolean')
                    )->ensure('array')
                        ->setMultiValued(true),

                    eloquent('title', 'jobtitle'),
                    eloquent('preferredLanguage', 'locale'),
                    (new Collection('groups'))->withSubAttributes(
                        (new class('value', 'id') extends Eloquent
                        {
                            // RFC 7643 §4.1.2 defines groups[].value as a
                            // string.
                            protected function doRead(&$object, $attributes = [])
                            {
                                $value = parent::doRead($object, $attributes);

                                return $value === null ? null : (string) $value;
                            }
                        }),
                        (new class('$ref') extends Eloquent
                        {
                            protected function doRead(&$object, $attributes = [])
                            {
                                return route(
                                    'scim.resource',
                                    [
                                        'resourceType' => 'Group',
                                        'resourceObject' => $object->id ?? 'not-saved',
                                    ]
                                );
                            }
                        }),
                        eloquent('display', 'name')
                    ),
                    (new JSONCollection('roles'))->withSubAttributes( // TODO - what is this?
                        eloquent('value')->ensure('required', 'min:3', 'alpha_dash:ascii'),
                        eloquent('display')->ensure('nullable', 'min:3', 'alpha_dash:ascii'),
                        eloquent('type')->ensure('nullable', 'min:3', 'alpha_dash:ascii'),
                        eloquent('primary')->ensure('boolean')->default(false)
                    )->ensure('nullable', 'array', 'max:20')
                ),
                (new AttributeSchema(self::ENTERPRISE, false))->withSubAttributes(
                    eloquent('employeeNumber', 'employee_num')->ensure('nullable'),
                    new MappedTable('department', 'department', Department::class, 'department_id', 'name'),
                    (new class('manager') extends UpdatableComplex
                    {
                        protected function doRead(&$object, $attributes = [])
                        {
                            if (! $object->manager) {
                                return null;
                            }

                            return [
                                'value' => (string) $object->manager->id, // TODO - ID's aren't unique like they're supposed to be :/
                                '$ref' => route('scim.resource', ['resourceType' => 'User', 'resourceObject' => $object->manager->id]),
                                'displayName' => $object->manager->display_name,
                            ];
                        }

                        public function doWrite($operation, $subop, $value, Model &$object, $path = null, $removeIfNotSet = false)
                        {
                            \Log::debug('What type of value is value? '.gettype($value));
                            $manager_id = null;
                            if (is_null($value)) {
                                // nothing to do
                            } elseif (is_scalar($value)) {
                                \Log::debug('Weird Microsoft mode - set manager to the $value and move on with life?');
                                $manager_id = $value;
                            } elseif (array_key_exists('$ref', $value)) {
                                // Here's the spec: https://datatracker.ietf.org/doc/html/rfc7643#section-4.3

                                // according to the spec it's _recommended_ to do:
                                // $ref - which should be the URI of the manager

                                // extract ID from URL, jam it in?
                                $url = $value['$ref'];
                                $users_prefix = route('scim.resources', ['resourceType' => 'User']).'/';
                                if (str_starts_with($url, $users_prefix)) {
                                    $manager_id = substr($url, strlen($users_prefix));
                                }
                            } elseif (array_key_exists('value', $value)) {
                                // this is _Snipe-IT_'s ID being passed as 'value' I believe?
                                // if you use the 'managerId' field in Okta, you get:
                                //     [value] => 9999999
                                // that, at least, is the spec - but *what* ID is that?! It's supposed to be a Snipe-IT one!
                                $manager_id = $value['value'];
                            }
                            \Log::debug("Non-Microsoft - Trying to '$operation' for manager with value: ".print_r($value, true));
                            if ($manager_id && User::find($manager_id)) {
                                $object->manager_id = $manager_id;

                                return;
                            }
                            throw new SCIMException("No manager given, or manager doesn't exist", 400);
                        }
                    }) // ->withSubAttributes() ... -> ensure() ?
                ),
                (new AttributeSchema(self::GROKABILITY, false))->withSubAttributes(
                    new MappedTable('location', 'location', Location::class, 'location_id', 'name'),
                    new SCIMCompanyAttribute('company'),
                    (new SCIMMultiCompanyArray('companies'))->ensure('array')->setMultiValued(true),
                )
            ),
        ];
    }

    public function getGroupConfig()
    {
        return [

            'class' => $this->getGroupClass(),
            'singular' => 'Group',

            // eager loading
            'withRelations' => [],
            'description' => 'Group',

            'map' => complex()->withSubAttributes(
                new class('schemas', ['urn:ietf:params:scim:schemas:core:2.0:Group']) extends Constant
                {
                    public function replace($value, &$object, $path = null)
                    {
                        // do nothing
                        $this->dirty = true;
                    }
                },
                (new class('id', null) extends Constant
                {
                    protected function doRead(&$object, $attributes = [])
                    {
                        return (string) $object->id;
                    }

                    public function remove($value, &$object, $path = null)
                    {
                        // do nothing
                    }
                }
                ),
                new EloquentWithRemove('externalId', 'scim_externalid'),
                new Meta('Groups'),
                (new AttributeSchema(Schema::SCHEMA_GROUP, true))->withSubAttributes(
                    eloquent('displayName', 'name')->ensure('required', 'min:3', function ($attribute, $value, $fail) {
                        // check if group does not exist or if it exists, it is the same group
                        $group = $this->getGroupClass()::where('name', $value)->first();
                        if (! $group) {
                            return;
                        }
                        // PATCH/PUT: the group being edited is route-bound.
                        $routeResource = request()->route('resourceObject');
                        if ($routeResource && $group->id == $routeResource->id) {
                            return;
                        }
                        // POST create with an initial members[] triggers SnipeMutableCollection
                        // to save the parent early (so the pivot INSERT has a valid group_id),
                        // which puts a row in the DB before this closure re-runs. Recognize the
                        // just-saved row as ours so validation doesn't false-positive.
                        $inFlight = request()->attributes->get('scim_in_flight_resource');
                        if ($inFlight && $group->id == $inFlight->id) {
                            return;
                        }
                        $fail('The name has already been taken.');
                    }),
                    // The per-member `required` rule on `value` used to live
                    // on the eloquent() below. Removed intentionally: Laravel's
                    // ValidationRuleParser::mergeRulesForAttribute allocates one
                    // rule stack per attribute path in the flattened payload, so
                    // an incoming members array of N entries produced O(N) rule
                    // stacks and blew the PHP memory_limit on large group syncs
                    // The per-member value check now lives inside
                    // SnipeMutableCollection::add() as one array walk, and the
                    // parent ensure() below adds a `max:` guardrail so a truly
                    // runaway payload still gets rejected with a clean 400.
                    (new SnipeMutableCollection('members'))->withSubAttributes(
                        eloquent('value', 'id'),
                        (new class('$ref') extends Eloquent
                        {
                            protected function doRead(&$object, $attributes = [])
                            {
                                return route(
                                    'scim.resource',
                                    [
                                        'resourceType' => 'Users',
                                        'resourceObject' => $object->id ?? 'not-saved',
                                    ]
                                );
                            }
                        }),
                        eloquent('display', 'name')
                    )->ensure('nullable', 'array', 'max:200000')
                )
            ),
        ];
    }

    public function getConfig()
    {
        return [
            'Users' => $this->getUserConfig(),
            'Groups' => $this->getGroupConfig(),
        ];
    }
}
