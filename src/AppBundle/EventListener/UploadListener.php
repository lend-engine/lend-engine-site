<?php

/**
 * Deal with files uploaded to items or contacts
 */
namespace AppBundle\EventListener;

use AppBundle\Entity\FileAttachment;
use AppBundle\Entity\Image;
use AppBundle\Services\SettingsService;
use Doctrine\ORM\EntityManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Symfony\Component\DependencyInjection\Container;

class UploadListener
{
    /** @var EntityManager  */
    private $em;

    /** @var Container  */
    private $container;

    public function __construct(EntityManager $em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function onUpload(PostPersistEvent $event)
    {

//        $s3_bucket = $this->container->get('service.tenant')->getS3Bucket();
//        $schema    = $this->container->get('service.tenant')->getSchema();

        $request  = $event->getRequest();
        $response = $event->getResponse();

        /** @var $file */
        $file = $event->getFile();
        $fileName = $file->getBasename();

        if ($request->get('uploadType') == 'logo') {

//            $this->settings->setSettingValue('logo_image_name', $fileName);
            $response['fileName'] = $fileName;

        }

    }
}