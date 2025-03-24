<?php

namespace Akyos\MakerBundle\Command;

use AllowDynamicProperties;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassSource\Model\ClassData;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AllowDynamicProperties] #[AsCommand(
    name: 'make:akyos:crud',
    description: 'Add a short description for your command',
)]
class MakeAkyosCrudCommand extends Command
{

    const MAKE_FORM_COMMAND = 'make:form';
    const BASE_TPL_FILE = __DIR__.'/../../templates/maker/';
    const BASE_TEMPLATE_TPL_FILE = self::BASE_TPL_FILE . 'twig/';

    const BASE_COMPONENT_TPL_FILE = self::BASE_TPL_FILE . 'components/';

    const TWIG_COMPONENTS = 'src/Twig/Components/';

    const TWIG_COMPONENTS_TEMPLATE = 'templates/components/';

    const TEMPLATE_LIST = [
        'index',
        'show',
        'new',
        'edit',
    ];

    const DEFAULT_VALUES = [
        'layout-template' => 'base.html.twig',
        'block' => 'body',
    ];

    private ConsoleStyle $io;

    public function __construct(
        private readonly EntityManagerInterface                   $entityManager,
        #[Autowire(service: 'maker.generator')] private Generator $generator
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('entity-class', InputArgument::OPTIONAL, \sprintf('The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('controller-name', InputArgument::OPTIONAL, \sprintf('The controller name of the CRUD (e.g. <fg=yellow>%s</>)', 'Admin\\PostCrudController'))
            ->addArgument('layout-template', InputArgument::OPTIONAL, \sprintf('The layout template of the CRUD (e.g. <fg=yellow>%s</>)', 'admin/layout.html.twig'))
            ->addArgument('block', InputArgument::OPTIONAL, \sprintf('The block name of the CRUD (e.g. <fg=yellow>%s</>)', 'content'))
            ->addArgument('route-prefix', InputArgument::OPTIONAL, \sprintf('The default route prefix of the CRUD (e.g. <fg=yellow>%s</>)', 'admin.'), '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new ConsoleStyle($input, $output);

        foreach ($input->getArguments() as $argument => $value) {
            if (null === $value) {
                $this->askForArgument($input, $argument);
            }
        }

        $this->initData($input);

        $application = $this->getApplication();
        if (!$application) {
            throw new \RuntimeException('L\'application Symfony est introuvable.');
        }

        $this->runMakeFormCommand($application, $output);
        $this->generateController();
        $this->generateTwig();
        $this->generateComponent();


        return Command::SUCCESS;
    }

    public function getAllEntities(): array
    {
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $entities = [];

        foreach ($metaData as $meta) {
            $entities[] = str_replace('App\\Entity\\', '', $meta->getName());
        }
        return $entities;
    }

    private function askForArgument(InputInterface $input, int|string $argumentName)
    {
        if (null === $input->getArgument($argumentName)) {
            $argument = $this->getDefinition()->getArgument($argumentName);

            $entities = $this->getAllEntities();
            $question = (new Question($argument->getDescription(), self::DEFAULT_VALUES[$argumentName] ?? null))->setValidator(function ($value) use ($entities) {
                if (null === $value) {
                    throw new \RuntimeException('Please enter a value');
                }

                return $value;
            });
            $question->setAutocompleterValues($entities);

            $value = $this->io->askQuestion($question);

            $input->setArgument($argumentName, $value);
        }
    }

    private function runMakeFormCommand($application, $output)
    {
        $command = $application->find(self::MAKE_FORM_COMMAND);

        $arguments = new ArrayInput([
            'command' => self::MAKE_FORM_COMMAND,
            'name' => $this->formData->getFullClassName(true),
            'bound-class' => $this->entityData->getFullClassName(true),
        ]);

        try {
            $command->run($arguments, $output);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), "can't be generated because it already exists")) {
                $output->writeln('<comment>⚠️  Le formulaire ' . $this->formData->getFullClassName() . ' déjà, commande ignorée.</comment>');
            } else {
                throw $e;
            }
        }
    }

