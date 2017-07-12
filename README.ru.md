# Yii2 поведение для сохранения файла в модели
[Russian](README.ru.md)

## Установка через композер
composer require oleg-chulakov-studio/yii2-filesaver
## Или добавьте эту строку в секцию require файла composer.json и выполните команду composer update в консоли
"oleg-chulakov-studio/yii2-filesaver": "*"
## Usage

- В модели добавьте поведение

```php
    public function behaviors()
    {
        return [
            ....
            [
                'class' => FileSaverBehavior::className(),
                'group_type' => 'photo',
                'in_attribute' => 'photoFile',
                'out_attribute' => 'photo_id',
                'del_attribute' => 'photoDel',
                'allowedExtensions' => [
                    'png',
                    'jpeg',
                    'jpg'
                ]
            ],
            ...
        ];
    }
 ```
 - В модели добавьте поля:
 ```php
 /**
  * @var UploadedFile
 /**
 $photoFile;
 
  /**
   * @var boolean
  /**
 $photoDel;
 ```

### Пример использования

 - Сохранение файла по адресу
 
 ```php
$model = new TestModel();
$model->photoFile = new \sem\filestorage\adapters\RemoteFile($url);
$model->save();

 ```
 
 - Сохранение файла загрузкой в форме
 
 ```php
$model = new TestModel();
$model->photoFile = UploadedFile::getInstance($model, 'photoFile');
$model->save();
 ```