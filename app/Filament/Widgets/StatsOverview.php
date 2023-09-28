<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2; // chart display arrangement
    protected static ?string $pollingInterval = '300s'; // null for not refresh
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', Customer::count())
                ->description('Increase in customer')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7,1,5,6,10]),

            Stat::make('Total Product', Product::count())
                ->description('total products')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7,1,5,6,10]),

            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->description('Pending Orders')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([7,1,5,6,10]),
        ];
    }
}
