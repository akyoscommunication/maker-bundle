<?= "<?php\n" ?>

namespace <?= $controllerData->getNamespace(); ?>;

<?= $controllerData->getUseStatements(); ?>

#[Route('/<?= $snake_case_entity; ?>', name: '<?= $snake_case_entity; ?>.')]
final class <?= $camel_case_entity; ?>Controller extends AbstractController
{
    #[Route('/', name: 'index')]
    #[Template('<?= $template_sub_namespace.'/'.$snake_case_entity; ?>/index.html.twig')]
    public function index(): array
    {
        return [];
    }

    #[Route('/new', name: 'new')]
    #[Template('<?= $template_sub_namespace.'/'.$snake_case_entity; ?>/new.html.twig')]
    public function new(): array
    {
        return [];
    }


    #[Route('/{<?= $snake_case_entity; ?>}/edit', name: 'edit')]
    #[Template('<?= $template_sub_namespace.'/'.$snake_case_entity; ?>/edit.html.twig')]
    public function edit(<?= $camel_case_entity; ?> $<?= $snake_case_entity; ?>): array
    {
        return ['<?= $snake_case_entity; ?>' => $<?= $snake_case_entity; ?>];
    }

    #[Route('/{<?= $snake_case_entity; ?>}/show', name: 'show')]
    #[Template('<?= $template_sub_namespace.'/'.$snake_case_entity; ?>/show.html.twig')]
    public function show(<?= $camel_case_entity; ?> $<?= $snake_case_entity; ?>): array
    {
        return ['<?= $snake_case_entity; ?>' => $<?= $snake_case_entity; ?>];
    }
}
