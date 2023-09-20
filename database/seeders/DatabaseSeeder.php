<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Status;
use App\Models\Valuta;
use Illuminate\Database\Seeder;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //----Category---
        // Category::create([
        //     'name'=>'Логистика'
        // ]);
        // Category::create([
        //     'name'=>'Подготовка'
        // ]);
        // Category::create([
        //     'name'=>'Другое'
        // ]);
        //-----Symbol---
        // Valuta::create([
        //     'code'=>'RUB',
        //     'name'=>'Российский рубль',
        //     'symbol'=>'₽'
        // ]);
      
        // Valuta::create([
        //     'code'=>'KGS',
        //     'name'=>'Киргизский сом',
        //     'symbol'=>'сом'
        // ]);
        // Valuta::create([
        //     'code'=>'USD',
        //     'name'=>'Доллар США',
        //     'symbol'=>'$'
        // ]); 
        //  Valuta::create([
        //     'code'=>'CNY',
        //     'name'=>'Китайский юань',
        //     'symbol'=>'¥'
        // ]);
        // Status::create(['name'=>'Активно']);
        // Status::create(['name'=>'Завершен']);
        // Status::create(['name'=>'Отменено']);
        // Status::create(['name'=>'Проведено']);
        // Status::create(['name'=>'Выставлено']);
       // Status::create(['name'=>'Ликвидация']);
        // Status::create(['name'=>'Отгружено']);
    }
    
}
