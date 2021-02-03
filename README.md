# WpGraphQLCrb

A Wordpress wrapper to expose Carbon Fields to WpGraphQL queries.

## Important

This is just the first version. There is a lot of work to be done. This packages exposes all the fields of the container, if the container type is `post_meta`, `term_meta`, or `user`. It does not work with `theme_options`.

## Usage

1. First you have to install Carbon Fields and WpGraphQL.
1. Then install this package via packagist: `composer require matepaiva/wp-graphql-crb`
1. Wrap every Carbon Field container that you want to expose via GraphQL with the static method `WpGraphQLCrb\Container::register`. For example:

```php
  <?php

  use WpGraphQLCrb\Container as WpGraphQLCrbContainer;
  use Carbon_Fields\Container\Container;
  use Carbon_Fields\Field\Field;

  WpGraphQLCrbContainer::register(
    Container::make('term_meta', __('Custom Data', 'app'))
      ->where('term_taxonomy', '=', 'category')
      ->add_fields([
        Field::make('image', 'crb_img')
          ->set_value_type('url')
      ])
  );
```

5. Now the query below will work:

```graphql
{
  categories {
    edges {
      node {
        id
        crb_img
      }
    }
  }
}
```
