<?php namespace Paxifi\Issue\Controller;

use Paxifi\Issue\Repository\IssueTypesRepository;
use Paxifi\Issue\Transformer\IssueTypesTransformer;
use Paxifi\Support\Controller\ApiController;

class IssueTypeController extends ApiController {

    /**
     * Retrieve all the
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if ($issue_types = IssueTypesRepository::all()) {
            return $this->setStatusCode(200)->respondWithCollection($issue_types);
        }

        return $this->errorInternalError();
    }
    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new IssueTypesTransformer();
    }
}