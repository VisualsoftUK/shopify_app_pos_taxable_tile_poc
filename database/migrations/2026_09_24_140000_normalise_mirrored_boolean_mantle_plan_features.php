<?php

use Illuminate\Database\Migrations\Migration;
use Visualsoft\ShopifyAppPricing\Actions\NormaliseMirroredBooleanMantlePlanFeaturesAction;

return new class extends Migration
{
    public function up(): void
    {
        app(NormaliseMirroredBooleanMantlePlanFeaturesAction::class)->execute();
    }

    public function down(): void
    {
        //
    }
};
