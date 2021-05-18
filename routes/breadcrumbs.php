<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push(trans('common.text.heading'), route('home'));
});

//Category
Breadcrumbs::for('category', function ($trail, $category = null) {

    $trail->parent('home');

    $ancestors = $category->load(['ancestors' => function ($q) {
        $q->withTranslate();
        $q->without('translates');
        $q->select('categories.id', 'categories.parent_id', 'categories._lft', 'categories._rgt', 'categories.slug', 'ct.name');
    }])->ancestors;

    foreach ($ancestors as $ancestor) {

        $trail->push($ancestor->name, $ancestor->href);
    }

    $trail->push($category->name, $category->href);

});

Breadcrumbs::for('product', function ($trail, $product) {

    if ($product->category) {
        $trail->parent('category', $product->category);
    } else {
        $trail->parent('home');
    }

    $trail->push($product->translate->name, route('product', [
            'slug' => $product->slug,
            'id' => $product->id])
    );
});

// Home > Register
Breadcrumbs::for('account.register', function ($trail) {
    $trail->parent('home');
    $trail->push(trans('account.text.heading.register'), route('account.register'));
});


Breadcrumbs::for('account', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('account.text.heading.account'), route('account'));
});

Breadcrumbs::for('comparelist', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.comparelist.heading'), route('comparelist'));
});

Breadcrumbs::for('comparelist.category', function ($trail, $category) {

    $trail->parent('comparelist');

    $trail->push($category->id == 0 ? $category->name : $category->translate->name, route('comparelist.category', ['category_id' => $category->id]));
});

Breadcrumbs::for('search', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.search.heading.single'), route('search'));
});

Breadcrumbs::for('checkout', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.checkout.heading'), route('checkout'));
});

Breadcrumbs::for('articles', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.blog.heading'), route('articles'));

});

Breadcrumbs::for('article', function ($trail, $article) {

    $trail->parent('articles');

    $trail->push($article->translate->name, route('article', [
            'slug' => $article->slug,
            'id' => $article->id])
    );
});

Breadcrumbs::for('information', function ($trail, $information) {

    $trail->parent('home');

    $trail->push($information->translate->name, route('information', [
            'slug' => $information->slug,
            'id' => $information->id])
    );
});

Breadcrumbs::for('testimonials', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.testimonials.heading'), route('testimonials'));

});

Breadcrumbs::for('contacts', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.contacts.heading'), route('contacts'));

});

Breadcrumbs::for('checkout.success', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('pages.checkout.success.heading'), route('checkout.success'));

});

Breadcrumbs::for('password.reset', function ($trail) {

    $trail->parent('home');

    $trail->push(trans('account.text.heading.reset'), '');

});



/*

// Home > Blog
Breadcrumbs::for('blog', function ($trail) {
    $trail->parent('home');
    $trail->push('Blog', route('blog'));
});

// Home > Blog > [Category]


// Home > Blog > [Category] > [Post]
Breadcrumbs::for('post', function ($trail, $post) {
    $trail->parent('category', $post->category);
    $trail->push($post->title, route('post', $post->id));
});
*/