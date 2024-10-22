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
    use \Stringable;
    use App\SchoolManager\Exception\EcoleException;

    class Adresse implements Stringable
    {
        protected readonly ?int $id;
        protected ?string $voie;
        protected ?string $quartier;
        protected ?string $commune;
        protected ?string $district;
        protected ?string $ville;
        protected ?string $reference;
        protected ?int $ecoleid;

        private static string $villeDefaut = "kinshasa";

        public function __construct
        (
            ?int $id, ?string $voie, ?string $quartier,
            ?string $commune,?string $district, ?string $ville,
            ?string $reference= null, ?int $ecoleid = null
        ) {

            $this
             ->setId($id)
             ->setVoie($voie)
             ->setQuartier($quartier)
             ->setCommune($commune)
             ->setDistrict($district)
             ->setVille($ville)
             ->setReference($reference)
             ->setEcoleid($ecoleid);
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
         * Set the value of id
         */
        public function setId(?int $id): self 
        {
            if (!is_null($id) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807)) {
                throw new EcoleException("Adresse ID error.");
            }
            $this->id = $id;
            return $this;
        }
        
        /**
         * Get the value of voie
         */
        public function getVoie(): ?string {
            return $this->voie;
        }

        /**
         * Set the value of voie
         */
        public function setVoie($voie): self {
            if (!is_null($voie) && (mb_strlen($voie) < 0 || mb_strlen($voie)>255)) {
                throw new EcoleException("School way error.");
            }
            $this->voie = $voie;
            return $this;
        }
        /**
         * Get the value of quartier
         */
        public function getQuartier(): ?string {
            return $this->quartier;
        }
        /**
         * Set the value of quartier
        */
        public function setQuartier($quartier): self 
        {
            if (!is_null($quartier) && (mb_strlen($quartier) < 0 || mb_strlen($quartier)>50 )) {
                throw new EcoleException("School District error.");
            }
            $this->quartier = $quartier;
            return $this;
        }

        /**
        * Get the value of commune
        */
        public function getCommune(): ?string {
            return $this->commune;
        }
        /**
         * Set the value of commune
         *
         * @param string $commune
         *
         * @return self
         */
        public function setCommune(?string $commune): self 
        {
            if (!is_null($commune) && (mb_strlen($commune) < 0 || mb_strlen($commune)>50)) {
                throw new EcoleException("Commune error");
            }
            $this->commune = $commune;
            return $this;
        }
        /**
         * Get the value of district
         */
        public function getDistrict(): ?string {
            return $this->district;
        }

        /**
         * Set the value of district
         *
         * @param ?string $district
         *
         * @return self
         */
        public function setDistrict(?string $district): self {
            if (!is_null($district) && (mb_strlen($district) < 0 || mb_strlen($district)>50 )) {
                throw new EcoleException("District error.");
            }
            $this->district = $district;
            return $this;
        }

        /**
         * Get the value of ville
         */
        public function getVille(): ?string {
            return $this->ville;
        }

        /**
         * Set the value of ville
         *
         * @param string $ville
         *
         * @return self
        */
        public function setVille(?string $ville): self 
        {
            if (
                !is_null($ville) 
                && (
                    mb_strlen($ville) < 0 || mb_strlen($ville)>50 
                    || mb_strtolower($ville) !== self::$villeDefaut 
                )
            ) {
                throw new EcoleException("Vill error.");
            }
            $this->ville = $ville;
            return $this;
        }

        /**
         * Get the value of indice
         *
         * @return ?string
         */
        public function getReference(): ?string {
            return $this->reference;
        }
        
        /**
         * Get the value of ecoleid
         *
         * @return ?int
         */
        public function getEcoleid(): ?int {
            return $this->ecoleid;
        }

        /**
         * Set the value of reference
         *
         * @param ?string $reference
         *
         * @return self
         */
        public function setReference(?string $reference): self {
            if (!is_null($reference) && (mb_strlen($reference) < 0 || mb_strlen($reference)>255 )) {
                throw new EcoleException("School reference error.");
            }
            $this->reference = $reference;
            return $this;
        }
         /**
         * Set the value of ecoleid
         *
         * @param ?int $ecoleid
         *
         * @return self
         */
        public function setEcoleid(?int $ecoleid): self {
        
            if (!is_null($ecoleid) 
                && (!is_numeric($ecoleid) || $ecoleid <= 0 || $ecoleid > 9223372036854775807)
            ) {
                throw new EcoleException("School ID error.");
            }
            $this->ecoleid = $ecoleid;
            return $this;
        }
        /**
         * Method Tostring
         *
         * @return string
         */
        public function __toString() {
            return nl2br(sprintf('%s, Q/%s, C/%s, %s, %s.', 
                !is_null($this->voie)? ucfirst($this->voie): '?-', 
                !is_null($this->quartier)? ucfirst($this->quartier): '?-', 
				!is_null($this->commune)? ucfirst($this->commune): '?-', 
                !is_null($this->district)? ucfirst($this->district): '?-', 
				!is_null($this->ville)? ucfirst($this->ville): '?-'
            ));
        }
        public function toArray() :array 
        {
            return [
                'id'        => $this->getId(),
                'voie'      => $this->getVoie(),
                'quartier'  => $this->getQuartier(),
                'commune'   => $this->getCommune(),
                'district'  => $this->getDistrict(),
                'ville'     => $this->getVille(),
                'reference' => $this->getReference(),
                'ecoleid'   => $this->getEcoleid()
            ];
        }
    }
}