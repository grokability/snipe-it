<?php

namespace Tests\Feature;

use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;

class NotificationsComponentTest extends TestCase
{
    public function testItCanAddSimpleDynamicSuccessNotification()
    {
        Livewire::test('notifications')
            ->call('notify', 'success', 'Saved!')
            ->assertSee('Saved!')
            ->assertSee('alert-success');
    }

    public function testItCanAddDynamicNotificationWithTitleDescriptionAndAcon()
    {
        Livewire::test('notifications')
            ->call('notify', 'success', 'Saved!', 'Asset Created', 'MacBook added', 'fas fa-laptop')
            ->assertSee('Asset Created')
            ->assertSee('MacBook added')
            ->assertSee('fas fa-laptop')
            ->assertSee('alert-success');
    }

    public function testItCanAddDynamicNotificationWithHtmlMessage()
    {
        Livewire::test('notifications')
            ->call('notify', 'success', '<strong>Bold</strong> text', null, null, null, true)
            ->assertSeeHtml('<strong>Bold</strong> text');
    }

    public function testItCanReplaceDynamicNotificationByTag()
    {
        $component = Livewire::test('notifications')
            ->call('notify', 'info', 'First message', null, null, null, false, false, 'progress');

        $component->assertSee('First message');

        $component->call('notify', 'info', 'Second message', null, null, null, false, false, 'progress');
        $component->assertSee('Second message');
        $component->assertDontSee('First message');
    }

    public function testLegacySessionSuccessNotificationIsRendered()
    {
        Session::flash('success', 'Legacy flash success!');
        Livewire::test('notifications')
            ->assertSee('Legacy flash success!')
            ->assertSee('alert-success');
    }

    public function testLegacySessionErrorNotificationIsRendered()
    {
        Session::flash('error', 'Legacy error!');
        Livewire::test('notifications')
            ->assertSee('Legacy error!')
            ->assertSee('alert-danger');
    }

    public function testLegacySessionSuccessUnescapedNotificationIsRendered()
    {
        Session::flash('success-unescaped', '<b>Legacy Unescaped</b>');
        Livewire::test('notifications')
            ->assertSeeHtml('<b>Legacy Unescaped</b>');
    }

    public function testLegacySessionWarningNotificationIsRendered()
    {
        Session::flash('warning', 'Legacy warning!');
        Livewire::test('notifications')
            ->assertSee('Legacy warning!')
            ->assertSee('alert-warning');
    }

    public function testLegacySessionInfoNotificationIsRendered()
    {
        Session::flash('info', 'Legacy info!');
        Livewire::test('notifications')
            ->assertSee('Legacy info!')
            ->assertSee(values: 'alert-info');
    }

    public function testLegacySessionBulkAssetErrorsAreRendered()
    {
        Session::flash('bulk_asset_errors', [
            'row1' => ['Missing tag'],
            'row2' => ['Model not found', 'Serial required'],
        ]);
        Livewire::test('notifications')
            ->assertSee('Missing tag')
            ->assertSee('Model not found')
            ->assertSee('Serial required');
    }
}