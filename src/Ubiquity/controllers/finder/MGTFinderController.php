<?php


namespace Ubiquity\controllers\finder;


use Ubiquity\controllers\Controller;
use Ubiquity\controllers\di\DiGraphqlMangager;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\http\URequest;

class MGTFinderController extends Controller
{
  
  private $config;
  private $grapHQL;
  private const IMAGE_EXTENSION = 'gif,jpg,jpeg,pjpeg,png,svg,webp';
  private const VIDEO_EXTENSION = 'mp4,webm,ogg';
  private const AUDIO_EXTENSION = 'mp3,ogg,wav';
  private const OTHER_EXTENSION = 'vnd.openxmlformats-officedocument.wordprocessingml.document,msword,docx,doc,csv,vnd.openxmlformats-officedocument.spreadsheetml.sheet,vnd.ms-excel,xlsx,xls,pdf,plain';
  
  function __construct()
  {
    parent::__construct();
    $this->config = Startup::getConfig();
    $this->grapHQL = new DiGraphqlMangager();
  }
  
  /**
   * @inheritDoc
   */
  public function index()
  {
    $name = urldecode(URequest::get('name'));
    $url = urldecode(URequest::get('url'));
    $fileType = urldecode(URequest::get('fileType'));
    $iconType = 'all';
    $action = '/UploadFileController/upload';
    $this->loadView("@framework/finder/index.html", compact('name', 'url', 'action', 'fileType', 'iconType'));
  }
  
  public function upload()
  {
    $file = $_FILES['file'];
    $fileType = explode('/', $_FILES['file']['type']);
    $name = URequest::post('nameMedia');
    $type = $type = $this->config['mediaType']['image'];
    if ($this->validateImageFile($fileType, $type, false)) {
      header("Location: /UploadFileController/index?name=" . urlencode($name) . '&url=&error=' . urlencode('Extend file not support'));
    }
    $type = $type = $this->config['mediaType']['video'];
    if ($this->validateVideoFile($fileType, $type, false)) {
      header("Location: /UploadFileController/index?name=" . urlencode($name) . '&url=&error=' . urlencode('Extend file not support'));
    }
    $type = $type = $this->config['mediaType']['audio'];
    if ($this->validateAudioFile($fileType, $type, false)) {
      header("Location: /UploadFileController/index?name=" . urlencode($name) . '&url=&error=' . urlencode('Extend file not support'));
    }
    $type = $type = $this->config['mediaType']['audio'];
    if ($this->validateOtherFile($fileType, $type, false)) {
      header("Location: /UploadFileController/index?name=" . urlencode($name) . '&url=&error=' . urlencode('Extend file not support'));
    }
    $type = $type = $this->config['mediaType']['file'];
    $result = $this->grapHQL->executeUploadFile("upload_file", "uploadSingleFile", $file);
    header('Location: /UploadFileController/index?' . urlencode($name) . '&url=' . urlencode($result) . '&fileType=' . urlencode($_FILES['file']['type']));
  }
  
  public function image()
  {
    $name = urldecode(URequest::get('name'));
    $type = $this->config['mediaType']['image'];
    if (!isset($type)) {
      $type = 'image/*';
    }
    $iconType = 'image';
    $fileType = urldecode(URequest::get('fileType'));
    $url = urldecode(URequest::get('url'));
    $action = '/UploadFileController/uploadImage';
    $this->loadView("@framework/finder/index.html", compact('name', 'url', 'type', 'action', 'iconType', 'fileType'));
  }
  
  public function uploadImage()
  {
    $file = $_FILES['file'];
    $fileType = explode('/', $_FILES['file']['type']);
    $name = URequest::post('nameMedia');
    $type = $this->config['mediaType']['image'];
    if (!isset($type)) {
      $type = 'image/*';
    }
    
    if ($this->validateImageFile($fileType, $type)) {
      header("Location: /UploadFileController/image?name=" . urlencode($name) . urlencode('Extend file not support'));
    }
    
    $result = $this->grapHQL->executeUploadFile("upload_file", "uploadSingleFile", $file);
    header("Location: /UploadFileController/image?name=" . urlencode($name) . '&url=' . urlencode($result) . '&fileType=' . urlencode($_FILES['file']['type']));
  }
  
  public function video()
  {
    $name = urldecode(URequest::get('name'));
    $type = $this->config['mediaType']['video'];
    if (!isset($type)) {
      $type = 'video/*';
    }
    $iconType = 'video';
    $fileType = urldecode(URequest::get('fileType'));
    $url = urldecode(URequest::get('url'));
    $action = '/UploadFileController/uploadVideo';
    $this->loadView("@framework/finder/index.html", compact('name', 'type', 'url', 'action', 'iconType', 'fileType'));
  }
  
