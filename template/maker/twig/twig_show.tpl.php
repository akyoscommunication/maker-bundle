{% extends '<?= $extend_template; ?>' %}

{% block title %}{% endblock %}

{% block <?= $block ?> %}
    <a href="{{ path('<?= $default_route; ?>.index') }}">Back</a>
    <twig:<?= $componentName; ?>:Show :<?= $snake_case_entity; ?>="<?= $snake_case_entity; ?>" />
{% endblock %}
