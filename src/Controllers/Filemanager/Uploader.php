<?php
namespace App\Controllers\Filemanager;

class Uploader  
{
    private $folder;
    private $model;
    private $field;
    private $createname = '';
    private $filename;
    private $base64_file;
    public $rootUpload = 'files';
    public $minwidth = 10;
    public $minheight = 10;
    public $resize_width = 100;
    public $base64_type;
    public $new_width;
    public $new_height;
    public $filesize;
    public $quality_jpg = 95;
    public $quality_png = 8;

    public function __construct($model = 'pic') {
        $this->model = $model;
    }

    private function setError($error) {
        @unlink($this->base64_file);
        throw new \Exception(htmlspecialchars($this->field).'. '.$error);
    }

    private function Validation($file, $is_image = false) {
        if(!file_exists($file)) {
            $this->setError('Bild nicht gefunden');
            return false;
        } else {
            $filesize = filesize($file);
            if($filesize < 500) {
                $this->setError('Bildgröße ist zu klein');
                return false;
            } elseif($filesize > 50000000) {
                $this->setError('Bildgröße ist zu groß');
                return false;
            }

            if($is_image) {
                $image = getimagesize($file);
                if($image[0] < $this->minwidth) {
                    $this->setError('Die Breite des Bildes ist zu klein. Mindestanforderungen '.$this->minwidth.' px');
                    return false;
                } elseif($image[1] < $this->minheight) {
                    $this->setError('Die Bildhöhe ist zu niedrig. Mindestanforderungen '.$this->minheight.' px');
                    return false;
                }
            }
            return true;
        }
    }

    public function generateFileName($file, $name, $type = 'jpg') {
        if(empty($name)) {
            $tmp = date('U').md5(str_random(10).time()).'.'.$type;
        } else {
            $tmp = $name.'.'.$type;
        }
        $this->filename = $this->model.$tmp;
        return true;
    }
    
    public function check_format($file) 
    {
        $get = explode(',', $file); 
        
        // Не пропускаем не разрешенные форматы
        $formats = [
            'data:image/jpeg;base64', 
            'data:image/png;base64', 
            'data:image/gif;base64',
            'data:image/svg+xml;base64',
            'data:application/pdf;base64',
            'data:application/msword;base64',
            'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64',
            'data:text/plain;base64',
            'data:video/mp4;base64'
        ];
        
        if(!in_array($get[0], $formats)) {
            $this->setError('ist keine Datei des Formats:(jpg, png, gif, svg, pdf, doc, docx, txt, mp4)');
        }
        
        // Выделяем изображение ли это или файл
        $formats = [
            'data:image/jpeg;base64', 
            'data:image/png;base64', 
            'data:image/gif;base64',
        ];
        if(in_array($get[0], $formats)) {
            return true;
        } else {
            return false;
        }
    }

    public function base64_creator($file) {

        $random = str_random(10).time();
        $temp = $this->folder.'temp_'.$this->model.'_'.$random;
        $type = '.jpg';

        $get = explode(',', $file);
        if($get[0] == 'data:image/jpeg;base64') {

            $create = fopen($temp.'.jpg', "w");
            $type = '.jpg';
        } elseif($get[0] == 'data:image/png;base64') {

            $create = fopen($temp.'.png', "w");
            $type = '.png';
        } elseif($get[0] == 'data:image/gif;base64') {

            $create = fopen($temp.'.gif', "w");
            $type = '.gif';
        } elseif($get[0] == 'data:image/svg+xml;base64') {

            $create = fopen($temp.'.svg', "w");
            $type = '.svg';
        } elseif($get[0] == 'data:application/pdf;base64') {
            
            $create = fopen($temp.'.pdf', "w");
            $type = '.pdf';
        } elseif($get[0] == 'data:application/msword;base64') {
            
            $create = fopen($temp.'.doc', "w");
            $type = '.doc';
        } elseif($get[0] == 'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64') {
            
            $create = fopen($temp.'.docx', "w");
            $type = '.docx';
        } elseif($get[0] == 'data:text/plain;base64') {
            
            $create = fopen($temp.'.text', "w");
            $type = '.text';
        } elseif($get[0] == 'data:video/mp4;base64') {
            
            $create = fopen($temp.'.mp4', "w");
            $type = '.mp4';
        }

        fwrite($create, base64_decode($get[1]));
        fclose($create);

        $this->base64_file = $temp.$type;
        $this->base64_type = $get[0];
        return $temp.$type;
    }

