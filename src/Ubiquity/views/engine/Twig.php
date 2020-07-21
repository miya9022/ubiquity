<?php

namespace Ubiquity\views\engine;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\TwigTest;
use Twig\Loader\FilesystemLoader;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\core\Framework;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\themes\ThemesManager;
use Ubiquity\assets\AssetsManager;

/**
 * Ubiquity Twig template engine.
 *
 * Ubiquity\views\engine$Twig
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.9
 *
 */
class Twig extends TemplateEngine
{
  private $twig;
  private $loader;
  
  public function __construct($options = array())
  {
    $loader = new FilesystemLoader (\ROOT . \DS . "views" . \DS);
    $loader->addPath(implode(\DS, [Startup::getFrameworkDir(), "..", "core", "views"]) . \DS, "framework");
    $this->loader = $loader;
    
    if (isset ($options ["cache"]) && $options ["cache"] === true) {
      $options ["cache"] = CacheManager::getCacheSubDirectory("views");
    }
    
    $this->twig = new Environment ($loader, $options);
    
    if (isset ($options ["activeTheme"])) {
      ThemesManager::setActiveThemeFromTwig($options ["activeTheme"]);
      $this->setTheme($options ["activeTheme"], ThemesManager::THEMES_FOLDER);
      unset ($options ["activeTheme"]);
    } else {
      $this->loader->setPaths([\ROOT . \DS . 'views'], "activeTheme");
    }
    
    $this->addFunction('path', function ($name, $params = [], $absolute = true) {
      return Router::path($name, $params, $absolute);
    });
    
    $this->addFunction('url', function ($name, $params) {
      return Router::url($name, $params);
    });
    
    $this->addFunction('css', function ($resource, $parameters = [], $absolute = false) {
      if ($this->hasThemeResource($resource)) {
        return AssetsManager::css_($resource, $parameters, $absolute);
      }
      return AssetsManager::css($resource, $parameters, $absolute);
    }, true);
    
    $this->addFunction('js', function ($resource, $parameters = [], $absolute = false) {
      if ($this->hasThemeResource($resource)) {
        return AssetsManager::js_($resource, $parameters, $absolute);
      }
      return AssetsManager::js($resource, $parameters, $absolute);
    }, true);
    
    $t = new TwigFunction ('t', function ($context, $id, array $parameters = array(), $domain = null, $locale = null) {
      $trans = TranslatorManager::trans($id, $parameters, $domain, $locale);
      return $this->twig->createTemplate($trans)->render($context);
    }, ['needs_context' => true]);
    
    $tc = new TwigFunction ('tc', function ($context, $id, array $choice, array $parameters = array(), $domain = null, $locale = null) {
      $trans = TranslatorManager::transChoice($id, $choice, $parameters, $domain, $locale);
      return $this->twig->createTemplate($trans)->render($context);
    }, ['needs_context' => true]);
    $this->twig->addFunction($t);
    $this->twig->addFunction($tc);
    
    $test = new TwigTest ('instanceOf', function ($var, $class) {
      return $var instanceof $class;
    });
    $this->twig->addTest($test);
    $this->twig->addGlobal("app", new Framework ());
    
    $this->addFunction('img', function ($resource, $parameters = [], $absolute = false) {
      if ($this->hasThemeResource($resource)) {
        return AssetsManager::img_($resource, $parameters, $absolute);
      }
      return AssetsManager::img($resource, $parameters, $absolute);
    }, true);
  
    $this->addFunction('mgtFinder', function ($name, $label, $value = "", $textParameters = [], $buttonParameters = [], $extendType = "img") {
      return AssetsManager::file($name, $label, $value, $textParameters, $buttonParameters, $extendType);
    }, true);
    
    $this->addFunction('src', function ($resource) {
      if ($this->hasThemeResource($resource)) {
        return AssetsManager::src_($resource);
      }
      return AssetsManager::src($resource);
    }, true);
    
    $this->addFunction('html_entity_decode', function ($resource) {
      return html_entity_decode($resource);
    }, true);
    
    $this->addFunction('unsignedCharacter', function ($resource) {
      return $this->unsignedCharacter($resource);
    }, true);
    
    $this->addFunction('implode', function ($resource) {
      return $this->implode($resource);
    }, true);
    
    $this->addFunction('contains', function ($resource, $dataArray = []) {
      return $this->contains($resource, $dataArray);
    }, true);
  }
  
