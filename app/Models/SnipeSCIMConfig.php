<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Helper;
use ArieTimmerman\Laravel\SCIMServer\SCIM\Schema;
use ArieTimmerman\Laravel\SCIMServer\Attribute\AttributeMapping;


class SnipeSCIMConfig extends \ArieTimmerman\Laravel\SCIMServer\SCIMConfig
{
    public function getUserConfig()
    {
        // Much of this is copied verbatim from the library, then adjusted for our needs

        /*
          more snipe-it attributes I'd like to check out (to map to 'enterprise' maybe?):
         - website
         - notes?
         - remote???
         - location_id ?
         - company_id to "organization?"
        */


        $user_prefix = 'urn:ietf:params:scim:schemas:core:2.0:User:';
        $enterprise_prefix = 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:';

        return [

            // Set to 'null' to make use of auth.providers.users.model (App\User::class)
            'class' => SCIMUser::class,

            'validations' => [
                $user_prefix . 'userName' => 'required',
                $user_prefix . 'displayName' => 'nullable|string',
                $user_prefix . 'name.givenName' => 'required',
                $user_prefix . 'name.familyName' => 'nullable|string',
                $user_prefix . 'externalId' => 'nullable|string',
                $user_prefix . 'emails' => 'nullable|array',
                $user_prefix . 'emails.*.value' => 'nullable|email',
                $user_prefix . 'active' => 'boolean',
                $user_prefix . 'phoneNumbers' => 'nullable|array',
                $user_prefix . 'phoneNumbers.*.value' => 'nullable|string',
                $user_prefix . 'addresses' => 'nullable|array',
                $user_prefix . 'addresses.*.streetAddress' => 'nullable|string',
                $user_prefix . 'addresses.*.locality' => 'nullable|string',
                $user_prefix . 'addresses.*.region' => 'nullable|string',
                $user_prefix . 'addresses.*.postalCode' => 'nullable|string',
                $user_prefix . 'addresses.*.country' => 'nullable|string',
                $user_prefix . 'title' => 'nullable|string',
                $user_prefix . 'preferredLanguage' => 'nullable|string',

                // Enterprise validations:
                $enterprise_prefix . 'employeeNumber' => 'nullable|string',
                $enterprise_prefix . 'department' => 'nullable|string',
                $enterprise_prefix . 'manager' => 'nullable',
                $enterprise_prefix . 'manager.value' => 'nullable|string'
            ],

            'singular' => 'User',
            'schema' => [Schema::SCHEMA_USER],

            //eager loading
            'withRelations' => [],
            'map_unmapped' => false,
            //            'unmapped_namespace' => 'urn:ietf:params:scim:schemas:laravel:unmapped',
            'description' => 'User Account',

            // Map a SCIM attribute to an attribute of the object.
            'mapping' => [

                'id' => (new AttributeMapping())->setRead(
                    function (&$object) {
                        return (string)$object->id;
                    }
                )->disableWrite(),

                'externalId' => AttributeMapping::eloquent('scim_externalid'), // FIXME - I have a PR that changes a lot of this.

                'meta' => [
                    'created' => AttributeMapping::eloquent("created_at")->disableWrite(),
                    'lastModified' => AttributeMapping::eloquent("updated_at")->disableWrite(),

                    'location' => (new AttributeMapping())->setRead(
                        function ($object) {
                            return route(
                                'scim.resource',
                                [
                                    'resourceType' => 'Users',
                                    'resourceObject' => $object->id
                                ]
                            );
                        }
                    )->disableWrite(),

                    'resourceType' => AttributeMapping::constant("User")
                ],

                'schemas' => AttributeMapping::constant(
                    [
                        'urn:ietf:params:scim:schemas:core:2.0:User',
                        'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User'
                    ]
                )->ignoreWrite(),

                'urn:ietf:params:scim:schemas:core:2.0:User' => [

                    'userName' => AttributeMapping::eloquent("username"),

                    'name' => [
                        'formatted' => (new AttributeMapping())->ignoreWrite()->setRead(
                            function (&$object) {
                                return $object->getFullNameAttribute();
                            }
                        ),
                        'familyName' => AttributeMapping::eloquent("last_name"),
                        'givenName' => AttributeMapping::eloquent("first_name"),
                        'middleName' => null,
                        'honorificPrefix' => null,
                        'honorificSuffix' => null
                    ],

                    'displayName' => AttributeMapping::eloquent("display_name"),
                    'nickName' => null,
                    'profileUrl' => null,
                    'title' => AttributeMapping::eloquent('jobtitle'),
                    'userType' => null,
                    'preferredLanguage' => AttributeMapping::eloquent('locale'), // Section 5.3.5 of [RFC7231]
                    'locale' => null, // see RFC5646
                    'timezone' => null, // see RFC6557
                    'active' => (new AttributeMapping())->setAdd(
                        function ($value, &$object) {
                            $object->activated = $value;
                        }
                    )->setReplace(
                        function ($value, &$object) {
                            $object->activated = $value;
                        }
                    )->setRead(
                        // this works as specified.
                        function (&$object) {
                            return (bool)$object->activated;
                        }
                    ),
                    'password' => AttributeMapping::eloquent('password')->disableRead(),

                    // Multi-Valued Attributes
                    'emails' => [[
                        "value" => AttributeMapping::eloquent("email"),
                        "display" => null,
                        "type" => AttributeMapping::constant("work")->ignoreWrite(),
                        "primary" => AttributeMapping::constant(true)->ignoreWrite()
                    ]],

                    'phoneNumbers' => [[
                        "value" => AttributeMapping::eloquent("phone"),
                        "display" => null,
                        "type" => AttributeMapping::constant("work")->ignoreWrite(),
                        "primary" => AttributeMapping::constant(true)->ignoreWrite()
                    ]],

                    'ims' => [[
                        "value" => null,
                        "display" => null,
                        "type" => null,
                        "primary" => null
                    ]], // Instant messaging addresses for the User

                    'photos' => [[
                        "value" => null,
                        "display" => null,
                        "type" => null,
                        "primary" => null
                    ]],

                    'addresses' => [[
                        'type' => AttributeMapping::constant("work")->ignoreWrite(),
                        'formatted' => AttributeMapping::constant("n/a")->ignoreWrite(), // TODO - is this right? This doesn't look right.
                        'streetAddress' => AttributeMapping::eloquent("address"),
                        'locality' => AttributeMapping::eloquent("city"),
                        'region' => AttributeMapping::eloquent("state"),
                        'postalCode' => AttributeMapping::eloquent("zip"),
                        'country' => AttributeMapping::eloquent("country"),
                        'primary' => AttributeMapping::constant(true)->ignoreWrite() //this isn't in the example?
                    ]],

                    'groups' => [[
                        'value' => null,
                        '$ref' => null,
                        'display' => null,
                        'type' => null,
                    ]],

                    'entitlements' => null,
                    'roles' => null,
                    'x509Certificates' => null
                ],

                'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User' => [
                    'employeeNumber' => AttributeMapping::eloquent('employee_num'),
                    'department' => (new AttributeMapping())->setAdd( // FIXME parent?
                        function ($value, &$object) {
                            $department = Department::where("name", $value)->first();
                            if ($department) {
                                $object->department_id = $department->id;
                            }
                        }
                    )->setReplace(
                        function ($value, &$object) {
                            $department = Department::where("name", $value)->first();
                            if ($department) {
                                $object->department_id = $department->id;
                            }
                        }
                    )->setRead(
                        function (&$object) {
                            return $object->department ? $object->department->name : null;
                        }
                    ),
                    'manager' => [
                        // FIXME - manager writes are disabled. This kinda works but it leaks errors all over the place. Not cool.
                        // '$ref' => (new AttributeMapping())->ignoreWrite()->ignoreRead(),
                        // 'displayName' => (new AttributeMapping())->ignoreWrite()->ignoreRead(),
                        // NOTE: you could probably do a 'plain' Eloquent mapping here, but we don't for future-proofing
                        'value' => (new AttributeMapping())->setAdd(
                            function ($value, &$object) {
                                $manager = User::find($value);
                                if ($manager) {
                                    $object->manager_id = $manager->id;
                                }
                            }
                        )->setReplace(
                            function ($value, &$object) {
                                $manager = User::find($value);
                                if ($manager) {
                                    $object->manager_id = $manager->id;
                                }
                            }
                        )->setRead(
                            function (&$object) {
                                return $object->manager_id;
                            }
                        ),
                    ]
                ]
            ]
        ];
    }
}
