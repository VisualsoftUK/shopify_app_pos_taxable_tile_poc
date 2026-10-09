<?php

namespace Database\Factories\Domain\Models;

use App\Domain\Enums\TemplatePlan;
use App\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Visualsoft\Mantle\Models\MantlePlan;
use Visualsoft\ShopifyAppPricing\Actions\SeedAppPricingPlanForShopAction;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = str(fake()->unique()->words(4, true))->slug() . '.myshopify.com';

        return [
            'name' => $domain,
            'email' => 'store@' . $domain,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Keep a hostname on the shop so App Pricing plan URLs do not call Shopify.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if (is_string($user->primary_domain) === true && $user->primary_domain !== '') {
                return;
            }

            $user->forceFill([
                'primary_domain' => $user->name,
            ])->saveQuietly();
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Attach an active Mantle plan after the user is created.
     *
     * @param  array<string, mixed>  $featureOverrides
     */
    public function withMantlePlan(array $featureOverrides = []): static
    {
        return $this->afterCreating(function (User $user): void {
            $mantlePlan = MantlePlan::factory()->createQuietly();

            $user->mantle()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'mantle_plan_id' => $mantlePlan->id,
                    'billable_status' => 'active',
                ],
            );
        });
    }

    /**
     * Seed a functional App Pricing plan (plan_assigned) for gate and Inertia shares.
     *
     * @param  array<string, bool|array<string, mixed>>  $featureOverrides
     * @param  array<string, mixed>  $planAttributes  Passed to SeedAppPricingPlanForShopAction (e.g. shopify_plan_handle)
     */
    public function withAppPricingPlan(array $featureOverrides = [], array $planAttributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($featureOverrides, $planAttributes): void {
            $planAttributes = array_merge([
                'shopify_plan_handle' => TemplatePlan::Free->value,
                'plan_name' => 'Free',
            ], $planAttributes);

            app(SeedAppPricingPlanForShopAction::class)->execute($user, $featureOverrides, $planAttributes);

            $user->unsetRelation('appPricingState');
        });
    }
}
