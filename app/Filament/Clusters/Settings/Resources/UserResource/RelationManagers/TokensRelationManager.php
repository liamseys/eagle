<?php

namespace App\Filament\Clusters\Settings\Resources\UserResource\RelationManagers;

use App\Models\PersonalAccessToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('expiration')
                    ->options([
                        7 => '7 days ('.Carbon::now()->addDays(7)->format('M d, Y').')',
                        30 => '30 days ('.Carbon::now()->addDays(30)->format('M d, Y').')',
                        60 => '60 days ('.Carbon::now()->addDays(60)->format('M d, Y').')',
                        90 => '90 days ('.Carbon::now()->addDays(90)->format('M d, Y').')',
                        'no_expiration' => 'No expiration',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (PersonalAccessToken $record): string => $record->expires_at
                        ? 'Expires on '.\Carbon\Carbon::parse($record->expires_at)->format('D, M d Y')
                        : '-'),
                Tables\Columns\TextColumn::make('last_used_at'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (Request $request, array $data, string $model): Model {
                        $user = $request->user();

                        $expiresAt = $data['expiration'] === 'no_expiration'
                            ? null
                            : now()->addDays((int) $data['expiration']);

                        $user->createToken($data['name'], ['*'], $expiresAt);

                        return $user;
                    })
                    ->modalWidth(MaxWidth::Large),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
