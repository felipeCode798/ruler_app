<?php

namespace App\Filament\Resources\PaymentAgreementResource\Pages;

use App\Filament\Resources\PaymentAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentAgreements extends ListRecords
{
    protected static string $resource = PaymentAgreementResource::class;
    protected static ?string $title = 'Acuerdos de pago';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Acuerdo de pago'),
        ];
    }
}
