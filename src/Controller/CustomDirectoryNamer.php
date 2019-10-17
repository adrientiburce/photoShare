<?php


namespace App\Controller;


use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;


class CustomDirectoryNamer implements DirectoryNamerInterface
{
    public function directoryName($object, PropertyMapping $mapping): string
    {
        var_dump($object);die;
        $albumName = $object->getMosaic()->getDescription();

        return $albumName;
    }
}
