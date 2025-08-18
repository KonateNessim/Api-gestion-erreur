<?php

namespace App\Service;

use App\Attribute\Source;
use App\Controller\FileTrait;
use App\Entity\Fichier;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Range;
use Twig\Environment;

class Utils
{
    private $em;
    public function __construct(
        private FileUploader $fileUploader,
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    use FileTrait;

    const MOIS = [
        1 => 'Janvier',
        'Février',
        'mars',
        'avril',
        'mai',
        'juin',
        'juillet',
        'août',
        'septembre',
        'octobre',
        'novembre',
        'décembre'
    ];

    const BASE_PATH = 'formation/certificat';





    public static function  localizeDate($value, $time = false)
    {
        $fmt = new \IntlDateFormatter(
            'fr',
            \IntlDateFormatter::FULL,
            $time ? \IntlDateFormatter::FULL : \IntlDateFormatter::NONE
        );
        return $fmt->format($value instanceof \DateTimeInterface ? $value : new \DateTime($value));
    }




    /**
     * @author Jean Mermoz Effi <mangoua.effi@uvci.edu.ci>
     * Cette fonction permet la création d'un nouveau fichier pour une entité liée
     *
     * @param mixed $filePath
     * @param mixed $entite
     * @param mixed $filePrefix
     * @param mixed $uploadedFile
     *
     * @return Fichier|null
     */
    public function sauvegardeFichier($filePath, $filePrefix, $uploadedFile, string $basePath = self::BASE_PATH): ?Fichier
    {

        if (!$filePrefix) {
            return false;
        }

        $path = $filePath;
        //dd($uploadedFile, $path, $filePrefix);
        $this->fileUploader->upload($uploadedFile, null, $path, $filePrefix, true);

        $fileExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $fichier = new Fichier();
        $fichier->setAlt(basename($path));
        $fichier->setPath($basePath);
        $fichier->setSize(filesize($path));
        $fichier->setUrl($fileExtension);

        //$this->em->persist($fichier);
        //$this->em->flush();
        //dd('');


        return $fichier;
    }


    /**
     * @return mixed
     */
    public static function getUploadDir($path, $uploadDir, $create = false)
    {
        $path = $uploadDir . '/' . $path;

        if ($create && !is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    
}
