<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('index', function (BreadcrumbTrail $trail) {
    $trail->push(__('Help Center'), route('index'));
});
