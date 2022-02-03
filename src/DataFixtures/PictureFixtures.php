<?php

namespace App\DataFixtures;

use App\Entity\Picture;
use App\Service\FileUploader;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureFixtures extends Fixture
{

    /**
     * @var array<string>
     */
    private static array $pictures = [
        'js-vert.png',
        'js.png',
        'njs.png',
        'node.png',
        'red.png',
        'vue.png'
    ];

    private string $filesToUploadDirectory;

    private FileUploader $fileUploader;

    private ObjectManager $manager; 

    public function __construct(FileUploader $fileUploader, KernelInterface $kernel)
    {
        $this->fileUploader = $fileUploader;
        $this->filesToUploadDirectory = "{$kernel->getProjectDir()}/public/to-upload/";
    }

    public function load(ObjectManager $manager): void
    {
        
        $this->manager = $manager;
        
        $this->generateArticlePicture();

        $this->manager->flush();
    }

    private function generateArticlePicture(): void
    {

        foreach(self::$pictures as $key => $pictureFile) {
            $picture = new Picture();

            [
                'fileName' => $pictureName,
                'filePath' => $picturePath
            ] = $this->fileUploader->upload(new UploadedFile($this->filesToUploadDirectory . $pictureFile, $pictureFile, null, null, true));

            $picture->setPictureName($pictureName)
                    ->setPicturePath($picturePath);

            $this->addReference("picture{$key}", $picture);

            $this->manager->persist($picture);

            if($key === array_key_last(self::$pictures)) {
                rmdir($this->filesToUploadDirectory);
            }

        }

    }
}
