<?php

namespace Database\Factories;

use App\Enums\MenuItemType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'page_id' => null,
            'label' => fake()->words(2, true),
            'item_type' => MenuItemType::Group,
            'position' => 0,
        ];
    }

    public function group(): static
    {
        return $this->state(fn () => [
            'item_type' => MenuItemType::Group,
            'page_id' => null,
        ]);
    }

    public function page(): static
    {
        return $this->state(fn () => [
            'item_type' => MenuItemType::Page,
            'page_id' => Page::factory(),
        ]);
    }
}
