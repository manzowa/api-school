<?php 

/**
 * File Ecole
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category Assessment
 * @package  SchoolManager
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\SchoolManager\Model
{

    use App\SchoolManager\Exception\EcoleException;
    class Ecole
    {
        protected readonly ?int $id;
        protected ?string $nom;
        protected ?string $email;
        protected ?string $telephone;
        protected ?string $type;
        protected ?string $site;
        protected ?int $maximage;
        protected array $adresses;

        public const MAXIMA_IMAGE = 5;

        protected ?array $images;
    
        public function __construct(
            ?int $id, ?string $nom, ?string $email = null, 
            ?string $telephone = null,  ?string $type = null, 
            ?string $site = null, ?int $maximage = null, 
            array $adresses = [], array $images = []
        ) {
           $this
            ->setId($id)
            ->setNom($nom)
            ->setEmail($email)
            ->setTelephone($telephone)
            ->setType($type)
            ->setSite($site)
            ->setMaximage($maximage)
            ->setAdresses($adresses)
            ->setImages($images);
        }

        /**
         * Get the value of id
         * 
         * @return ?int
         */
        public function getId(): ?int {
            return $this->id;
        }

        /**
         * Get the value of nom
         *
         * @return ?string
         */
        public function getNom(): ?string {
            return $this->nom;
        }
        /**
         * Get the value of adresses
         *
         * @return array
         */
        public function getAdresses(): array{
            return $this->adresses;
        }
        /**
         * Get the value of type
         *
         * @return ?string
         */
        public function getType(): ?string {
            return $this->type;
        }
        /**
         * Get the value of telephone
         *
         * @return ?string
         */
        public function getTelephone(): ?string {
            return $this->telephone;
        }
        /**
         * Get the value of email
         *
         * @return ?string
         */
        public function getEmail(): ?string {
            return $this->email;
        }
        /**
         * Get the value of site
         *
         * @return ?string
         */
        public function getSite(): ?string {
            return $this->site;
        }
        /**
         * Get the value of maximage
         *
         * @return ?int
         */
        public function getMaximage(): ?int {
            return $this->maximage;
        }
        /**
         * Get the value of images
         *
         * @return ?array
         */
        public function getImages(): ?array {
            return $this->images;
        }
        /**
         * Set the value of id
         */
        public function setId(?int $id): self 
        {
            if ((!is_null($id)) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807)) {
                throw new EcoleException("School ID error");
            }
            $this->id = $id;
            return $this;
        }
        /**
         * Set the value of nom
         *
         * @param string $nom
         *
         * @return self
         */
        public function setNom(?string $nom): self 
        {
            if (is_null($nom) || mb_strlen($nom) < 0 || mb_strlen($nom)>255) {
                throw new EcoleException("School name error.");
            }
            $this->nom = $nom;
            return $this;
        }
        /**
         * Set the value of adresses
         *
         * @param string $adresses
         *
         * @return self
         */
        public function setAdresses(?array $adresses = null): self 
        {
            if (!is_array($adresses)) {
                throw new EcoleException(" School address should be array and not empy.");
            }
            $this->adresses= $adresses;
            return $this;
        }
        /**
         * Set the value of email
         *
         * @param ?string $email
         *
         * @return self
         */
        public function setEmail(?string $email): self {
            if ((!is_null($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
                || (!is_null($email) && (mb_strlen($email) < 0 || mb_strlen($email)>50))
            ) {
                throw new EcoleException("School Email is not valid.");
            }
            $this->email = $email;
            return $this;
        }
        /**
         * Set the value of telephone
         *
         * @param ?string $telephone
         *
         * @return self
         */
        public function setTelephone(?string $telephone): self {
            if ((!is_null($telephone)) && (mb_strlen($telephone) < 0 || mb_strlen($telephone)>30 )) {
                throw new EcoleException("School Phone error.");
            }
            $this->telephone = $telephone;
            return $this;
        }
      
        /**
         * Set the value of Type
         *
         * @param ?string $type
         *
         * @return self
         */
        public function setType(?string $type): self {
            if (!is_null($type) && (mb_strlen($type) < 0 || mb_strlen($type)>50 )) {
                throw new EcoleException("School type error.");
            }
            $this->type = $type;
            return $this;
        }
        /**
         * Set the value of site
         *
         * @param ?string $site
         *
         * @return self
         */
        public function setSite(?string $site): self {
            if (!is_null($site) && (mb_strlen($site) < 0 || mb_strlen($site)> 255 )) {
                throw new EcoleException("School Site error.");
            }
            $this->site = $site;
            return $this;
        }
         /**
         * Set the value of maximage
         *
         * @param ?int $maximage
         *
         * @return self
         */
        public function setMaximage(?int $maximage): self {
            if ((!is_null($maximage)) 
                && (
                    !is_numeric($maximage) 
                    || $maximage <= 0 || $maximage > 10
                )
            ) {
                throw new EcoleException("Maxime image error");
            }
            $this->maximage = $maximage;
            return $this;
        }
   
         /**
         * Set the value of images
         *
         * @param ?array $images
         *
         * @return self
         */
        public function setImages(?array $images): self {
            $this->images = $images;
            return $this;
        }
        /**
         * Method toArray
         *
         * @return array
         */
        public function toArray() :array 
        {
            return [
                'id'        => $this->getId(),
                'nom'       => $this->getNom(),
                'email'     => $this->getEmail(),
                'telephone' => $this->getTelephone(),
                'type'      => $this->getType(),
                'site'      => $this->getSite(),
                'maximage'  => $this->getMaximage(),
                'adresses'  => $this->getAdresses(),
                'images'    => $this->getImages()
            ];
        }

        public static function build
        (
            ?int $id, ?string $nom, 
            ?array $adresses = [], ?string $type = null
        ) {
            $ecole = new self(
                $id, $nom, $adresses, $type
            );
            return $ecole;
        }

        public function isMaximunImage(): bool 
        {
            $isBool = (
                !is_null($this->getMaximage()
            ) && self::MAXIMA_IMAGE <= $this->getMaximage())? true : false;
            return $isBool;
        }
    }
}