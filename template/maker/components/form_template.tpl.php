<div{{ attributes }}>
{{ form_start(form) }}
<?php foreach ($selectedFormProperties as $property): ?>
    {{ form_row(form.<?= $property ?>) }}
<?php endforeach; ?>
{{ form_end(form) }}
<button {{ live_action('save') }} >Save</button>
</div>

