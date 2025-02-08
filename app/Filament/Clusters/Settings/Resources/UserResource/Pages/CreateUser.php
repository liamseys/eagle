<?php

namespace App\Filament\Clusters\Settings\Resources\UserResource\Pages;

use App\Actions\Users\CreateUser as CreateUserAction;
use App\Filament\Clusters\Settings\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $createUser = app(CreateUserAction::class);

        return $createUser->handle($data);
    }
}
