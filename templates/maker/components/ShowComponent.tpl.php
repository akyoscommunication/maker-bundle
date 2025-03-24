<?= "<?php\n" ?>

namespace <?= $showComponentData->getNamespace(); ?>;

<?= $showComponentData->getUseStatements(); ?>

#[AsLiveComponent]
final class <?= $showComponentData->getClassName(); ?> extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public <?= $entityData->getClassName(); ?> $<?= $snake_case_entity ?>;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[LiveAction]
    public function delete(): Response
    {
        $this->entityManager->remove($this-><?= $snake_case_entity ?>);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('<?= $snake_case_entity ?>.deleted', [], '<?= $snake_case_entity ?>'));

        return $this->redirectToRoute('<?= $default_route ?>.index');
    }
}