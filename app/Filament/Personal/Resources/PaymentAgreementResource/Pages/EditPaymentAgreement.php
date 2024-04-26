<?php

namespace App\Filament\Personal\Resources\PaymentAgreementResource\Pages;

use App\Filament\Personal\Resources\PaymentAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentAgreement extends EditRecord
{
    protected static string $resource = PaymentAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
