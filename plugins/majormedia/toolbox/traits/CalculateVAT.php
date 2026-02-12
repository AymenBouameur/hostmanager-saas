<?php namespace MajorMedia\ToolBox\Traits;

use Event;
use Exception;
use October\Rain\Exception\ValidationException;

// todo: choose base HT or TTC before auto-caculating
trait CalculateVAT
{
  public static function bootCalculateVAT()
  {
    if (!property_exists(get_called_class(), 'vat')) {
      throw new Exception(sprintf(
        'You must define a $vat property in %s to use the bootCalculateVAT trait.',
        get_called_class()
      ));
    }

    static::extend(function ($model) {
      $model->bindEvent('model.saveInternal', function () use ($model) {
        if (isset($model->vat['ht'])) {
          $model->vat = [$model->vat];
        }
        foreach ($model->vat as $item) {
          if (!isset($item['ht']) || !isset($item['vat']) || !isset($item['ttc'])) {
            throw new Exception(sprintf(
              'You must define a $vat property in %s as (["ht" => HT_FIELD_NAME, "vat" => VAT_FIELD_NAME, "ttc" => TTC_FIELD_NAME]) to use the bootCalculateVAT trait.',
              get_called_class()
            ));
          }
          if ($model->{$item['vat']} < 0 || $model->{$item['vat']} > 100) {
            throw new ValidationException([$item['vat'] => "La tva doit être une valeur comprise entre 0 et 100 !"]);
          }
          if ($model->{$item['ht']} > 0) {
            $model->{$item['ttc']} = $model->{$item['vat']} > 0 ? ($model->{$item['ht']} * (1 + $model->{$item['vat']} / 100)) : $model->{$item['ht']};
          } elseif ($model->{$item['ttc']} > 0) {
            $model->{$item['ht']} = $model->{$item['vat']} > 0 ? ((100 * $model->{$item['ttc']}) / (100 + $model->{$item['vat']})) : $model->{$item['ttc']};
          }
          if (empty($model->{$item['ht']})) {
            $model->{$item['ht']} = 0;
          }
          if (empty($model->{$item['vat']})) {
            $model->{$item['vat']} = 0;
          }
          if (empty($model->{$item['ttc']})) {
            $model->{$item['ttc']} = 0;
          }
        }
      }, 600);
    });
  }
}
