# Yii2 behavior for file saving in model
[Russian](README.ru.md)

## Install by composer
composer require oleg-chulakov-studio/yii2-filesaver
## Or add this code into require section of your composer.json and then call composer update in console
"oleg-chulakov-studio/yii2-filesaver": "*"
## Usage

- In model add behavior

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
 - In model add fields
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

### Example usage

 - saving by url
 
 ```php
$model = new TestModel();
$model->photoFile = new \sem\filestorage\adapters\RemoteFile($url);
$model->save();

 ```
 
 - saving by upload in form
 
 ```php
$model = new TestModel();
$model->photoFile = UploadedFile::getInstance($model, 'photoFile');
$model->save();
 ```