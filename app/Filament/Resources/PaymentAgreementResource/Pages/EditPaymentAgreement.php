<?php

namespace App\Filament\Resources\PaymentAgreementResource\Pages;

use App\Filament\Resources\PaymentAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentAgreement extends EditRecord
{
    protected static string $resource = PaymentAgreementResource::class;
    protected static ?string $title = 'Editar acuerdo de pago';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
