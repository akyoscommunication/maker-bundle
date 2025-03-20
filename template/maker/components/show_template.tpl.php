<div{{ attributes }}>
    <?php foreach ($selectedTableProperties as $property): ?>
        <div>
            <p><strong><?= ucfirst($property) ?>:</strong> {{ <?= $snake_case_entity ?>.<?= $property ?><?= $stringPropertie($property) ?> }}</p>
        </div>
    <?php endforeach; ?>
</div>
<div>
    <a href="{{ path('<?= $default_route ?>.edit', {'<?= $snake_case_entity ?>': <?= $snake_case_entity ?>.id}) }}">Edit</a>
    <a href="#" {{ live_action('delete') }}>Delete</a>
</div>
