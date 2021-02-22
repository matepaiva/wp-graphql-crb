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

  public function __construct(CrbField $field, String $recursiveType)
  {
    $this->field = $field;
    $this->recursiveType = $recursiveType;
  }

  static public function create(CrbField $field, String $recursiveType = '')
  {
    return new Self($field, $recursiveType);
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
        $type = $this->getTextType();
        break;

      case 'radio':
      case 'select':
        $type = 'Crb_Select';
        break;
      
      case 'multiselect':
        $type = ['list_of' => 'Crb_Select'];
        break;
        
      case 'set':
        $type = ['list_of' => 'Crb_Set'];
        break;
        
      case 'media_gallery':
        $type = ['list_of' => 'mediaItem'];
        break;
        
      case 'association':
        $type = ['list_of' => $this->getTypeFromAssociation()];
        break;
        
      case 'complex':
        $type = ['list_of' => $this->getTypeFromComplex()];
        break;
        
      case 'checkbox':
        $type = 'Boolean';
        break;

      default:
      $type = 'String';
    }

    return apply_filters('wp_graphql_crb_type', $type, $this->getCrbType());
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

  public function getFields() {
    return $this->field->get_fields();
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

  public function getResolverName()
  {
    if ($this->getCrbType() === 'association') {
      return 'getAssociation';
    }

    if ($this->getCrbType() === 'complex') {
      return 'getComplex';
    }

    $type = $this->getType();

    if (is_array($type)) {
      $type = json_encode($type);
    }

    switch ($type) {
      case 'String':
      case 'Boolean':
        $resolver_name = 'getScalar';
        break;

      case 'Float':
        $resolver_name = 'getFloat';
        break;
        
      case '{"list_of":"mediaItem"}':
        $resolver_name = 'getMediaGallery';
        break;
        
      case '{"list_of":"Crb_Set"}':
        $resolver_name = 'getSet';
        break;
        
      case 'Crb_Select':
        $resolver_name = 'getSelect';
        break;
        
      case '{"list_of":"Crb_Select"}':
        $resolver_name = 'getMultiSelect';
        break;
        
      default:
      $resolver_name = 'getNull';
    }

    return apply_filters('wp_graphql_crb_resolver_name', $resolver_name, $type);
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

  private function getTypeFromComplex()
  {
    $complex_prefix = $this->recursiveType ? $this->recursiveType : 'Complex';
    $type = $complex_prefix . '_' . $this->getBaseName();

    $fields = array_reduce($this->field->get_fields(), function($fields, $f) use ($type) {
      $field = Field::create($f, $type);

      $fields[$field->getBaseName()] = [
        'type' => $field->getType(),
        'description' => $field->getDescription()
      ];

      return $fields;
    }, []);

    register_graphql_object_type($type, [
      'description' => $this->getDescription(),
      'fields' => $fields,
    ]);

    return $type;
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
