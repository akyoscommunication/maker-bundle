<tr>
<?php foreach ($selectedTableProperties as $property): ?>
    <td>{{ el.<?= $property ?><?= $stringPropertie($property) ?> }}</td>
<?php endforeach; ?>
    <td>
        <a href="{{ path('<?= $default_route ?>.show', {'<?= $snake_case_entity ?>': el.id}) }}" class="btn btn-primary">Show</a>
        <a href="{{ path('<?= $default_route ?>.edit', {'<?= $snake_case_entity ?>': el.id}) }}" class="btn btn-warning">Edit</a>
        <a href="#" {{ live_action('delete', {'<?= $snake_case_entity ?>': el.id }) }} class="btn btn-danger">Delete</a>
    </td>
</tr>
