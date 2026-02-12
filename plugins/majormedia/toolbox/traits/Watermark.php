<?php namespace MajorMedia\ToolBox\Traits;

use Intervention\Image\ImageManagerStatic;

trait Watermark
{
  public static function applyWatermark(&$picture, $logoPath)
  {
    $path_pic = $picture->getLocalPath();
    if (file_exists($path_pic) && filesize($path_pic) > 0 && is_readable($path_pic)) {
      $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path_pic);
      if (!in_array($mime, ['image/webp', 'image/x-webp'])) {
        $pictureWithWatermark = ImageManagerStatic::make($path_pic);
        $pictureWithWatermark->insert($logoPath, 'bottom-right', 20, 20);
        $pictureWithWatermark->save($path_pic);
        return true;
      }
    }
    return false;
  }
}