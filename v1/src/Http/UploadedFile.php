<?php

namespace ApiSchool\V1\Http; 

/**
 * Class UploadedFile
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category ApiSchool\V1
 * @package  ApiSchool\V1
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
class UploadedFile 
{
    protected ?string $name; 
    protected ?string $fullPath;
    protected ?string $tmpName;
    protected ?string $type;
    protected ?int $error;
    protected ?int $size;

    protected ?int $imageWith;
    protected ?int $imageHeight;
    protected ?int $imageBits;
    protected ?string $imageAttributes;
    protected ?string $imageMime;


    public function __construct(
        ?string $name, ?string $fullPath, ?string $tmpName, 
        ?string $type, ?int $error, ?int $size
    )
    {
      $this->setName($name)
            ->setFullPath($fullPath)
            ->setTmpName($tmpName)
            ->setType($type)
            ->setError($error)
            ->setSize($size)
            ->infosImage();
    }

    /**
     * Get the value of name
     *
     * @return ?string
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setName(?string $name): self {
        $this->name = $name;
        return $this;
    }
	

    /**
     * Get the value of tmpName
     *
     * @return ?string
     */
    public function getTmpName(): ?string {
        return $this->tmpName;
    }

    /**
     * Set the value of tmpName
     *
     * @param ?string $tmpName
     *
     * @return self
     */
    public function setTmpName(?string $tmpName): self {
        $this->tmpName = $tmpName;
        return $this;
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
     * Set the value of type
     *
     * @param ?string $type
     *
     * @return self
     */
    public function setType(?string $type): self {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the value of error
     *
     * @return ?int
     */
    public function getError(): ?int {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @param ?int $error
     *
     * @return self
     */
    public function setError(?int $error): self {
        $this->error = $error;
        return $this;
    }

    /**
     * Get the value of size
     *
     * @return ?int
     */
    public function getSize(): ?int {
        return $this->size;
    }

    /**
     * Set the value of size
     *
     * @param ?int $size
     *
     * @return self
     */
    public function setSize(?int $size): self {
        $this->size = $size;
        return $this;
    }

    public function isImage() {
        if (!empty($this->getTmpName())) {
            return (is_array(getimagesize($this->getTmpName()))) ? true : false;
        }
        return false;
       
    }

    private function infosImage(): self
    {
        if ($this->isImage() && !is_null($this->getTmpName())) {
            $details = getimagesize($this->getTmpName());
            $this->setImageWith($details[0]?? NULL)
                ->setImageHeight($details[1]?? NULL)
                ->setImageAttributes($details[3]?? NULL)
                ->setImageBits($details['bits']?? NULL)
                ->setimageMime($details['mime']?? NULL);
        }
        return $this;
    }

    public function isMime(array $mimes = []): bool {
        if ($this->isImage() && in_array($this->getImageMime(), $mimes)) {
            return true;
        }
        return false;
    }
    /**
     * Get the value of fullPath
     *
     * @return ?string
     */
    public function getFullPath(): ?string {
        return $this->fullPath;
    }

    /**
     * Set the value of fullPath
     *
     * @param ?string $fullPath
     *
     * @return self
     */
    public function setFullPath(?string $fullPath): self {
        $this->fullPath = $fullPath;
        return $this;
    }

    /**
     * Get the value of ImageFileExtension
     *
     * @return ?string
     */
    public function getImageFileExtension(): ?string {
        $fileExtension = NULL;
        if ($this->isImage()) {
            $fileExtension = match($this->getImageMime()) {
                'image/jpeg'    => '.jpg',
                'image/gif'     => '.gif',
                'image/png'     => '.png',
                'image/apng'    => '.apng',
                'image/avif'    => '.avif',
                'image/svg+xml' => '.svg',
                'image/webp'    => '.webp',
                'image/bmp'     => '.bmp',
                'image/x-icon'  => '.ico',
                'image/tiff'    => '.tif',
                default         => '.png',
            };
        }
        return $fileExtension;
    }

    /**
     * Get the value of imageWith
     *
     * @return ?int
     */
    public function getImageWith(): ?int {
        return $this->imageWith;
    }

    /**
     * Set the value of imageWith
     *
     * @param ?int $imageWith
     *
     * @return self
     */
    public function setImageWith(?int $imageWith): self {
        $this->imageWith = $imageWith;
        return $this;
    }

    /**
     * Get the value of imageHeight
     *
     * @return ?int
     */
    public function getImageHeight(): ?int {
        return $this->imageHeight;
    }

    /**
     * Set the value of imageHeight
     *
     * @param ?int $imageHeight
     *
     * @return self
     */
    public function setImageHeight(?int $imageHeight): self {
        $this->imageHeight = $imageHeight;
        return $this;
    }
    
    /**
     * Get the value of imageBits
     *
     * @return ?int
     */
    public function getImageBits(): ?int {
        return $this->imageBits;
    }

    /**
     * Set the value of imageBits
     *
     * @param ?int $imageBits
     *
     * @return self
     */
    public function setImageBits(?int $imageBits): self {
        $this->imageBits = $imageBits;
        return $this;
    }

    /**
     * Get the value of imageAttributes
     *
     * @return ?string
     */
    public function getImageAttributes(): ?string {
        return $this->imageAttributes;
    }

    /**
     * Set the value of imageAttributes
     *
     * @param ?int $imageAttributes
     *
     * @return self
     */
    public function setImageAttributes(?string $imageAttributes): self {
        $this->imageAttributes = $imageAttributes;
        return $this;
    }

     /**
     * Get the value of imageMime
     *
     * @return ?int
     */
    public function getImageMime(): ?string {
        return $this->imageMime;
    }

    /**
     * Set the value of imageMime
     *
     * @param ?int $imageMime
     *
     * @return self
     */
    public function setimageMime(?string $imageMime): self {
        $this->imageMime = $imageMime;
        return $this;
    }
}