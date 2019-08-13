<?php

namespace App\Admin\Organisation;

use App\Entity\Organisation\IndividualMember;
use App\Entity\Organisation\Person;
use App\Entity\Organisation\Role;
use App\Util\Organisation\AppUtil;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use App\Admin\BaseAdmin;
use App\Entity\User\User;
use App\Service\User\UserService;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use App\Service\User\UserManager;
use App\Service\User\UserManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\FormMapper;

use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\FormatterBundle\Form\Type\FormatterType;
use Sonata\FormatterBundle\Form\Type\SimpleFormatterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\Valid;

class IndividualMemberAdmin extends BaseAdmin
{

    const CHILDREN = [];

    protected $action;

    protected $datagridValues = array(
        // display the first page (default = 1)
//        '_page' => 1,
        // reverse order (default = 'ASC')
        '_sort_order' => 'DESC',
        // name of the ordered field (default = the model's id field, if any)
        '_sort_by' => 'updatedAt',
    );

    public function getIndividualMember()
    {
        return $this->subject;
    }

    public function getCurrentChapter()
    {
        return null;
    }

    public function getNewInstance()
    {
        /** @var IndividualMember $object */
        $object = parent::getNewInstance();
        if (empty($person = $object->getPerson())) {
            $object->setPerson($person = new Person());
        }

        return $object;
    }

    public function toString($object)
    {
        return $object instanceof IndividualMember
            ? $object->getPerson()->getName()
            : 'Members'; // shown in the breadcrumb on the create view
    }

    public function createQuery($context = 'list')
    {
        /** @var ProxyQueryInterface $query */
        $query = parent::createQuery($context);
        if (empty($this->getParentFieldDescription())) {
//            $this->filterQueryByPosition($query, 'position', '', '');
        }

//        $query->andWhere()

        return $query;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('contentEdit', $this->getRouterIdParameter().'/edit-content');
        $collection->add('publish', $this->getRouterIdParameter().'/publish');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('person.name', null, ['label' => 'form.label_name'])
            ->add('person.email', null, ['label' => 'form.label_email'])
            ->add('person.phoneNumber', null, ['label' => 'form.label_telephone'])
            ->add('roles', null, [
                'label' => 'form.label_roles',
                'associated_property' => 'nameTrans'])
            ->add('createdAt', null, ['label' => 'form.label_created_at']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General', ['class' => 'col-md-6'])->end()
            ->with('Account', ['class' => 'col-md-6'])->end();
        $this->getFilterByOrganisationQueryForModel(Role::class);
//        $propertyAccessor = $this->getConfigurationPool()->getContainer()->get('access');
        $formMapper
            ->with('General')
            ->add('person.givenName', null, ['label' => 'form.label_given_name'])
            ->add('person.middleName', null, ['label' => 'form.label_middle_name'])
            ->add('person.familyName', null, ['label' => 'form.label_family_name'])
            ->add('person.phoneNumber', null, ['label' => 'form.label_telephone'])
            ->add('person.gender', ChoiceType::class, [
                'required' => false,
                'label' => 'form.label_gender',
                'multiple' => false,
                'placeholder' => 'Select Gender',
                'choices' => [
                    'MALE' => 'MALE',
                    'FEMALE' => 'FEMALE'
                ],
                'translation_domain' => $this->translationDomain,
            ])
            ->add('person.birthDate', DatePickerType::class, [
                'label' => 'form.label_birth_date',
                'format' => 'dd-MM-yyyy',
                'placeholder' => 'dd-mm-yyyy',
                'datepicker_use_button' => false,
            ])
//            ->add('person')
//            ->add('createdAt', DateTimePickerType::class, ['label' => 'form.label_created_at'])

        ;
        $formMapper->end();
        $formMapper
            ->with('Account');

        $formMapper
            ->add('person.email', null, ['label' => 'form.label_email'])
            ->add('person.password', null, ['label' => 'form.label_password']);

        $formMapper
            ->add('roles', ModelType::class, [
                'required' => false,
                'multiple' => true,
                'property' => 'nameTrans',
                'btn_add' => false,
                'query' => $this->getFilterByOrganisationQueryForModel(Role::class)
            ]);

        $formMapper->end();
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        parent::configureTabMenu($menu, $action, $childAdmin);
//        if (!empty($this->subject) && !empty($this->subject->getId())) {
//            $menu->addChild('Manage Content', [
//                'uri' => $this->getConfigurationPool()->getContainer()->get('router')->generate('admin_magenta_cbookmodel_book_book_show', ['id' => $this->getSubject()->getId()])
//            ]);
//        }
    }

    /**
     * @param IndividualMember $object
     */
    public function preValidate($object)
    {
        parent::preValidate($object);
        $person = $object->getPerson();
        if (empty($person->getId())) {
            $container = $this->getContainer();
            $manager = $container->get('doctrine.orm.default_entity_manager');
            $fopRepo = $manager->getRepository(Person::class);
            /** @var Person $foPerson */
            $foPerson = $fopRepo->findOneBy(['email' => $person->getEmail(),
            ]);
            if (empty($foPerson)) {
                $foPerson = $fopRepo->findOneBy(['phoneNumber' => $person->getPhoneNumber(),
                ]);
            }
            if (!empty($foPerson)) {
                $person->removeIndividualMember($object);
                $foPerson->addIndividualMember($object);
            }
        }
    }

    /**
     * @param IndividualMember $object
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
//        if (!$object->isEnabled()) {
//            $object->setEnabled(true);
//        }
    }

    /**
     * @param User $object
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
//        if (!$object->isEnabled()) {
//            $object->setEnabled(true);
//        }
    }

    ///////////////////////////////////
    ///

    ///
    ///////////////////////////////////
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('id')
            ->add('uuid', null, ['label' => 'form.label_uuid'])
            ->add('accessToken', null, ['label' => 'form.label_access_token'])
            ->add('createdAt', null, ['label' => 'form.label_created_at']);
    }


}

