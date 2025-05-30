<?php

/**
 * File ArticleController
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

namespace ApiSchool\V1\Controller {

    use ApiSchool\V1\Attribute\Route;
    use ApiSchool\V1\Database\Connexion;
    use ApiSchool\V1\Exception\ArticleException;
    use ApiSchool\V1\Model\Article;
    use ApiSchool\V1\Mapper\VendorMapper;

    #[Route(path: '/api/v1')]
    class ArticleController
    {
        #[Route("/articles", "GET")]
        public function getAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: 'Database connection error'
                );
            }

            try {
                $mapper = new VendorMapper($connexionRead);
                $rows = $mapper->articleRetrieve()
                    ->executeQuery()
                    ->getResults();


                if ($mapper->rowCount() === 0) {
                    return $response->json(
                        statusCode: 500,
                        success: false,
                        message: 'Articles Not Found.'
                    );
                }
                $returnData = [];
                $returnData['rows_returned'] = $mapper->rowCount();
                $returnData['articles'] = $rows;

                return $response->json(
                    statusCode: 200,
                    success: true,
                    toCache: true,
                    data: $returnData
                );
            } catch (ArticleException $ex) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: $ex->getMessage()
                );
            }
        }
        #[Route(path: '/articles/page/([0-9]+)', name: 'article.getPerPage')]
        public function getPerPageAction(
            \ApiSchool\V1\Http\Request $request,
            \ApiSchool\V1\Http\Response $response
        ) {
            // Establish the connection Database
            $connexionRead = Connexion::read();
            // Check the Connection Database
            if (!Connexion::is($connexionRead)) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: 'Database connection error'
                );
            }

            $page = $request->getParam('page');
            // Check Parameter Page
            if (is_null($page) || !is_numeric($page)) {
                return $response->json(
                    statusCode: 400,
                    success: false,
                    message: 'Page number cannot be blank or string. It\'s must be numeric'
                );
            }
            try {
                
                $mapper = new VendorMapper($connexionRead);

                $counter = $mapper->articleCounter();
                $limitPerPage = 3;
                $articleCount  = intval($counter);
                $numOfPages   = intval(ceil($articleCount / $limitPerPage));

                // First Page
                if ($numOfPages == 0)  $numOfPages = 1;
                if ($numOfPages < $page || 0 == $page) {
                    return $response->json(
                        statusCode: 404,
                        success: false,
                        message: 'Page not found.'
                    );
                }
                // Offset Page
                $offset = (($page == 1) ? 0 : ($limitPerPage * ($page - 1)));

                $rows = $mapper
                    ->findArticlesByLimitAndOffset(limit: $limitPerPage, offset: $offset)
                    ->executeQuery()
                    ->getResults();

                $rowCounted = $mapper->rowCount();
                $returnData = [];
                $returnData['rows_returned'] = $rowCounted;
                $returnData['total_rows'] = $articleCount;
                $returnData['total_pages'] = $numOfPages;
                $returnData['has_next_page'] =  ($page < $numOfPages) ? true : false;
                $returnData['has_privious_page'] =  ($page > 1) ? true : false;
                $returnData['articles'] = $rows;
                
                return $response->json(
                    statusCode: 200,
                    success: true,
                    toCache: true,
                    data: $returnData
                );
                
            } catch (ArticleException $ex) {
                return $response->json(
                    statusCode: 500,
                    success: false,
                    message: $ex->getMessage()
                );
            }

        }
    }
}
