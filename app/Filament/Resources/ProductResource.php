<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypesEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

//    protected static ?string $navigationLabel = 'Hello';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(Product::class, 'name', ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set){
                                if($operation != 'create'){
                                    return;
                                }
                                $set('slug', \Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        Forms\Components\MarkdownEditor::make('description')
                        ->columnSpan('full')
                    ])->columns('2'),
                    Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU (Stock Keeping Unit)')
                            ->required()
                            ->unique(Product::class, 'sku', ignoreRecord: true),

                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('quantity')
//                            ->rule(['required', 'integer','min:0']),
                            ->numeric()->minValue(0)->maxValue(100)->required(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'downloadable' => ProductTypesEnum::DOWNLOADABLE->value,
                                'deliverable' => ProductTypesEnum::DELIVERABLE->value,
                            ])->required(),
                    ])->columns('2')
                ]),
                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Visibility')
                                    ->helperText('Enable or Disable product visibility')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Enable or disable Featured'),

                                Forms\Components\DatePicker::make('published_at')
                                    ->columnSpan('full')
                                    ->label('Availability')
                                    ->default(now()),
                            ])->columns('2'),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make('Image')
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->directory('products')
                                            ->image()
                                            // ->preserveFilenames() // preserve same file name
                                            ->imageEditor()
                                            ->required(),
                                    ])->collapsible(),
                            ]),

                        Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\Section::make('Associate')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->required(),
                            ]),
                        ])
                    ]),



            ]);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->searchable()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')->sortable()->toggleable()->boolean()->label('Visibility'),
                Tables\Columns\TextColumn::make('price')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('quantity')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->boolean()
                    ->trueLabel('Only Visible Products')
                    ->falseLabel('Only Hidden Products')
                    ->native(false)
                    ->label('Visibility'),

                Tables\Filters\SelectFilter::make('brand')->relationship('brand', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
