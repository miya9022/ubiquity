<?php

/**
 * Assets managment
 */

namespace Ubiquity\assets;

use Ubiquity\themes\ThemesManager;
use Ubiquity\utils\base\UArray;

/**
 * Assets manager for css and js inclusions in templates.
 * Ubiquity\assets$AssetsManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 * @since Ubiquity 2.1.0
 *
 */
class AssetsManager
{
  const ASSETS_FOLDER = '/public/assets/';
  private static $siteURL;
  
  private static function gString($template, $variable, $attributes = [])
  {
    $implode = UArray::implodeAsso($attributes, ' ');
    return sprintf($template, $variable, $implode);
  }
  
  private static function script($src, $attributes = [])
  {
    return self::gString('<script type="text/javascript" src="%s" %s></script>', $src, $attributes);
  }
  
  private static function stylesheet($link, $attributes = [])
  {
    return self::gString('<link href="%s" type="text/css" rel="stylesheet" %s>', $link, $attributes);
  }
  
  private static function img_gen($src, $attributes = [])
  {
    return self::gStr('<img src="%s" %s/>', $src, $attributes);
  }
  
  private static function src_gen($src)
  {
    return self::gStr('%s', $src, []);
  }
  
  private static function gStr($template, $variable, $attributes = [])
  {
    $implode = UArray::implodeAsso($attributes, ' ', '=', '', '"');
    return sprintf($template, $variable, $implode);
  }
  
  private static function file_gen($name, $label, $value = "", $attributes1 = [], $attributes2 = [], $extendType = "img")
  {
    $template = '<div class="input-group" data-type="' . $extendType . '">';
    if (is_null($attributes1) || empty($attributes1)) {
      $attributes1 = ['class' => 'form-control mgt-file', 'placeholder' => $label];
    } else {
      if (!array_key_exists('class', $attributes1)) {
        $attributes1['class'] .= 'mgt-file';
      } else {
        $attributes1['class'] = 'form-control mgt-file';
      }
      $attributes1['placeholder'] = $label;
    }
    if (!is_null($value) && !empty($value)) {
      $attributes1['value'] = $value;
    }
    $template .= self::gStr('<input type="text" name="%s" %s>', $name, $attributes1);
    $template .= self::gStr('<input type="hidden" name="%s" >', $name . '_type');
    $template .= '<span class="input-group-btn" data-type="' . $extendType . '" data-name="' . urlencode($name) . '">';
    if (is_null($attributes2) || empty($attributes2)) {
      $attributes2 = ['class' => 'btn bg-teal mgt-upload'];
    } else {
      if (array_key_exists('class', $attributes2)) {
        $attributes2['class'] .= 'mgt-upload';
      } else {
        $attributes2['class'] = 'btn bg-teal mgt-upload';
      }
    }
    $template .= self::gStr('<input type="button" value="%s" %s >', $label, $attributes2);
    $template .= '</span><span class="input-group-btn"><img width="50px" class="mgt-img"';
    if (!is_null($value) && !empty($value)) {
      $template .= ' src="' . $value . '"';
    }
    $template .= ' /></span></div>';
    return $template;
  }
  
  /**
   * Starts the assets manager.
   * Essential to define the siteURL part
   *
   * @param array $config
   */
  public static function start(&$config)
  {
    $siteURL = $config ['siteUrl'] ?? '';
    self::$siteURL = rtrim($siteURL, '/');
  }
  
  /**
   * Returns the absolute or relative url to the resource.
   *
   * @param string $resource
   * @param boolean $absolute
   * @return string
   */
  public static function getUrl($resource, $absolute = false)
  {
    if (strpos($resource, '//') !== false) {
      return $resource;
    }
    if ($absolute) {
      return self::$siteURL . self::ASSETS_FOLDER . $resource;
    }
    return '/' . ltrim(self::ASSETS_FOLDER, '/') . $resource;
  }
  
