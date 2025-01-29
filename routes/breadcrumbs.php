<?php

use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Category;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('index', function (BreadcrumbTrail $trail) {
    $trail->push(__('Help Center'), route('index'));
});

Breadcrumbs::for('category', function (BreadcrumbTrail $trail, Category $category) {
    $trail->parent('index');
    $trail->push($category->name, route('categories.show', $category));
});

Breadcrumbs::for('article', function (BreadcrumbTrail $trail, Article $article) {
    $trail->parent('category', $article->category);
    $trail->push($article->title, route('articles.show', $article));
});