  public function uploadVideo()
  {
    $file = $_FILES['file'];
    $fileType = explode('/', $_FILES['file']['type']);
    $name = URequest::post('nameMedia');
    $type = $this->config['mediaType']['video'];
    if (!isset($type)) {
      $type = 'video/*';
    }
    
    if ($this->validateVideoFile($fileType, $type)) {
      header("Location: /UploadFileController/video?name=" . urlencode($name) . urlencode('Extend file not support'));
    }
    
    $result = $this->grapHQL->executeUploadFile("upload_file", "uploadSingleFile", $file);
    header("Location: /UploadFileController/video?name=" . urlencode($name) . '&url=' . urlencode($result) . '&fileType=' . urlencode($_FILES['file']['type']));
  }
  
  public function audio()
  {
    $name = urldecode(URequest::get('name'));
    $type = $this->config['mediaType']['audio'];
    if (!isset($type)) {
      $type = 'audio/*';
    }
    $iconType = 'audio';
    $fileType = urldecode(URequest::get('fileType'));
    $url = urldecode(URequest::get('url'));
    $action = '/UploadFileController/uploadAudio';
    $this->loadView("@framework/finder/index.html", compact('name', 'url', 'type', 'action', 'iconType', 'fileType'));
  }
  
  public function uploadAudio()
  {
    $file = $_FILES['file'];
    $fileType = explode('/', $_FILES['file']['type']);
    $name = URequest::post('nameMedia');
    $type = $this->config['mediaType']['audio'];
    if (!isset($type)) {
      $type = 'audio/*';
    }
    
    if ($this->validateAudioFile($fileType, $type)) {
      header("Location: /UploadFileController/audio?name=" . urlencode($type) . urlencode('Extend file not support'));
    }
    
    $result = $this->grapHQL->executeUploadFile("upload_file", "uploadSingleFile", $file);
    header("Location: /UploadFileController/audio?name=" . urlencode($name) . '&url=' . urlencode($result) . '&fileType=' . urlencode($_FILES['file']['type']));
  }
  
  public function media()
  {
    $name = urldecode(URequest::get('name'));
    $typeImage = $this->config['mediaType']['image'];
    $typeAudio = $this->config['mediaType']['audio'];
    $typeVideo = $this->config['mediaType']['video'];
    if (!isset($typeImage)) {
      $typeImage = 'image/*';
    }
    if (!isset($typeAudio)) {
      $typeAudio = 'audio/*';
    }
    if (!isset($typeVideo)) {
      $typeVideo = 'video/*';
    }
    $type = $typeImage . ',' . $typeAudio . ',' . $typeVideo;
    $iconType = 'image';
    $fileType = urldecode(URequest::get('fileType'));
    $url = urldecode(URequest::get('url'));
    $action = '/UploadFileController/uploadMedia';
    $this->loadView("@framework/finder/index.html", compact('name', 'url', 'type', 'action', 'iconType', 'fileType'));
  }
  
  public function uploadMedia()
  {
    $file = $_FILES['file'];
    $fileType = explode('/', $_FILES['file']['type']);
    $name = URequest::post('nameMedia');
    
    $typeImage = $this->config['mediaType']['image'];
    $typeAudio = $this->config['mediaType']['audio'];
    $typeVideo = $this->config['mediaType']['video'];
    if (!isset($typeImage)) {
      $typeImage = 'image/*';
    }
    if (!isset($typeAudio)) {
      $typeAudio = 'audio/*';
    }
    if (!isset($typeVideo)) {
      $typeVideo = 'video/*';
    }
    $type = $typeImage . ',' . $typeAudio . ',' . $typeVideo;
    if ($fileType[0] != 'image' && $fileType[0] != 'audio' && $fileType[0] != 'video') {
      header("Location: /UploadFileController/audio?name=" . urlencode($type) . urlencode('Extend file not support'));
    }
    
    if ($this->validateAudioFile($fileType, $type, false)) {
      header("Location: /UploadFileController/audio?name=" . urlencode($name) . urlencode('Extend file not support'));
    }
    if ($this->validateVideoFile($fileType, $type, false)) {
      header("Location: /UploadFileController/audio?name=" . urlencode($name) . urlencode('Extend file not support'));
    }
    if ($this->validateImageFile($fileType, $type, false)) {
      header("Location: /UploadFileController/audio?name=" . urlencode($name) . urlencode('Extend file not support'));
    }
    
    $result = $this->grapHQL->executeUploadFile("upload_file", "uploadSingleFile", $file);
    header("Location: /UploadFileController/audio?name=" . urlencode($name) . '&url=' . urlencode($result) . '&fileType=' . urlencode($_FILES['file']['type']));
  }
  
