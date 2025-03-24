<?= "<?php\n" ?>

namespace <?= $indexComponentData->getNamespace(); ?>;

<?= $indexComponentData->getUseStatements(); ?>

#[AsLiveComponent]
final class <?= $indexComponentData->getClassName(); ?> extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFilterTrait;
    use ComponentWithTableTrait;

    public function __construct(
        private readonly TranslatorInterface $translator
    )
    {
        $this->setRepository(<?= $entityData->getClassName(); ?>::class);
        $this->trTemplate = 'components/<?= str_replace(':', '/', $componentName); ?>/elements/_tr.html.twig';
    }

    protected function setFilters(): iterable
    {
        yield (new Text('search', 'search', 'search'))
            ->setSearchType('like')
            ->setParams([
                <?php foreach ($selectedTableProperties as $property): ?>
                <?php if ($propertyInfos[$property]['type'] !== 'string') continue; ?>
'entity.<?= $property ?>',
                <?php endforeach; ?>
            ]);
    }

    protected function getTHeader(): iterable
    {
        yield (new TR([
<?php foreach ($selectedTableProperties as $property): ?>
            new TH('<?= $property ?>', 'entity.<?= $property ?>'),
<?php endforeach; ?>
        ]));
    }

    #[LiveAction]
    public function delete(#[LiveArg] <?= $entityData->getClassName(); ?> $<?= $snake_case_entity ?>): void
    {
        $this->entityManager->remove($<?= $snake_case_entity ?>);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('<?= $snake_case_entity ?>.deleted', [], '<?= $snake_case_entity ?>'));
    }
}
