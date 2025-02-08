<?php

namespace App\Filament\Imports;

use App\Models\Client;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ClientImporter extends Importer
{
    protected static ?string $model = Client::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('phone')
                ->rules(['max:255']),
            ImportColumn::make('locale')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('timezone')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function resolveRecord(): ?Client
    {
        return Client::firstOrNew([
            'email' => $this->data['email'],
        ], [
            'name' => $this->data['name'],
            'phone' => $this->data['phone'],
            'language' => $this->data['language'],
            'timezone' => $this->data['timezone'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your client import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
