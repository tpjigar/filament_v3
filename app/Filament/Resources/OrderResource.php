<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Shop';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '=', 'processing')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', '=', 'processing')->count() > 10
            ? 'warning'
            : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->required()->disabled()->dehydrated()->default('OR-'.rand(10000, 999999)),
                        Forms\Components\Select::make('customer_id')
                            ->required()
                            ->searchable()
                            ->relationship('customer', 'name'),

                        Forms\Components\TextInput::make('shipping_price')
                            ->label('Shipping Cost')->required()->dehydrated()->numeric(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => OrderStatusEnum::PENDING->value,
                                'processing' => OrderStatusEnum::PROCESSING->value,
                                'completed' => OrderStatusEnum::COMPLETED->value,
                                'declined' => OrderStatusEnum::DECLINED->value,
                            ])->required(),
                        Forms\Components\MarkdownEditor::make('notes')->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Wizard\Step::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('Items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('unit_price', Product::find($state)?->price ?? 0)),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()->default(1)->required()->live()->dehydrated(),
                                Forms\Components\TextInput::make('unit_price')
                                    ->required()->numeric()->disabled()->dehydrated(),
                                Forms\Components\Placeholder::make('total_price')
                                    ->label('Total Price')->content(function ($get){
                                        return $get('quantity') * $get('unit_price');
                                    })
                            ])->columns(4),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Order Date')->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
