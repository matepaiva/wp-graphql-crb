<?php

namespace WpGraphQLCrb;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;

class MetaResolver
{
  public static function getScalar($value, Field $field, Container $container, $args = null, AppContext $context = null, ResolveInfo $info = null)
  {
    switch ($field->getCrbType()) {
      case 'rich_text':
        return apply_filters('the_content', $value);
      
      default:
        return $value;
    }

  }

  public static function getFloat($value, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    return floatval($value);
  }

  public static function getComplex($value, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    $fields = $field->getFields();
    return array_map(function ($val) use ($fields, $container, $args, $context, $info) {
      $complex_item = [];
      foreach ($fields as $f) {
        $field = Field::create($f);
        $name = $field->getBaseName();
        if(!array_key_exists($name, $val)) continue;
        $inner_value = $val[$name];
        $resolver_name = $field->getResolverName();
        $complex_item[$name] = call_user_func(
          [MetaResolver::class, $resolver_name],
          $inner_value,
          $field,
          $container,
          $args,
          $context,
          $info
        );
      }
      return $complex_item;
    }, $value);
  }

  public static function getMediaItem($value, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    return DataSource::resolve_post_object((int)$value, $context);
  }

  public static function getMediaGallery($gallery_ids, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    return array_map(function ($id) use ($context) {
      return DataSource::resolve_post_object($id, $context);
    }, $gallery_ids);
  }

  public static function getSelect($value, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    $options = $field->getOptions() ?? [];

    return [
      'id' => $value,
      'value' => $value ?? null,
      'label' => $options[$value] ?? null,
    ];
  }

  public static function getMultiSelect($value, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    $values = $value ?? [];
    $options = $field->getOptions() ?? [];
    return array_map(function ($value) use ($options) {
      return [
        'id' => $value,
        'value' => $value,
        'label' => $options[$value] ?? $value,
      ];
    }, $values);
  }

  public static function getSet($value, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    $values = $value ?? [];
    $options = $field->getOptions() ?? [];
    $option_keys = array_keys($options);

    return array_map(function ($key) use ($values, $options) {
      return [
        'id' => $key,
        'value' => in_array($key, $values),
        'label' => $options[$key] ?? $key,
      ];
    }, $option_keys);
  }

  public static function getAssociation($assocations, Field $field, Container $container, $args, AppContext $context, ResolveInfo $info)
  {
    return array_map(function ($assocation) use ($context) {
      ['type' => $type, 'subtype' => $subtype, 'id' => $id] = $assocation;

      switch ($type) {
        case 'post':
          $post = DataSource::resolve_post_object($id, $context);
          return $post;

        case 'term':
          return DataSource::resolve_term_object($id, $context);

        case 'user':
          $user = DataSource::resolve_user($id, $context);
          return $user;

        case 'comment':
          $comment = DataSource::resolve_comment($id, $context);
          return $comment;

        default:
          return Self::getNull();
      }
    }, $assocations);
  }

  public static function getNull()
  {
    return function () {
      return null;
    };
  }
}
