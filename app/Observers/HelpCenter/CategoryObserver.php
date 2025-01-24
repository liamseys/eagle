<?php

namespace App\Observers\HelpCenter;

use App\Models\HelpCenter\Category;
use Illuminate\Support\Str;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     */
    public function creating(Category $category): void
    {
        if (isset($category->name)) {
            // Generate the base slug from the name
            $baseSlug = Str::slug($category->name);

            // Prepend an unique ID to ensure the slug is unique
            $uniqueSlug = uniqid().'-'.$baseSlug;

            // Assign the generated unique slug to the category
            $category->slug = $uniqueSlug;
        }

        $category->sort = Category::max('sort') + 1;
    }

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
    }
}