  /**
   * Returns the absolute or relative url for a resource in the **activeTheme**.
   *
   * @param string $resource
   * @param boolean $absolute
   * @return string
   */
  public static function getActiveThemeUrl($resource, $absolute = false)
  {
    $activeTheme = ThemesManager::getActiveTheme();
    return self::getThemeUrl($activeTheme, $resource, $absolute);
  }
  
  /**
   * Returns the absolute or relative url for a resource in a theme.
   *
   * @param string $theme
   * @param string $resource
   * @param boolean $absolute
   * @return string
   */
  public static function getThemeUrl($theme, $resource, $absolute = false)
  {
    if ($absolute) {
      return self::$siteURL . self::ASSETS_FOLDER . $theme . '/' . $resource;
    }
    return '/' . ltrim(self::ASSETS_FOLDER, '/') . $theme . '/' . $resource;
  }
  
  /**
   * Returns the script inclusion for a javascript resource.
   *
   * @param string $resource The javascript resource to include
   * @param array $attributes The other html attributes of the script element
   * @param boolean $absolute True if url must be absolute (containing siteUrl)
   * @return string
   */
  public static function js($resource, $attributes = [], $absolute = false)
  {
    return self::script(self::getUrl($resource, $absolute), $attributes);
  }
  
  /**
   * Returns the css inclusion for a stylesheet resource.
   *
   * @param string $resource The css resource to include
   * @param array $attributes The other html attributes of the script element
   * @param boolean $absolute True if url must be absolute (containing siteUrl)
   * @return string
   */
  public static function css($resource, $attributes = [], $absolute = false)
  {
    return self::stylesheet(self::getUrl($resource, $absolute), $attributes);
  }
  
  /**
   * Returns the script inclusion for a javascript resource in **activeTheme**.
   *
   * @param string $resource The javascript resource to include
   * @param array $attributes The other html attributes of the script element
   * @param boolean $absolute True if url must be absolute (containing siteUrl)
   * @return string
   */
  public static function js_($resource, $attributes = [], $absolute = false)
  {
    return self::script(self::getActiveThemeUrl($resource, $absolute), $attributes);
  }
  
  /**
   * Returns the css inclusion for a stylesheet resource in **activeTheme**.
   *
   * @param string $resource The css resource to include
   * @param array $attributes The other html attributes of the script element
   * @param boolean $absolute True if url must be absolute (containing siteUrl)
   * @return string
   */
  public static function css_($resource, $attributes = [], $absolute = false)
  {
    return self::stylesheet(self::getActiveThemeUrl($resource, $absolute), $attributes);
  }
  
  /**
   * get image
   * @param $src
   * @param array $attributes
   * @param bool $absolute
   * @return string
   */
  public static function img($src, $attributes = [], $absolute = false)
  {
    return self::img_gen(self::getActiveThemeUrl($src, $absolute), $attributes);
  }
  
  /**
   * get image
   * @param $resource
   * @param array $attributes
   * @param bool $absolute
   * @return string
   */
  public static function img_($resource, $attributes = [], $absolute = false)
  {
    return self::img_gen(self::getActiveThemeUrl($resource, $absolute), $attributes);
  }
  
  /**
   * get link
   * @param $src
   * @param bool $absolute
   * @return string
   */
  public static function src($src, $absolute = false)
  {
    return self::src_gen(self::getActiveThemeUrl($src, $absolute));
  }
  
  /**
   * get link
   * @param $resource
   * @param bool $absolute
   * @return string
   */
  public static function src_($resource, $absolute = false)
  {
    return self::src_gen(self::getActiveThemeUrl($resource, $absolute));
  }
  
  /**
   * @param $name
   * @param $label
   * @param array $attributes1
   * @param array $attributes2
   * @param int $extendType
   * @return string
   */
  public static function file($name, $label, $value = "", $attributes1 = [], $attributes2 = [], $extendType = "img")
  {
    return self::file_gen($name, $label, $value, $attributes1, $attributes2, $extendType);
  }
}
