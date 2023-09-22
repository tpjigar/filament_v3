<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name'; // only one column can searchable globally

    protected static ?string $activeNavigationIcon = 'heroicon-o-check-badge'; // icon change active navigation menu

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
                            ->unique(Brand::class, 'name', ignoreRecord: true)
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
                            ->unique(Brand::class, 'slug', ignoreRecord: true),

                        Forms\Components\TextInput::make('url')
                            ->label('Website')->required()->columnSpan('full'),

                        Forms\Components\MarkdownEditor::make('description')
                            ->columnSpan('full'),
                    ])->columns(2)

                ]),

                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Status & Color')
                        ->schema([
                            Forms\Components\Toggle::make('is_visible')
                                ->label('Visibility')
                                ->helperText('Enable or Disable brand visibility')
                                ->default(true),

                            Forms\Components\ColorPicker::make('primary_hax')
                                ->label('Primary Color')->required(),
                        ]),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->searchable()->sortable()->label('Website URL'),

                Tables\Columns\ColorColumn::make('primary_hax')
                    ->label('Primary Color'),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visibility')->searchable()->sortable()->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->date()->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ],
//                position: Tables\Enums\ActionsPosition::BeforeColumns // Actions button positions
            )
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