    public function filemanagerUpload($file, $resize = false, $filename = false) 
    {
        $this->folder = $_SERVER['DOCUMENT_ROOT'].'/static/'.$this->rootUpload.'/';
        
        $is_image = false;
        if($this->check_format($file)) {
            $is_image = true;
        }
        
        // Создание временного файла в из base64
        $file = $this->base64_creator($file);

        // Проверка и обработка
        if($this->Validation($file, $is_image)) {

            if(!$is_image) {
                preg_match('#\..*#ui', $file, $match);

                $this->generateFileName($file, $this->createname, $match[0]);
                if(!copy($file, $this->folder.$this->filename)) {
                    $this->setError('Datei konnte nicht geladen werden');
                }

                @unlink($this->base64_file);
            } else {

                 /* --> Обработка обычных jpg png gif файлов */
                $image = getimagesize($file);

                // Пропорциональное уменьшение размера картины, если активирован resize
                if($resize) {
                    $this->new_width = $this->resize_width;
                    $this->new_height = $image[1]/($image[0]/$this->resize_width);
                } else {
                    $this->new_width = $image[0];
                    $this->new_height = $image[1];
                }

                $thumb = imagecreatetruecolor($this->new_width, $this->new_height);
                if($image['mime'] == 'image/jpeg' || $image['mime'] == 'image/jpg') {

                    if($filename !== false) {
                        $this->filename = $filename;
                    } else {
                        $this->generateFileName($file, $this->createname, 'jpg');
                    }

                    $filed = imagecreatefromjpeg($file);

                    imagecopyresampled($thumb, $filed, 0, 0, 0, 0, $this->new_width, $this->new_height, $image[0], $image[1]);
                    imagejpeg($thumb, $this->folder.$this->filename, $this->quality_jpg);
                } elseif($image['mime'] == 'image/png') {

                    if($filename !== false) {
                        $this->filename = $filename;
                    } else {
                        $this->generateFileName($file, $this->createname, 'png');
                    }

                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);

                    $filed = imagecreatefrompng($file);
                    imagecopyresampled($thumb, $filed, 0, 0, 0, 0, $this->new_width, $this->new_height, $image[0], $image[1]);
                    imagepng($thumb, $this->folder.$this->filename, $this->quality_png);
                } elseif($image['mime'] == 'image/gif') {

                    // Анимация пока не работает. Надо сделать
                    if($filename !== false) {
                        $this->filename = $filename;
                    } else {
                        $this->generateFileName($file, $this->createname, 'gif');
                    }

                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);

                    $filed = imagecreatefromgif($file);
                    imagecopyresampled($thumb, $filed, 0, 0, 0, 0, $this->new_width, $this->new_height, $image[0], $image[1]);
                    imagegif($thumb, $this->folder.$this->filename);
                }
                imagedestroy($thumb);
            }

            $this->filesize = filesize($this->folder.$this->filename);
            @unlink($this->base64_file);

            return $this->filename;
        }
    }

    public function resize($file, $resize = false) 
    {
        $this->folder = $_SERVER['DOCUMENT_ROOT'].'/static/'.$this->rootUpload.'/';
        $file_path = $this->folder.''.$file;
        
        $image = getimagesize($file_path);

        // Пропорциональное уменьшение размера картины, если активирован resize
        if($resize) {
            $this->new_width = $this->resize_width;
            $this->new_height = $image[1]/($image[0]/$this->resize_width);
        } else {
            $this->new_width = $image[0];
            $this->new_height = $image[1];
        }

        $thumb = imagecreatetruecolor($this->new_width, $this->new_height);
        if($image['mime'] == 'image/jpeg' || $image['mime'] == 'image/jpg') {
            $this->generateFileName($file_path, $this->createname, 'jpg');

            $filed = imagecreatefromjpeg($file_path);
            imagecopyresampled($thumb, $filed, 0, 0, 0, 0, $this->new_width, $this->new_height, $image[0], $image[1]);
            imagejpeg($thumb, $this->folder.$this->filename, $this->quality_jpg);
        } elseif($image['mime'] == 'image/png') {
            $this->generateFileName($file_path, $this->createname, 'png');

            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            $filed = imagecreatefrompng($file_path);
            imagecopyresampled($thumb, $filed, 0, 0, 0, 0, $this->new_width, $this->new_height, $image[0], $image[1]);
            imagepng($thumb, $this->folder.$this->filename, $this->quality_png);
        } elseif($image['mime'] == 'image/gif') {
            $this->generateFileName($file_path, $this->createname, 'gif');

            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            $filed = imagecreatefromgif($file_path);
            imagecopyresampled($thumb, $filed, 0, 0, 0, 0, $this->new_width, $this->new_height, $image[0], $image[1]);
            imagegif($thumb, $this->folder.$this->filename);
        }
        imagedestroy($thumb);
            
        $this->filesize = filesize($this->folder.$this->filename);
        @unlink($file_path);

        return $this->filename;
    }
}