    public function generateComponent(): void
    {
        $files = [
            [
                'path' => self::TWIG_COMPONENTS . str_replace('\\', '/', $this->indexComponentData->getFullClassName(true)) . '.php',
                'template' => self::BASE_COMPONENT_TPL_FILE . 'IndexComponent.tpl.php'
            ],
            [
                'path' => self::TWIG_COMPONENTS_TEMPLATE . str_replace('\\', '/', $this->indexComponentData->getFullClassName(true)) . '.html.twig',
                'template' => self::BASE_COMPONENT_TPL_FILE . 'index_template.tpl.php'
            ],
            [
                'path' => self::TWIG_COMPONENTS_TEMPLATE . str_replace('\\', '/', $this->subNamespace.'\\'.$this->entityData->getClassName()) . '/elements/' . '_tr.html.twig',
                'template' => self::BASE_COMPONENT_TPL_FILE . '_tr.tpl.php'
            ],
            [
                'path' => self::TWIG_COMPONENTS . str_replace('\\', '/', $this->formComponentData->getFullClassName(true)) . '.php',
                'template' => self::BASE_COMPONENT_TPL_FILE . 'FormComponent.tpl.php'
            ],
            [
                'path' => self::TWIG_COMPONENTS_TEMPLATE . str_replace('\\', '/', $this->formComponentData->getFullClassName(true)) . '.html.twig',
                'template' => self::BASE_COMPONENT_TPL_FILE . 'form_template.tpl.php'
            ],
            [
                'path' => self::TWIG_COMPONENTS . str_replace('\\', '/', $this->showComponentData->getFullClassName(true)) . '.php',
                'template' => self::BASE_COMPONENT_TPL_FILE . 'ShowComponent.tpl.php'
            ],
            [
                'path' => self::TWIG_COMPONENTS_TEMPLATE . str_replace('\\', '/', $this->showComponentData->getFullClassName(true)) . '.html.twig',
                'template' => self::BASE_COMPONENT_TPL_FILE . 'show_template.tpl.php'
            ],
        ];

        foreach ($files as $file) {
            $this->generateFileWithHandling($file['path'], $file['template']);
        }
    }

