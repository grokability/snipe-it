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
    // attribute; otherwise defer to the library.
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
    public function add($value, Model &$object)
    {
        if (! $object->exists) {
            $object->save();
            request()->attributes->set('scim_in_flight_resource', $object);
        }

        parent::add($value, $object);
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
        $object->{$this->relationship_id_field} = $value ? $this->relationship_class::firstOrCreate([$this->relationship_field => $value])->id : null;
    }

    public function replace($value, Model &$object, $path = null, $removeIfNotSet = false)
    {
        $object->{$this->relationship_id_field} = $value ? $this->relationship_class::firstOrCreate([$this->relationship_field => $value])->id : null;
    }

    public function patch($operation, $value, Model &$object, ?Path $path = null, $removeIfNotSet = false)
    {
        $object->{$this->relationship_id_field} = $value ? $this->relationship_class::firstOrCreate([$this->relationship_field => $value])->id : null;
    }
}

// Company is stored only in the company_user pivot, not company_id. Read from the pivot
// and sync it on write. For new users (not yet saved) defer the sync via a saved() callback.
class SCIMCompanyAttribute extends MappedTable
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
        return Group::class;
    }

    const ENTERPRISE = 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User';

    const GROKABILITY = 'urn:ietf:params:scim:schemas:extension:grokability:2.0:User';

    public function getUserConfig()
    {
        return [

            // Set to 'null' to make use of auth.providers.users.model (App\User::class)
            'class' => SCIMUser::class,
            'singular' => 'User',

            // eager loading
            'withRelations' => [],
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
                            // TODO - this is validated *just* for 'patch' operations, so this may not work in other write contexts
                            if ($path->getValuePathFilter() != null) {
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
                        eloquent('value', 'id'),
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
                                'value' => $object->manager->id, // TODO - ID's aren't unique like they're supposed to be :/
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
                    new SCIMCompanyAttribute('company', 'company', Company::class, 'company_id', 'name'),
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
                    (new SnipeMutableCollection('members'))->withSubAttributes(
                        eloquent('value', 'id')->ensure('required'),
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
                    )->ensure('nullable', 'array')
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
