<?php namespace Paxifi\Issue\Controller;

use Paxifi\Issue\Repository\IssueRepository;
use Paxifi\Support\Controller\ApiController;

class IssueController extends ApiController
{

    /**
     * Post a issue report to paxifi email.
     */
    public function store()
    {
        try {
            $inputs = \Input::all();

            // Todo:: validation
            if ($issue = IssueRepository::create($inputs)) {

                $emailOptions = array(
                    'template' => 'issues.email',
                    'context' => [
                        "from" => $inputs['email'],
                        "name" => $this->translator->trans('email.issue.name'),
                        "subject" => $inputs['subject']
                    ],
//                    'to' => $issue->type->email
                    'to' => "sonny@mobilenowgroup.com"
                );

                \Event::fire('paxifi.email', [$emailOptions]);

                return $this->setStatusCode(200)->respond([
                    'success' => [
                        'code' => 200,
                        'message' => 'Your issue has been post to Paxifi successfully.'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Retrieves the Data Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return [];
    }
}