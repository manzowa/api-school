<?php

/**
 *  ArticleTrait
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

namespace ApiSchool\V1\Mapper\Trait;


trait ArticleTrait
{
    public function articleRetrieve(
        ?int $id = null,
        ?\ApiSchool\V1\Model\Article $article = null
    ): self {
        if (!is_null($id) && is_null($article)) {
            $command = 'SELECT articles.* FROM articles WHERE id = :id';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        } elseif (
            (!is_null($article) && $article instanceof \ApiSchool\V1\Model\Article)
            && is_null($id)
        ) {
            $id = $article->getId();
            $title = $article->getTitle();

            $command = 'SELECT articles.* FROM articles ';
            $command .= 'WHERE id = :id ';
            $command .= 'AND title = :title ';

            $this->prepare($command)
                ->bindParam(':id',  $id, \PDO::PARAM_STR)
                ->bindParam(':title',  $title, \PDO::PARAM_STR);
        } else {
            $command = 'SELECT articles.* FROM articles ';
            $this->prepare($command);
        }
        return $this;
    }
    public function articleAdd(\ApiSchool\V1\Model\Article $article): self
    {
        $title = $article->getTitle();
        $author = $article->getAuthor();
        $content = $article->getContent();
        $category = $article->getCategory();
        $imageUrl = $article->getImageUrl();
        $linkUrl = $article->getLinkUrl();
        $published = $article->getPublished();

        $command  = 'INSERT INTO articles ';
        $command .= '(title, author, content, category, imageUrl, linkUrl, pubished)';
        $command .= 'VALUES (:title, :author, :content, :category, ';
        $command .= ' :imageUrl, :linkUrl, :published)';

        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':author', $author, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':content', $content, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':category', $category, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':imageUrl', $imageUrl, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':linkUrl', $linkUrl, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':published', $published, \PDO::PARAM_STR | \PDO::PARAM_NULL);
        return $this;
    }
    public function articleUpdate(\ApiSchool\V1\Model\Article $article): self
    {
        $title = $article->getTitle();
        $author = $article->getAuthor();
        $content = $article->getContent();
        $category = $article->getCategory();
        $imageUrl = $article->getImageUrl();
        $linkUrl = $article->getLinkUrl();
        $published = $article->getPublished();
        $id = $article->getId();

        $command  = 'UPDATE artices SET title= :title, author = :author, ';
        $command .= 'content = :content, category = :category, imageUrl = :imageUrl, ';
        $command .= 'linkUrl = :linkUrl, published = :published WHERE id = :id ';

        $this
            ->prepare($command)
            ->bindParam(':title', $title, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':author', $author, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':content', $content, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':category', $category, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':imageUrl', $imageUrl, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':linkUrl', $linkUrl, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':published', $published, \PDO::PARAM_STR | \PDO::PARAM_NULL)
            ->bindParam(':id', $id, \PDO::PARAM_INT);
        return $this;
    }
    public function articleRemove(int $id): self
    {
        if (!is_null($id)) {
            $command = 'DELETE FROM articles WHERE id = :id ';
            $this->prepare($command)
                ->bindParam(':id', $id, \PDO::PARAM_INT);
        }
        return $this;
    }
    public function articleCounter()
    {
        $command = 'SELECT count(id) as totalCount FROM articles';
        $data = $this->prepare($command)
            ->executeQuery()
            ->getResults();
        $result = current($data);
        return intval($result['totalCount']);
    }
}
