<?php

namespace OlegChulakovStudio\filesaver\behaviors;

use OlegChulakovStudio\filesaver\FileSaverException;
use sem\filestorage\models\File;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Поведение для сохранения/редактирования/удаления файлов привязанных к моделям
 * Class FileSaverBehavior
 * @package common\behaviors
 * @property ActiveRecord $owner
 */
class FileSaverBehavior extends Behavior
{

    /**
     * Наименование виртуального атрибута, в котором хранится загруженный файл
     * @var string
     */
    public $in_attribute = 'image_src';


    /**
     * Наименование реального атрибута, в котором хранится  id  файла
     * @var string
     */
    public $out_attribute = 'image_id';

    /**
     * Наименование атрибута, в котором хранится признак того, нужно ли удалить файл
     * @var boolean
     */
    public $del_attribute = false;

    /**
     * Группа к которой относится изображение
     * @var string
     */
    public $group_type = 'general';

    /**
     * Список допустимых разрешений
     * @var array
     */
    public $allowedExtensions = [
        'png',
        'jpeg',
        'jpg'
    ];

    /**
     * @var File
     */
    protected $file;


    /**
     * @return array
     */
    public function events()
    {
        return [
            // Получаем файл изображения
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'validateFile',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveFile',
            ActiveRecord::EVENT_AFTER_INSERT => 'saveFile',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteFile',
        ];
    }

    /**
     * Проверка файла
     * @return bool
     */
    public function validateFile()
    {
        $uploadedFile = $this->owner->{$this->in_attribute};
        if (!$uploadedFile) {
            return true;
        }

        if (!($uploadedFile instanceof UploadedFile)) {
            throw new \InvalidArgumentException("Аргумент " . $this->in_attribute . ' должен быть экземляром класса ' . UploadedFile::className());
        }

        $this->file = new \sem\filestorage\models\File($uploadedFile, [
            'group_code' => $this->group_type,
            'object_id' => '' . $this->owner->id,
            'mime' => $uploadedFile->type,
            'ori_extension' => $uploadedFile->extension,
            'ori_name' => $uploadedFile->baseName,
            'sys_file' => uniqid() . '.' . $uploadedFile->extension,
            'allowedExtensions' => $this->allowedExtensions,
        ]);

        if (!$this->file->validate()) {
            $errors = $this->file->firstErrors;
            $this->owner->addError($this->in_attribute, current($errors));
            return false;
        }

        return true;
    }

    /**
     * Сохранение файла
     * @param $event
     * @throws \yii\base\Exception
     */
    public function saveFile($event)
    {
        $oldFileID = $this->owner->{$this->out_attribute};

        /** @var UploadedFile $uploadedFile */
        if (!$this->file) {
            if ($oldFileID && $this->del_attribute && $this->owner->{$this->del_attribute}) { //если передан флаг удаления, удалим файл
                $oldFile = File::findOne($oldFileID);
                $oldFile->object_id = $this->owner->id;
                $oldFile->delete();
            }

            return;
        }

        if (!$this->file->save()) {
            throw new FileSaverException("Невозможно сохранить информацию об изображении!" . var_export($this->file->errors,
                    1));
        }

        /** @var ActiveRecord $activeRecordClass */
        $activeRecordClass = get_class($this->owner);
        $currentMaterial = $activeRecordClass::findOne($this->owner->id);
        $currentMaterial->{$this->out_attribute} = $this->file->id;

        $currentMaterial->update(false, [$this->out_attribute]);

        if ($oldFileID) {
            $oldFile = File::findOne($oldFileID);
            $oldFile->delete();
        }

    }

    /**
     * Удаление фото
     * @param $event
     */
    public function deleteFile($event)
    {
        if ($file = File::findOne($this->owner->{$this->out_attribute})) {
            $file->delete();
        }
    }

}