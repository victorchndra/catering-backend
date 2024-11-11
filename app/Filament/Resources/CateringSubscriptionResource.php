<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CateringSubscriptionResource\Pages;
use App\Filament\Resources\CateringSubscriptionResource\RelationManagers;
use App\Models\CateringSubscription;
use App\Models\CateringTier;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CateringSubscriptionResource extends Resource
{
    protected static ?string $model = CateringSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-thumb-up';

    protected static ?string $navigationGroup = 'Customers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Wizard::make([
                    // step 1
                    Step::make('Product and Price')
                    ->icon('heroicon-m-shopping-bag')
                    ->completedIcon('heroicon-m-hand-thumb-up')
                    ->description('Which catering you choose?')
                    ->schema([
                        Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('catering_package_id')
                            ->relationship('cateringPackage', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('catering_tier_id', null); // Reset the tier selection when package changes
                                $set('price', null);
                                $set('total_amount', null);
                                $set('total_tax_amount', null);
                                $set('quantity', null);
                                $set('duration', null);
                                $set('ended_at', null);
                            }),

                            Forms\Components\Select::make('catering_tier_id')
                            ->label('Catering Tier')
                            ->options(function (callable $get) {
                                $cateringPackageId = $get('catering_package_id');
                                if ($cateringPackageId) {
                                    return CateringTier::where('catering_package_id', $cateringPackageId)
                                        ->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required()
                            ->live() // setiap ada perubahan pada state akan di-cek
                            ->afterStateUpdated(function ($state, callable $set) {
                                $cateringTier = CateringTier::find($state);
                                $price = $cateringTier ? $cateringTier->price : 0;
                                $quantity = $cateringTier ? $cateringTier->quantity : 0;
                                $duration = $cateringTier ? $cateringTier->duration : 0;

                                $set('price', $price);
                                $set('quantity', $quantity);
                                $set('duration', $duration);

                                $tax = 0.11;
                                $totalTaxAmount = $tax * $price;

                                $totalAmount = $price + $totalTaxAmount;
                                $set('total_amount', number_format($totalAmount, 0, '', ''));
                                $set('total_tax_amount', number_format($totalTaxAmount, 0, '', ''));
                            }),

                            TextInput::make('price')
                            ->required()
                            ->readOnly()
                            ->numeric()
                            ->prefix('IDR'),

                            TextInput::make('total_amount')
                            ->required()
                            ->readOnly()
                            ->numeric()
                            ->prefix('IDR'),

                            TextInput::make('total_tax_amount')
                            ->readOnly()
                            ->required()
                            ->numeric()
                            ->helperText('Pajak 11%')
                            ->prefix('IDR'),

                            TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->prefix('People'),

                            TextInput::make('duration')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->prefix('Days'),

                            DatePicker::make('started_at')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $duration = $get('duration');
                                if ($state && $duration) {
                                    $endedAt = \Carbon\Carbon::parse($state)->addDays($duration);
                                    $set('ended_at', $endedAt->format('Y-m-d'));
                                } else {
                                    $set('ended_at', null); // Reset if there's no valid duration
                                }
                            }),

                            DatePicker::make('ended_at')
                            ->required(),
                        ]),
                    ]),

                    Step::make('Customer Information')
                    ->completedIcon('heroicon-m-hand-thumb-up')
                    ->description('For our marketing')
                    ->schema([
                        Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                            TextInput::make('phone')
                            ->required()
                            ->maxLength(255),

                            TextInput::make('email')
                            ->required()
                            ->maxLength(255),
                        ]),
                    ]),

                    Step::make('Delivery Information')
                    ->completedIcon('heroicon-m-hand-thumb-up')
                    ->description('Put your correct address')
                    ->schema([
                        Grid::make(2)
                        ->schema([
                            TextInput::make('city')
                            ->required()
                            ->maxLength(255),

                            TextInput::make('post_code')
                            ->required()
                            ->maxLength(255),

                            TextInput::make('delivery_time')
                            ->required()
                            ->maxLength(255),

                            Textarea::make('address')
                            ->required()
                            ->maxLength(255),

                            Textarea::make('notes')
                            ->required()
                            ->maxLength(255),
                        ]),
                    ]),

                    Step::make('Payment Information')
                    ->completedIcon('heroicon-m-hand-thumb-up')
                    ->description('Review your payment')
                    ->schema([
                        Grid::make(3)
                        ->schema([
                            TextInput::make('booking_trx_id')
                            ->required()
                            ->maxLength(255),

                            ToggleButtons::make('is_paid')
                            ->label('Apakah sudah membayar?')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-pencil',
                                false => 'heroicon-o-clock',
                            ])
                            ->required(),

                            FileUpload::make('proof')
                            ->image()
                            ->required(),
                        ]),
                    ]),
                ])
                ->columnSpan('full') // use full width for the wizard
                ->columns(1) // make sure the form has a single column layout
                ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('cateringPackage.thumbnail'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('booking_trx_id')->searchable(),
                IconColumn::make('is_paid')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Terverifikasi'),
            ])
            ->filters([
                SelectFilter::make('catering_package_id')
                ->label('Catering Package')
                ->relationship('cateringPackage', 'name'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCateringSubscriptions::route('/'),
            'create' => Pages\CreateCateringSubscription::route('/create'),
            'edit' => Pages\EditCateringSubscription::route('/{record}/edit'),
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
