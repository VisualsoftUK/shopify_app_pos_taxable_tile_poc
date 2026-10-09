<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Osiset\ShopifyApp\Traits\ShopModel;
use Visualsoft\Mantle\Concerns\MantleModel;
use Visualsoft\Mantle\Contracts\MantleableModel;
use Visualsoft\Shopify\Concerns\HasShopifyIdentifiers;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\ShopifyAdmin\Models\Common\SetUpStep;
use Visualsoft\ShopifyAppPricing\Concerns\AppPricingStateModel;
use Visualsoft\ShopifyAppPricing\Contracts\HasAppPricingState;

class User extends Authenticatable implements HasAppPricingState, MantleableModel, VsShopModel
{
    use AppPricingStateModel;
    use HasApiTokens, HasFactory, Notifiable;
    use HasShopifyIdentifiers, MantleModel, ShopModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'shop_id',
        'installation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setUpSteps(): HasMany
    {
        return $this->hasMany(SetUpStep::class);
    }
}
