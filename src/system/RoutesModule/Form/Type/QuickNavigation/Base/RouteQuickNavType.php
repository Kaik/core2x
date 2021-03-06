<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Form\Type\QuickNavigation\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\RoutesModule\Helper\ListEntriesHelper;

/**
 * Route quick navigation form type base class.
 */
class RouteQuickNavType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    /**
     * RouteQuickNavType constructor.
     *
     * @param TranslatorInterface $translator   Translator service instance.
     * @param RequestStack        $requestStack RequestStack service instance.
     * @param ListEntriesHelper   $listHelper   ListEntriesHelper service instance.
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, ListEntriesHelper $listHelper)
    {
        $this->setTranslator($translator);
        $this->requestStack = $requestStack;
        $this->listHelper = $listHelper;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance.
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('all', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'data' => $options['all'],
                'empty_data' => 0
            ])
            ->add('own', 'Symfony\Component\Form\Extension\Core\Type\HiddenType', [
                'data' => $options['own'],
                'empty_data' => 0
            ])
        ;

        $this->addListFields($builder, $options);
        $this->addSearchField($builder, $options);
        $this->addSortingFields($builder, $options);
        $this->addAmountField($builder, $options);
        $this->addBooleanFields($builder, $options);
        $builder->add('updateview', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
            'label' => $this->__('OK'),
            'attr' => [
                'id' => 'quicknavSubmit',
                'class' => 'btn btn-default btn-sm'
            ]
        ]);
    }

    /**
     * Adds list fields.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     */
    public function addListFields(FormBuilderInterface $builder, array $options)
    {
        $listEntries = $this->listHelper->getEntries('route', 'workflowState');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('workflowState', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $this->__('Workflow state'),
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => false,
            'expanded' => false
        ]);
        $listEntries = $this->listHelper->getEntries('route', 'routeType');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('routeType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $this->__('Route type'),
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => false,
            'expanded' => false
        ]);
        $listEntries = $this->listHelper->getEntries('route', 'schemes');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('schemes', 'Zikula\RoutesModule\Form\Type\Field\MultiListType', [
            'label' => $this->__('Schemes'),
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => true
        ]);
        $listEntries = $this->listHelper->getEntries('route', 'methods');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('methods', 'Zikula\RoutesModule\Form\Type\Field\MultiListType', [
            'label' => $this->__('Methods'),
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => true
        ]);
    }

    /**
     * Adds a search field.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     */
    public function addSearchField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('q', 'Symfony\Component\Form\Extension\Core\Type\SearchType', [
            'label' => $this->__('Search'),
            'attr' => [
                'id' => 'searchTerm',
                'class' => 'input-sm'
            ],
            'required' => false,
            'max_length' => 255
        ]);
    }


    /**
     * Adds sorting fields.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     */
    public function addSortingFields(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Sort by'),
                'attr' => [
                    'id' => 'zikulaRoutesModuleSort',
                    'class' => 'input-sm'
                ],
                'choices' => [
                    $this->__('Id') => 'id',
                    $this->__('Route type') => 'routeType',
                    $this->__('Replaced route name') => 'replacedRouteName',
                    $this->__('Bundle') => 'bundle',
                    $this->__('Controller') => 'controller',
                    $this->__('Action') => 'action',
                    $this->__('Path') => 'path',
                    $this->__('Host') => 'host',
                    $this->__('Schemes') => 'schemes',
                    $this->__('Methods') => 'methods',
                    $this->__('Prepend bundle prefix') => 'prependBundlePrefix',
                    $this->__('Translatable') => 'translatable',
                    $this->__('Translation prefix') => 'translationPrefix',
                    $this->__('Defaults') => 'defaults',
                    $this->__('Requirements') => 'requirements',
                    $this->__('Condition') => 'condition',
                    $this->__('Description') => 'description',
                    $this->__('Sort') => 'sort',
                    $this->__('Group') => 'group',
                    $this->__('Creation date') => 'createdDate',
                    $this->__('Creator') => 'createdUserId',
                    $this->__('Update date') => 'updatedDate'
                ],
                'choices_as_values' => true,
                'required' => false,
                'expanded' => false
            ])
            ->add('sortdir', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Sort direction'),
                'empty_data' => 'asc',
                'attr' => [
                    'id' => 'zikulaRoutesModuleSortDir',
                    'class' => 'input-sm'
                ],
                'choices' => [
                    $this->__('Ascending') => 'asc',
                    $this->__('Descending') => 'desc'
                ],
                'choices_as_values' => true,
                'required' => false,
                'expanded' => false
            ])
        ;
    }

    /**
     * Adds a page size field.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     */
    public function addAmountField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('num', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $this->__('Page size'),
            'empty_data' => 20,
            'attr' => [
                'id' => 'zikulaRoutesModulePageSize',
                'class' => 'input-sm text-right'
            ],
            'choices' => [
                5 => 5,
                10 => 10,
                15 => 15,
                20 => 20,
                30 => 30,
                50 => 50,
                100 => 100
            ],
            'choices_as_values' => true,
            'required' => false,
            'expanded' => false
        ]);
    }

    /**
     * Adds boolean fields.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     */
    public function addBooleanFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add('prependBundlePrefix', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $this->__('Prepend bundle prefix'),
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
            'choices' => [
                $this->__('No') => 'no',
                $this->__('Yes') => 'yes'
            ],
            'choices_as_values' => true
        ]);
        $builder->add('translatable', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $this->__('Translatable'),
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
            'choices' => [
                $this->__('No') => 'no',
                $this->__('Yes') => 'yes'
            ],
            'choices_as_values' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikularoutesmodule_routequicknav';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'all' => 0,
                'own' => 0
            ])
            ->setRequired(['all', 'own'])
            ->setAllowedValues([
                'all' => [0, 1],
                'own' => [0, 1]
            ])
        ;
    }
}
