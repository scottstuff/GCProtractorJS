<?php

namespace GotChosen\User;

use Gaufrette\Filesystem;
use GotChosen\SiteBundle\Entity\ProfileProperty;
use GotChosen\SiteBundle\Entity\UserProfile;
use GotChosen\SiteBundle\Form\Type\StateType;
use GotChosen\Util\Upload\AbstractUploader;
use GotChosen\Util\Upload\CustomUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Intl\Intl;

class UserPropertyHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CustomUploader
     */
    private $uploader;

    /**
     * @var string
     */
    private $awsBucket;

    public function __construct(Filesystem $fs, $awsBucket)
    {
        $this->filesystem = $fs;
        $this->uploader = new CustomUploader($this->filesystem);
        $this->awsBucket = $awsBucket;
    }

    public function transformToData(ProfileProperty $property, $value)
    {
        if ( $property->getFieldType() == ProfileProperty::TYPE_DATE && $value instanceof \DateTime ) {
            return $value->format('Y-m-d');
        } else if ( $property->getFieldType() == ProfileProperty::TYPE_FILE && $value instanceof UploadedFile ) {
            $result = $this->handleFileUpload($property, $value);
            return $result ? $result : '';
        }
        return $value === null ? '' : $value;
    }

    public function transformToForm(ProfileProperty $property, $value)
    {
        if ( $property->getFieldType() == ProfileProperty::TYPE_DATE ) {
            if ( empty($value) ) {
                return null;
            }
            return date_create($value);
        } else if ( $property->getFieldType() == ProfileProperty::TYPE_FILE ) {
            return new UploadedFile($value, basename($value), null, null, UPLOAD_ERR_NO_FILE);
        }
        return $value;
    }

    public function transformToView(ProfileProperty $property, $value)
    {
        if ( $property->getFieldType() == ProfileProperty::TYPE_DATE ) {
            return date_create($value)->format('F j, Y');
        } else if ( $property->getFieldType() == ProfileProperty::TYPE_CHOICE ) {
            $opts = $property->getFieldOptions();
            if ( isset($opts['country']) && $opts['country'] ) {
                $countries = Intl::getRegionBundle()->getCountryNames();
                return isset($countries[$value]) ? $countries[$value] : '';
            }
            if ( isset($opts['state']) && $opts['state'] ) {
                $states = StateType::getStates();
                return isset($states[$value]) ? $states[$value] : '';
            }
            return isset($opts['choices'][$value]) ? $opts['choices'][$value] : '';
        }
        return $value;
    }

    public function cleanup(UserProfile $prop)
    {
        if ( $prop->getProperty()->getFieldType() === ProfileProperty::TYPE_FILE ) {
            /*$fileName = str_replace($this->getBaseFileUrl() . '/', '', $prop->getPropertyValue());
            if ( is_file($this->getBaseFilePath() . '/' . $fileName) ) {
                echo 'maybe removing ' . $this->getBaseFilePath() . '/' . $fileName;
                @unlink($this->getBaseFilePath() . '/' . $fileName);
            }*/
            $fileKey = AbstractUploader::extractKey($prop->getPropertyValue(), $this->awsBucket);
            if ( $fileKey && $this->filesystem->has($fileKey) ) {
                $this->filesystem->delete($fileKey);
            }
        }
    }

    public function handleFileUpload(ProfileProperty $property, UploadedFile $file)
    {
        if ( $file->isValid() ) {
            $opts = $property->getFieldOptions();
            if ( isset($opts['allowed_types']) ) {
                $mime = $file->getMimeType();
                if ( !in_array($mime, $opts['allowed_types']) ) {
                    return false;
                }
                $this->uploader->setMimeTypes($opts['allowed_types']);
            }

            try {
                $url = $this->uploader->upload($file);
                return $url;
            } catch ( \InvalidArgumentException $e ) {
                return false;
            }
        }

        return false;
    }
}
