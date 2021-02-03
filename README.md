# WpGraphQLCrb

A Wordpress wrapper to expose Carbon Fields to WpGraphQL queries.

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

> Note: This is just the first version. There is a lot of work to be done. This packages exposes all the fields of the container, if the container type is `post_meta`, `term_meta`, or `user`.

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
