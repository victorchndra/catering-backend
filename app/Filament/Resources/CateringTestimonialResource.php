<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CateringTestimonialResource\Pages;
use App\Filament\Resources\CateringTestimonialResource\RelationManagers;
use App\Models\CateringTestimonial;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CateringTestimonialResource extends Resource
{
    protected static ?string $model = CateringTestimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Customers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name')
                ->required()
                ->maxLength(255),

                FileUpload::make('photo')
                ->image()
                ->required(),

                Select::make('catering_package_id')
                ->relationship('cateringPackage', 'name') // relational method from model
                ->searchable()
                ->preload()
                ->required(),

                Textarea::make('message')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('cateringPackage.thumbnail'),
                ImageColumn::make('photo')->circular(),
                TextColumn::make('name')->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCateringTestimonials::route('/'),
            'create' => Pages\CreateCateringTestimonial::route('/create'),
            'edit' => Pages\EditCateringTestimonial::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
