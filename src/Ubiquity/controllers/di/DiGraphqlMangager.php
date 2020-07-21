<?php


namespace Ubiquity\controllers\di;


use Ubiquity\cache\CacheManager;
use Ubiquity\cache\system\ArrayCache;
use Ubiquity\controllers\Startup;
use Ubiquity\log\Logger;
use Ubiquity\utils\http\USession;

class DiGraphqlMangager
{
  private $cache;
  private $config;
  
  function __construct()
  {
    $this->cache = new ArrayCache(CacheManager::getCacheSubDirectory("queries"), ".graphql");
    $this->config = Startup::getConfig();
  }
  
  function executeQuery(string $fileName, string $queryName, array $variables = null, $base = true)
  {
    $query = $this->cache->fetch($fileName);
    $headers = ['Content-Type: application/json', 'User-Agent: Dunglas\'s minimal GraphQLService client'];
    if (USession::exists('cookie')) {
      $headers[] = 'Cookie: ' . USession::get('cookie');
    }
    if (USession::exists("token")) {
      $headers[] = "Authorization: MangotechJSC" . USession::get("token");
    } else {
      $headers[] = "Authorization: MangotechJSC" . $this->config['token'];
    }
    
    $url = $base ? $this->config['apiBaseUrl'] : $this->config['apiAuthUrl'];
    
    if (false === $data = @file_get_contents($url, false, stream_context_create([
        'http' => [
          'method' => 'POST',
          'header' => $headers,
          'content' => is_null($variables) ? json_encode(['query' => $query[$queryName]['content']]) : json_encode(['query' => $query[$queryName]['content'], 'variables' => $variables]),
        ]
      ]))) {
      
      $error = error_get_last();
      Logger::warn("message", $error['message']);
      Logger::warn("type", $error['type']);
      throw new \ErrorException($error['message'], $error['type']);
    }
    $this->setSessionCookie($http_response_header);
    
    $result = json_decode($data, true)['data'];
    if (is_array($query[$queryName]['name'])) {
      return $result;
    }
    if (isset($result[$query[$queryName]['name']])) {
      return $result[$query[$queryName]['name']];
    }
    Logger::warn("executeQuery data ", json_encode($data));
    return null;
  }
  
  function executeQueryAuth(string $fileName, string $queryName, array $variables = null)
  {
    return $this->executeQuery($fileName, $queryName, $variables, false);
  }
  
  function executeUploadFile(string $fileName, string $queryName, $files, $multiple = false)
  {
    $query = $this->cache->fetch($fileName);
    
    $ch = curl_init();
    $headers = ['Accept: application/json'];
    if (USession::exists("token")) {
      $headers[] = "Authorization: MangotechJSC" . USession::get("token");
    } else {
      $headers[] = "Authorization: MangotechJSC" . $this->config['token'];
    }
    
    if ($multiple) {
      $postFields['operations'] = '{"query":"' . $query[$queryName]['content'] . '", "variables": { "files": [';
      $postFields['map'] = '{';
      foreach ($files as $k => $v) {
        $postFields['operations'] .= 'null,';
        $postFields['map'] .= '"' . $k . '": ["variables.files.' . $k . '"],';
        $postFields[$k] = new \cURLFile($v['tmp_name'], $v['type'], $v['name']);
      }
      substr_replace($postFields['operations'], '', strlen($postFields['operations']) - 1, 1);
      substr_replace($postFields['map'], '', strlen($postFields['map']) - 1, 1);
      $postFields['operations'] .= '] } }';
      $postFields['map'] .= '}';
    } else {
      $postFields['operations'] = '{"query":"' . $query[$queryName]['content'] . '", "variables": { "file": null } }';
      $postFields['map'] = '{ "0": ["variables.file"] }';
      $postFields['0'] = new \cURLFile($files['tmp_name'], $files['type'], $files['name']);
    }
    
    $options = array(
      CURLOPT_URL => $this->config['apiBaseUrl'],
      CURLOPT_POST => true,
      CURLOPT_HEADER => false,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_POSTFIELDS => $postFields,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true
    );
    curl_setopt_array($ch, $options);
    
    $data = curl_exec($ch);
    $result = json_decode($data, true)['data'];
    
    if (curl_errno($ch)) {
      Logger::warn("executeUploadFile Error ", curl_error($ch));
    }
    curl_close($ch);
    if (isset($result[$query[$queryName]['name']][$query[$queryName]['url']])) {
      return $result[$query[$queryName]['name']][$query[$queryName]['url']];
    }
    Logger::warn("executeUploadFile data ", json_encode($data));
    return null;
  }
  
  private function setSessionCookie($header)
  {
    if (!USession::exists('cookie')) {
      foreach ($header as $item) {
        if (preg_match('/^Set-Cookie:\s*([^;]+)/', $item, $matches)) {
          USession::set("cookie", $matches[1]);
        }
      }
    }
  }
}
