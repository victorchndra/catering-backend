<?php

namespace App\Filament\Resources\CateringPackageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->required()
                ->maxLength(255),

                FileUpload::make('photo')
                ->image()
                ->required(),

                TextInput::make('tagline')
                ->required()
                ->maxLength(255),

                TextInput::make('price')
                ->required()
                ->numeric()
                ->prefix('IDR'),

                TextInput::make('quantity')
                ->required()
                ->numeric()
                ->prefix('People'),

                TextInput::make('duration')
                ->required()
                ->numeric()
                ->prefix('Days'),

                Repeater::make('benefits')
                ->relationship('benefits')
                ->schema([
                    TextInput::make('name')
                    ->required(),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('photo'),
                TextColumn::make('name'),
                TextColumn::make('quantity'),
                TextColumn::make('duration'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