  protected function hasThemeResource(&$resource)
  {
    $resource = str_replace('@activeTheme/', "", $resource, $count);
    return $count > 0;
  }
  
  protected function addFunction($name, $callback, $safe = false)
  {
    $options = ($safe) ? ['is_safe' => ['html']] : [];
    $this->twig->addFunction(new TwigFunction ($name, $callback, $options));
  }
  
  /*
   * (non-PHPdoc)
   * @see TemplateEngine::render()
   */
  public function render($viewName, $pData, $asString)
  {
    $pData ["config"] = Startup::getConfig();
    EventsManager::trigger(ViewEvents::BEFORE_RENDER, $viewName, $pData);
    $render = $this->twig->render($viewName, $pData);
    EventsManager::trigger(ViewEvents::AFTER_RENDER, $render, $viewName, $pData);
    if ($asString) {
      return $render;
    } else {
      echo $render;
    }
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Ubiquity\views\engine\TemplateEngine::getBlockNames()
   */
  public function getBlockNames($templateName)
  {
    return $this->twig->load($templateName)->getBlockNames();
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Ubiquity\views\engine\TemplateEngine::getCode()
   */
  public function getCode($templateName)
  {
    return UFileSystem::load($this->twig->load($templateName)->getSourceContext()->getPath());
  }
  
  /**
   * Adds a new path in a namespace
   *
   * @param string $path The path to add
   * @param string $namespace The namespace to use
   */
  public function addPath(string $path, string $namespace)
  {
    $this->loader->addPath($path, $namespace);
  }
  
  /**
   * Defines the activeTheme.
   * **activeTheme** namespace is @activeTheme
   *
   * @param string $theme
   * @param string $themeFolder
   * @throws ThemesException
   */
  public function setTheme($theme, $themeFolder = ThemesManager::THEMES_FOLDER)
  {
    $path = \ROOT . \DS . 'views' . \DS . $themeFolder . \DS . $theme;
    if ($theme == '') {
      $path = \ROOT . \DS . 'views';
    }
    if (file_exists($path)) {
      $this->loader->setPaths([$path], "activeTheme");
    } else {
      throw new ThemesException (sprintf('The path `%s` does not exists!', $path));
    }
  }
  
  /**
   * Checks if we have the source code of a template, given its name.
   *
   * @param string $name
   * @return boolean
   */
  public function exists($name)
  {
    return $this->twig->getLoader()->exists($name);
  }
  
  
  /**
   * unsigned Character
   * @param $str
   * @return string|string[]|null
   */
  private function unsignedCharacter($str)
  {
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
    $str = preg_replace("/(đ)/", 'd', $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
    $str = preg_replace("/(Đ)/", 'D', $str);
    $str = preg_replace("/(\“|\”|\‘|\’|\,|\!|\&|\;|\@|\#|\%|\~|\`|\=|\_|\'|\]|\[|\}|\{|\)|\(|\+|\^)/", '-', $str);
    $str = preg_replace("/( )/", '-', $str);
    return $str;
  }
  
  private function implode($resource)
  {
    if (is_array($resource)) {
      return implode(",", $resource);
    }
    return $resource;
  }
  
  private function contains($resource, array $dataArray)
  {
    if(is_null($dataArray)){
      return false;
    }
    if(is_array($dataArray)){
      return in_array($resource, $dataArray);
    }
    return strcmp($resource, $dataArray) == 0;
  }
}
