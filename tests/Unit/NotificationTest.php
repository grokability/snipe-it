<?php
namespace Tests\Unit;

use App\Mail\CheckoutAssetMail;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function testAUserIsEmailedIfTheyCheckoutAnAssetWithEULA()
    {
        $admin = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()
        ->create(
            [
                'model_id' => AssetModel::factory()
                    ->create(
                        [
                            'category_id' => Category::factory()->assetLaptopCategory()->create()->id
                        ]
                )->id,
                'warranty_months' => 24,
                'purchase_date' =>   Carbon::createFromDate(2017, 1, 1)->hour(0)->minute(0)->second(0)->format('Y-m-d')
            ]);

        Mail::fake();
        $asset->checkOut($user, $admin->id);
        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) use ($user, $asset) {
            return $mail->hasTo($user->email) && $mail->hasSubject(trans('mail.Asset_Checkout_Notification', ['tag' => $asset->asset_tag]));
        });
    }
    public function testDefaultEulaIsSentWhenSetInCategory()
    {
        Mail::fake();

        $this->settings->setEula('My Custom EULA Text');

        $user = User::factory()->create();

        $category = Category::factory()->create([
            'use_default_eula' => 1,
            'eula_text' => 'EULA Text that should not be used',
        ]);

        $model = AssetModel::factory()->for($category)->create();
        $asset = Asset::factory()->for($model, 'model')->create();

        $asset->checkOut($user, User::factory()->superuser()->create()->id);

        Mail::assertSent(CheckoutAssetMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) &&
                str_contains($mail->render(), 'My Custom EULA Text') &&
                !str_contains($mail->render(), 'EULA Text that should not be used');
        });
    }
}
