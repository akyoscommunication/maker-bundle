<?= "<?php\n" ?>

namespace <?= $formComponentData->getNamespace(); ?>;

<?= $formComponentData->getUseStatements(); ?>

#[AsLiveComponent]
final class <?= $formComponentData->getClassName(); ?> extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true, fieldName: 'data')]
    public ?<?= $entityData->getClassName(); ?> $<?= $snake_case_entity ?> = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function mount(?<?= $entityData->getClassName(); ?> $<?= $snake_case_entity ?> = null): void
    {
        $this-><?= $snake_case_entity ?> = $<?= $snake_case_entity ?> ?? new <?= $entityData->getClassName(); ?>();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(<?= $formData->getClassName(); ?>::class, $this-><?= $snake_case_entity ?>);
    }

    #[LiveAction]
    public function save(): Response
    {
        $this->submitForm();

        $this->entityManager->persist($this-><?= $snake_case_entity ?>);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('<?= $snake_case_entity ?>.saved', [], '<?= $snake_case_entity ?>'));

        return $this->redirectToRoute('<?= $default_route ?>.index', [
            '<?= $snake_case_entity ?>' => $this-><?= $snake_case_entity ?>->getId(),
        ]);
    }
}
