<?php

namespace App\Filament\Resources\RegistrationFormResource\Pages;

use App\Filament\Resources\RegistrationFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationForm extends EditRecord
{
    protected static string $resource = RegistrationFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
