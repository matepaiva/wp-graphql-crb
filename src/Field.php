<?php

namespace WpGraphQLCrb;

use Carbon_Fields\Field\Field as CrbField;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;

class Field
{
  /**
   * @var CrbField
   */
  private $field;

  private static $blacklisted_fields = ['html', 'separator'];

  public function __construct(CrbField $field)
  {
    $this->field = $field;
  }

  static public function create(CrbField $field)
  {
    return new Self($field);
  }

  public function getDescription()
  {
    return $this->field->get_help_text() ?? '';
  }

  public function isCompatible()
  {
    return !\in_array($this->getCrbType(), Field::$blacklisted_fields);
  }

  public function getType()
  {
    switch ($this->getCrbType()) {
      case 'text':
        return $this->getTextType();

      case 'radio':
      case 'select':
        return 'Crb_Select';

      case 'multiselect':
        return ['list_of' => 'Crb_Select'];

      case 'media_gallery':
        return ['list_of' => 'mediaItem'];

      case 'association':
        return ['list_of' => $this->getTypeFromAssociation()];

      case 'checkbox':
        return 'Boolean';

      default:
        return 'String';
    }
  }

  public function getBaseName()
  {
    return $this->field->get_base_name();
  }

  public function getOptions()
  {
    return $this->field->get_options();
  }

  public function getResolver()
  {
    return [MetaResolver::class, $this->getResolverName()];
  }

  private function getTextType()
  {
    $attributes = $this->field->get_attributes();

    $html_type = $attributes['type'] ?? 'text';

    switch ($html_type) {
      case 'number':
        return 'Float';

      default:
        return 'String';
    }
  }

  private function getCrbType()
  {
    return $this->field->get_type();
  }

  private function getResolverName()
  {
    if ($this->getCrbType() === 'association') {
      return 'getAssociation';
    }

    $type = $this->getType();

    if (is_array($type)) {
      $type = json_encode($type);
    }

    switch ($type) {
      case 'String':
      case 'Boolean':
        return 'getScalar';
      case '{"list_of":"mediaItem"}':
        return 'getMediaGallery';
      case 'Crb_Select':
        return 'getSelect';
      case '{"list_of":"Crb_Select"}':
        return 'getMultiSelect';
      default:
        return 'getNull';
    }
  }

  private function getTypeFromAssociation()
  {
    $types = $this->field->get_types();

    if (count($types) === 1) {
      [$type] = $types;

      return $this->getGraphQLTypeFromAssociationType($type);
    }

    $type_names = array_map([$this, 'getGraphQLTypeFromAssociationType'], $types);
    $union_name = 'Union_' . $this->field->get_base_name();

    register_graphql_union_type($union_name, [
      'typeNames' => $type_names,
      'resolveType' => function ($object) {
        if ($object instanceof Post) {
          $graphql_single_name = \get_post_type_object($object->post_type)->graphql_single_name;

          if ($graphql_single_name === 'post') {
            return 'Post';
          }

          return $graphql_single_name;
        }

        if ($object instanceof Term) {
          $taxonomy_name = \get_taxonomy($object->taxonomyName)->graphql_single_name;
          if ($taxonomy_name === 'category') {
            return 'Category';
          }

          if ($taxonomy_name === 'tag') {
            return 'Tag';
          }

          return $taxonomy_name;
        }

        if ($object instanceof Comment) {
          return 'Comment';
        }

        if ($object instanceof User) {
          return 'User';
        }

        return '';
      }
    ]);

    return $union_name;
  }

  private function getGraphQLTypeFromAssociationType($type)
  {
    switch ($type['type']) {
      case 'post':
        return \get_post_type_object($type['post_type'])->graphql_single_name;

      case 'term':
        return \get_taxonomy($type['taxonomy'])->graphql_single_name;

      case 'user':
        return 'user';

      case 'comment':
        return 'comment';

      default:
        return $type['type'];
    }
  }
}
