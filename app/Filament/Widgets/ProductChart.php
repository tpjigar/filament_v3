<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ProductChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'Chart';

    public $productPerMonth = [];

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();
        return [
            'datasets' =>[
                [
                    'label' => 'Blog Post Created',
                    'data' => $data['productsPerMonth'],
//                    'data' => [1, 2, 3, 4, 5, 60, 7]
                ]
            ],
            'labels' => $data['months'],
//            'labels' => ['7','6','5','4','3','2','1'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProductsPerMonth(): array
    {
        $now = Carbon::now();

        $months = collect(range(1, 12))->map(function ($month) use ($now){
            $count = Product::whereMonth('created_at', Carbon::parse($now->month($month)->format('Y-m')))
                ->count();
            $this->productPerMonth[] = $count;
            return $now->month($month)->format('M');
        })->toArray();

        return [
            'productsPerMonth' => $this->productPerMonth,
            'months' => $months
        ];
    }
}
