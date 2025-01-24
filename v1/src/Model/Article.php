<?php

/**
 * File Article
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category ApiSchool\V1
 * @package  ApiSchool\V1
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace ApiSchool\V1\Model {

    use ApiSchool\V1\Exception\ArticleException;
    use \DateTime;

    final class Article
    {
        protected readonly ?int $id;
        protected ?string $title;
        protected ?string $author;
        protected ?string $content;
        protected ?string $category;
        protected ?string $imageUrl;
        protected ?string $linkUrl;
        protected ?string $published;

        public function __construct(
            ?int $id,
            ?string $title,
            ?string $author,
            ?string $content,
            ?string $category,
            ?string $imageUrl,
            ?string $linkUrl,
            ?string $published
        ) {
            $this
                ->setId($id)
                ->setTitle($title)
                ->setAuthor($author)
                ->setContent($content)
                ->setCategory($category)
                ->setImageUrl($imageUrl)
                ->setLinkUrl($linkUrl)
                ->setPublished($published);
        }


        /**
         * Get the value of id
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Get the value of title
         *
         * @return ?string
         */
        public function getTitle(): ?string
        {
            return $this->title;
        }

        /**
         * Get the value of author
         *
         * @return ?string
         */
        public function getAuthor(): ?string
        {
            return $this->author;
        }

        /**
         * Get the value of content
         *
         * @return ?string
         */
        public function getContent(): ?string
        {
            return $this->content;
        }

        /**
         * Get the value of category
         *
         * @return ?string
         */
        public function getCategory(): ?string
        {
            return $this->category;
        }

        /**
         * Get the value of imageUrl
         *
         * @return ?string
         */
        public function getImageUrl(): ?string
        {
            return $this->imageUrl;
        }

        /**
         * Get the value of linkUrl
         *
         * @return ?string
         */
        public function getLinkUrl(): ?string
        {
            return $this->linkUrl;
        }

        /**
         * Get the value of published
         *
         * @return ?string
         */
        public function getPublished(): ?string
        {
            return $this->published;
        }

        /**
         * Set the value of id
         */
        public function setId($id): self
        {
            if ((!is_null($id)) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807)) {
                throw new ArticleException("Article ID error");
            }
            $this->id = $id;
            return $this;
        }

        /**
         * Set the value of title
         *
         * @param ?string $title
         *
         * @return self
         */
        public function setTitle(?string $title): self
        {
            if (is_null($title) || mb_strlen($title) < 0 || mb_strlen($title)>255) {
                throw new ArticleException("Article title error.");
            }
            $this->title = $title;
            return $this;
        }

        /**
         * Set the value of author
         *
         * @param ?string $author
         *
         * @return self
         */
        public function setAuthor(?string $author): self
        {
            if (is_null($author) || mb_strlen($author) < 0 || mb_strlen($author)>255) {
                throw new ArticleException("Article author error.");
            }
            $this->author = $author;
            return $this;
        }

        /**
         * Set the value of content
         *
         * @param ?string $content
         *
         * @return self
         */
        public function setContent(?string $content): self
        {
            if (is_null($content) || mb_strlen($content) < 0 || mb_strlen($content)>255) {
                throw new ArticleException("Article content error.");
            }
            $this->content = $content;
            return $this;
        }

        /**
         * Set the value of category
         *
         * @param ?string $category
         *
         * @return self
         */
        public function setCategory(?string $category): self
        {
            if (is_null($category) || mb_strlen($category) < 0 || mb_strlen($category)>255) {
                throw new ArticleException("Article category error.");
            }
            $this->category = $category;
            return $this;
        }

        /**
         * Set the value of imageUrl
         *
         * @param ?string $imageUrl
         *
         * @return self
         */
        public function setImageUrl(?string $imageUrl): self
        {
            if (is_null($imageUrl) || mb_strlen($imageUrl) < 0 || mb_strlen($imageUrl)>255) {
                throw new ArticleException("Article image url error.");
            }
            $this->imageUrl = $imageUrl;
            return $this;
        }

        /**
         * Set the value of linkUrl
         *
         * @param ?string $linkUrl
         *
         * @return self
         */
        public function setLinkUrl(?string $linkUrl): self
        {
            if (is_null($linkUrl) || mb_strlen($linkUrl) < 0 || mb_strlen($linkUrl)>255) {
                throw new ArticleException("Article link url error.");
            }
            $this->linkUrl = $linkUrl;
            return $this;
        }

        /**
         * Set the value of published
         *
         * @param ?string $published
         *
         * @return self
         */
        public function setPublished(?string $published): self
        {
            if (is_null($published) || mb_strlen($published) < 0 || mb_strlen($published)>255) {
                throw new ArticleException("Article Date published error.");
            }
            $this->published = $published;
            return $this;
        }
        public function toArray(): array {
            return [
                'id' => $this->getId(),
                'title' => $this->getTitle(),
                'author' => $this->getAuthor(),
                'content' => $this->getContent(),
                'category' => $this->getCategory(),
                'imageUrl' => $this->getImageUrl(),
                'linkUrl' => $this->getLinkUrl(),
                'published' => $this->getPublished(),
            ];
        }
        public static function fromState(array $data = []) {
            return new static(
                id: $data['id']?? null,
                title: $data['title']?? null,
                author: $data['author']?? null,
                content: $data['content']?? null,
                category: $data['category']?? null,
                imageUrl: $data['imageUrl']?? null,
                linkUrl: $data['linkUrl']?? null,
                published: $data['published']?? null
            );
        }
        public function isPublished(): bool {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $this->getPublished());
            return $date && $date->format('U') > time();
        }
    }
}