  public function fileOther()
  {
    $name = urldecode(URequest::get('name'));
    $type = $this->config['mediaType']['file'];
    $url = urldecode(URequest::get('url'));
    $iconType = 'file';
    $fileType = urldecode(URequest::get('fileType'));
    $action = '/UploadFileController/uploadAudio';
    $this->loadView("@framework/finder/index.html", compact('name', 'url', 'type', 'action', 'iconType', 'fileType'));
  }
  
  public function uploadFileOther()
  {
    $file = $_FILES['file'];
    $fileType = explode('/', $_FILES['file']['type']);
    $name = URequest::post('nameMedia');
    $type = $this->config['mediaType']['file'];
    
    if ($this->validateOtherFile($fileType, $type)) {
      header("Location: /UploadFileController/audio?name=" . urlencode($name) . urlencode('Extend file not support'));
    }
    
    $result = $this->grapHQL->executeUploadFile("upload_file", "uploadSingleFile", $file);
    header("Location: /UploadFileController/audio?name=" . urlencode($name) . '&url=' . urlencode($result));
  }
  
  private function validateImageFile(array $fileType, string $type, bool $required = true): bool
  {
    if (strtolower($fileType[0]) == 'image') {
      if (isset($type) && !empty($type)) {
        $types = explode(',', $type);
        foreach ($types as $key => $value) {
          $ex = explode('/', $value);
          if (count($ex) < 2) {
            if (strpos(self::IMAGE_EXTENSION, strtolower($fileType[1]))) {
              return false;
            }
            return true;
          } else {
            if ($ex[1] == '*') {
              if (strpos(self::IMAGE_EXTENSION, strtolower($fileType[1]))) {
                return false;
              }
            } else {
              if (strtolower($fileType[1]) == strtolower($ex[1])) {
                return false;
              }
            }
          }
        }
      } else {
        if (strpos(self::IMAGE_EXTENSION, strtolower($fileType[1]))) {
          return false;
        } else {
          return true;
        }
      }
    }
    return $required;
  }
  
  private function validateVideoFile(array $fileType, $type, bool $required = true): bool
  {
    if (strtolower($fileType[0]) == 'video') {
      if (isset($type) && !empty($type)) {
        $types = explode(',', $type);
        foreach ($types as $key => $value) {
          $ex = explode('/', $value);
          if (count($ex) < 2) {
            if (strpos(self::VIDEO_EXTENSION, strtolower($fileType[1]))) {
              return false;
            }
            return true;
          } else {
            if ($ex[1] == '*') {
              if (strpos(self::VIDEO_EXTENSION, strtolower($fileType[1]))) {
                return false;
              }
            } else {
              if (strtolower($fileType[1]) == strtolower($ex[1])) {
                return false;
              }
            }
          }
        }
      } else {
        if (strpos(self::VIDEO_EXTENSION, strtolower($fileType[1]))) {
          return false;
        } else {
          return true;
        }
      }
    }
    return $required;
  }
  
  private function validateAudioFile(array $fileType, $type, bool $required = true): bool
  {
    if (strtolower($fileType[0]) == 'audio') {
      if (isset($type) && !empty($type)) {
        $types = explode(',', $type);
        foreach ($types as $key => $value) {
          $ex = explode('/', $value);
          if (count($ex) < 2) {
            if (strpos(self::AUDIO_EXTENSION, strtolower($fileType[1]))) {
              return false;
            }
            return true;
          } else {
            if ($ex[1] == '*') {
              if (strpos(self::AUDIO_EXTENSION, strtolower($fileType[1]))) {
                return false;
              }
            } else {
              if (strtolower($fileType[1]) == strtolower($ex[1])) {
                return false;
              }
            }
          }
        }
      } else {
        if (strpos(self::AUDIO_EXTENSION, strtolower($fileType[1]))) {
          return false;
        } else {
          return true;
        }
      }
    }
    return $required;
  }
  
  private function validateOtherFile(array $fileType, $type, bool $required = true): bool
  {
    if (strtolower($fileType[0]) == 'application') {
      if (isset($type) && !empty($type)) {
        $types = explode(',', $type);
        foreach ($types as $key => $value) {
          $ex = explode('/', $value);
          if (count($ex) < 2) {
            if (strpos(self::OTHER_EXTENSION, strtolower($fileType[1]))) {
              return false;
            }
            return true;
          } else {
            if ($ex[1] == '*') {
              if (strpos(self::OTHER_EXTENSION, strtolower($fileType[1]))) {
                return false;
              }
            } else {
              if (strtolower($fileType[1]) == strtolower($ex[1])) {
                return false;
              }
            }
          }
        }
      } else {
        if (strpos(self::OTHER_EXTENSION, strtolower($fileType[1]))) {
          return false;
        } else {
          return true;
        }
      }
    } else if (strtolower($fileType[0]) == 'text') {
      if (strpos(self::OTHER_EXTENSION, strtolower($fileType[1]))) {
        return false;
      } else {
        return true;
      }
    }
    return $required;
  }
  
}
