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
    }
}