    private function generateFileWithHandling(string $filePath, string $templatePath): void
    {
        try {
            $this->generator->generateFile($filePath, $templatePath, $this->templateArgs);
            $this->generator->writeChanges();
            $this->io->text('Next: Open your new components class and add some logics!');
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), "can't be generated because it already exists")) {
                $this->io->writeln("<comment>⚠️  Le fichier {$filePath} existe déjà.</comment>");
            } else {
                throw $e;
            }
        }
    }

    public function generateController(): void
    {
        try {
            $this->generator->generateFile(
                'src/Controller/' . str_replace('\\', '/', $this->controllerData->getFullClassName(true) . '.php'),
                self::BASE_TPL_FILE . 'Controller.tpl.php',
                $this->templateArgs
            );

            $this->generator->writeChanges();

            $this->io->text('Next: Open your new controller class and add some pages!');
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), "can't be generated because it already exists")) {
                $this->io->writeln('<comment>⚠️  Le controller ' . $this->controllerData->getFullClassName() . ' déjà.</comment>');
            } else {
                throw $e;
            }
        }
    }

    public function generateTwig(): void
    {
        foreach (self::TEMPLATE_LIST as $template) {
            try {
                $this->generator->generateFile(
                    'templates/' . $this->templateSubNamespace . '/'. $this->snake_case_entity . '/' . $template . '.html.twig',
                    self::BASE_TEMPLATE_TPL_FILE . 'twig_'.$template.'.tpl.php',
                    $this->templateArgs
                );

                $this->generator->writeChanges();

                $this->io->text('Next: Open your new template and add some content!');
            } catch (\RuntimeException $e) {
                if (str_contains($e->getMessage(), "can't be generated because it already exists")) {
                    $this->io->writeln('<comment>⚠️  Le template templates/' . $this->templateSubNamespace . '/'. $this->snake_case_entity . '/' . $template . '.html.twig existe déjà.</comment>');
                } else {
                    throw $e;
                }
            }
        }
    }

    private function getEntityProperties(string $entityClass): array
    {
        $metadata = $this->entityManager->getClassMetadata('App\Entity\\' . $entityClass);
        $properties = $metadata->getFieldNames();
        return array_values(array_filter($properties, fn($property) => $property !== 'id'));
    }

    private function initData(InputInterface $input)
    {
        $entityClass = Str::asClassName($input->getArgument('entity-class'));

        // Get entity properties and ask user to select
        $properties = array_merge(['all'], $this->getEntityProperties($entityClass));
        $selectedProperties = $this->io->choice(
            'Select properties to include in table (comma-separated numbers, or none for all)',
            $properties,
            0,
            true
        );

        array_shift($properties);

        $this->selectedTable = $selectedProperties !== ['0' => 'all'] ? $selectedProperties : $properties;

        // Ask for form properties with option to skip
        $selectFormProperties = $this->io->confirm('Do you want to select specific properties for the form?', false);

        if ($selectFormProperties) {
            $this->selectedFormProperties = $this->io->choice(
                'Select properties to include in form (comma-separated numbers)',
                $properties,
                0,
                true
            );

            $this->selectedFormProperties = !empty($this->selectedFormProperties) ? $this->selectedFormProperties : $properties;
        } else {
            // Use all properties as default form properties
            $this->selectedFormProperties = $properties;
        }

        $this->entityData = ClassData::create(
            class: $entityClass,
            suffix: '',
            isEntity: true,
        )->setRootNamespace('App\\Entity');

        $this->formData = ClassData::create(
            class: Str::asClassName($input->getArgument('entity-class')),
            suffix: 'Type',
        )->setRootNamespace('App\\Form');

        $this->controllerData = ClassData::create(
            class: Str::asClassName($input->getArgument('controller-name')),
            suffix: 'Controller',
            useStatements: [
                $this->entityData->getFullClassName(),
                'Symfony\Bridge\Twig\Attribute\Template',
                'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
                'Symfony\Component\Routing\Attribute\Route',
            ]
        )->setRootNamespace('App\\Controller');

        $subNamespace = explode('\\', $input->getArgument('controller-name'));
        array_pop($subNamespace);
        foreach ($subNamespace as $key => $dir){
            $subNamespace[$key] = Str::asCamelCase($dir);
        }
        $this->subNamespace = implode('\\', $subNamespace);

        $this->snake_case_entity = Str::asSnakeCase($this->entityData->getClassName());
        $this->camel_case_entity = Str::asCamelCase($this->entityData->getClassName());

        $componentName = explode('\\', $this->subNamespace.'\\'.$this->entityData->getClassName());
        foreach ($componentName as $key => $value) {
            $componentName[$key] = Str::asCamelCase($value);
        }
        $this->componentName = implode(':', $componentName);

        $templateSubNamespace = explode('\\', $this->subNamespace);
        foreach ($templateSubNamespace as $key => $value) {
            $templateSubNamespace[$key] = Str::asSnakeCase($value);
        }
        $this->templateSubNamespace = implode('/', $templateSubNamespace);


        $this->indexComponentData = ClassData::create(
            class: $this->subNamespace.'\\'.$this->entityData->getClassName().'\\Index',
            suffix: '',
            useStatements: [
                $this->entityData->getFullClassName(),
                'Akyos\UXFilters\Class\Text',
                'Akyos\UXFilters\Trait\ComponentWithFilterTrait',
                'Akyos\UXTable\Trait\ComponentWithTableTrait',
                'Symfony\Contracts\Translation\TranslatorInterface',
                'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
                'Symfony\UX\LiveComponent\Attribute\AsLiveComponent',
                'Symfony\UX\LiveComponent\DefaultActionTrait',
                'Symfony\UX\LiveComponent\Attribute\LiveAction',
                'Symfony\UX\LiveComponent\Attribute\LiveArg',
                'Akyos\UXTable\Class\TH',
                'Akyos\UXTable\Class\TR'
            ]
        )->setRootNamespace('App\\Twig\\Components');

        $this->formComponentData = ClassData::create(
            class: $this->subNamespace.'\\'.$this->entityData->getClassName().'\\Form',
            suffix: '',
            useStatements: [
                $this->entityData->getFullClassName(),
                $this->formData->getFullClassName(),
                'Doctrine\ORM\EntityManagerInterface',
                'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
                'Symfony\Component\Form\FormInterface',
                'Symfony\Component\HttpFoundation\Response',
                'Symfony\Contracts\Translation\TranslatorInterface',
                'Symfony\UX\LiveComponent\Attribute\AsLiveComponent',
                'Symfony\UX\LiveComponent\Attribute\LiveAction',
                'Symfony\UX\LiveComponent\Attribute\LiveProp',
                'Symfony\UX\LiveComponent\ComponentWithFormTrait',
                'Symfony\UX\LiveComponent\DefaultActionTrait',
            ]
        )->setRootNamespace('App\\Twig\\Components');

        $this->showComponentData = ClassData::create(
            class: $this->subNamespace.'\\'.$this->entityData->getClassName().'\\Show',
            suffix: '',
            useStatements: [
                $this->entityData->getFullClassName(),
                'Doctrine\ORM\EntityManagerInterface',
                'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
                'Symfony\Component\HttpFoundation\Response',
                'Symfony\Contracts\Translation\TranslatorInterface',
                'Symfony\UX\LiveComponent\Attribute\AsLiveComponent',
                'Symfony\UX\LiveComponent\Attribute\LiveAction',
                'Symfony\UX\LiveComponent\Attribute\LiveProp',
                'Symfony\UX\LiveComponent\DefaultActionTrait',
            ]
        )->setRootNamespace('App\\Twig\\Components');

        $metadata = $this->entityManager->getClassMetadata('App\Entity\\' . $entityClass);

        $properties = array_merge($this->selectedFormProperties, $this->selectedTable);

        foreach ($properties as $property) {
            $propertyMetadata = $metadata->getFieldMapping($property);
            $propertyInfos[$property] = [
                'type' => $propertyMetadata['type'],
                'nullable' => $propertyMetadata['nullable'] ?? false,
                'length' => $propertyMetadata['length'] ?? null,
                'precision' => $propertyMetadata['precision'] ?? null,
                'scale' => $propertyMetadata['scale'] ?? null,
                'unique' => $propertyMetadata['unique'] ?? false,
                'options' => $propertyMetadata['options'] ?? [],
            ];
        }

        $this->templateArgs = [
            'extend_template' => $input->getArgument('layout-template'),
            'block' => $input->getArgument('block'),
            'template_sub_namespace' => $this->templateSubNamespace,
            'default_route' => $input->getArgument('route-prefix').$this->snake_case_entity,
            'componentName' => $this->componentName,
            'controllerData' => $this->controllerData,
            'entityData' => $this->entityData,
            'formData' => $this->formData,
            'propertyInfos' => $propertyInfos,
            'stringPropertie' => function (string $propertie) use ($propertyInfos): string
            {
                $type = $propertyInfos[$propertie]['type'];
                switch ($type){
                    case 'json':
                        return '|join(", ")';
                    case 'datetime':
                        return '|date("Y-m-d H:i:s")';
                    case 'datetime_immutable':
                        return '|date("Y-m-d H:i:s")';
                    case 'date':
                        return '|date("Y-m-d")';
                    case 'time':
                        return '|date("H:i:s")';
                    case 'boolean':
                        return '|yesNo';
                    case 'float':
                        return '|number_format(2)';
                    case 'decimal':
                        return '|number_format('.$propertyInfos[$propertie]['scale'].')';
                    case 'integer':
                        return '|number_format(0)';
                }
                return '';
            },
            'indexComponentData' => $this->indexComponentData,
            'formComponentData' => $this->formComponentData,
            'showComponentData' => $this->showComponentData,
            'snake_case_entity' => $this->snake_case_entity,
            'camel_case_entity' => $this->camel_case_entity,
            'selectedTableProperties' => $this->selectedTable,
            'selectedFormProperties' => $this->selectedFormProperties,
        ];
    }
}
