<?php

namespace App\Admin\Organisation;

use App\Admin\BaseCRUDAdminController;
use App\Entity\Messaging\Message;
use App\Entity\Organisation\IndividualMember;
use App\Entity\Organisation\Organisation;
use App\Security\DecisionMakingInterface;
use App\Service\Organisation\OrganisationService;
use App\Entity\Person\Person;
use App\Service\ServiceContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IndividualMemberAdminController extends BaseCRUDAdminController
{

    /** @var IndividualMemberAdmin $admin */
    protected $admin;

    /**
     * @param Organisation $object
     *
     * @return RedirectResponse
     */
    protected function redirectTo($object)
    {
        $request = $this->getRequest();

//        if (null !== $request->get('btn_create_and_edit')) {
//            return new RedirectResponse($this->admin->generateUrl('show', ['id' => $object->getId()]));
//        }

        return parent::redirectTo($object);
    }

    public function editAction($id = null)
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        /** @var IndividualMember $existingObject */
        $existingObject = $this->admin->getObject($id);

        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
        $existingObject->setDraft(false);
        return parent::editAction($id);
    }

    public function createAction()
    {

//        return parent::createAction();
       return parent::createAction();
//        /** @var IndividualMember $newObject */
//        $newObject = new IndividualMember();
//        $person = new \App\Entity\Organisation\Person();
//        $person->setGivenName('   ');
//
//        $newObject->setPerson($person);
//        $newObject->initiateUuid();
//        $this->admin->getModelManager()->create($newObject);
//
//
//        return $this->redirectTo($newObject);
    }

    public function showAction($id = null)
    {
//        $this->admin->setTemplate('show', 'Admin/Messaging/PendingApprovalMessage/CRUD/show.html.twig');
        return parent::showAction($id);
    }

    public function listAction()
    {
//        $this->admin->setTemplate('list', 'Admin/Messaging/Person/CRUD/list.html.twig');

        return parent::listAction();
    }

    public function editCurrentOrganisationAction()
    {
        $org = $this->admin->getCurrentOrganisation();
        return $this->redirectTo($org);
    }
}
