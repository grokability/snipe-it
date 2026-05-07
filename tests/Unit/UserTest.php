<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_first_name_split()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_firstname = 'Natalia';
        $expected_lastname = "Allanovna Romanova-O'Shostakova";
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname');
        $this->assertEquals($expected_firstname, $user['first_name']);
        $this->assertEquals($expected_lastname, $user['last_name']);
    }

    public function test_first_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'natalia';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'natalia@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname');
        $this->assertEquals($expected_username, $user['username'].'@example.com');
    }

    public function test_first_name_dot_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'natalia.allanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname.lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_dot_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'natalia.allanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname.lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name_first_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakovan';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastnamefirstinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_first_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'allanovna-romanova-oshostakovan@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastnamefirstinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_initial_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'filastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_initial_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'filastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_initial_underscore_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial_lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_initial_underscore_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial_lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_single_name()
    {
        $fullname = 'Natalia';
        $expected_username = 'natalia';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname_lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_single_name_email()
    {
        $fullname = 'Natalia';
        $expected_email = 'natalia@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname_lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_initial_dot_lastname()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial.lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_initial_dot_lastname_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial.lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name_dot_first_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova.n';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname.firstinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_dot_first_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'allanovna-romanova-oshostakova.n@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname.firstinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name_underscore_first_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova_n';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname_firstinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_underscore_first_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'allanovna-romanova-oshostakova_n@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname_firstinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_name_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nataliaallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nataliaallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_name_last_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nataliaa';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_last_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nataliaa@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }
}
