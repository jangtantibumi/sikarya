<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginAndCheckoutTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test the critical happy path of a POS Checkout.
     * This is a blueprint demonstrating how Dusk tests verify the UI and JS functionality.
     */
    public function test_cashier_can_login_and_complete_checkout(): void
    {
        $user = User::factory()->create([
            'email' => 'cashier@subaerp.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                    // Using dusk attributes makes tests immune to CSS class changes
                    ->type('@email-input', $user->email)
                    ->type('@password-input', 'password')
                    ->click('@login-submit-btn')
                    ->assertPathIs('/dashboard')
                    
                    // Navigate to POS
                    ->click('@nav-pos-module')
                    ->assertSee('Point of Sale')
                    
                    // Simulate adding an item to cart via UI
                    ->click('@product-card-1')
                    ->waitForText('Total: Rp')
                    
                    // Proceed to checkout
                    ->click('@checkout-btn')
                    ->waitFor('@payment-modal')
                    ->type('@payment-amount-input', '100000')
                    ->click('@confirm-payment-btn')
                    
                    // Verify success toast/notification appears
                    ->waitForText('Transaction Successful')
                    ->assertSee('Transaction Successful');
        });
    }
}
