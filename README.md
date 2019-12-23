# WpGraphQLCrb

A Wordpress wrapper to expose Carbon Fields to WpGraphQL queries.

## Usage

1. First you have to install Carbon Fields and WpGraphQL.
2. As this package is not published at Packagist yet, you must add the repository to your composer:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:matepaiva/wp-graphql-crb.git"
    }
  ]
}
```

3. Add the package to the required packages in your composer:

```json
{
  "require": {
    "matepaiva/wp-graphql-crb": "dev-master"
  }
}
```

4. Wrap every Carbon Field container that you want to expose via GraphQL with the static method `WpGraphQLCrb\Container::register`. For example:

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

> Note: This is just the first version. There is a lot of work to be done. This packages exposes all the fields of the container, if the container type is `post_meta`, `term_meta`, or `user`.

# Important: about `Association_Field`!

To make it work, I had to modify the original Carbon Fields package. Currently the `Carbon_Fields\Field\Association_Field` don't expose the `types` of the association, so it is not possible to make the GraphQL Union. To make this happen, I forked the original package and added a getter method in the `Association_Field` class:

```php
/**
	 * Get the types.
	 *
	 * @return  array $types New types
	 */
	public function get_types() {
		return $this->types;
	}
```

I think this is the only way to get the `types` of the `Association_Field` and I hope that this change will be included in the next version, as it has very low impact to the package and a very high benefit to this integration.

If you need the `Association_Field` exposed in the GraphQL queries right now, you can use my temporary repository. Just add it to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:matepaiva/carbon-fields.git"
    }
  ],
  "require": {
    "htmlburger/carbon-fields": "dev-exopse_registered_fields"
  }
}
```

## Example

Query:

```
{
  companies {
    nodes {
      id
      title
      nif
      specializations {
        id
        label
      }
      crb_media_gallery {
        id
        altText
        title
        mediaItemUrl
      }
      crb_association {
        ... on Node {
          __typename
          id
        }
        ... on Company {
          title
        }
        ... on Comment {
          content
          author {
            ... on CommentAuthor {
              email
            }
          }
        }
        ... on Post {
          title
        }
        ... on Category {
          description
        }
      }
    }
  }
  users {
    nodes {
      name
      crb_text
    }
  }
}
```

Result:

```json
{
  "data": {
    "companies": {
      "nodes": [
        {
          "id": "Y29tcGFueTo0OQ==",
          "title": "asdasdasdasdasdsad",
          "nif": "",
          "specializations": [],
          "crb_media_gallery": [],
          "crb_association": []
        },
        {
          "id": "Y29tcGFueToyOA==",
          "title": "ablidu",
          "nif": "123123123",
          "specializations": [
            {
              "id": "1",
              "label": "Arquitectura Bioclimática"
            },
            {
              "id": "4",
              "label": "Compilação Técnica"
            }
          ],
          "crb_media_gallery": [
            {
              "id": "YXR0YWNobWVudDo0OA==",
              "altText": "",
              "title": "Screenshot from 2019-12-19 10-41-19",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-19-10-41-19.png"
            },
            {
              "id": "YXR0YWNobWVudDo0Nw==",
              "altText": "",
              "title": "Screenshot from 2019-12-18 18-54-30",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-18-18-54-30.png"
            },
            {
              "id": "YXR0YWNobWVudDo0Ng==",
              "altText": "",
              "title": "Screenshot from 2019-12-16 20-39-46",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-16-20-39-46.png"
            },
            {
              "id": "YXR0YWNobWVudDo0NQ==",
              "altText": "",
              "title": "Screenshot from 2019-12-16 19-44-57",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-16-19-44-57.png"
            },
            {
              "id": "YXR0YWNobWVudDo0NA==",
              "altText": "",
              "title": "Screenshot from 2019-12-15 14-18-00",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-15-14-18-00.png"
            },
            {
              "id": "YXR0YWNobWVudDo0Mw==",
              "altText": "",
              "title": "Screenshot from 2019-12-11 20-32-42",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-11-20-32-42.png"
            },
            {
              "id": "YXR0YWNobWVudDo0Mg==",
              "altText": "",
              "title": "Screenshot from 2019-12-10 17-07-49",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-10-17-07-49.png"
            },
            {
              "id": "YXR0YWNobWVudDo0MQ==",
              "altText": "",
              "title": "Screenshot from 2019-12-09 11-51-48",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-09-11-51-48.png"
            },
            {
              "id": "YXR0YWNobWVudDo0MA==",
              "altText": "",
              "title": "Screenshot from 2019-12-07 16-57-20",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-07-16-57-20.png"
            },
            {
              "id": "YXR0YWNobWVudDozOQ==",
              "altText": "",
              "title": "Screenshot from 2019-12-06 17-38-04",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-06-17-38-04.png"
            },
            {
              "id": "YXR0YWNobWVudDozOA==",
              "altText": "",
              "title": "Screenshot from 2019-12-06 17-35-22",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-06-17-35-22.png"
            },
            {
              "id": "YXR0YWNobWVudDozNw==",
              "altText": "",
              "title": "Screenshot from 2019-12-04 16-05-50",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-12-04-16-05-50.png"
            },
            {
              "id": "YXR0YWNobWVudDozNg==",
              "altText": "",
              "title": "Screenshot from 2019-11-28 13-12-30",
              "mediaItemUrl": "http://localhost:3003/app/uploads/2019/12/Screenshot-from-2019-11-28-13-12-30.png"
            }
          ],
          "crb_association": [
            {
              "__typename": "User",
              "id": "dXNlcjox"
            },
            {
              "__typename": "Company",
              "id": "Y29tcGFueToyOA==",
              "title": "ablidu"
            },
            {
              "__typename": "Comment",
              "id": "Y29tbWVudDox",
              "content": "<p>Olá, isto é um comentário.<br />\nPara iniciar a moderar, editar e eliminar comentários, por favor visite o ecrã de comentários no painel.<br />\nOs avatares dos comentadores são do <a href=\"https://gravatar.com\">Gravatar</a>.</p>\n",
              "author": {
                "email": "wapuu@wordpress.example"
              }
            },
            {
              "__typename": "Post",
              "id": "cG9zdDox",
              "title": "Olá mundo"
            }
          ]
        }
      ]
    },
    "users": {
      "nodes": [
        {
          "name": "mpaiva",
          "crb_text": "uhuuuu"
        }
      ]
    }
  }
}
```