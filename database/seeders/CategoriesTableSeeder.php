<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Fontanería',
                'slug' => 'fontaneria',
                'description' => 'Servicios de fontanería para tu hogar.',
                'image' => '/storage/categories/fontaneria.png', 
            ],
            [
                'name' => 'Electricidad',
                'slug' => 'electricidad',
                'description' => 'Reparaciones e instalaciones eléctricas.',
                'image' => '/storage/categories/electricidad.jpg',
            ],
            [
                'name' => 'Limpieza del Hogar',
                'slug' => 'limpieza-hogar',
                'description' => 'Limpieza general y profunda del hogar.',
                'image' => '/storage/categories/limpieza-hogar.jpg',
            ],
            [
                'name' => 'Jardinería',
                'slug' => 'jardineria',
                'description' => 'Mantenimiento y diseño de jardines.',
                'image' => '/storage/categories/jardineria.jpg',
            ],
            [
                'name' => 'Pintura',
                'slug' => 'pintura',
                'description' => 'Servicios de pintura para interiores y exteriores.',
                'image' => '/storage/categories/pintura.jpg',
            ],
            [
                'name' => 'Carpintería',
                'slug' => 'carpinteria',
                'description' => 'Reparación y fabricación de muebles.',
                'image' => '/storage/categories/carpinteria.jpg',
            ],
            [
                'name' => 'Herrería',
                'slug' => 'herreria',
                'description' => 'Trabajos en metal para el hogar.',
                'image' => '/storage/categories/herreria.jpg',
            ],
            [
                'name' => 'Electrodomésticos',
                'slug' => 'electrodomesticos',
                'description' => 'Reparación de electrodomésticos.',
                'image' => '/storage/categories/electrodomesticos.jpg',
            ],
            [
                'name' => 'Mudanzas',
                'slug' => 'mudanzas',
                'description' => 'Servicios de mudanza local.',
                'image' =>'/storage/categories/mudanzas.jpg',
            ],
            [
                'name' => 'Climatización',
                'slug' => 'climatizacion',
                'description' => 'Instalación y mantenimiento de aires acondicionados.',
                'image' => '/storage/categories/climatizacion.jpg',
            ],
           
            [
                'name' => 'Servicios de Cerrajería',
                'slug' => 'cerrajeria',
                'description' => 'Apertura de puertas, cambio de cerraduras, duplicado de llaves.',
                'image' => '/storage/categories/cerrajeria.jpg',
            ],
            
          
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}