<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader 
{
    private SluggerInterface $slugger;
    private string $uploadsDirectory;

    public function __construct(SluggerInterface $slugger, string $uploadsDirectory)
    {
        $this->slugger = $slugger;
        $this->uploadsDirectory = $uploadsDirectory;
    }

        
    /**
     * Upload a file and return it's filename and filepath.
     *
     * @param  UploadedFile $file The uploaded file.
     * @return array{fileName: string, filePath :string}
     */
    public function upload(UploadedFile $file): array
    {
        $fileName = $this->generateUniqFileName($file);

        try {
            $file->move($this->uploadsDirectory, $fileName);
        } catch (FileException $fileExeption) {
            throw $fileExeption;
        }

        return [
            'fileName' => $fileName,
            'filePath' => $this->uploadsDirectory . $fileName
        ];
    }

        
    /**
     * generate a unique filename for the uploaded file.
     *
     * @param  UploadedFile $file The uploaded file.
     * @return string The unique filename slugged
     */
    private function generateUniqFileName(UploadedFile $file): string
    {
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $originalFileNameSlugged = $this->slugger->slug(strtolower($originalFileName));

        $randomID = uniqid();

        return "{$originalFileNameSlugged}-{$randomID}.{$file->guessExtension()}";
    }
}